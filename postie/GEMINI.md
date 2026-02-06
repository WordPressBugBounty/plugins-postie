# Postie - WordPress Plugin

## Project Overview

**Postie** is a WordPress plugin that allows users to create posts, pages, and other content types via email. It supports advanced features such as category assignment, tag handling, custom post formats, and extensive attachment processing (images, videos, audio). It uses the Flourish library for robust email handling (IMAP/POP3/SSL/TLS).

### Key Features
*   **Email Protocols:** IMAP, POP3, SSL, TLS.
*   **Content Control:** Supports HTML and Plain Text emails, strips signatures/replies.
*   **Taxonomies:** Set categories, tags, and post status via email commands.
*   **Attachments:** Handles images (galleries, featured images), videos, and audio with templating support.
*   **Extensibility:** Rich set of filters and actions for developers (`postie-filters.php`).

## Directory Structure

*   **`postie.php`**: Main plugin file. Handles initialization, hooks, and cron scheduling.
*   **`postie.class.php`**: Core logic class (`Postie`) for fetching and processing emails.
*   **`postie-config.class.php`**: Manages plugin configuration and settings validation.
*   **`lib/`**: Contains bundled dependencies:
    *   **Flourish Library** (`fMailbox.php`, `fEmail.php`, etc.): Used for email protocol handling.
    *   **`simple_html_dom.php`**: For parsing and manipulating HTML content.
*   **`templates/`**: PHP templates used to render attachments (e.g., `image_templates.php`, `video1_templates.php`).
*   **`test/`**: Unit tests and test helpers.
    *   `bootstrap.php`: Test bootstrapper.
    *   `wpstub.php`: Mocks WordPress functions for isolated testing.
*   **`languages/`**: Localization files (`.po`, `.mo`).
*   **`docs/`**: Documentation files (`Usage.txt`, `FAQ.txt`, `Changes.txt`).

## Building and Running

Since this is a WordPress plugin, "building" typically involves ensuring the directory is placed within the `wp-content/plugins/` directory of a WordPress installation.

### Installation
1.  Copy the `postie` directory to `wp-content/plugins/`.
2.  Activate the plugin via the WordPress Admin interface.
3.  Configure mail server settings under the **Postie** menu.

### Execution
*   **Scheduled:** Runs via WordPress Cron (configurable intervals).
*   **Manual Trigger:** `http://<your-site>/?postie=get-mail`
*   **Debug Mode:** Can be enabled in settings to log detailed output.

## Development and Testing

### Testing
Tests are located in the `test/` directory. The presence of `wpstub.php` indicates that tests are designed to run in isolation by mocking WordPress core functions, rather than requiring a full WordPress environment.

*   **Framework:** Likely PHPUnit (standard for WP plugins), though no `phpunit.xml` was immediately found in the root.
*   **Running Tests:** Execute PHPUnit pointing to the `test` directory or specific test files. Ensure `bootstrap.php` is loaded.

### Conventions
*   **Coding Style:** Follows general WordPress Coding Standards.
*   **Hooks:** Extensive use of `add_filter` and `add_action`. New features should likely use these hooks rather than modifying core logic where possible.
*   **Configuration:** Settings are stored in the WP options table (`postie-settings`) and managed via `PostieConfig` class.

## Key Files to Watch
*   `postie.php`: Entry point.
*   `postie-functions.php`: Helper functions (if applicable, though logic seems distributed in classes).
*   `config_form.php`: Renders the settings page in WP Admin.
