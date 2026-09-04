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
 * Category settings page for the H5P Themer plugin.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$id = required_param('id', PARAM_INT);

$category = $DB->get_record('course_categories', ['id' => $id], '*', MUST_EXIST);
$context = context_coursecat::instance($category->id);

require_login();
require_capability('moodle/category:manage', $context);

$PAGE->set_url('/local/h5pthemer/category_settings.php', ['id' => $id]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_h5pthemer')); // Will fallback to component if not defined, but good practice.
$PAGE->set_heading($category->name);
$PAGE->set_pagelayout('admin');

$PAGE->requires->js_call_amd('local_h5pthemer/settings', 'init');

$mform = new \local_h5pthemer\form\category_settings_form(null, ['categoryid' => $id]);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/course/management.php', ['categoryid' => $id]));
} else if ($fromform = $mform->get_data()) {
    $rawconfig = $fromform->local_h5pthemer_category_config ?? '';
    $configvalue = !empty($rawconfig) ? \local_h5pthemer\util::clean_theme_config($rawconfig) : '';

    $existing = $DB->get_record('local_h5pthemer_category', ['categoryid' => $id]);
    if ($existing) {
        $existing->config = $configvalue;
        $existing->timemodified = time();
        $DB->update_record('local_h5pthemer_category', $existing);
    } else {
        $newrecord = new stdClass();
        $newrecord->categoryid = $id;
        $newrecord->config = $configvalue;
        $newrecord->timecreated = time();
        $newrecord->timemodified = time();
        $DB->insert_record('local_h5pthemer_category', $newrecord);
    }

    \core\notification::success(get_string('changessaved'));
    redirect(new moodle_url('/local/h5pthemer/category_settings.php', ['id' => $id]));
}

$currentconfig = '';
$record = $DB->get_record('local_h5pthemer_category', ['categoryid' => $id], 'config');
if ($record) {
    $currentconfig = $record->config;
}

$mform->set_data(['local_h5pthemer_category_config' => $currentconfig]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_h5pthemer'));
$mform->display();
echo $OUTPUT->footer();
