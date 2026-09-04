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

namespace local_h5pthemer\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * External function for getting H5P Themer config.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class get_config extends external_api {
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED),
        ]);
    }

    /**
     * Execute the external function.
     *
     * @param int $courseid
     * @return string JSON encoded config
     */
    public static function execute($courseid) {
        global $SITE, $DB;
        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);
        $courseid = $params['courseid'];

        if ($courseid && $courseid != $SITE->id) {
            $context = \context_course::instance($courseid, IGNORE_MISSING);
            if (!$context) {
                // If course does not exist fallback to system context.
                $context = \context_system::instance();
                $courseid = 0;
            }
        } else {
            $context = \context_system::instance();
        }

        self::validate_context($context);

        // If a specific course is requested, ensure the user has permission to view it.
        if ($context->contextlevel == CONTEXT_COURSE) {
            require_capability('moodle/course:view', $context);
        }

        $jsonconfig = get_config('local_h5pthemer', 'css_variables');
        $config = [];
        if ($jsonconfig) {
            $config = json_decode($jsonconfig, true);
        }

        if ($courseid && $courseid != $SITE->id) {
            $record = $DB->get_record('local_h5pthemer_course', ['courseid' => $courseid], 'config');
            if ($record && !empty($record->config)) {
                $courseconfig = json_decode($record->config, true);
                if (is_array($courseconfig) && !empty($courseconfig['theme']) && $courseconfig['theme'] !== 'default') {
                    // Course specific config wins.
                    return json_encode($courseconfig);
                }
            }

            // Fallback to category inheritance.
            $sql = "SELECT cc.path
                      FROM {course} c
                      JOIN {course_categories} cc ON cc.id = c.category
                     WHERE c.id = :courseid";
            $path = $DB->get_field_sql($sql, ['courseid' => $courseid]);

            if ($path) {
                $categoryids = explode('/', trim($path, '/'));
                // Reverse to start from the most specific (closest to course) to the root.
                $categoryids = array_reverse($categoryids);

                if (!empty($categoryids)) {
                    [$insql, $inparams] = $DB->get_in_or_equal($categoryids);
                    $catconfigs = $DB->get_records_select(
                        'local_h5pthemer_category',
                        "categoryid $insql",
                        $inparams,
                        '',
                        'categoryid, config'
                    );

                    foreach ($categoryids as $catid) {
                        if (isset($catconfigs[$catid]) && !empty($catconfigs[$catid]->config)) {
                            $catconfig = json_decode($catconfigs[$catid]->config, true);
                            if (is_array($catconfig) && !empty($catconfig['theme']) && $catconfig['theme'] !== 'default') {
                                return json_encode($catconfig);
                            }
                        }
                    }
                }
            }
        }

        // Global fallback.
        return json_encode($config);
    }

    /**
     * Returns description of method result value
     *
     * @return external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_RAW, 'JSON encoded config');
    }
}
