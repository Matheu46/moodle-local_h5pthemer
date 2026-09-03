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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

/**
 * Reusable admin setting for H5P Themer JSON configurations (theme and presets).
 *
 * @package     local_h5pthemer
 * @copyright   2026 Matheus Mathias
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_config_json extends \admin_setting_configtextarea {
    /** @var string JSON configuration type for theme settings. */
    public const TYPE_THEME = 'theme';

    /** @var string JSON configuration type for presets settings. */
    public const TYPE_PRESETS = 'presets';

    /** @var string The JSON configuration type (TYPE_THEME or TYPE_PRESETS). */
    protected string $jsontype;

    /** @var bool Whether to load the frontend AMD settings module on output. */
    protected bool $loadjs;

    /**
     * Constructor.
     *
     * @param string $name Setting name.
     * @param string $visiblename Setting visible title.
     * @param string $description Setting description.
     * @param string $defaultsetting Default value.
     * @param string $jsontype Type of JSON data (TYPE_THEME or TYPE_PRESETS).
     * @param bool $loadjs Whether to load the JS themer settings module when rendered.
     */
    public function __construct(
        string $name,
        string $visiblename,
        string $description,
        string $defaultsetting,
        string $jsontype = self::TYPE_THEME,
        bool $loadjs = false
    ) {
        $this->jsontype = $jsontype;
        $this->loadjs = $loadjs;
        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_RAW);
    }

    /**
     * Validate data before saving.
     *
     * @param string $data
     * @return mixed true if valid, error message otherwise.
     */
    public function validate($data) {
        if ($this->jsontype === self::TYPE_THEME) {
            if (!util::validate_theme_json($data)) {
                return get_string('invalid_theme_config', 'local_h5pthemer');
            }
        } else if ($this->jsontype === self::TYPE_PRESETS) {
            if (!util::validate_presets_json($data)) {
                return get_string('invalid_presets_config', 'local_h5pthemer');
            }
        }

        return parent::validate($data);
    }

    /**
     * Save a setting after sanitizing.
     *
     * @param string $data
     * @return string empty string if ok, error message otherwise.
     */
    public function write_setting($data) {
        if ($this->jsontype === self::TYPE_THEME) {
            $data = util::clean_theme_config($data);
        } else if ($this->jsontype === self::TYPE_PRESETS) {
            $data = util::clean_presets_config($data);
        }

        return parent::write_setting($data);
    }

    /**
     * Output setting HTML and conditionally load required AMD module.
     *
     * @param string $data
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        if ($this->loadjs) {
            global $PAGE;
            $PAGE->requires->js_call_amd('local_h5pthemer/settings', 'init');
        }

        return parent::output_html($data, $query);
    }
}
