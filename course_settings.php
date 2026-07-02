<?php
require_once('../../config.php');
require_once('classes/form/course_settings_form.php');

$id = required_param('id', PARAM_INT); // Course ID

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('moodle/course:update', $context);

$PAGE->set_url('/local/h5pthemer/course_settings.php', array('id' => $id));
$PAGE->set_context($context);
$PAGE->set_title(get_string('coursesettings', 'local_h5pthemer'));
$PAGE->set_heading($course->fullname);

$PAGE->requires->js_call_amd('local_h5pthemer/settings', 'init');

$mform = new \local_h5pthemer\form\course_settings_form(null, ['courseid' => $id]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', array('id' => $id)));
} else if ($fromform = $mform->get_data()) {
    $config_value = $fromform->local_h5pthemer_course_config ?? '';
    set_config("course_{$id}_config", $config_value, 'local_h5pthemer');
    \core\notification::success(get_string('changessaved'));
    redirect(new moodle_url('/local/h5pthemer/course_settings.php', array('id' => $id)));
}

$current_config = get_config('local_h5pthemer', "course_{$id}_config");
$mform->set_data(['local_h5pthemer_course_config' => $current_config]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursesettings', 'local_h5pthemer'));
$mform->display();
echo $OUTPUT->footer();
