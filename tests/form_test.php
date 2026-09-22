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
use PHPUnit\Framework\Attributes\CoversClass;

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Unit tests for H5P Themer forms validation.
 *
 * @package     local_h5pthemer
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\local_h5pthemer\form\course_settings_form::class)]
#[CoversClass(\local_h5pthemer\form\category_settings_form::class)]
final class form_test extends advanced_testcase {
    /**
     * Set up before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Helper to mock a moodleform validation submission.
     *
     * @param string $formclass The full form class name.
     * @param array $data The data to validate.
     * @param array $customdata Form custom data array.
     * @return array The validation errors.
     */
    private function validate_form(string $formclass, array $data, array $customdata = []): array {
        // Moodle forms require a valid URL to instantiate without throwing coding_exceptions.
        $url = new \moodle_url('/');
        $form = new $formclass($url->out(false), $customdata, 'post', '', null, true, $data);
        return $form->validation($data, []);
    }

    /**
     * Test validation on course settings form with valid config.
     */
    public function test_course_settings_form_valid(): void {
        $data = [
            'id' => 1,
            'local_h5pthemer_course_config' => json_encode(['theme' => 'daylight', 'density' => 'large']),
        ];

        $errors = $this->validate_form(\local_h5pthemer\form\course_settings_form::class, $data, ['courseid' => 1]);
        $this->assertArrayNotHasKey('local_h5pthemer_course_config', $errors);
    }

    /**
     * Test validation on course settings form with invalid config.
     */
    public function test_course_settings_form_invalid(): void {
        $data = [
            'id' => 1,
            'local_h5pthemer_course_config' => '{invalid_json',
        ];

        $errors = $this->validate_form(\local_h5pthemer\form\course_settings_form::class, $data, ['courseid' => 1]);
        $this->assertArrayHasKey('local_h5pthemer_course_config', $errors);
        $this->assertEquals(get_string('invalid_theme_config', 'local_h5pthemer'), $errors['local_h5pthemer_course_config']);
    }

    /**
     * Test validation on category settings form with valid config.
     */
    public function test_category_settings_form_valid(): void {
        $data = [
            'id' => 1,
            'local_h5pthemer_category_config' => json_encode(['theme' => 'blue', 'density' => 'medium']),
        ];

        $errors = $this->validate_form(\local_h5pthemer\form\category_settings_form::class, $data, ['categoryid' => 1]);
        $this->assertArrayNotHasKey('local_h5pthemer_category_config', $errors);
    }

    /**
     * Test validation on category settings form with invalid config.
     */
    public function test_category_settings_form_invalid(): void {
        // Test malicious CSS injection attempt in form submission.
        $attackjson = json_encode([
            'theme' => 'custom',
            'colors' => [
                '--h5p-theme-main-cta-base' => 'red; } body { display: none; }',
            ],
        ]);

        $data = [
            'id' => 1,
            'local_h5pthemer_category_config' => $attackjson,
        ];

        $errors = $this->validate_form(\local_h5pthemer\form\category_settings_form::class, $data, ['categoryid' => 1]);
        $this->assertArrayHasKey('local_h5pthemer_category_config', $errors);
        $this->assertEquals(get_string('invalid_theme_config', 'local_h5pthemer'), $errors['local_h5pthemer_category_config']);
    }
}
