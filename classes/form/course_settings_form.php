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

namespace local_h5pthemer\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Course settings form.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_settings_form extends \moodleform {
    /**
     * Form definition.
     */
    public function definition() {
        $mform = $this->_form;

        // Hidden fields for the custom element to read/write from.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if (isset($this->_customdata['courseid'])) {
            $mform->setDefault('id', $this->_customdata['courseid']);
        }

        $mform->addElement(
            'textarea',
            'local_h5pthemer_course_config',
            get_string('themecolors', 'local_h5pthemer'),
            ['rows' => 5, 'id' => 'id_local_h5pthemer_course_config']
        );
        $mform->setType('local_h5pthemer_course_config', PARAM_RAW);

        $globalpresets = get_config('local_h5pthemer', 'presets_json');
        $mform->addElement(
            'textarea',
            'local_h5pthemer_presets_json_readonly',
            'Presets',
            ['rows' => 5, 'id' => 'id_local_h5pthemer_presets_json_readonly']
        );
        $mform->setType('local_h5pthemer_presets_json_readonly', PARAM_RAW);
        $mform->setDefault('local_h5pthemer_presets_json_readonly', $globalpresets);

        $this->add_action_buttons(true, get_string('savechanges', 'admin'));
    }
}
