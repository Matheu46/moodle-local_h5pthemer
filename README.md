# H5P Themer (local_h5pthemer)

**H5P Themer** is a Moodle local plugin that allows administrators, category managers, and teachers to customize visual themes (colors), density, and custom CSS for H5P content, supporting both the traditional **`mod_hvp`** plugin and Moodle Core H5P (**`core_h5p`** / **`mod_h5pactivity`**).

This project is adapted from the WordPress [snordians-h5p-themer](https://github.com/otacke/snordians-h5p-themer) plugin, tailored for seamless integration into the Moodle ecosystem with full support for hierarchical overrides and category-level cascading.

---

## 🚀 How It Works

1. **Simple Configuration Panel:**
   The plugin integrates the official [h5p-theme-picker](https://github.com/otacke/h5p-theme-picker) web component directly into Moodle's administration interface (`Site Administration > Plugins > Local plugins > H5P Themer`).

2. **Preset Management (Create, Edit, Import/Export):**
   Administrators can create and save custom color palettes as named presets, edit them later, export them as `.json` files, or import existing preset files directly from the UI.

3. **Multi-Level Hierarchy (Global, Category, Course):**
   Themes can be set at three distinct levels:
   - **Global (Site):** Applied platform-wide by administrators.
   - **Category:** Category managers (`moodle/category:manage`) can override themes for all courses within a specific category.
   - **Course:** Teachers with course edit privileges (`moodle/course:update`) can select specific themes for individual courses.
   - *Rule:* Themes and color palettes follow a **most-specific-wins** override strategy (Course > Category > Global).

4. **Cumulative Custom CSS:**
   - Administrators can add custom CSS snippets to fine-tune typography, layout, borders, or any H5P elements.
   - **Top-Down Accumulation:** Unlike color themes (which overwrite parent levels), Custom CSS is **cumulative**. The plugin concatenates stylesheets in hierarchical order: `Global CSS` ➔ `Category CSS` ➔ `Course CSS`. This ensures course-specific tweaks don't erase global branding.
   - **Security & Safety:** Only users with `moodle/site:config` can edit Custom CSS. For non-administrators, the field is safely rendered as **read-only**, allowing teachers to inspect inherited styles without XSS risks.

5. **Dynamic Injection via External API:**
   On the frontend, an AMD module (`local_h5pthemer/themer`) queries the Moodle External API (`local_h5pthemer_get_config`), resolves the active configuration for the course/context, and injects CSS variables (`--h5p-theme-*`) and custom stylesheets (`<style id="h5p-themer-custom-css">`) directly into the `<head>` of H5P iframes.

6. **Density Management:**
   Applies the configured density class (`h5p-large`, `h5p-medium`, `h5p-small`) to the H5P root element (`.h5p-content`) and dispatches resize triggers to ensure interactive elements adapt their scale seamlessly.

7. **Support for Nested Iframes:**
   Moodle Core renders H5P content inside nested iframes (`h5p-player` ➔ `h5p-iframe`). The frontend script automatically traverses the DOM tree with smart polling and a `MutationObserver` to ensure styles are injected into dynamically rendered or AJAX-loaded activities.

---

## 🛠️ Technical Architecture

- **Visual Component:** Custom `h5p-theme-picker` web component library.
- **Frontend AMD Modules:**
  - `local_h5pthemer/settings`: Manages theme picking, preset storage, and Mustache template rendering in form pages.
  - `local_h5pthemer/themer`: Responsible for detecting H5P iframes, fetching configuration via AJAX, and applying themes and CSS.
- **Templates:**
  - `templates/settings_layout.mustache`: Centralized Bootstrap card layout for theme selection and CSS editor.
- **Backend & Database:**
  - **Global Configuration:** Stored in Moodle plugin configuration (`local_h5pthemer/css_variables`, `presets_json`, `custom_css`).
  - **Category Settings:** Persisted in the dedicated `{local_h5pthemer_category}` database table.
  - **Course Settings:** Persisted in the dedicated `{local_h5pthemer_course}` database table.
  - **External API:** `\local_h5pthemer\external\get_config` evaluates permissions and computes the accumulated configuration top-down.

---

## 📦 Installation

### Manual Installation

1. Clone or extract this repository into your Moodle installation under:
   ```bash
   {moodle_root}/local/h5pthemer
   ```
2. Log in as an administrator on your Moodle site and go to **Notifications** to trigger the database upgrade process.
3. Alternatively, you can complete the installation via CLI:
   ```bash
   php admin/cli/upgrade.php
   ```

---

## 🔨 Development and Compilation

This plugin complies with Moodle development standards. If you modify any Javascript files inside `amd/src/`, you must compile the minified versions using Grunt:

```bash
# Using Grunt from your Moodle root:
npx grunt amd --component=local_h5pthemer
```

### Running Tests

Automated PHPUnit tests cover external API functions, cascading rules, CSS accumulation, and capability-based navigation nodes:

```bash
# From your Moodle root directory:
vendor/bin/phpunit --filter local_h5pthemer
```

---

## 📄 License

2026 Matheus Mathias

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
