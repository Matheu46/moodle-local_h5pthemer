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
 * Built-in presets for local_h5pthemer.
 *
 * @module     local_h5pthemer/presets
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    return {
        /**
         * Returns an object containing the built-in theme presets.
         *
         * @param {Object} translations Translated strings object.
         * @returns {Object} Presets formatted for the h5p-theme-picker component.
         */
        getBuiltInPresets: function(translations) {
            return {
                'dark': {
                    label: translations ? (translations.selector_theme_value_dark || 'Dark') : 'Dark',
                    backgroundColor: '#f1f5f9',
                    color: '#1e293b',
                    values: {
                        '--h5p-theme-background': '#16191d',
                        '--h5p-theme-ui-base': '#21252b',
                        '--h5p-theme-alternative-base': '#2a2f37',
                        '--h5p-theme-alternative-light': '#323842',
                        '--h5p-theme-alternative-dark': '#1a1d22',
                        '--h5p-theme-alternative-darker': '#121417',
                        '--h5p-theme-text-primary': '#f0f3f6',
                        '--h5p-theme-text-secondary': '#c9d1d9',
                        '--h5p-theme-text-third': '#8b949e',
                        '--h5p-theme-stroke-1': '#3b434f',
                        '--h5p-theme-stroke-2': '#2e343e',
                        '--h5p-theme-stroke-3': '#22272e',
                        '--h5p-theme-main-cta-base': '#3b82f6',
                        '--h5p-theme-main-cta-light': '#60a5fa',
                        '--h5p-theme-main-cta-dark': '#2563eb',
                        '--h5p-theme-contrast-cta': '#ffffff',
                        '--h5p-theme-contrast-cta-white': '#3b82f6',
                        '--h5p-theme-contrast-cta-light': 'rgba(59, 130, 246, 0.15)',
                        '--h5p-theme-contrast-cta-dark': '#93c5fd',
                        '--h5p-theme-secondary-cta-base': '#374151',
                        '--h5p-theme-secondary-cta-light': '#4b5563',
                        '--h5p-theme-secondary-cta-dark': '#1f2937',
                        '--h5p-theme-secondary-contrast-cta': '#f3f4f6',
                        '--h5p-theme-secondary-contrast-cta-hover': '#ffffff',
                        '--h5p-theme-feedback-correct-main': '#34d399',
                        '--h5p-theme-feedback-correct-secondary': '#064e3b',
                        '--h5p-theme-feedback-correct-third': '#047857',
                        '--h5p-theme-feedback-incorrect-main': '#f87171',
                        '--h5p-theme-feedback-incorrect-secondary': '#450a0a',
                        '--h5p-theme-feedback-incorrect-third': '#991b1b',
                        '--h5p-theme-feedback-neutral-main': '#fbbf24',
                        '--h5p-theme-feedback-neutral-secondary': '#451a03',
                        '--h5p-theme-feedback-neutral-third': '#78350f',
                        '--h5p-theme-focus': '#60a5fa'
                    }
                }
            };
        }
    };
});
