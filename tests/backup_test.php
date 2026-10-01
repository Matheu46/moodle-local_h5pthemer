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
 * Unit tests for backup and restore of local_h5pthemer.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_h5pthemer;

use advanced_testcase;
use backup_local_h5pthemer_plugin;
use restore_local_h5pthemer_plugin;
use stdClass;
use PHPUnit\Framework\Attributes\CoversClass;

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Tests for local_h5pthemer backup and restore.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\backup_local_h5pthemer_plugin::class)]
#[CoversClass(\restore_local_h5pthemer_plugin::class)]
final class backup_test extends advanced_testcase {
    /**
     * Helper to backup and restore a course.
     *
     * @param stdClass $course The course to backup.
     * @param int $userid The user ID performing the backup and restore.
     * @return int The restored course ID.
     */
    protected function backup_and_restore_course(stdClass $course, int $userid): int {
        global $CFG;

        require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
        require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

        $backupid = 'test_h5pthemer_' . uniqid();

        // 1. Perform backup.
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $userid
        );
        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];
        $fp = get_file_packer('application/vnd.moodle.backup');
        $filepath = $CFG->dataroot . '/temp/backup/' . $backupid;
        $file->extract_to_pathname($fp, $filepath);
        $bc->destroy();

        // 2. Perform restore into a new course.
        $category = $this->getDataGenerator()->create_category();
        $catcontext = \context_coursecat::instance($category->id);
        $coursecontext = \context_course::instance($course->id);

        if (!is_siteadmin($userid)) {
            $roleid = $this->getDataGenerator()->create_role();
            assign_capability('moodle/restore:restorecourse', CAP_ALLOW, $roleid, $catcontext->id);
            assign_capability('moodle/course:create', CAP_ALLOW, $roleid, $catcontext->id);
            assign_capability('moodle/backup:backupcourse', CAP_ALLOW, $roleid, $coursecontext->id);
            role_assign($roleid, $userid, $catcontext->id);
            role_assign($roleid, $userid, $coursecontext->id);
        }

        $newcourseid = \restore_dbops::create_new_course('Restored course', 'restored_' . uniqid(), $category->id);
        $rc = new \restore_controller(
            $backupid,
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $userid,
            \backup::TARGET_NEW_COURSE
        );
        $rc->execute_precheck();
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }

    /**
     * Test backup and restore as admin preserves custom_css and theme settings.
     */
    public function test_backup_and_restore_as_admin(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $admin = get_admin();

        $configdata = [
            'theme' => 'sunset',
            'density' => 'small',
            'colors' => [
                '--h5p-theme-primary' => '#123456',
            ],
            'custom_css' => '.h5p-content { background: #fff; }',
        ];

        $DB->insert_record('local_h5pthemer_course', (object)[
            'courseid' => $course->id,
            'config' => json_encode($configdata),
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $newcourseid = $this->backup_and_restore_course($course, (int)$admin->id);

        // Verify settings in restored course.
        $record = $DB->get_record('local_h5pthemer_course', ['courseid' => $newcourseid]);
        $this->assertNotEmpty($record);

        $restoredconfig = json_decode($record->config, true);
        $this->assertEquals('sunset', $restoredconfig['theme']);
        $this->assertEquals('small', $restoredconfig['density']);
        $this->assertEquals('#123456', $restoredconfig['colors']['--h5p-theme-primary']);
        $this->assertEquals('.h5p-content { background: #fff; }', $restoredconfig['custom_css']);

        // Verify config_plugins was not polluted.
        $oldplugins = $DB->get_records_select(
            'config_plugins',
            "plugin = 'local_h5pthemer' AND name LIKE 'course_%_config'"
        );
        $this->assertEmpty($oldplugins);
    }

    /**
     * Test backup and restore as non-admin teacher restores theme but strips custom_css.
     */
    public function test_backup_and_restore_as_teacher_strips_custom_css(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        $configdata = [
            'theme' => 'ocean',
            'density' => 'large',
            'colors' => [
                '--h5p-theme-primary' => '#aabbcc',
            ],
            'custom_css' => 'body { color: red; }',
        ];

        $DB->insert_record('local_h5pthemer_course', (object)[
            'courseid' => $course->id,
            'config' => json_encode($configdata),
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        // Switch to teacher user for restore.
        $this->setUser($teacher);

        $newcourseid = $this->backup_and_restore_course($course, (int)$teacher->id);

        // Verify settings in restored course.
        $record = $DB->get_record('local_h5pthemer_course', ['courseid' => $newcourseid]);
        $this->assertNotEmpty($record);

        $restoredconfig = json_decode($record->config, true);
        $this->assertEquals('ocean', $restoredconfig['theme']);
        $this->assertEquals('large', $restoredconfig['density']);
        $this->assertEquals('#aabbcc', $restoredconfig['colors']['--h5p-theme-primary']);
        // Custom CSS must NOT be restored for non-admin.
        $this->assertArrayNotHasKey('custom_css', $restoredconfig);
    }

    /**
     * Test course without settings restores cleanly without creating records.
     */
    public function test_backup_course_without_settings(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $admin = get_admin();

        $newcourseid = $this->backup_and_restore_course($course, (int)$admin->id);

        $record = $DB->get_record('local_h5pthemer_course', ['courseid' => $newcourseid]);
        $this->assertFalse($record);
    }
}
