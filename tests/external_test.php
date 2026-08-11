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

namespace local_h5pthemer;

use advanced_testcase;
use local_h5pthemer\external\get_config;
use core_external\external_api;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;

/**
 * External functions testcase for H5P Themer.
 *
 * @package     local_h5pthemer
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\local_h5pthemer\external\get_config::class)]
final class external_test extends advanced_testcase {
    /**
     * Set up before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Test get_config when there is no specific course config.
     */
    public function test_get_config_global_fallback(): void {
        global $SITE;

        // Set global config.
        $globalconfig = json_encode(['theme' => 'dark', 'primary_color' => '#000000']);
        set_config('css_variables', $globalconfig, 'local_h5pthemer');

        // Test with site ID.
        $result = get_config::execute($SITE->id);
        $result = external_api::clean_returnvalue(get_config::execute_returns(), $result);

        $this->assertEquals($globalconfig, $result);

        // Test with a regular course that has no specific config.
        $course = $this->getDataGenerator()->create_course();
        $resultcourse = get_config::execute($course->id);
        $resultcourse = external_api::clean_returnvalue(get_config::execute_returns(), $resultcourse);

        $this->assertEquals($globalconfig, $resultcourse);
    }

    /**
     * Test get_config when course config has 'theme' = 'default'.
     */
    public function test_get_config_course_default_theme(): void {
        $course = $this->getDataGenerator()->create_course();

        // Set global config.
        $globalconfig = json_encode(['theme' => 'dark', 'primary_color' => '#000000']);
        set_config('css_variables', $globalconfig, 'local_h5pthemer');

        // Set course config with default theme.
        $courseconfig = json_encode(['theme' => 'default', 'primary_color' => '#ffffff']);
        set_config("course_{$course->id}_config", $courseconfig, 'local_h5pthemer');

        // Should return global config because theme is 'default'.
        $result = get_config::execute($course->id);
        $result = external_api::clean_returnvalue(get_config::execute_returns(), $result);

        $this->assertEquals($globalconfig, $result);
    }

    /**
     * Test get_config when course config has a specific theme.
     */
    public function test_get_config_course_specific_theme(): void {
        $course = $this->getDataGenerator()->create_course();

        // Set global config.
        $globalconfig = json_encode(['theme' => 'dark', 'primary_color' => '#000000']);
        set_config('css_variables', $globalconfig, 'local_h5pthemer');

        // Set course config with a specific theme.
        $courseconfig = json_encode(['theme' => 'light', 'primary_color' => '#ffffff']);
        set_config("course_{$course->id}_config", $courseconfig, 'local_h5pthemer');

        // Should return the course specific config.
        $result = get_config::execute($course->id);
        $result = external_api::clean_returnvalue(get_config::execute_returns(), $result);

        $this->assertEquals($courseconfig, $result);
    }

    /**
     * Test that querying a non-existent course safely falls back to global configs.
     * (Because validation is context_system, it doesn't throw a missing course exception).
     */
    public function test_get_config_invalid_course(): void {
        // Set global config.
        $globalconfig = json_encode(['theme' => 'blue', 'primary_color' => '#0000ff']);
        set_config('css_variables', $globalconfig, 'local_h5pthemer');

        // Query with an absurdly high ID that won't exist.
        $result = get_config::execute(99999999);
        $result = external_api::clean_returnvalue(get_config::execute_returns(), $result);

        // Should gracefully fallback to global config since it can't find a course config.
        $this->assertEquals($globalconfig, $result);
    }
}
