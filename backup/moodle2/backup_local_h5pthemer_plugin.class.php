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
 * Backup plugin class for local_h5pthemer.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides the steps to backup H5P Themer plugin data.
 *
 * @package    local_h5pthemer
 */
class backup_local_h5pthemer_plugin extends backup_local_plugin {
    /**
     * Define course-level plugin structure.
     * All data is wrapped at the course level as this is a local plugin.
     *
     * @return backup_plugin_element
     */
    protected function define_course_plugin_structure(): backup_nested_element {
        $plugin = $this->get_plugin_element(null);

        // Use the recommended plugin wrapper to encapsulate all plugin data.
        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);

        // Course-level settings node.
        $coursenode = new backup_nested_element('h5pthemer_course', ['id'], ['configvalue']);
        $pluginwrapper->add_child($coursenode);

        $courseid = $this->step->get_task()->get_courseid();
        $configvalue = get_config('local_h5pthemer', "course_{$courseid}_config");
        
        $coursenode->set_source_array([
            ['id' => $courseid, 'configvalue' => $configvalue !== false ? $configvalue : '']
        ]);

        return $plugin;
    }
}
