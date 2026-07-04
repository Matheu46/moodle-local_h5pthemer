This directory contains the h5p-theme-picker library, which provides the web component for selecting H5P themes.

Steps to update this library:
1. Clone the repository: `git clone https://github.com/otacke/h5p-theme-picker.git`
2. Checkout the desired stable release or main branch.
3. Install dependencies: `npm install`
4. Build the distributable files: `npm run build`
5. Copy `dist/index.js` to this directory and rename it to `h5p-theme-picker.js` (e.g. `cp dist/index.js /path/to/local_h5pthemer/js/h5p-theme-picker.js`).
6. Update the version number in `thirdpartylibs.xml` located in the root of the plugin.
7. Mention the update in `CHANGES.md`.
