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

namespace local_h5pthemer\privacy;

use core_privacy\tests\provider_testcase;

/**
 * Privacy Subsystem testcase for local_h5pthemer.
 *
 * @package     local_h5pthemer
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \local_h5pthemer\privacy\provider
 */
final class provider_test extends provider_testcase {
    /**
     * Test the reason for null provider.
     */
    public function test_get_reason(): void {
        $this->assertEquals('privacy:metadata', \local_h5pthemer\privacy\provider::get_reason());
    }
}
