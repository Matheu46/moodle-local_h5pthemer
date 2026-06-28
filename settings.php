<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Settings for the H5P Themer plugin.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_h5pthemer', get_string('pluginname', 'local_h5pthemer'));
    $ADMIN->add('localplugins', $settings);

    // Default configuration for the Theme Picker (JSON string)
    $default_config = json_encode([
        'theme' => 'daylight',
        'density' => 'large',
        'colors' => []
    ]);

    // Provide a textarea that will be enhanced by the JS Theme Picker.
    // The web component will interact with this hidden field.
    $settings->add(new admin_setting_configtextarea(
        'local_h5pthemer/css_variables',
        get_string('themecolors', 'local_h5pthemer'),
        get_string('themecolors_desc', 'local_h5pthemer'),
        $default_config,
        PARAM_RAW
    ));

    // Load AMD module to initialize the web component and handle the textarea
    $PAGE->requires->js_call_amd('local_h5pthemer/settings', 'init');
}
