/**
 * Themer AMD module for local_h5pthemer.
 * Intercepts H5P iframes and injects custom CSS variables and applies density.
 *
 * @module     local_h5pthemer/themer
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery'], function($) {
    const VALID_DENSITY_CLASSES = ['h5p-large', 'h5p-medium', 'h5p-small'];

    return {
        init: function(pluginConfig) {
            $(document).ready(function() {
                var config = pluginConfig || {};

                /**
                 * Injects custom CSS variables into the iframe document's head.
                 *
                 * @param {HTMLDocument} doc The iframe document
                 * @param {Object} colors The colors configuration object
                 */
                function injectCustomColors(doc, colors) {
                    if (!colors || typeof colors !== 'object') {
                        return;
                    }
                    var styleId = 'h5p-themer-custom-colors';
                    var styleEl = doc.getElementById(styleId);

                    if (styleEl) {
                        return;
                    }

                    var css = ':root {\n';
                    Object.entries(colors).forEach(function([key, value]) {
                        if (key.startsWith('--h5p-theme-') && typeof value === 'string') {
                            css += '  ' + key + ': ' + value + ' !important;\n';
                        }
                    });
                    css += '}\n';

                    styleEl = doc.createElement('style');
                    styleEl.id = styleId;
                    styleEl.type = 'text/css';
                    styleEl.appendChild(doc.createTextNode(css));
                    doc.head.appendChild(styleEl);
                }

                /**
                 * Applies density class and triggers H5P resize.
                 *
                 * @param {Window} win The iframe window object
                 * @param {HTMLElement} h5pContent The .h5p-content element
                 * @param {string} density The density setting
                 */
                function applyDensitySettings(win, h5pContent, density) {
                    var densityClass = density ? 'h5p-' + density : '';
                    if (densityClass === '' || !VALID_DENSITY_CLASSES.includes(densityClass)) {
                        return;
                    }
                    // Remove old density classes
                    VALID_DENSITY_CLASSES.forEach(function(cls) {
                        h5pContent.classList.remove(cls);
                    });
                    // Add new density class
                    h5pContent.classList.add(densityClass);

                    // Trigger resize so H5P adapts to the new density widths/heights
                    if (win.H5P && win.H5P.instances && win.H5P.instances[0]) {
                        win.H5P.instances[0].trigger('resize');
                    }
                }

                /**
                 * Processes an iframe to inject colors and density.
                 *
                 * @param {HTMLIFrameElement} iframe The iframe element to process
                 * @returns {boolean} True if completely processed, false otherwise
                 */
                function processIframe(iframe) {
                    try {
                        var doc = iframe.contentDocument || iframe.contentWindow.document;
                        var win = iframe.contentWindow;

                        if (!doc || !doc.head || !doc.body) {
                            return false; // Not fully ready
                        }

                        // 1. Inject Colors
                        injectCustomColors(doc, config.colors);

                        // Look for nested iframes (e.g. core_h5p often nests h5p-iframe inside h5p-player)
                        var innerIframes = doc.querySelectorAll('iframe.h5p-iframe, iframe.h5p-player');
                        for (var i = 0; i < innerIframes.length; i++) {
                            setupPolling(innerIframes[i]);
                        }

                        // 2. Apply Density
                        var density = config.density || '';
                        var densityClass = density ? 'h5p-' + density : '';

                        var h5pContent = doc.querySelector('.h5p-content');
                        if (!h5pContent) {
                            return false; // The h5p-content is not created yet.
                        }

                        // Check if density is already applied correctly
                        var hasCorrectDensity = false;
                        if (densityClass === '') {
                            hasCorrectDensity = true; // Nothing to apply
                        } else if (h5pContent.classList.contains(densityClass)) {
                            hasCorrectDensity = true; // Already applied
                        }

                        // Also verify if we've explicitly marked it as processed
                        if (h5pContent.h5pThemerApplied && hasCorrectDensity) {
                            return true; // We are completely done with this iframe
                        }

                        applyDensitySettings(win, h5pContent, density);

                        h5pContent.h5pThemerApplied = true;

                        // If this iframe has inner nested iframes, we shouldn't consider
                        // it completely "done" until those inner iframes are also processed.
                        // But we return true to stop polling the *outer* iframe, since the
                        // inner ones have their own polling interval now.
                        return true; // Finished successfully

                    } catch (e) {
                        return false;
                    }
                }

                /**
                 * Sets up polling to check if an iframe is ready and process it.
                 *
                 * @param {HTMLIFrameElement} iframe The iframe element to poll
                 */
                function setupPolling(iframe) {
                    if (iframe.h5pThemerInterval) {
                        return; // Already polling this iframe
                    }

                    // Poll the iframe during its load
                    iframe.h5pThemerInterval = setInterval(function() {
                        var isFullyApplied = processIframe(iframe);
                        if (isFullyApplied) {
                            clearInterval(iframe.h5pThemerInterval);
                        }
                    }, 250);

                    // Failsafe: stop polling after 15 seconds to save CPU
                    setTimeout(function() {
                        clearInterval(iframe.h5pThemerInterval);
                    }, 15000);

                    $(iframe).on('load', function() {
                        processIframe(iframe);
                    });
                }

                var processAllIframes = function() {
                    $('iframe.h5p-iframe, iframe.h5p-player').each(function() {
                        setupPolling(this);
                        processIframe(this);
                    });
                };

                processAllIframes();

                // Watch for dynamically added iframes (like in modals or ajax navigation)
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.addedNodes) {
                            $(mutation.addedNodes).find('iframe.h5p-iframe, iframe.h5p-player').each(function() {
                                processAllIframes();
                            });
                        }
                    });
                });

                observer.observe(document.body, {childList: true, subtree: true});
            });
        }
    };
});
