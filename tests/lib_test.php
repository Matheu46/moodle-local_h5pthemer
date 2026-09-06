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
 * Unit tests for lib.php functions.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_h5pthemer;

use advanced_testcase;
use context_course;
use navigation_node;
use moodle_url;
use PHPUnit\Framework\Attributes\CoversFunction;

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Unit tests for lib.php functions.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversFunction('local_h5pthemer_extend_navigation')]
#[CoversFunction('local_h5pthemer_extend_navigation_course')]
#[CoversFunction('local_h5pthemer_extend_navigation_category_settings')]
final class lib_test extends advanced_testcase {
    /**
     * Setup before each test.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * Test extend_navigation function.
     */
    public function test_local_h5pthemer_extend_navigation(): void {
        global $PAGE, $COURSE;

        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;

        // Ensure page is set up enough for requires.
        $PAGE->set_url(new moodle_url('/'));

        $navigation = new navigation_node('Test node');

        // Call the function.
        local_h5pthemer_extend_navigation($navigation);

        // Since it's hard to directly access the protected properties of page_requirements_manager,
        // we can generate the footer HTML which includes the AMD calls.
        $footerhtml = $PAGE->requires->get_end_code();

        // Assert that our AMD module is included.
        $this->assertStringContainsString('local_h5pthemer/themer', $footerhtml);
        $this->assertStringContainsString('init', $footerhtml);
        $this->assertStringContainsString((string)$course->id, $footerhtml);
    }

    /**
     * Test extend_navigation does not load themer on admin pages.
     */
    public function test_local_h5pthemer_extend_navigation_excluded_on_admin(): void {
        global $PAGE, $COURSE;

        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;

        $PAGE->set_url(new moodle_url('/admin/settings.php'));
        $PAGE->set_pagelayout('admin');

        $navigation = new navigation_node('Test node');

        local_h5pthemer_extend_navigation($navigation);

        $footerhtml = $PAGE->requires->get_end_code();
        $this->assertStringNotContainsString('local_h5pthemer/themer', $footerhtml);
    }

    /**
     * Test course navigation for user with capability.
     */
    public function test_local_h5pthemer_extend_navigation_course_with_capability(): void {
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        // Give capability.
        assign_capability('moodle/course:update', CAP_ALLOW, $roleid, $context->id);
        role_assign($roleid, $user->id, $context->id);

        $this->setUser($user);

        $navigation = new navigation_node('Test course node');

        local_h5pthemer_extend_navigation_course($navigation, $course, $context);

        // Check if the node was added.
        $node = $navigation->get('local_h5pthemer_course_settings');
        $this->assertInstanceOf(navigation_node::class, $node);
        $expectedurl = new moodle_url('/local/h5pthemer/course_settings.php', ['id' => $course->id]);
        $this->assertEquals($expectedurl, $node->action);
    }

    /**
     * Test course navigation for user without capability.
     */
    public function test_local_h5pthemer_extend_navigation_course_without_capability(): void {
        $course = $this->getDataGenerator()->create_course();
        $context = context_course::instance($course->id);

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $navigation = new navigation_node('Test course node');

        local_h5pthemer_extend_navigation_course($navigation, $course, $context);

        // Check that the node was NOT added.
        $node = $navigation->get('local_h5pthemer_course_settings');
        $this->assertFalse($node);
    }

    /**
     * Test category navigation for user with capability.
     */
    public function test_local_h5pthemer_extend_navigation_category_settings_with_capability(): void {
        $category = $this->getDataGenerator()->create_category();
        $context = \context_coursecat::instance($category->id);

        $user = $this->getDataGenerator()->create_user();
        $roleid = $this->getDataGenerator()->create_role();
        // Give capability.
        assign_capability('moodle/category:manage', CAP_ALLOW, $roleid, $context->id);
        role_assign($roleid, $user->id, $context->id);

        $this->setUser($user);

        $navigation = new navigation_node('Test category node');

        local_h5pthemer_extend_navigation_category_settings($navigation, $context);

        // Check if the node was added.
        $node = $navigation->get('local_h5pthemer_category_settings');
        $this->assertInstanceOf(navigation_node::class, $node);
        $expectedurl = new moodle_url('/local/h5pthemer/category_settings.php', ['id' => $category->id]);
        $this->assertEquals($expectedurl, $node->action);
    }

    /**
     * Test category navigation for user without capability.
     */
    public function test_local_h5pthemer_extend_navigation_category_settings_without_capability(): void {
        $category = $this->getDataGenerator()->create_category();
        $context = \context_coursecat::instance($category->id);

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $navigation = new navigation_node('Test category node');

        local_h5pthemer_extend_navigation_category_settings($navigation, $context);

        // Check that the node was NOT added.
        $node = $navigation->get('local_h5pthemer_category_settings');
        $this->assertFalse($node);
    }
}
