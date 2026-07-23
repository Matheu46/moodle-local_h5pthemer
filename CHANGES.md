# Changes Log

All notable changes to the **H5P Themer** (`local_h5pthemer`) plugin will be documented in this file.

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
