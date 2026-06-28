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
 * Core logic for the H5P Themer plugin.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Extend navigation to inject our JS on every page.
 * This handles injecting the theme into both core_h5p and mod_hvp iframes.
 *
 * @param navigation_node $navigation
 */
function local_h5pthemer_extend_navigation(navigation_node $navigation) {
    global $PAGE;

    $jsonconfig = get_config('local_h5pthemer', 'css_variables');
    $config = [];
    if ($jsonconfig) {
        $config = json_decode($jsonconfig, true);
    }

    // We load the themer AMD module which will look for H5P iframes and inject variables.
    $PAGE->requires->js_call_amd('local_h5pthemer/themer', 'init', [$config]);
}
