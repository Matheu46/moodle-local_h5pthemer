# Changes Log

All notable changes to the **H5P Themer** (`local_h5pthemer`) plugin will be documented in this file.

## [0.1.0] - 2026-07-04

This is the initial release of the plugin, introducing dynamic theme coloring and styling capabilities for H5P content in Moodle.

### Added
- **Dynamic H5P Theme Picker Integration**: Bundled `h5p-theme-picker` (v0.0.10) as a third-party library (`thirdpartylibs.xml` & `readme_moodle.txt` compliant).
- **Global Theme Presets**: Administrators can choose from predefined presets (Daylight, Lavender, Mint, Sunset) or define a custom configuration.
- **Custom Preset Creator**: Interface to create, save, and delete custom CSS color variables directly within the plugin's administration page.
- **Course-level Override**: Teachers with course update capabilities can select specific H5P themes at the course level, overriding the site-wide settings.
- **Native Moodle Confirmations**: Replaced native browser popups with Moodle's `core/notification` confirmation dialogs for a consistent and accessible UI experience.
- **Privacy API Compliance**: Implemented `null_provider` (`classes/privacy/provider.php`) to explicitly state that the plugin does not store user-identifiable data.
