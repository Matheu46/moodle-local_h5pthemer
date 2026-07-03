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
 * Course settings page for the H5P Themer plugin.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('classes/form/course_settings_form.php');

$id = required_param('id', PARAM_INT);

$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$context = context_course::instance($course->id);

require_login($course);
require_capability('moodle/course:update', $context);

$PAGE->set_url('/local/h5pthemer/course_settings.php', ['id' => $id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('coursesettings', 'local_h5pthemer'));
$PAGE->set_heading($course->fullname);

$PAGE->requires->js_call_amd('local_h5pthemer/settings', 'init');

$mform = new \local_h5pthemer\form\course_settings_form(null, ['courseid' => $id]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/view.php', ['id' => $id]));
} else if ($fromform = $mform->get_data()) {
    $configvalue = $fromform->local_h5pthemer_course_config ?? '';
    set_config("course_{$id}_config", $configvalue, 'local_h5pthemer');
    \core\notification::success(get_string('changessaved'));
    redirect(new moodle_url('/local/h5pthemer/course_settings.php', ['id' => $id]));
}

$currentconfig = get_config('local_h5pthemer', "course_{$id}_config");
$mform->set_data(['local_h5pthemer_course_config' => $currentconfig]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('coursesettings', 'local_h5pthemer'));
$mform->display();
echo $OUTPUT->footer();
