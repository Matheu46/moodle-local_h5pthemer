/**
 * Settings AMD module for local_h5pthemer.
 *
 * @module     local_h5pthemer/settings
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/config'], function($, cfg) {
    /**
     * Restores saved configuration into the theme picker.
     *
     * @param {Object} textarea The Moodle config textarea element
     * @param {HTMLElement} picker The theme picker element
     */
    function restoreConfig(textarea, picker) {
        var val = textarea.val();
        if (!val || val.trim() === '') {
            return;
        }
        var savedConfig = JSON.parse(val);

        if (savedConfig.theme) {
            picker.setAttribute('theme-name', savedConfig.theme);
        }
        if (savedConfig.density) {
            picker.setAttribute('density', savedConfig.density);
        }
        // Restore custom colors if using custom theme
        if (savedConfig.theme !== 'custom' || !savedConfig.colors) {
            return;
        }
        var colors = savedConfig.colors;
        if (colors['--h5p-theme-main-cta-base']) {
            picker.setAttribute('custom-color-buttons', colors['--h5p-theme-main-cta-base']);
        }
        if (colors['--h5p-theme-secondary-cta-base']) {
            picker.setAttribute('custom-color-navigation', colors['--h5p-theme-secondary-cta-base']);
        }
        if (colors['--h5p-theme-alternative-base']) {
            picker.setAttribute('custom-color-alternative', colors['--h5p-theme-alternative-base']);
        }
        if (colors['--h5p-theme-background']) {
            picker.setAttribute('custom-color-background', colors['--h5p-theme-background']);
        }
    }

    return {
        init: function() {
            // Dynamically load the h5p-theme-picker JS module
            var script = document.createElement('script');
            script.type = 'module';
            script.src = cfg.wwwroot + '/local/h5pthemer/js/h5p-theme-picker.js';
            document.head.appendChild(script);

            $(document).ready(function() {
                var textarea = $('#id_s_local_h5pthemer_css_variables');
                if (textarea.length) {
                    // Hide the textarea to replace it with our picker
                    textarea.hide();

                    var picker = document.createElement('h5p-theme-picker');

                    // Try to restore previous values from the saved textarea value
                    try {
                        restoreConfig(textarea, picker);
                    } catch (e) {
                        // Ignore parse errors on init.
                    }

                    // Insert the picker into the DOM
                    textarea.after(picker);

                    // Listen to changes and update the textarea with the new JSON
                    picker.addEventListener('theme-change', function(e) {
                        var details = e.detail;
                        if (details) {
                            var newConfig = {
                                theme: details.theme,
                                density: details.data ? details.data.density : 'large',
                                colors: (details.data && details.data.colors) ? details.data.colors : {}
                            };
                            textarea.val(JSON.stringify(newConfig, null, 2));
                        }
                    });
                }
            });
        }
    };
});
