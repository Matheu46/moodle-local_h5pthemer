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
 * Event observers for local_h5pthemer.
 *
 * @package    local_h5pthemer
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\core\event\course_deleted',
        'callback'    => 'local_h5pthemer\observer::course_deleted',
    ],
    [
        'eventname'   => '\core\event\course_category_deleted',
        'callback'    => 'local_h5pthemer\observer::course_category_deleted',
    ],
];
