<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Event observers business logic handlers are defined here.
 *
 * @package     local_certifaction
 * @copyright   2022 Adrian Perez <me@adrianperez.me>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_certifaction;

use Certifaction\Api\File;
use Certifaction\Client;
use context_module;
use kornrunner\Keccak;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../vendor/autoload.php');

class utils {

    const CONFIG_TABLE = 'local_certifaction_config';
    const SUBMISSION_SETTING = 'certifactionsubmission';
    const SUBMISSION_FILEHASHES = 'certifactionsubmissionfilehashes';

    public function assessable_submitted_event_handler($event) {
        $eventdata = $event->get_data();
        $cm = get_coursemodule_from_id('assign', $eventdata['contextinstanceid'], $eventdata['courseid'], null, MUST_EXIST);

        if (!self::is_certifaction_enabled($cm)) {
            return;
        }

        $client = self::init_blockchain_client();
        if (is_null($client)) {
            return;
        }

        $modulecontext = context_module::instance($cm->id);

        $fs = get_file_storage();
        $files = $fs->get_area_files($modulecontext->id, 'assignsubmission_file', ASSIGNSUBMISSION_FILE_FILEAREA,
                $eventdata['objectid'], 'id', false);

        foreach ($files as $file) {
            if ($file->get_mimetype() != 'application/pdf') {
                continue;
            }

            $record = self::get_helper_table_entry($eventdata);
            $filehashes = isset($record->value) ? json_decode($record->value) : [];

            if (self::is_file_already_stored_on_helper_table($file, $filehashes)) {
                return;
            }

            if (self::is_file_already_stored_on_chain($file, $filehashes, $client)) {
                return;
            }

            $lang = strtok(current_language(), '_');
            $chainfile = $client->register($file->get_content(), $lang, $file->get_filename(), 'register');

            $filehashdata = self::prepare_filehash_data($modulecontext, $eventdata, $fs, $file, $chainfile);

            if (!empty($filehashes)) {
                self::update_record($eventdata, $filehashdata, $filehashes, $record);
                return;
            }

            self::insert_record($eventdata, $filehashdata);
        }
    }

    private static function is_certifaction_enabled(stdClass $coursemodule): bool {
        global $DB;

        $certifactionenabled =
                $DB->get_record(self::CONFIG_TABLE, ['cmid' => $coursemodule->id, 'name' => utils::SUBMISSION_SETTING]);

        if (isset($certifactionenabled) && $certifactionenabled->value) {
            return true;
        }

        return false;
    }

    private static function init_blockchain_client(): ?File {
        $apikey = get_config('local_certifaction', 'apikey');
        $baseuri = get_config('local_certifaction', 'baseuri');

        if (!isset($apikey) || !isset($baseuri)) {
            return null;
        }

        $client = new Client($baseuri, $apikey);

        return new File($client);
    }

    private static function get_helper_table_entry(array $eventdata) {
        global $DB;

        return $DB->get_record(utils::CONFIG_TABLE, ['cmid' => $eventdata['objectid'], 'name' => utils::SUBMISSION_FILEHASHES]);
    }

    private static function is_file_already_stored_on_helper_table(\stored_file $file, array $filehashes): bool {
        if (in_array($file->get_contenthash(), $filehashes)) {
            return true;
        }

        return false;
    }

    private static function is_file_already_stored_on_chain(\stored_file $file, array $filehashes, File $client): bool {
        foreach ($filehashes as $filehash) {
            if (substr($filehash, 0, strlen('0x')) !== '0x' or $filehash === $file->get_contenthash()) {
                continue;
            }

            $chainfile = $client->verify($filehash);
            if (isset($chainfile->verified) && $chainfile->verified) {
                return true;
            }
        }

        return false;
    }

    private function prepare_filehash_data(\context_module $modulecontext, array $eventdata, \file_storage $fs, \stored_file $file,
            $chainfile): array {
        $fileinfo = [
                'contextid' => $modulecontext->id,
                'component' => 'certifactionsubmission_file',
                'filearea' => ASSIGNSUBMISSION_FILE_FILEAREA,
                'itemid' => $eventdata['objectid'],
                'userid' => $eventdata['userid'],
                'filepath' => '/',
                'filename' => str_replace('.pdf', '_salted.pdf', $file->get_filename())
        ];

        $fs->create_file_from_string($fileinfo, $chainfile);

        $data[] = $file->get_contenthash();
        $data[] = '0x' . Keccak::hash($chainfile, 256);

        return $data;
    }

    private static function update_record(array $eventdata, array $filehashdata, array $filehashes, stdClass $record) {
        global $DB;

        $data = array_merge($filehashdata, $filehashes);

        $record->value = json_encode($data);
        $record->usermodified = $eventdata['userid'];
        $record->timecreated = $record->timemodified = time();

        $DB->update_record(utils::CONFIG_TABLE, $record);
    }

    private static function insert_record(array $eventdata, array $filehashdata) {
        global $DB;

        $record = new stdClass();
        $record->cmid = $eventdata['objectid'];
        $record->name = utils::SUBMISSION_FILEHASHES;
        $record->value = json_encode($filehashdata);
        $record->usermodified = $eventdata['userid'];
        $record->timecreated = $record->timemodified = time();

        $DB->insert_record(utils::CONFIG_TABLE, $record);
    }
}
