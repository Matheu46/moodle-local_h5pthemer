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
 * Upgrade script for local_h5pthemer.
 *
 * @package    local_h5pthemer
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_h5pthemer_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026090300) {
        // Define table local_h5pthemer_course to be created.
        $table = new xmldb_table('local_h5pthemer_course');

        // Adding fields to table local_h5pthemer_course.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('config', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_h5pthemer_course.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('courseid_fk', XMLDB_KEY_FOREIGN_UNIQUE, ['courseid'], 'course', ['id']);

        // Conditionally launch create table for local_h5pthemer_course.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Migrate existing course configurations from config_plugins to the new table.
        $sql = "SELECT id, name, value
                  FROM {config_plugins}
                 WHERE plugin = 'local_h5pthemer'
                   AND name LIKE 'course_%_config'";

        $records = $DB->get_records_sql($sql);

        foreach ($records as $record) {
            // Extract the course ID from the name 'course_{id}_config'.
            if (preg_match('/^course_(\d+)_config$/', $record->name, $matches)) {
                $courseid = (int)$matches[1];

                // Ensure we don't insert duplicates if somehow they exist or were created.
                if (!$DB->record_exists('local_h5pthemer_course', ['courseid' => $courseid])) {
                    $newrecord = new stdClass();
                    $newrecord->courseid = $courseid;
                    $newrecord->config = $record->value;
                    $newrecord->timecreated = time();
                    $newrecord->timemodified = time();

                    $DB->insert_record('local_h5pthemer_course', $newrecord);
                }
            }
            // Delete the old record from config_plugins to free up memory cache.
            $DB->delete_records('config_plugins', ['id' => $record->id]);
        }

        // H5pthemer savepoint reached.
        upgrade_plugin_savepoint(true, 2026090300, 'local', 'h5pthemer');
    }

    if ($oldversion < 2026090301) {
        // Define table local_h5pthemer_category to be created.
        $table = new xmldb_table('local_h5pthemer_category');

        // Adding fields to table local_h5pthemer_category.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('categoryid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('config', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table local_h5pthemer_category.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('categoryid_fk', XMLDB_KEY_FOREIGN_UNIQUE, ['categoryid'], 'course_categories', ['id']);

        // Conditionally launch create table for local_h5pthemer_category.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // H5pthemer savepoint reached.
        upgrade_plugin_savepoint(true, 2026090301, 'local', 'h5pthemer');
    }

    return true;
}
