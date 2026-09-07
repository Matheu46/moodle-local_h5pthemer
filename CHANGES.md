# Changes Log

All notable changes to the **H5P Themer** (`local_h5pthemer`) plugin will be documented in this file.

## [0.5.0] - 2026-09-06

### Added
- **Custom CSS Support**: Added `custom_css` field across Global, Category, and Course levels. Custom CSS is accumulated and concatenated (Top-Down) allowing modular styling overrides.
- **Cascading Configuration**: Configurations on custom css is cascade automatically from Global -> Category -> Course. Themes override higher levels (most specific wins).
- **Category Level Settings**: Managers can now define specific H5P themes and CSS for entire categories of courses via a new navigation node.
- **Security for CSS Injection**: Enforced strict security on the `custom_css` field. It is rendered as `readonly` for standard users, only allowing users with `moodle/site:config` to save raw CSS.
- **Database Architecture**: Implemented `local_h5pthemer_category` and `local_h5pthemer_course` tables to store contextual settings natively, complete with `upgrade.php` routines.

### Changed / Refactored
- **UI & Layout Modernization**: Overhauled the administration and contextual settings pages using a card-based layout (Bootstrap) powered by Moodle's Mustache templates (`settings_layout.mustache`), removing legacy jQuery DOM generation.
- **Internationalization (i18n)**: Stripped all hardcoded English strings from JavaScript and migrated them to proper Moodle language strings (`local_h5pthemer.php`).
- **Comprehensive Testing**: Expanded PHPUnit test coverage (`external_test.php`, `lib_test.php`, `util_test.php`) testing hierarchical cascading, CSS accumulation, and capability-based navigation nodes.

## [0.4.0] - 2026-08-01

### Added
- **External API**: Introduced a new external API method `get_config` to retrieve H5P Themer configuration based on course ID.
- **Licensing**: Added Moodle licensing information to `settings.js` and `themer.js`.

### Changed / Refactored
- **Configuration Fetching**: Updated `themer.js` to use `core/ajax` for fetching configuration instead of `core/config`.

## [0.3.0] - 2026-07-31

### Added
- **Backup and Restore**: Added backup and restore plugin classes.

## [0.2.0] - 2026-07-23

### Added
- **AJAX Configuration Endpoint**: Introduced `ajax.php` to asynchronously serve course-level and site-level H5P theme settings.
- **Preset Editing**: Added ability to edit existing custom presets directly in the administration settings page.
- **Preset Import/Export**: Added functionality to export custom presets to JSON files and import preset configurations.
- **Preset Deletion & Management**: Enhanced custom preset deletion handling and management within the H5P Theme Picker UI.

### Changed / Refactored
- **Asynchronous Theme Fetching**: Updated `themer.js` AMD module to fetch theme configurations via AJAX based on course ID instead of inline script execution.
- **Modularized Settings Logic**: Refactored `settings.js` AMD module to modularize settings parsing, attribute assignment logic, and preset state management.

### Dependencies
- **H5P Theme Picker Update**: Updated third-party library `h5p-theme-picker` to version `0.0.13`.

## [0.1.0] - 2026-07-04

This is the initial release of the plugin, introducing dynamic theme coloring and styling capabilities for H5P content in Moodle.

### Added
- **Dynamic H5P Theme Picker Integration**: Bundled `h5p-theme-picker` (v0.0.10) as a third-party library (`thirdpartylibs.xml` & `readme_moodle.txt` compliant).
- **Global Theme Presets**: Administrators can choose from predefined presets (Daylight, Lavender, Mint, Sunset) or define a custom configuration.
- **Custom Preset Creator**: Interface to create, save, and delete custom CSS color variables directly within the plugin's administration page.
- **Course-level Override**: Teachers with course update capabilities can select specific H5P themes at the course level, overriding the site-wide settings.
- **Native Moodle Confirmations**: Replaced native browser popups with Moodle's `core/notification` confirmation dialogs for a consistent and accessible UI experience.
- **Privacy API Compliance**: Implemented `null_provider` (`classes/privacy/provider.php`) to explicitly state that the plugin does not store user-identifiable data.
