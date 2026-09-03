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
use local_h5pthemer\util;
use PHPUnit\Framework\Attributes\CoversClass;

// phpcs:disable moodle.PHPUnit.TestCaseCovers.Missing

/**
 * Unit tests for util class (JSON and CSS sanitization).
 *
 * @package     local_h5pthemer
 * @category    test
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\local_h5pthemer\util::class)]
final class util_test extends advanced_testcase {
    /**
     * Test clean_css_color with valid color formats.
     */
    public function test_clean_css_color_valid_formats(): void {
        // Hex formats.
        $this->assertEquals('#fff', util::clean_css_color('#FFF'));
        $this->assertEquals('#123456', util::clean_css_color('#123456'));
        $this->assertEquals('#12345678', util::clean_css_color('#12345678'));
        $this->assertEquals('#abcd', util::clean_css_color('#ABCD'));

        // RGB / RGBA formats.
        $this->assertEquals('rgb(255, 0, 0)', util::clean_css_color('rgb(255, 0, 0)'));
        $this->assertEquals('rgba(0, 128, 255, 0.5)', util::clean_css_color('rgba(0, 128, 255, 0.5)'));

        // HSL / HSLA formats.
        $this->assertEquals('hsl(120, 100%, 50%)', util::clean_css_color('hsl(120, 100%, 50%)'));
        $this->assertEquals('hsla(120, 100%, 50%, 0.8)', util::clean_css_color('hsla(120, 100%, 50%, 0.8)'));

        // Named colors.
        $this->assertEquals('transparent', util::clean_css_color('transparent'));
        $this->assertEquals('rebeccapurple', util::clean_css_color('RebeccaPurple'));
        $this->assertEquals('white', util::clean_css_color(' White '));
    }

    /**
     * Test clean_css_color rejects invalid or malicious values.
     */
    public function test_clean_css_color_rejects_malicious_inputs(): void {
        // CSS injection attempts.
        $this->assertNull(util::clean_css_color('red; } body { background: black; }'));
        $this->assertNull(util::clean_css_color('#fff; color: red'));
        $this->assertNull(util::clean_css_color('url(https://attacker.com/leak)'));
        $this->assertNull(util::clean_css_color('expression(alert(1))'));
        $this->assertNull(util::clean_css_color('@import "evil.css"'));

        // XSS and HTML tags.
        $this->assertNull(util::clean_css_color('</style><script>alert(1)</script>'));
        $this->assertNull(util::clean_css_color('javascript:alert(1)'));

        // Invalid hex lengths / characters.
        $this->assertNull(util::clean_css_color('#12345')); // 5 chars
        $this->assertNull(util::clean_css_color('#gggggg'));
        $this->assertNull(util::clean_css_color('notacolor123'));
        $this->assertNull(util::clean_css_color(''));
    }

    /**
     * Test is_valid_css_variable_name.
     */
    public function test_is_valid_css_variable_name(): void {
        $this->assertTrue(util::is_valid_css_variable_name('--h5p-theme-main-cta-base'));
        $this->assertTrue(util::is_valid_css_variable_name('--h5p-theme-secondary-cta-base'));
        $this->assertTrue(util::is_valid_css_variable_name('--h5p-theme-alternative-base'));
        $this->assertTrue(util::is_valid_css_variable_name('--h5p-theme-background'));
        $this->assertTrue(util::is_valid_css_variable_name('--h5p-theme-custom-1'));

        // Invalid names.
        $this->assertFalse(util::is_valid_css_variable_name('color'));
        $this->assertFalse(util::is_valid_css_variable_name('--other-var'));
        $this->assertFalse(util::is_valid_css_variable_name('--h5p-theme-var;'));
        $this->assertFalse(util::is_valid_css_variable_name('--h5p-theme-var {'));
        $this->assertFalse(util::is_valid_css_variable_name('<script>'));
    }

    /**
     * Test validate_theme_json with valid JSON.
     */
    public function test_validate_theme_json_valid(): void {
        $validjson = json_encode([
            'theme' => 'daylight',
            'density' => 'large',
            'colors' => [
                '--h5p-theme-main-cta-base' => '#1a73e8',
                '--h5p-theme-background' => '#ffffff',
            ],
        ]);
        $this->assertTrue(util::validate_theme_json($validjson));
        $this->assertTrue(util::validate_theme_json(''));
    }

    /**
     * Test validate_theme_json with malicious or invalid inputs.
     */
    public function test_validate_theme_json_invalid(): void {
        // Broken JSON syntax.
        $this->assertFalse(util::validate_theme_json('{invalid json}'));

        // Dangerous color payload.
        $attackjson = json_encode([
            'theme' => 'custom',
            'colors' => [
                '--h5p-theme-main-cta-base' => 'red; } body { display: none; }',
            ],
        ]);
        $this->assertFalse(util::validate_theme_json($attackjson));

        // Invalid variable name.
        $badvarjson = json_encode([
            'theme' => 'custom',
            'colors' => [
                'evil-variable' => '#ffffff',
            ],
        ]);
        $this->assertFalse(util::validate_theme_json($badvarjson));

        // Invalid density.
        $baddensityjson = json_encode([
            'theme' => 'daylight',
            'density' => 'huge',
        ]);
        $this->assertFalse(util::validate_theme_json($baddensityjson));
    }

    /**
     * Test clean_theme_config sanitizes bad values.
     */
    public function test_clean_theme_config_sanitization(): void {
        $raw = json_encode([
            'theme' => 'custom_theme',
            'density' => 'small',
            'colors' => [
                '--h5p-theme-main-cta-base' => '#FF0000',
                '--h5p-theme-secondary-cta-base' => 'blue',
                '--h5p-theme-malicious' => 'red; } body { display:none; }',
                'bad-key' => '#000000',
            ],
        ]);

        $cleaned = util::clean_theme_config($raw);
        $decoded = json_decode($cleaned, true);

        $this->assertEquals('custom_theme', $decoded['theme']);
        $this->assertEquals('small', $decoded['density']);
        $this->assertEquals('#ff0000', $decoded['colors']['--h5p-theme-main-cta-base']);
        $this->assertEquals('blue', $decoded['colors']['--h5p-theme-secondary-cta-base']);
        // Malicious entry and bad key must be stripped.
        $this->assertArrayNotHasKey('--h5p-theme-malicious', $decoded['colors']);
        $this->assertArrayNotHasKey('bad-key', $decoded['colors']);
    }

    /**
     * Test validate_presets_json and clean_presets_config.
     */
    public function test_presets_validation_and_sanitization(): void {
        $validpresets = json_encode([
            [
                'id' => 'my-preset',
                'name' => 'My Preset',
                'colors' => [
                    '--h5p-theme-main-cta-base' => '#123456',
                ],
            ],
        ]);
        $this->assertTrue(util::validate_presets_json($validpresets));

        // Malicious preset.
        $badpreset = json_encode([
            [
                'id' => 'bad<script>',
                'name' => 'Bad',
            ],
        ]);
        $this->assertFalse(util::validate_presets_json($badpreset));

        // Clean preset.
        $cleaned = util::clean_presets_config($validpresets);
        $decoded = json_decode($cleaned, true);
        $this->assertCount(1, $decoded);
        $this->assertEquals('my-preset', $decoded[0]['id']);
        $this->assertEquals('#123456', $decoded[0]['colors']['--h5p-theme-main-cta-base']);
    }

    /**
     * Test modern CSS values such as color-mix(), var(), and dimensions.
     */
    public function test_modern_css_values(): void {
        $colormix = 'color-mix(in srgb, var(--h5p-theme-main-cta-base), transparent 90%)';
        $this->assertEquals($colormix, util::clean_css_color($colormix));

        $varval = 'var(--h5p-theme-spacing-xl-primary-small)';
        $this->assertEquals($varval, util::clean_css_color($varval));

        $numberval = '0.6';
        $this->assertEquals('0.6', util::clean_css_color($numberval));

        $remval = '1.25rem';
        $this->assertEquals('1.25rem', util::clean_css_color($remval));

        // Full theme with generated variables.
        $fulltheme = json_encode([
            'theme' => 'lavender',
            'density' => 'large',
            'colors' => [
                '--h5p-theme-main-cta-base' => '#834DD5',
                '--h5p-theme-secondary-cta-base' => '#000000',
                '--h5p-theme-contrast-cta-light' => $colormix,
                '--h5p-theme-spacing-xl' => $varval,
                '--h5p-theme-scaling' => '0.6',
            ],
        ]);

        $this->assertTrue(util::validate_theme_json($fulltheme));
        $cleaned = util::clean_theme_config($fulltheme);
        $decoded = json_decode($cleaned, true);
        $this->assertEquals('lavender', $decoded['theme']);
        $this->assertEquals($colormix, $decoded['colors']['--h5p-theme-contrast-cta-light']);
    }

    /**
     * Test should_load_themer returns true for content pages and false for admin/auth/excluded pages.
     */
    public function test_should_load_themer(): void {
        // Course page: should load.
        $page = new \moodle_page();
        $page->set_url(new \moodle_url('/course/view.php', ['id' => 2]));
        $page->set_pagetype('course-view');
        $page->set_pagelayout('course');
        $this->assertTrue(util::should_load_themer($page));

        // H5P activity: should load.
        $page = new \moodle_page();
        $page->set_url(new \moodle_url('/mod/h5pactivity/view.php', ['id' => 5]));
        $page->set_pagetype('mod-h5pactivity-view');
        $page->set_pagelayout('incourse');
        $this->assertTrue(util::should_load_themer($page));

        // Embedded H5P: should load.
        $page = new \moodle_page();
        $page->set_url(new \moodle_url('/h5p/embed.php', ['url' => 'test']));
        $page->set_pagetype('h5p-embed');
        $page->set_pagelayout('embedded');
        $this->assertTrue(util::should_load_themer($page));

        // Admin page layout: should NOT load.
        $page = new \moodle_page();
        $page->set_url(new \moodle_url('/admin/settings.php'));
        $page->set_pagetype('admin-settings');
        $page->set_pagelayout('admin');
        $this->assertFalse(util::should_load_themer($page));

        // Login page: should NOT load.
        $page = new \moodle_page();
        $page->set_url(new \moodle_url('/login/index.php'));
        $page->set_pagetype('login-index');
        $page->set_pagelayout('login');
        $this->assertFalse(util::should_load_themer($page));

        // Course settings of our plugin: should NOT load.
        $page = new \moodle_page();
        $page->set_url(new \moodle_url('/local/h5pthemer/course_settings.php', ['id' => 2]));
        $page->set_pagetype('local-h5pthemer-course_settings');
        $page->set_pagelayout('course');
        $this->assertFalse(util::should_load_themer($page));
    }
}
