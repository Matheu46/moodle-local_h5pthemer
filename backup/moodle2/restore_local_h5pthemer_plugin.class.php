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
 * Restore plugin class for local_h5pthemer.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Provides the steps to restore H5P Themer plugin data.
 *
 * @package    local_h5pthemer
 */
class restore_local_h5pthemer_plugin extends restore_local_plugin {
    /** @var stdClass|null Temporary storage for course settings during XML reading. */
    protected $courseconfig = null;

    /**
     * Define the restore plugin structure.
     *
     * @return restore_path_element[]
     */
    protected function define_course_plugin_structure(): array {
        return [
            new restore_path_element('h5pthemer_course', $this->get_pathfor('/h5pthemer_course')),
        ];
    }

    /**
     * Temporarily store the course-level setting element from the XML.
     *
     * @param array $data Record data from the backup file.
     * @return void
     */
    public function process_h5pthemer_course($data) {
        $this->courseconfig = (object)$data;
    }

    /**
     * Executes after the course has been fully restored.
     *
     * @return void
     */
    public function after_restore_course() {
        $courseid = $this->task->get_courseid();

        // Restore course-level configuration.
        if ($this->courseconfig && isset($this->courseconfig->configvalue)) {
            set_config("course_{$courseid}_config", $this->courseconfig->configvalue, 'local_h5pthemer');
        }
    }
}
