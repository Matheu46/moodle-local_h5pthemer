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
 * AJAX endpoint for local_h5pthemer configuration.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');

$courseid = optional_param('courseid', SITEID, PARAM_INT);

// Basic security checks.
// If courseid is valid, require_login will check if user can access it.
if ($courseid != SITEID) {
    require_login($courseid, false);
} else {
    require_login(0, false);
}

$jsonconfig = get_config('local_h5pthemer', 'css_variables');
$config = [];
if ($jsonconfig) {
    $config = json_decode($jsonconfig, true);
}

// Override with course level config if it exists.
if ($courseid && $courseid != SITEID) {
    $courseconfigjson = get_config('local_h5pthemer', "course_{$courseid}_config");
    if ($courseconfigjson) {
        $courseconfig = json_decode($courseconfigjson, true);
        if (!empty($courseconfig['theme']) && $courseconfig['theme'] !== 'default') {
            $config = $courseconfig;
        }
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($config);
die();
