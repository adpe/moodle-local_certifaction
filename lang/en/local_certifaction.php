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
 * Plugin strings are defined here.
 *
 * @package     local_certifaction
 * @category    string
 * @copyright   2022 Adrian Perez <me@adrianperez.me>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Certifaction';

// Settings.
$string['certifactionsettings'] = 'Certifaction settings';
$string['certifactionenabled'] = 'Enable integration';
$string['certifactionbaseuri'] = 'Base URI';
$string['certifactionbaseuri_desc'] = 'Please set the Görli Testnet base URI "https://api.testnet.certifaction.io" for testing purposes.';
$string['certifactionapikey'] = 'API key';
$string['certifactionapikey_desc'] = 'Please set the API key from <a href="https://app.certifaction.io/settings/api-keys" target="_blank">Certification.io</a>.';

// Misc.
$string['certifactionsubmission'] = 'Make your documents tamper-proof on the blockchain';
$string['certifactionsubmission_help'] =
        'If enabled, the submitted files will be stored tamper-proof and protected against fraud by signing their digital fingerprints on the blockchain.';
