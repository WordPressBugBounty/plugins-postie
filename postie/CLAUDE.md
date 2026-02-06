# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Postie is a WordPress plugin that allows users to create posts, pages, and other content types via email. It supports IMAP/POP3 protocols with SSL/TLS, handles attachments (images, video, audio), and provides extensive hooks for developer customization.

## Build & Test Commands

```bash
# Install dependencies
composer install

# Run all tests (must run from test/ directory with bootstrap)
cd test && ../vendor/bin/phpunit --bootstrap bootstrap.php .

# Run a specific test file
cd test && ../vendor/bin/phpunit --bootstrap bootstrap.php postiefunctionsTest.php

# Run a specific test method
cd test && ../vendor/bin/phpunit --bootstrap bootstrap.php --filter testMethodName .
```

## Architecture

### Core Files

- **`postie.php`** - Main plugin entry point. Defines `PostieInit` class that registers all WordPress hooks, cron schedules, and admin menus.
- **`postie.class.php`** - Core `Postie` class that handles email fetching and processing. Loads the Flourish library for mail protocol handling.
- **`postie-message.php`** - `PostieMessage` class that processes individual emails: parses content, handles shortcodes, extracts metadata.
- **`postie-config.class.php`** - `PostieConfig` class that manages plugin settings stored in WP options (`postie-settings`).
- **`postie-api.php`** - Public API functions: `postie_config_read()`, `DebugEcho()`, `EchoError()`, `postie_get_mail()`.
- **`postie-filters.php`** - Filter functions for processing email content, attachments, and templates.
- **`postie-tags.php`** - Tag/taxonomy handling for email commands.

### Library (`lib/`)

Bundled dependencies:
- **Flourish Library** (`fMailbox.php`, `fEmail.php`, `fCore.php`, etc.) - Email protocol handling (IMAP/POP3)
- **Mail Server Classes** (`pMailServer.php`, `pImapMailServer.php`, `pPop3MailServer.php`) - Server connection abstractions
- **Connection Classes** (`pConnection.php`, `pCurlConnection.php`, `pSocketConnection.php`) - Transport layer
- **`simple_html_dom.php`** - HTML parsing
- **`autolink.php`** - URL autolinking

### Templates (`templates/`)

PHP templates for rendering attachments in posts:
- `image_templates.php` - Image display templates
- `video1_templates.php`, `video2_templates.php` - Video player templates
- `audio_templates.php` - Audio player templates
- `general_template.php` - Generic attachment template

### Testing (`test/`)

Tests run in isolation using `wpstub.php` to mock WordPress functions. No full WordPress environment required.

- `bootstrap.php` - Test setup, defines `ABSPATH`, loads stubs and main plugin
- `wpstub.php` - Mocks WordPress core functions and globals (`$wpdb`, `$g_user`, etc.)
- `postiefunctionsTest.php` - Main test suite for tag parsing, filters, and utilities
- `inlineimageTest.php` - Email parsing and image processing tests
- `fMailboxTest.php` - Mailbox connection tests
- `apiTest.php` - Public API function tests
- `data/` - Test fixture files (serialized emails, raw email files)

#### wpstub.php Global Variables

The stub file provides mock globals that tests can manipulate. These use `$GLOBALS['...']` for PHPUnit compatibility:

- `$wpdb` - Mock database object with `get_var()`, `terms`, `term_taxonomy`
- `$g_user` - Mock `WP_User` object
- `$g_option` - Controls `get_option()` return value
- `$g_current_user_can` - Array of return values for `current_user_can()` calls
- `$g_get_term_by` - Mock term object for `get_term_by()`
- `$g_get_posts` - Return value for `get_posts()`

## Key Patterns

### Configuration Access
```php
$config = postie_config_read();  // Returns validated settings array
```

### Debug Logging
```php
DebugEcho("message");           // Only outputs when POSTIE_DEBUG is true
DebugDump($variable);           // Dumps variable when debugging
EchoError("error message");     // Always logs errors
```

### Filter/Action Hooks
Postie uses extensive WordPress hooks. Key filters for extending functionality:
- `postie_post_before` - Modify post data before creation
- `postie_filter_email` - Custom email address mapping
- `postie_place_media_before`/`postie_place_media_after` - Media placement
- `postie_session_start`/`postie_session_end` - Processing lifecycle

### Custom Email Processing
Place `filterPostie.php` in `wp-content/` directory for custom email processing filters.

## Manual Trigger

Email checking can be triggered via URL: `http://<site>/?postie=get-mail`
