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

### Testing and Linting
Tests are located in the `test/` directory. The presence of `wpstub.php` indicates that tests are designed to run in isolation by mocking WordPress core functions, rather than requiring a full WordPress environment.

*   **Framework:** PHPUnit (standard for WP plugins), utilizing `test/bootstrap.php` for isolated environment mocking.
*   **Running Tests & Enforcing Linter:** To guarantee PHP 7.0 compatibility, the compatibility linter **must** be run every time tests are executed. A Composer command is available that chains the two checks together, running the linter first, followed by PHPUnit:
    ```bash
    composer test
    ```
*   **Running Tests Separately:** To run only the PHPUnit suite:
    ```bash
    vendor/bin/phpunit
    ```
*   **Running Linter Separately:** To run only the PHP 7.0 compatibility scanner:
    ```bash
    vendor/bin/phpcs -p . --standard=PHPCompatibility --runtime-set testVersion 7.0 --runtime-set ignore_warnings_on_exit true --ignore=vendor/,test/
    ```

### Conventions
*   **Coding Style:** Follows general WordPress Coding Standards.
*   **PHP Version Compatibility:** The plugin must strictly maintain compatibility with **PHP 7.0**. Therefore, only PHP 7.0 compatible syntax is permitted in the codebase.
    *   **NO** typed class properties (e.g., `public string $prop` - PHP 7.4+).
    *   **NO** nullable typed properties (e.g., `public ?string $prop` - PHP 7.4+).
    *   **NO** `: void` return type declarations (PHP 7.1+).
    *   **NO** union types, arrow functions (`fn()`), match expressions, or `??=` operators.
    *   Scalar type hints in function parameters (e.g., `string`, `int`, `bool`, `array`) and scalar/array return typehints (e.g., `: bool`, `: array`, `: string`) are supported in PHP 7.0 and are permitted.
*   **Hooks:** Extensive use of `add_filter` and `add_action`. New features should likely use these hooks rather than modifying core logic where possible.
*   **Configuration:** Settings are stored in the WP options table (`postie-settings`) and managed via the `PostieConfig` class. The configuration is accessed globally via `postie_config_read()`, which returns a strongly-typed `PostieSettings` DTO. This DTO implements `ArrayAccess` to maintain complete backwards compatibility with legacy array lookups (e.g., `$config['post_type']`), but direct, strongly-typed property accesses (e.g., `$config->post_type`) should be used for all new development.

## Adding a New Setting

Adding a new configuration setting is a clean and structured process:

1.  **Declare the Property in `PostieSettings`** (`postie-config.class.php`):
    Add your new setting as a public property inside the `PostieSettings` class with its default value (without type hints to maintain PHP 7.0 compatibility):
    ```php
    public $my_new_setting = false;
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

## Releasing a New Version

Releasing a new version is a streamlined process guided by the deployment configurations in `deploy/_deploy.txt`. Do **not** manually update `readme.txt`, as it is automatically generated and updated by the deployment script.

### Release Steps:

1.  **Changelog & Documentation**:
    *   Update `docs/Changes.txt` under the `== CHANGELOG ==` section with the new version number, release date (formatted `YYYY-MM-DD`), and a list of modifications.
    *   Add an optional `Upgrade Notice` in `docs/Changes.txt` if there are critical notices for users.
2.  **Version Numbers**:
    *   Update the version number in `docs/Postie.txt` (the `Stable tag` setting).
    *   Update the `Version` field in the main plugin comment block at the top of `postie.php`.
3.  **Validate**:
    *   Run the test suite and compatibility scanner via `composer test` to ensure complete code compliance.
4.  **Execute Deployment Script**:
    *   Run `deploy\deploy.cmd` (or `deploy/deploy.sh` on Unix-like environments) to automatically generate the `readme.txt` and build/package the release assets.
5.  **Source Control**:
    *   Commit all finalized files locally: `git commit -am "Prepare <version> release"`
    *   Create a release tag: `git tag -a <version> -m "Release <version>"`
6.  **Public Announcements**:
    *   Add a release post on the official Postie site: `http://postieplugin.com/`
    *   Add a release post on the WordPress Support Forums: `http://wordpress.org/support/plugin/postie`

## Key Files to Watch
*   `postie.php`: Entry point.
*   `postie-functions.php`: Helper functions (if applicable, though logic seems distributed in classes).
*   `config_form.php`: Renders the settings page in WP Admin.
