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

    // Default configuration for the Theme Picker (JSON string).
    $defaultconfig = json_encode([
        'theme' => 'daylight',
        'density' => 'large',
        'colors' => [],
    ]);

    // Provide a textarea that will be enhanced by the JS Theme Picker.
    // The web component will interact with this hidden field.
    $settings->add(new \local_h5pthemer\admin_setting_config_json(
        'local_h5pthemer/css_variables',
        get_string('themecolors', 'local_h5pthemer'),
        get_string('themecolors_desc', 'local_h5pthemer'),
        $defaultconfig,
        \local_h5pthemer\admin_setting_config_json::TYPE_THEME,
        true
    ));

    $settings->add(new \local_h5pthemer\admin_setting_config_json(
        'local_h5pthemer/presets_json',
        get_string('presets_json', 'local_h5pthemer'),
        get_string('presets_json_desc', 'local_h5pthemer'),
        '',
        \local_h5pthemer\admin_setting_config_json::TYPE_PRESETS
    ));
}
