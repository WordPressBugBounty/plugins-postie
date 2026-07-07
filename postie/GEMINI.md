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
*   **`postie-config.class.php`**: Contains the strongly-typed `PostieSettings` DTO class and the `PostieConfig` manager class, which loads, sanitizes, and validates settings.
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
*   **Configuration:** Settings are stored in the WP options table (`postie-settings`) and managed via the `PostieConfig` class. The configuration is accessed globally via `postie_config_read()`, which returns a strongly-typed `PostieSettings` DTO. This DTO implements `ArrayAccess` to maintain complete backwards compatibility with legacy array lookups (e.g., `$config['post_type']`), but direct, strongly-typed property accesses (e.g., `$config->post_type`) should be used for all new development.

## Adding a New Setting

Adding a new configuration setting is a clean and structured process:

1.  **Declare the Property in `PostieSettings`** (`postie-config.class.php`):
    Add your new setting as a public typed property inside the `PostieSettings` class with its default value:
    ```php
    public bool $my_new_setting = false;
    ```
2.  **Classify the Type for Automatic Casting** (`postie-config.class.php`):
    Add your property's key to the corresponding type list array (`$boolKeys`, `$intKeys`, `$floatKeys`, or `$arrayKeys`) in the `PostieSettings::castValue()` helper method to ensure input is sanitized on load:
    ```php
    $boolKeys = array(
        ...,
        'my_new_setting'
    );
    ```
3.  **Register the Default Value** (`postie-config.class.php`):
    Add your key and default value inside the `defaults()` method returned array:
    ```php
    return array(
        ...,
        'my_new_setting' => false,
    );
    ```
4.  **Handle Delimited Arrays** (Optional, `postie-config.class.php`):
    If your setting is a delimited list (e.g., comma- or newline-separated), register it in `arrayed_settings()` to dynamically explode it:
    ```php
    ', ' => array(..., 'my_new_setting')
    ```
5.  **Render the HTML Option Field** (`config_form_*.php`):
    Create your input field in the relevant admin config page sub-template, using standard `postie-settings[...]` array inputs:
    ```html
    <input type="checkbox" name="postie-settings[my_new_setting]" value="yes" <?php checked($my_new_setting); ?> />
    ```
6.  **Verify via Unit Tests** (`test/postiesettingsTest.php`):
    Assert your new setting cascades and sanitizes correctly in `testDefaultsFallback()` and/or initialization test cases.

## Key Files to Watch
*   `postie.php`: Entry point.
*   `postie-functions.php`: Helper functions (if applicable, though logic seems distributed in classes).
*   `config_form.php`: Renders the settings page in WP Admin.
