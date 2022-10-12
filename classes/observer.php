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
 * Event observers supported by this plugin.
 *
 * @package     local_certifaction
 * @copyright   2022 Adrian Perez <me@adrianperez.me>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_certifaction\utils;
use mod_assign\event\assessable_submitted;

defined('MOODLE_INTERNAL') || die();

class local_certifaction_observer {

    public static function assignsubmission_submitted(assessable_submitted $event) {
        if (get_config('local_certifaction', 'certifactionenabled')) {
            $certifaction = new utils();
            $certifaction->assessable_submitted_event_handler($event);
        }
    }
}