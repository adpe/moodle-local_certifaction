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
 * Plugin administration settings are defined here.
 *
 * @package     local_certifaction
 * @category    admin
 * @copyright   2022 Adrian Perez <me@adrianperez.me>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_certifaction', new lang_string('certifactionsettings', 'local_certifaction'),
            'moodle/site:config');

    if ($ADMIN->fulltree) {
        $name = 'local_certifaction/certifactionenabled';
        $displayname = new lang_string('certifactionenabled', 'local_certifaction');
        $setting = new admin_setting_configcheckbox($name, $displayname, '', 0);
        $settings->add($setting);

        $name = 'assign/certifactionsubmission';
        $displayname = new lang_string('certifactionsubmission', 'local_certifaction');
        $description = new lang_string('certifactionsubmission_help', 'local_certifaction');
        $setting = new admin_setting_configcheckbox($name, $displayname, $description, 0);
        $setting->set_advanced_flag_options(admin_setting_flag::ENABLED, false);
        $setting->set_locked_flag_options(admin_setting_flag::ENABLED, false);
        $settings->add($setting);

        $name = 'local_certifaction/baseuri';
        $displayname = new lang_string('certifactionbaseuri', 'local_certifaction');
        $description = new lang_string('certifactionbaseuri_desc', 'local_certifaction');
        $setting = new admin_setting_configtext($name, $displayname, $description, 'https://api.certifaction.io', PARAM_RAW_TRIMMED);
        $settings->add($setting);

        $name = 'local_certifaction/apikey';
        $displayname = new lang_string('certifactionapikey', 'local_certifaction');
        $description = new lang_string('certifactionapikey_desc', 'local_certifaction');
        $setting = new admin_setting_configpasswordunmask($name, $displayname, $description, '');
        $settings->add($setting);
    }

    $ADMIN->add('localplugins', $settings);
}
