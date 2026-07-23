/**
 * Settings AMD module for local_h5pthemer.
 *
 * @module     local_h5pthemer/settings
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/config', 'core/str', 'core/notification'], function($, cfg, str, notification) {
    var componentTranslations = null;
    var uiTranslations = {};

    /**
     * Maps the simple array format to the component's expected customPresets format.
     *
     * @param {Array} presetsArr Array of preset objects.
     * @return {Object} Object mapped for h5p-theme-picker.
     */
    function mapPresetsForComponent(presetsArr) {
        var componentPresets = {};
        if (Array.isArray(presetsArr)) {
            presetsArr.forEach(function(preset) {
                if (preset && preset.id) {
                    var vals = preset.colors || {
                        '--h5p-theme-main-cta-base': preset.color1 || '#000000',
                        '--h5p-theme-secondary-cta-base': preset.color2 || '#000000',
                        '--h5p-theme-alternative-base': preset.color3 || '#000000',
                        '--h5p-theme-background': preset.color4 || '#ffffff'
                    };

                    componentPresets[preset.id] = {
                        label: preset.name || preset.id,
                        backgroundColor: (preset.colors && preset.colors['--h5p-theme-background']) || preset.color4 || '#ffffff',
                        color: (preset.colors && preset.colors['--h5p-theme-main-cta-base']) || preset.color1 || '#000000',
                        values: vals
                    };
                }
            });
        }
        return componentPresets;
    }

    /**
     * Parses the current theme and colors settings from textarea.
     *
     * @param {jQuery} textarea
     * @param {jQuery} presetsTextarea
     * @param {Object} translations
     * @returns {Object} Picker options
     */
    function parsePickerOptions(textarea, presetsTextarea, translations) {
        var options = {};

        translations = translations || componentTranslations;
        if (translations) {
            options.translations = translations;
        }

        var val = textarea.val();
        if (val && val.trim() !== '') {
            try {
                var savedConfig = JSON.parse(val);
                if (savedConfig.theme) {
                    options.theme = savedConfig.theme;
                }
                if (savedConfig.density) {
                    options.density = savedConfig.density;
                }
                if (savedConfig.theme === 'custom' && savedConfig.colors) {
                    var colors = savedConfig.colors;
                    var colorMap = {
                        '--h5p-theme-main-cta-base': 'customColorButtons',
                        '--h5p-theme-secondary-cta-base': 'customColorNavigation',
                        '--h5p-theme-alternative-base': 'customColorAlternative',
                        '--h5p-theme-background': 'customColorBackground'
                    };
                    Object.keys(colorMap).forEach(function(key) {
                        if (colors[key]) {
                            options[colorMap[key]] = colors[key];
                        }
                    });
                }
            } catch (e) {
                // Ignore parse errors on init.
            }
        }

        if (presetsTextarea && presetsTextarea.length) {
            var presetsVal = presetsTextarea.val();
            if (presetsVal && presetsVal.trim() !== '') {
                try {
                    var presetsArr = JSON.parse(presetsVal);
                    // Pass the object directly into options
                    options.customPresets = mapPresetsForComponent(presetsArr);
                } catch (e) {
                    // Ignore JSON error
                }
            }
        }

        return options;
    }

    /**
     * Helper to create and configure a new h5p-theme-picker DOM element.
     *
     * @param {jQuery} textarea
     * @param {jQuery} presetsTextarea
     * @param {Object} translations
     * @returns {HTMLElement}
     */
    function createPicker(textarea, presetsTextarea, translations) {
        var options = parsePickerOptions(textarea, presetsTextarea, translations);

        var PickerClass = customElements.get('h5p-theme-picker');
        var picker;

        // Temporarily monkey-patch getAttribute to provide the attributes to the Web Component's constructor
        // because the component currently reads them synchronously before we have a chance to set them.
        var originalGetAttribute = HTMLElement.prototype.getAttribute;
        HTMLElement.prototype.getAttribute = function(name) {
            var mapping = {
                'custom-color-buttons': options.customColorButtons,
                'custom-color-navigation': options.customColorNavigation,
                'custom-color-alternative': options.customColorAlternative,
                'custom-color-background': options.customColorBackground,
                'theme-name': options.theme,
                'density': options.density
            };
            if (mapping[name]) {
                return mapping[name];
            }
            return originalGetAttribute.call(this, name);
        };
        try {
            if (PickerClass) {
                picker = new PickerClass(options);
            } else {
                picker = document.createElement('h5p-theme-picker');
            }
        } finally {
            HTMLElement.prototype.getAttribute = originalGetAttribute;
        }

        var attributes = {
            'theme-name': options.theme,
            'density': options.density,
            'custom-color-buttons': options.customColorButtons,
            'custom-color-navigation': options.customColorNavigation,
            'custom-color-alternative': options.customColorAlternative,
            'custom-color-background': options.customColorBackground
        };
        Object.keys(attributes).forEach(function(key) {
            if (attributes[key]) {
                picker.setAttribute(key, attributes[key]);
            }
        });

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

        return picker;
    }

    /**
     * Handles saving a new preset.
     *
     * @param {Object} textarea Main config textarea
     * @param {Object} presetsTextarea Presets textarea
     * @param {Object} presetInput Input field for preset name
     * @param {HTMLElement} currentPicker The current picker instance
     * @return {HTMLElement|null} The new picker instance if created
     */
    function handleSavePreset(textarea, presetsTextarea, presetInput, currentPicker) {
        var name = presetInput.val().trim();
        if (!name) {
            return null;
        }

        var slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        var currentConfig = textarea.val();
        var colors = {};

        if (currentConfig) {
            try {
                var parsed = JSON.parse(currentConfig);
                if (parsed.colors) {
                    colors = parsed.colors;
                }
            } catch (err) {
                // Ignore
            }
        }

        var newPreset = {
            id: slug,
            name: name,
            colors: colors
        };

        var presetsArr = [];
        var existingPresets = presetsTextarea.val();
        if (existingPresets && existingPresets.trim() !== '') {
            try {
                presetsArr = JSON.parse(existingPresets);
                if (!Array.isArray(presetsArr)) {
                    presetsArr = [];
                }
            } catch (err) {
                // Ignore
            }
        }

        var existingIndex = -1;
        for (var i = 0; i < presetsArr.length; i++) {
            if (presetsArr[i].id === slug) {
                existingIndex = i;
                break;
            }
        }

        if (existingIndex !== -1) {
            presetsArr[existingIndex] = newPreset;
        } else {
            presetsArr.push(newPreset);
        }
        presetsTextarea.val(JSON.stringify(presetsArr, null, 2));

        var currentConfigObj = {};
        if (currentConfig) {
            try {
                currentConfigObj = JSON.parse(currentConfig);
            } catch (err) {
                // Ignore
            }
        }
        currentConfigObj.theme = slug;
        textarea.val(JSON.stringify(currentConfigObj, null, 2));

        var newPickerEl = createPicker(textarea, presetsTextarea);
        $(currentPicker).replaceWith(newPickerEl);

        presetInput.val('');
        return newPickerEl;
    }

    /**
     * Sets up the preset creation and deletion UI.
     *
     * @param {jQuery} textarea
     * @param {jQuery} presetsTextarea
     * @param {HTMLElement} pickerEl
     */
    function setupPresetManagement(textarea, presetsTextarea, pickerEl) {
        var presetUI = $('<div class="mt-3 d-flex align-items-center gap-2"></div>');
        var inputHtml = '<input type="text" class="form-control" style="width: 250px;" ';
        inputHtml += 'placeholder="' + uiTranslations.preset_name + '">';
        var presetInput = $(inputHtml);
        var btnHtml = '<button type="button" class="btn btn-secondary">';
        btnHtml += uiTranslations.save_new_preset + '</button>';
        var presetButton = $(btnHtml);

        var fileInput = $('<input type="file" accept=".json" class="d-none">');
        var importBtnText = uiTranslations.importpreset || 'Import preset';
        var importButton = $('<button type="button" class="btn btn-outline-secondary">' +
            importBtnText + '</button>');

        presetUI.append(presetInput).append(presetButton).append(importButton).append(fileInput);
        $(pickerEl).after(presetUI);

        importButton.on('click', function(e) {
            e.preventDefault();
            fileInput.click();
        });

        fileInput.on('change', function(e) {
            var file = e.target.files[0];
            if (!file) {
                return;
            }
            var reader = new FileReader();
            reader.onload = function(evt) {
                try {
                    var data = JSON.parse(evt.target.result);
                    if (data && data.id && data.colors) {
                        var slug = data.id.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                        var name = data.name || data.id;
                        var existingPresetsStr = presetsTextarea.val();
                        var presetsArr = [];
                        if (existingPresetsStr && existingPresetsStr.trim() !== '') {
                            try {
                                presetsArr = JSON.parse(existingPresetsStr);
                            } catch (e) {
                                // Ignore JSON parsing error.
                            }
                        }

                        var existingIndex = -1;
                        for (var i = 0; i < presetsArr.length; i++) {
                            if (presetsArr[i].id === slug) {
                                existingIndex = i;
                                break;
                            }
                        }

                        var newPreset = {
                            id: slug,
                            name: name,
                            colors: data.colors
                        };

                        if (existingIndex !== -1) {
                            presetsArr[existingIndex] = newPreset;
                        } else {
                            presetsArr.push(newPreset);
                        }

                        presetsTextarea.val(JSON.stringify(presetsArr, null, 2));

                        var currentConfigStr = textarea.val();
                        var currentConfig = {};
                        if (currentConfigStr) {
                            try {
                                currentConfig = JSON.parse(currentConfigStr);
                            } catch (err) {
                                // Ignore JSON parsing error.
                            }
                        }
                        currentConfig.theme = 'custom';
                        currentConfig.colors = data.colors;
                        textarea.val(JSON.stringify(currentConfig, null, 2));

                        var newPickerEl = createPicker(textarea, presetsTextarea);
                        $(pickerEl).replaceWith(newPickerEl);
                        pickerEl = newPickerEl;
                        bindVisibilityToggle(pickerEl);

                        presetInput.val(name);
                        presetButton.text(uiTranslations.update_preset || 'Update preset');
                        presetUI.removeClass('d-none').addClass('d-flex');

                        renderList();
                    } else {
                        notification.addNotification({
                            message: uiTranslations.importpreset_error || 'Invalid preset file format.',
                            type: 'error'
                        });
                    }
                } catch (err) {
                    notification.addNotification({
                        message: uiTranslations.importpreset_error || 'Invalid preset file format.',
                        type: 'error'
                    });
                }
                fileInput.val('');
            };
            reader.readAsText(file);
        });

        var checkPresetExists = function(val) {
            var slug = val.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
            var presetsStr = presetsTextarea.val();
            var presetsArr = [];
            if (presetsStr && presetsStr.trim() !== '') {
                try {
                    presetsArr = JSON.parse(presetsStr);
                } catch (e) {
                    // Ignore JSON parsing error.
                }
            }
            for (var i = 0; i < presetsArr.length; i++) {
                if (presetsArr[i].id === slug) {
                    return true;
                }
            }
            return false;
        };

        presetInput.on('input', function() {
            var exists = checkPresetExists($(this).val().trim());
            presetButton.text(exists ? (uiTranslations.update_preset || 'Update preset') : uiTranslations.save_new_preset);
        });

        var bindVisibilityToggle = function(pickerInstance) {
            pickerInstance.addEventListener('theme-change', function(e) {
                if (e.detail && e.detail.theme) {
                    var themeName = e.detail.theme;

                    if (themeName !== 'custom') {
                        presetUI.addClass('d-none').removeClass('d-flex');
                        presetInput.val('');
                        presetButton.text(uiTranslations.save_new_preset);
                    } else {
                        presetUI.removeClass('d-none').addClass('d-flex');
                        var buttonText = checkPresetExists(presetInput.val().trim())
                            ? (uiTranslations.update_preset || 'Update preset')
                            : uiTranslations.save_new_preset;
                        presetButton.text(buttonText);
                    }
                }
            });
        };

        bindVisibilityToggle(pickerEl);

        // Initial setup for visibility based on current config
        var currentConfigStr = textarea.val();
        try {
            var parsed = JSON.parse(currentConfigStr);
            var themeName = parsed.theme || 'daylight';
            if (themeName !== 'custom') {
                presetUI.addClass('d-none').removeClass('d-flex');
            } else {
                presetUI.removeClass('d-none').addClass('d-flex');
            }
        } catch (e) {
            presetUI.addClass('d-none').removeClass('d-flex');
        }

        var listContainer = $('<div class="mt-4"></div>');
        presetUI.after(listContainer);

        var renderList = function() {
            listContainer.empty();
            var currentPresets = presetsTextarea.val();
            if (!currentPresets || currentPresets.trim() === '') {
                return;
            }
            var presetsArr = [];
            try {
                presetsArr = JSON.parse(currentPresets);
            } catch (e) {
                return;
            }
            if (!presetsArr.length) {
                return;
            }

            var ul = $('<ul class="list-group"></ul>');
            var title = $('<h6>' + uiTranslations.saved_custom_themes + '</h6>');
            listContainer.append(title).append(ul);

            presetsArr.forEach(function(preset) {
                var li = $('<li class="list-group-item d-flex ' +
                    'justify-content-between align-items-center p-2"></li>');

                var nameSpan = $('<span></span>').text(preset.name || preset.id);
                li.append(nameSpan);

                var actionsDiv = $('<div></div>');

                var editBtn = $('<button type="button" class="btn btn-sm btn-outline-primary me-2">' +
                    (uiTranslations.edit || 'Edit') + '</button>');
                editBtn.on('click', function(e) {
                    e.preventDefault();

                    var currentConfigObj = {};
                    var currentConfig = textarea.val();
                    if (currentConfig) {
                        try {
                            currentConfigObj = JSON.parse(currentConfig);
                        } catch (err) {
                            // Ignore JSON parsing error.
                        }
                    }
                    currentConfigObj.theme = 'custom';
                    if (preset.colors) {
                         currentConfigObj.colors = preset.colors;
                    }
                    textarea.val(JSON.stringify(currentConfigObj, null, 2));

                    var newPickerEl = createPicker(textarea, presetsTextarea);
                    $(pickerEl).replaceWith(newPickerEl);
                    pickerEl = newPickerEl;
                    bindVisibilityToggle(pickerEl);

                    presetInput.val(preset.name || preset.id);
                    presetButton.text(uiTranslations.update_preset || 'Update preset');
                    presetUI.removeClass('d-none').addClass('d-flex');
                });

                var delBtn = $('<button type="button" class="btn btn-sm btn-outline-danger">' +
                    uiTranslations.delete + '</button>');
                delBtn.on('click', function(e) {
                    e.preventDefault();
                    var confirmMsg = uiTranslations.confirm_delete_preset.replace('{$a}', preset.name || preset.id);

                    notification.confirm(
                        uiTranslations.confirm,
                        confirmMsg,
                        uiTranslations.delete,
                        uiTranslations.cancel,
                        function() {
                            var newPresets = [];
                            for (var i = 0; i < presetsArr.length; i++) {
                                if (presetsArr[i].id !== preset.id) {
                                    newPresets.push(presetsArr[i]);
                                }
                            }
                            presetsTextarea.val(JSON.stringify(newPresets, null, 2));

                            var currentConfig = textarea.val();
                            try {
                                var parsed = JSON.parse(currentConfig);
                                if (parsed.theme === preset.id) {
                                    parsed.theme = 'daylight';
                                    textarea.val(JSON.stringify(parsed, null, 2));
                                }
                            } catch (err) {
                                // Ignore
                            }

                            var newPickerEl = createPicker(textarea, presetsTextarea);
                            $(pickerEl).replaceWith(newPickerEl);
                            pickerEl = newPickerEl;
                            bindVisibilityToggle(pickerEl);

                            renderList();
                        }
                    );
                });

                var exportBtn = $('<button type="button" class="btn btn-sm btn-outline-info me-2">' +
                    (uiTranslations.export || 'Export') + '</button>');
                exportBtn.on('click', function(e) {
                    e.preventDefault();
                    var blob = new Blob([JSON.stringify(preset, null, 2)], {type: "application/json"});
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement("a");
                    a.href = url;
                    a.download = (preset.id || 'preset') + '.json';
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(function() {
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }, 0);
                });

                actionsDiv.append(editBtn).append(exportBtn).append(delBtn);
                li.append(actionsDiv);
                ul.append(li);
            });
        };

        renderList();

        presetButton.on('click', function(e) {
            e.preventDefault();
            var newPicker = handleSavePreset(textarea, presetsTextarea, presetInput, pickerEl);
            if (newPicker) {
                pickerEl = newPicker;
                bindVisibilityToggle(pickerEl);
                presetButton.text(uiTranslations.update_preset || 'Update preset');
                renderList();
            }
        });
    }

    /**
     * Initialize the theme picker UI after translations are loaded.
     *
     * @param {jQuery} textarea
     * @param {jQuery} presetsTextarea
     * @param {jQuery} presetsReadonly
     * @param {Array} strs Loaded translation strings
     */
    function initializeUI(textarea, presetsTextarea, presetsReadonly, strs) {
        componentTranslations = {
            'selector_theme_label': strs[0],
            'selector_theme_value_daylight': strs[1],
            'selector_theme_value_lavender': strs[2],
            'selector_theme_value_mint': strs[3],
            'selector_theme_value_sunset': strs[4],
            'selector_theme_value_custom': strs[5],
            'selector_density_label': strs[6],
            'selector_density_value_large': strs[7],
            'selector_density_value_medium': strs[8],
            'selector_density_value_small': strs[9],
            'color_selector_title': strs[10],
            'color_selector_buttons_label': strs[11],
            'color_selector_buttons_button_aria': strs[12],
            'color_selector_navigation_label': strs[13],
            'color_selector_navigation_button_aria': strs[14],
            'color_selector_alternative_label': strs[15],
            'color_selector_alternative_button_aria': strs[16],
            'color_selector_background_label': strs[17],
            'color_selector_background_button_aria': strs[18],
            'preview_preview_label_prefix': strs[19]
        };
        uiTranslations = {
            'preset_name': strs[20],
            'save_new_preset': strs[21],
            'saved_custom_themes': strs[22],
            'delete': strs[23],
            'confirm_delete_preset': strs[24],
            'confirm': strs[25],
            'cancel': strs[26],
            'edit': strs[27],
            'update_preset': strs[28],
            'export': strs[29],
            'importpreset': strs[30],
            'importpreset_error': strs[31]
        };

        var activePresetsTextarea = null;
        if (presetsTextarea.length) {
            activePresetsTextarea = presetsTextarea;
        } else if (presetsReadonly.length) {
            activePresetsTextarea = presetsReadonly;
        }

        // Wait for the custom element to be fully registered before creating
        customElements.whenDefined('h5p-theme-picker').then(function() {
            var pickerEl = createPicker(textarea, activePresetsTextarea);

            var fitem = textarea.closest('.form-item, .fitem');
            if (fitem.length) {
                fitem.after(pickerEl);
                fitem.hide();
            } else {
                textarea.after(pickerEl);
                textarea.hide();
            }

            // Only show preset creation/deletion UI if it's the admin setting (not readonly)
            if (presetsTextarea.length) {
                setupPresetManagement(textarea, presetsTextarea, pickerEl);
            }
            return null;
        }).catch(notification.exception);
    }

    return {
        init: function() {
            var script = document.createElement('script');
            script.type = 'module';
            script.src = cfg.wwwroot + '/local/h5pthemer/js/h5p-theme-picker.js?rev=' + cfg.jsrev;
            document.head.appendChild(script);

            $(document).ready(function() {
                var textarea = $('#id_s_local_h5pthemer_css_variables');
                if (!textarea.length) {
                    textarea = $('#id_local_h5pthemer_course_config'); // For course level settings
                }

                var presetsTextarea = $('#id_s_local_h5pthemer_presets_json');
                var presetsReadonly = $('#id_local_h5pthemer_presets_json_readonly');

                if (!textarea.length) {
                    return;
                }

                textarea.hide();

                var activePresetsTextarea = null;
                if (presetsTextarea.length) {
                    activePresetsTextarea = presetsTextarea;
                } else if (presetsReadonly.length) {
                    activePresetsTextarea = presetsReadonly;
                }

                if (activePresetsTextarea) {
                    activePresetsTextarea.closest('.form-item, .fitem').hide();
                }

                // Load translations from Moodle before initializing the picker
                str.get_strings([
                    {key: 'selector_theme_label', component: 'local_h5pthemer'},
                    {key: 'selector_theme_value_daylight', component: 'local_h5pthemer'},
                    {key: 'selector_theme_value_lavender', component: 'local_h5pthemer'},
                    {key: 'selector_theme_value_mint', component: 'local_h5pthemer'},
                    {key: 'selector_theme_value_sunset', component: 'local_h5pthemer'},
                    {key: 'selector_theme_value_custom', component: 'local_h5pthemer'},
                    {key: 'selector_density_label', component: 'local_h5pthemer'},
                    {key: 'selector_density_value_large', component: 'local_h5pthemer'},
                    {key: 'selector_density_value_medium', component: 'local_h5pthemer'},
                    {key: 'selector_density_value_small', component: 'local_h5pthemer'},
                    {key: 'color_selector_title', component: 'local_h5pthemer'},
                    {key: 'color_selector_buttons_label', component: 'local_h5pthemer'},
                    {key: 'color_selector_buttons_button_aria', component: 'local_h5pthemer'},
                    {key: 'color_selector_navigation_label', component: 'local_h5pthemer'},
                    {key: 'color_selector_navigation_button_aria', component: 'local_h5pthemer'},
                    {key: 'color_selector_alternative_label', component: 'local_h5pthemer'},
                    {key: 'color_selector_alternative_button_aria', component: 'local_h5pthemer'},
                    {key: 'color_selector_background_label', component: 'local_h5pthemer'},
                    {key: 'color_selector_background_button_aria', component: 'local_h5pthemer'},
                    {key: 'preview_preview_label_prefix', component: 'local_h5pthemer'},
                    {key: 'preset_name', component: 'local_h5pthemer'},
                    {key: 'save_new_preset', component: 'local_h5pthemer'},
                    {key: 'saved_custom_themes', component: 'local_h5pthemer'},
                    {key: 'delete', component: 'local_h5pthemer'},
                    {key: 'confirm_delete_preset', component: 'local_h5pthemer'},
                    {key: 'confirm', component: 'core'},
                    {key: 'cancel', component: 'core'},
                    {key: 'edit', component: 'core'},
                    {key: 'update_preset', component: 'local_h5pthemer'},
                    {key: 'export', component: 'local_h5pthemer'},
                    {key: 'importpreset', component: 'local_h5pthemer'},
                    {key: 'importpreset_error', component: 'local_h5pthemer'}
                ]).done(function(strs) {
                    initializeUI(textarea, presetsTextarea, presetsReadonly, strs);
                }).fail(function() {
                    // Fallback to english or proceed without translation if strings fail to load
                    customElements.whenDefined('h5p-theme-picker').then(function() {
                        var pickerEl = createPicker(textarea, activePresetsTextarea);
                        var fitem = textarea.closest('.form-item, .fitem');
                        if (fitem.length) {
                            fitem.after(pickerEl);
                            fitem.hide();
                        } else {
                            textarea.after(pickerEl);
                            textarea.hide();
                        }
                        return null;
                    }).catch(notification.exception);
                });
            });
        }
    };
});
