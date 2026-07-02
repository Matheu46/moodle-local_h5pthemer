<?php
namespace local_h5pthemer\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class course_settings_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;

        // Hidden fields for the custom element to read/write from
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if (isset($this->_customdata['courseid'])) {
            $mform->setDefault('id', $this->_customdata['courseid']);
        }

        $mform->addElement('textarea', 'local_h5pthemer_course_config', get_string('themecolors', 'local_h5pthemer'), ['rows' => 5, 'id' => 'id_local_h5pthemer_course_config']);
        $mform->setType('local_h5pthemer_course_config', PARAM_RAW);
        
        $global_presets = get_config('local_h5pthemer', 'presets_json');
        $mform->addElement('textarea', 'local_h5pthemer_presets_json_readonly', 'Presets', ['rows' => 5, 'id' => 'id_local_h5pthemer_presets_json_readonly']);
        $mform->setType('local_h5pthemer_presets_json_readonly', PARAM_RAW);
        $mform->setDefault('local_h5pthemer_presets_json_readonly', $global_presets);

        $this->add_action_buttons(true, get_string('savechanges', 'admin'));
    }
}
