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
 * Functions used by plugin.
 *
 * @package     local_certifaction
 * @copyright   2022 Adrian Perez <me@adrianperez.me>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_certifaction\utils;

/**
 * Inject the Certifaction settings into all assign module settings forms.
 *
 * @param moodleform $formwrapper The Moodle quick form wrapper object.
 * @param MoodleQuickForm $mform The actual form object (required to modify the form).
 * @throws coding_exception
 * @throws dml_exception
 */
function local_certifaction_coursemodule_standard_elements(moodleform $formwrapper, MoodleQuickForm $mform) {
    global $DB;

    if (!$cm = $formwrapper->get_coursemodule()) {
        return;
    }

    if (!get_config('local_certifaction', 'certifactionenabled')) {
        return;
    }

    $name = get_string('certifactionsubmission', 'local_certifaction');

    $mform->addElement('header', 'certifcationsection', get_string('pluginname', 'local_certifaction'));
    $mform->addElement('selectyesno', 'certifactionsubmission', $name);
    $mform->addHelpButton('certifactionsubmission', 'certifactionsubmission', 'local_certifaction');

    if ($config = $DB->get_record(utils::CONFIG_TABLE, ['cmid' => $cm->id, 'name' => utils::SUBMISSION_SETTING])) {
        $mform->setDefault('certifactionsubmission', $config->value);
    }
}

/**
 * Hook to save Certifaction specific settings on an assign settings page.
 *
 * @param stdClass $data
 * @param stdClass $course
 * @return stdClass
 * @throws dml_exception
 */
function local_certifaction_coursemodule_edit_post_actions(stdClass $data, stdClass $course): stdClass {
    global $DB, $USER;

    if (!get_config('local_certifaction', 'certifactionenabled')) {
        return $data;
    }

    if (!isset($data->certifactionsubmission)) {
        return $data;
    }

    $record = $DB->get_record(utils::CONFIG_TABLE, ['cmid' => $data->coursemodule, 'name' => utils::SUBMISSION_SETTING]);
    if (!$record) {
        $record = new stdClass();
        $record->cmid = $data->coursemodule;
        $record->name = utils::SUBMISSION_SETTING;
        $record->value = $data->certifactionsubmission;
        $record->usermodified = $USER->id;
        $record->timecreated = $record->timemodified = time();

        $DB->insert_record(utils::CONFIG_TABLE, $record);

        return $data;
    }

    if ($record->value !== $data->certifactionsubmission) {
        $record->value = $data->certifactionsubmission;
        $record->usermodified = $USER->id;
        $record->timemodified = time();

        $DB->update_record(utils::CONFIG_TABLE, $record);
    }

    return $data;
}
