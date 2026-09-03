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

/**
 * Utility class for local_h5pthemer.
 *
 * Handles validation and sanitization of JSON configurations, CSS variables, and color values.
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util {
    /** @var array Allowed density options. */
    public const ALLOWED_DENSITIES = ['large', 'medium', 'small'];

    /** @var array Standard CSS color names allowed. */
    public const ALLOWED_NAMED_COLORS = [
        'transparent', 'currentcolor', 'inherit', 'initial', 'unset',
        'black', 'silver', 'gray', 'white', 'maroon', 'red', 'purple', 'fuchsia',
        'green', 'lime', 'olive', 'yellow', 'navy', 'blue', 'teal', 'aqua',
        'orange', 'aliceblue', 'antiquewhite', 'aquamarine', 'azure', 'beige',
        'bisque', 'blanchedalmond', 'blueviolet', 'brown', 'burlywood', 'cadetblue',
        'chartreuse', 'chocolate', 'coral', 'cornflowerblue', 'cornsilk', 'crimson',
        'cyan', 'darkblue', 'darkcyan', 'darkgoldenrod', 'darkgray', 'darkgreen',
        'darkgrey', 'darkkhaki', 'darkmagenta', 'darkolivegreen', 'darkorange',
        'darkorchid', 'darkred', 'darksalmon', 'darkseagreen', 'darkslateblue',
        'darkslategray', 'darkslategrey', 'darkturquoise', 'darkviolet', 'deeppink',
        'deepskyblue', 'dimgray', 'dimgrey', 'dodgerblue', 'firebrick', 'floralwhite',
        'forestgreen', 'gainsboro', 'ghostwhite', 'gold', 'goldenrod', 'greenyellow',
        'grey', 'honeydew', 'hotpink', 'indianred', 'indigo', 'ivory', 'khaki',
        'lavender', 'lavenderblush', 'lawngreen', 'lemonchiffon', 'lightblue',
        'lightcoral', 'lightcyan', 'lightgoldenrodyellow', 'lightgray', 'lightgreen',
        'lightgrey', 'lightpink', 'lightsalmon', 'lightseagreen', 'lightskyblue',
        'lightslategray', 'lightslategrey', 'lightsteelblue', 'lightyellow', 'limegreen',
        'linen', 'magenta', 'mediumaquamarine', 'mediumblue', 'mediumorchid',
        'mediumpurple', 'mediumseagreen', 'mediumslateblue', 'mediumspringgreen',
        'mediumturquoise', 'mediumvioletred', 'midnightblue', 'mintcream', 'mistyrose',
        'moccasin', 'navajowhite', 'oldlace', 'olivedrab', 'orangered', 'orchid',
        'palegoldenrod', 'palegreen', 'paleturquoise', 'palevioletred', 'papayawhip',
        'peachpuff', 'peru', 'pink', 'plum', 'powderblue', 'rosybrown', 'royalblue',
        'saddlebrown', 'salmon', 'sandybrown', 'seagreen', 'seashell', 'sienna',
        'skyblue', 'slateblue', 'slategray', 'slategrey', 'snow', 'springgreen',
        'steelblue', 'tan', 'thistle', 'tomato', 'turquoise', 'violet', 'wheat',
        'whitesmoke', 'yellowgreen', 'rebeccapurple',
    ];

    /**
     * Validates and cleans a CSS value or color string.
     *
     * Supports Hex (#RGB, #RGBA, #RRGGBB, #RRGGBBAA), RGB(A), HSL(A), color-mix(),
     * var(--h5p-theme-*), numeric/dimensions (e.g. 0.6, 1rem), and standard named colors.
     * Rejects any string containing dangerous characters or unexpected CSS syntax.
     *
     * @param string $color Raw color or CSS value string.
     * @return string|null Cleaned value string or null if invalid.
     */
    public static function clean_css_color(string $color): ?string {
        $color = trim($color);

        if ($color === '') {
            return null;
        }

        // Block dangerous characters or attack keywords.
        if (preg_match('/[;{}<>"\x27\\\\]/', $color)) {
            return null;
        }
        if (preg_match('/(javascript|expression|url|@import|behavior|eval)/i', $color)) {
            return null;
        }

        // 1. Hex color check (#fff, #ffff, #ffffff, #ffffffff).
        if (preg_match('/^#([0-9a-fA-F]{3,4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $color)) {
            return strtolower($color);
        }

        // 2. Numeric / percentage / dimensions (e.g. 0.6, 1rem, 16px, 100%).
        if (preg_match('/^-?\d+(?:\.\d+)?(?:px|rem|em|%|deg|turn|rad)?$/i', $color)) {
            return $color;
        }

        // 3. RGB / RGBA check.
        $rgbapattern = '/^rgba?\(\s*(?:(?:\d{1,3}(?:\.\d+)?%?|\.\d+%?)\s*[, ]\s*){2}'
            . '(?:\d{1,3}(?:\.\d+)?%?|\.\d+%?)'
            . '(?:\s*(?:,\s*|\/\s*)(?:0|1|0?\.\d+|\d{1,3}%))?\s*\)$/i';
        if (preg_match($rgbapattern, $color)) {
            return $color;
        }

        // 4. HSL / HSLA check.
        $hslpattern = '/^hsla?\(\s*(?:\d{1,3}(?:\.\d+)?(?:deg|rad|turn)?|\.\d+(?:deg|rad|turn)?)\s*[, ]\s*'
            . '\d{1,3}(?:\.\d+)?%\s*[, ]\s*\d{1,3}(?:\.\d+)?%'
            . '(?:\s*(?:,\s*|\/\s*)(?:0|1|0?\.\d+|\d{1,3}%))?\s*\)$/i';
        if (preg_match($hslpattern, $color)) {
            return $color;
        }

        // 5. CSS variable references.
        if (preg_match('/^var\(\s*--h5p-theme-[a-z0-9-]+\s*\)$/i', $color)) {
            return $color;
        }

        // 6. CSS color-mix expressions.
        if (preg_match('/^color-mix\(\s*in\s+[a-z0-9-]+\s*,\s*[^,;{}]+\s*,\s*[^,;{}]+\s*\)$/i', $color)) {
            return $color;
        }

        // 7. Named colors check.
        $lowered = strtolower($color);
        if (in_array($lowered, self::ALLOWED_NAMED_COLORS, true)) {
            return $lowered;
        }

        return null;
    }

    /**
     * Checks whether a CSS variable name is valid and allowed for H5P Themer.
     *
     * @param string $name CSS variable name (e.g. '--h5p-theme-main-cta-base').
     * @return bool True if valid.
     */
    public static function is_valid_css_variable_name(string $name): bool {
        return (bool)preg_match('/^--h5p-theme-[a-z0-9-]+$/', $name) && strlen($name) <= 64;
    }

    /**
     * Validates a theme JSON configuration string.
     *
     * @param string $json Raw JSON string.
     * @return bool True if valid and safe, false otherwise.
     */
    public static function validate_theme_json(string $json): bool {
        $json = trim($json);
        if ($json === '') {
            return true;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if (isset($data['theme'])) {
            if (!is_string($data['theme']) || !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $data['theme'])) {
                return false;
            }
        }

        if (isset($data['density'])) {
            $isvaliddensity = in_array($data['density'], self::ALLOWED_DENSITIES, true) || $data['density'] === '';
            if (!is_string($data['density']) || !$isvaliddensity) {
                return false;
            }
        }

        if (isset($data['colors'])) {
            if (!is_array($data['colors'])) {
                return false;
            }
            foreach ($data['colors'] as $key => $value) {
                if (!is_string($key) || !self::is_valid_css_variable_name($key)) {
                    return false;
                }
                if (!is_string($value) || self::clean_css_color($value) === null) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Cleans and normalizes a theme JSON configuration string.
     *
     * @param string $json Raw JSON string.
     * @return string Sanitized JSON string.
     */
    public static function clean_theme_config(string $json): string {
        $json = trim($json);
        if ($json === '') {
            return '';
        }

        $data = json_decode($json, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            return json_encode([
                'theme' => 'daylight',
                'density' => 'large',
                'colors' => (object)[],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        $clean = [];

        // Clean theme name.
        if (
            !empty($data['theme']) && is_string($data['theme']) &&
            preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $data['theme'])
        ) {
            $clean['theme'] = $data['theme'];
        } else {
            $clean['theme'] = 'daylight';
        }

        // Clean density.
        if (
            !empty($data['density']) && is_string($data['density']) &&
            in_array($data['density'], self::ALLOWED_DENSITIES, true)
        ) {
            $clean['density'] = $data['density'];
        } else {
            $clean['density'] = 'large';
        }

        // Clean colors.
        $cleancolors = [];
        if (!empty($data['colors']) && is_array($data['colors'])) {
            foreach ($data['colors'] as $key => $value) {
                if (is_string($key) && self::is_valid_css_variable_name($key)) {
                    $cleanedcolor = is_string($value) ? self::clean_css_color($value) : null;
                    if ($cleanedcolor !== null) {
                        $cleancolors[$key] = $cleanedcolor;
                    }
                }
            }
        }
        $clean['colors'] = empty($cleancolors) ? (object)[] : $cleancolors;

        return json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Validates a presets JSON configuration string.
     *
     * @param string $json Raw JSON string representing array of presets.
     * @return bool True if valid, false otherwise.
     */
    public static function validate_presets_json(string $json): bool {
        $json = trim($json);
        if ($json === '') {
            return true;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        foreach ($data as $preset) {
            if (!is_array($preset)) {
                return false;
            }
            if (
                empty($preset['id']) || !is_string($preset['id']) ||
                !preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $preset['id'])
            ) {
                return false;
            }
            if (isset($preset['colors'])) {
                if (!is_array($preset['colors'])) {
                    return false;
                }
                foreach ($preset['colors'] as $key => $value) {
                    if (!is_string($key) || !self::is_valid_css_variable_name($key)) {
                        return false;
                    }
                    if (!is_string($value) || self::clean_css_color($value) === null) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Cleans and normalizes a presets JSON configuration string.
     *
     * @param string $json Raw JSON string.
     * @return string Sanitized JSON string.
     */
    public static function clean_presets_config(string $json): string {
        $json = trim($json);
        if ($json === '') {
            return '';
        }

        $data = json_decode($json, true);
        if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
            return '[]';
        }

        $cleanpresets = [];
        foreach ($data as $preset) {
            if (!is_array($preset) || empty($preset['id']) || !is_string($preset['id'])) {
                continue;
            }

            $id = $preset['id'];
            if (!preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $id)) {
                continue;
            }

            $name = $id;
            if (!empty($preset['name']) && is_string($preset['name'])) {
                $name = clean_param($preset['name'], PARAM_TEXT);
            }

            $cleancolors = [];
            if (!empty($preset['colors']) && is_array($preset['colors'])) {
                foreach ($preset['colors'] as $key => $value) {
                    if (is_string($key) && self::is_valid_css_variable_name($key)) {
                        $cleanedcolor = is_string($value) ? self::clean_css_color($value) : null;
                        if ($cleanedcolor !== null) {
                            $cleancolors[$key] = $cleanedcolor;
                        }
                    }
                }
            }

            $cleanpresets[] = [
                'id' => $id,
                'name' => $name,
                'colors' => empty($cleancolors) ? (object)[] : $cleancolors,
            ];
        }

        return json_encode($cleanpresets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
