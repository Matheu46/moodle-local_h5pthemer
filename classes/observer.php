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

namespace local_h5pthemer;

/**
 * Event observer class for local_h5pthemer.
 *
 * @package    local_h5pthemer
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Observer for course deletion.
     * Cleans up orphaned records in the local_h5pthemer_course table.
     *
     * @param \core\event\course_deleted $event
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;
        $DB->delete_records('local_h5pthemer_course', ['courseid' => $event->objectid]);
    }

    /**
     * Observer for category deletion.
     * Cleans up orphaned records in the local_h5pthemer_category table.
     *
     * @param \core\event\course_category_deleted $event
     */
    public static function course_category_deleted(\core\event\course_category_deleted $event) {
        global $DB;
        $DB->delete_records('local_h5pthemer_category', ['categoryid' => $event->objectid]);
    }
}
