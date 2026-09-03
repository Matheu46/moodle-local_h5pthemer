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
 * Themer AMD module for local_h5pthemer.
 * Intercepts H5P iframes and injects custom CSS variables and applies density.
 *
 * @module     local_h5pthemer/themer
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax'], function($, ajax) {
    const VALID_DENSITY_CLASSES = ['h5p-large', 'h5p-medium', 'h5p-small'];
    const CSS_VAR_REGEX = /^--h5p-theme-[a-z0-9-]+$/i;
    const DANGEROUS_CSS_REGEX = /[;{}<>"\x27\\]|(javascript|expression|url|@import|behavior|eval)/i;

    const RGB_PATTERN = '^rgba?\\(\\s*(?:(?:\\d{1,3}(?:\\.\\d+)?%?|\\.\\d+%?)\\s*[, ]\\s*){2}' +
        '(?:\\d{1,3}(?:\\.\\d+)?%?|\\.\\d+%?)' +
        '(?:\\s*(?:,\\s*|\\/\\s*)(?:0|1|0?\\.\\d+|\\d{1,3}%))?\\s*\\)$';
    const HSL_PATTERN = '^hsla?\\(\\s*(?:\\d{1,3}(?:\\.\\d+)?(?:deg|rad|turn)?|\\.\\d+(?:deg|rad|turn)?)\\s*[, ]\\s*' +
        '\\d{1,3}(?:\\.\\d+)?%\\s*[, ]\\s*\\d{1,3}(?:\\.\\d+)?%' +
        '(?:\\s*(?:,\\s*|\\/\\s*)(?:0|1|0?\\.\\d+|\\d{1,3}%))?\\s*\\)$';
    const RGB_REGEX = new RegExp(RGB_PATTERN, 'i');
    const HSL_REGEX = new RegExp(HSL_PATTERN, 'i');

    /**
     * Validates if a string is a safe and valid CSS value for H5P.
     *
     * @param {string} val
     * @returns {boolean}
     */
    function isValidCssValue(val) {
        if (typeof val !== 'string') {
            return false;
        }
        var trimmed = val.trim();
        if (trimmed === '' || DANGEROUS_CSS_REGEX.test(trimmed)) {
            return false;
        }

        return /^#([0-9a-f]{3,4}|[0-9a-f]{6}|[0-9a-f]{8})$/i.test(trimmed) ||
               /^-?\d+(?:\.\d+)?(?:px|rem|em|%|deg|turn|rad)?$/i.test(trimmed) ||
               RGB_REGEX.test(trimmed) ||
               HSL_REGEX.test(trimmed) ||
               /^var\(\s*--h5p-theme-[a-z0-9-]+\s*\)$/i.test(trimmed) ||
               /^color-mix\(\s*in\s+[a-z0-9-]+\s*,\s*[^,;{}]+\s*,\s*[^,;{}]+\s*\)$/i.test(trimmed) ||
               /^[a-z]{3,25}$/i.test(trimmed);
    }

    return {
        init: function(courseId, initialConfig) {
            $(document).ready(function() {
                var config = typeof initialConfig === 'object' && initialConfig !== null ? initialConfig : null;
                var fetchingPromise = null;

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
                    var count = 0;
                    Object.entries(colors).forEach(function([key, value]) {
                        if (CSS_VAR_REGEX.test(key) && isValidCssValue(value)) {
                            css += '  ' + key + ': ' + value.trim() + ' !important;\n';
                            count++;
                        }
                    });
                    css += '}\n';

                    if (count === 0) {
                        return;
                    }

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
                            // Stop polling this outer wrapper iframe if inner iframes were found.
                            if (innerIframes.length > 0) {
                                return true;
                            }
                            return false; // The h5p-content is not created yet and no inner iframes found.
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

                        return true;

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
                    var iframes = $('iframe.h5p-iframe, iframe.h5p-player');
                    if (iframes.length === 0) {
                        return; // No iframes found yet.
                    }

                    if (!config) {
                        if (!fetchingPromise) {
                            fetchingPromise = ajax.call([{
                                methodname: 'local_h5pthemer_get_config',
                                args: {courseid: courseId}
                            }])[0].done(function(response) {
                                try {
                                    config = typeof response === 'string' ? JSON.parse(response) : response;
                                } catch (e) {
                                    config = {};
                                }
                            }).fail(function() {
                                config = {};
                            });
                        }

                        fetchingPromise.done(function() {
                            iframes.each(function() {
                                setupPolling(this);
                                processIframe(this);
                            });
                        });
                        return;
                    }

                    // Config is already loaded, process normally.
                    iframes.each(function() {
                        setupPolling(this);
                        processIframe(this);
                    });
                };

                processAllIframes();

                // Watch for dynamically added iframes using optimized native DOM traversal
                var observer = new MutationObserver(function(mutations) {
                    var hasNewIframe = false;
                    for (var i = 0; i < mutations.length; i++) {
                        var addedNodes = mutations[i].addedNodes;
                        for (var j = 0; j < addedNodes.length; j++) {
                            var node = addedNodes[j];
                            if (node.nodeType === 1) { // Element node
                                var isIframe = node.tagName === 'IFRAME';
                                var hasClass = node.classList.contains('h5p-iframe') || node.classList.contains('h5p-player');
                                if (isIframe && hasClass) {
                                    hasNewIframe = true;
                                    break;
                                }
                                if (node.querySelectorAll) {
                                    var innerIframes = node.querySelectorAll('iframe.h5p-iframe, iframe.h5p-player');
                                    if (innerIframes.length > 0) {
                                        hasNewIframe = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if (hasNewIframe) {
                            break;
                        }
                    }

                    if (hasNewIframe) {
                        processAllIframes();
                    }
                });
                observer.observe(document.body, {childList: true, subtree: true});
            });
        }
    };
});
