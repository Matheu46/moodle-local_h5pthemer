/**
 * Settings AMD module for local_h5pthemer.
 *
 * @module     local_h5pthemer/settings
 * @copyright  2026 Matheus Mathias
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/config', 'core/str'], function($, cfg, str) {
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
     * Helper to create and configure a new h5p-theme-picker DOM element.
     *
     * @param {jQuery} textarea 
     * @param {jQuery} presetsTextarea
     * @param {Object} translations 
     * @returns {HTMLElement}
     */
    function createPicker(textarea, presetsTextarea, translations) {
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
                    if (colors['--h5p-theme-main-cta-base']) {
                        options.customColorButtons = colors['--h5p-theme-main-cta-base'];
                    }
                    if (colors['--h5p-theme-secondary-cta-base']) {
                        options.customColorNavigation = colors['--h5p-theme-secondary-cta-base'];
                    }
                    if (colors['--h5p-theme-alternative-base']) {
                        options.customColorAlternative = colors['--h5p-theme-alternative-base'];
                    }
                    if (colors['--h5p-theme-background']) {
                        options.customColorBackground = colors['--h5p-theme-background'];
                    }
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

        var PickerClass = customElements.get('h5p-theme-picker');
        var picker;
        if (PickerClass) {
            picker = new PickerClass(options);
        } else {
            picker = document.createElement('h5p-theme-picker');
        }

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

        presetsArr.push(newPreset);
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

    return {
        init: function() {
            var script = document.createElement('script');
            script.type = 'module';
            script.src = cfg.wwwroot + '/local/h5pthemer/js/h5p-theme-picker.js';
            document.head.appendChild(script);

            $(document).ready(function() {
                var textarea = $('#id_s_local_h5pthemer_css_variables');
                if (!textarea.length) {
                    textarea = $('#id_local_h5pthemer_course_config'); // for course level settings
                }

                var presetsTextarea = $('#id_s_local_h5pthemer_presets_json');
                var presetsReadonly = $('#id_local_h5pthemer_presets_json_readonly');

                if (!textarea.length) {
                    return;
                }

                textarea.hide();

                var activePresetsTextarea = presetsTextarea.length ? presetsTextarea :
                    (presetsReadonly.length ? presetsReadonly : null);

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
                    {key: 'confirm_delete_preset', component: 'local_h5pthemer'}
                ]).done(function(strs) {
                    componentTranslations = {
                        selector_theme_label: strs[0],
                        selector_theme_value_daylight: strs[1],
                        selector_theme_value_lavender: strs[2],
                        selector_theme_value_mint: strs[3],
                        selector_theme_value_sunset: strs[4],
                        selector_theme_value_custom: strs[5],
                        selector_density_label: strs[6],
                        selector_density_value_large: strs[7],
                        selector_density_value_medium: strs[8],
                        selector_density_value_small: strs[9],
                        color_selector_title: strs[10],
                        color_selector_buttons_label: strs[11],
                        color_selector_buttons_button_aria: strs[12],
                        color_selector_navigation_label: strs[13],
                        color_selector_navigation_button_aria: strs[14],
                        color_selector_alternative_label: strs[15],
                        color_selector_alternative_button_aria: strs[16],
                        color_selector_background_label: strs[17],
                        color_selector_background_button_aria: strs[18],
                        preview_preview_label_prefix: strs[19]
                    };
                    uiTranslations = {
                        preset_name: strs[20],
                        save_new_preset: strs[21],
                        saved_custom_themes: strs[22],
                        delete: strs[23],
                        confirm_delete_preset: strs[24]
                    };

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
                        var presetUI = $('<div class="mt-3 d-flex align-items-center gap-2"></div>');
                        var inputHtml = '<input type="text" class="form-control" style="width: 250px;" ';
                        inputHtml += 'placeholder="' + uiTranslations.preset_name + '">';
                        var presetInput = $(inputHtml);
                        var btnHtml = '<button type="button" class="btn btn-secondary">';
                        btnHtml += uiTranslations.save_new_preset + '</button>';
                        var presetButton = $(btnHtml);

                        presetUI.append(presetInput).append(presetButton);
                        $(pickerEl).after(presetUI);

                        var currentConfigStr = textarea.val();
                        var isCustom = false;
                        try {
                            var parsed = JSON.parse(currentConfigStr);
                            if (parsed.theme === 'custom') {
                                isCustom = true;
                            }
                        } catch (e) {
                            // ignore
                        }

                        if (!isCustom) {
                            presetUI.addClass('d-none').removeClass('d-flex');
                        }

                        var bindVisibilityToggle = function(pickerInstance) {
                            pickerInstance.addEventListener('theme-change', function(e) {
                                if (e.detail && e.detail.theme) {
                                    if (e.detail.theme === 'custom') {
                                        presetUI.removeClass('d-none').addClass('d-flex');
                                    } else {
                                        presetUI.addClass('d-none').removeClass('d-flex');
                                    }
                                }
                            });
                        };

                        bindVisibilityToggle(pickerEl);

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
                                li.text(preset.name || preset.id);
                                var delBtn = $('<button type="button" class="btn btn-sm btn-outline-danger">' + uiTranslations.delete + '</button>');
                                delBtn.on('click', function(e) {
                                    e.preventDefault();
                                    var confirmMsg = uiTranslations.confirm_delete_preset.replace('{$a}', preset.name || preset.id);
                                    if (!confirm(confirmMsg)) {
                                        return;
                                    }

                                    var newPresets = presetsArr.filter(function(p) {
                                        return p.id !== preset.id;
                                    });
                                    presetsTextarea.val(JSON.stringify(newPresets, null, 2));

                                    var currentConfig = textarea.val();
                                    try {
                                        var parsed = JSON.parse(currentConfig);
                                        if (parsed.theme === preset.id) {
                                            parsed.theme = 'daylight';
                                            textarea.val(JSON.stringify(parsed, null, 2));
                                        }
                                    } catch (err) {}

                                    var newPickerEl = createPicker(textarea, presetsTextarea);
                                    $(pickerEl).replaceWith(newPickerEl);
                                    pickerEl = newPickerEl;
                                    bindVisibilityToggle(pickerEl);

                                    renderList();
                                });
                                li.append(delBtn);
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
                                presetUI.addClass('d-none').removeClass('d-flex');
                                renderList();
                            }
                        });
                    }
                });
                }).fail(function(ex) {
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
                    });
                });
            });
        }
    };
});
