<?php
// This file is part of Moodle - https://moodle.org/

/**
 * Core logic for the H5P Themer plugin.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend navigation to inject our JS on every page.
 * This handles injecting the theme into both core_h5p and mod_hvp iframes.
 *
 * @param navigation_node $navigation
 */
function local_h5pthemer_extend_navigation(navigation_node $navigation) {
    global $PAGE;
    
    $json_config = get_config('local_h5pthemer', 'css_variables');
    $config = [];
    if ($json_config) {
        $config = json_decode($json_config, true);
    }
    
    // We load the themer AMD module which will look for H5P iframes and inject variables.
    $PAGE->requires->js_call_amd('local_h5pthemer/themer', 'init', [$config]);
}
