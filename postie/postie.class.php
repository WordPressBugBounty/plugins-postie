<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fUnexpectedException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fExpectedException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fConnectivityException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fValidationException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fProgrammerException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fEnvironmentException.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fCore.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fMailbox.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/fEmail.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pConnection.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pCurlConnection.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pSocketConnection.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pMailServer.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pImapMailServer.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "lib/pPop3MailServer.php");

require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php'); //wp_tempnam()

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "postie-filters.php");
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "postie-tags.php");

class Postie {

    public $last_run_result = array();

    function load_html($text) {
        return \voku\helper\HtmlDomParser::str_get_html($text, true, true, DEFAULT_TARGET_CHARSET, false);
    }

    function return_false() {
        return false;
    }

    function get_mail() {
        $config = postie_config_read();
        $this->setup_logging($config);
        if (true == $config->duplicate_comments) {
            DebugEcho("enabling duplicate comments");
            add_filter('duplicate_comment_id', array($this, 'return_false'));
        }
        add_filter('intermediate_image_sizes_advanced', array($this, 'intermediate_image_sizes_advanced'));

        try {
            DebugEcho("doing postie_session_start");
            do_action('postie_session_start');
        } catch (Throwable $exc) {
            EchoError('postie_session_start: ' . $exc->getMessage());
            $this->log_trace($exc, $config->mail_password, 'postie_session_start');
        }

        DebugEcho('Starting mail fetch');
        DebugEcho('WordPress datetime: ' . current_time('mysql'));

        $wp_content_path = dirname(dirname(dirname(__FILE__)));
        DebugEcho("wp_content_path: $wp_content_path");
        if (file_exists($wp_content_path . DIRECTORY_SEPARATOR . 'filterPostie.php')) {
            DebugEcho("found filterPostie.php in $wp_content_path");
            include_once ($wp_content_path . DIRECTORY_SEPARATOR . 'filterPostie.php');
        }

        $this->postie_environment();
        $this->postie_environment_encoding();

        if (function_exists('memory_get_usage')) {
            DebugEcho(__("memory at start of email processing: ", 'postie') . memory_get_usage());
        }

        if (has_filter('postie_post')) {
            echo "Postie: filter 'postie_post' is depricated in favor of 'postie_post_before'";
        }

        //don't output the password
        DebugEcho("postie configuration");
        $tmp_config = $config->toSafeArray();
        DebugDump($tmp_config);

        $conninfo = $this->connection_info($config);

        $summary = $this->fetch_mail($conninfo['mail_server'], $conninfo['mail_port'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_protocol'], $conninfo['email_delete_after_processing'], $conninfo['email_max']);

        try {
            DebugEcho("doing postie_session_end");
            do_action('postie_session_end');
        } catch (Throwable $exc) {
            EchoError('postie_session_end: ' . $exc->getMessage());
            $this->log_trace($exc, $conninfo['mail_password'], 'postie_session_end');
        }

        if (function_exists('memory_get_usage')) {
            DebugEcho('memory at end of email processing: ' . memory_get_usage());
        }

        return $summary;
    }

    function intermediate_image_sizes_advanced($sizes) {
        $config = postie_config_read();
        if ($config->image_resize) {
            DebugEcho('intermediate_image_sizes_advanced');
            DebugDump($sizes);
            return $sizes;
        }

        DebugEcho('intermediate_image_sizes_advanced: None');
        return false;
    }

    function show_filters_for($hook = '') {
        global $wp_filter;
        if (empty($hook) || !isset($wp_filter[$hook])) {
            DebugEcho("No registered filters for $hook");
            return;
        }
        DebugEcho("Registered filters for $hook");
        DebugDump($wp_filter[$hook]->callbacks);
    }

    function save_email_debug($raw, $email) {
        if (self::is_debugmode()) {
            //DebugDump($email);
            //DebugDump($mimeDecodedEmail);

            $dname = POSTIE_ROOT . DIRECTORY_SEPARATOR . 'test_emails' . DIRECTORY_SEPARATOR;
            if (is_dir($dname)) {
                $mid = $email['headers']['message-id'];
                if (empty($mid)) {
                    $mid = uniqid();
                }
                $fname = $dname . sanitize_file_name($mid);

                file_put_contents($fname . '-raw.txt', $raw);
                file_put_contents($fname . '.txt', $email['text']);
                file_put_contents($fname . '.html', $email['html']);
            }
        }
    }

    function get_mailbox($server, $port, $email, $password, $protocol, $config) {
        $protocol = strtolower($protocol);
        if ($protocol == 'imap' || $protocol == 'imap-ssl') {
            $type = 'imap';
        } else {
            $type = 'pop3';
        }
        $connectiontype = $config->input_connection;
        if ($connectiontype == 'curl') {
            $conn = new pCurlConnection($type, trim($server), $email, $password, $port, ($protocol == 'imap-ssl' || $protocol == 'pop3-ssl'));
        } else {
            $conn = new pSocketConnection($type, trim($server), $email, $password, $port, ($protocol == 'imap-ssl' || $protocol == 'pop3-ssl'));
        }

        if ($type == 'imap') {
            $srv = new pImapMailServer($conn);
        } else {
            $srv = new pPop3MailServer($conn);
        }

        $mailbox = new fMailbox($type, $conn, $srv);
        return $mailbox;
    }

    /**
     * This function handles determining the protocol and fetching the mail
     */
    function fetch_mail($server, $port, $email, $password, $protocol, $deleteMessages, $maxemails) {

        $result = array(
            'success'   => true,
            'total'     => 0,
            'processed' => 0,
            'skipped'   => 0,
            'posts'     => array(),
            'comments'  => array(),
            'errors'    => array(),
        );

        if (!$server || !$port || !$email) {
            EchoError("Missing Configuration For Mail Server");
            $result['success'] = false;
            $result['errors'][] = __("Missing Configuration For Mail Server", 'postie');
            $this->last_run_result = $result;
            return $result;
        }

        $posts_hook = function($details) use (&$result) {
            $post_id = isset($details['ID']) ? $details['ID'] : 0;
            if ($post_id) {
                $title = function_exists('get_the_title') ? get_the_title($post_id) : '';
                if (empty($title) && isset($details['post_title'])) {
                    $title = $details['post_title'];
                }
                if (empty($title)) {
                    $title = __('(No title)', 'postie');
                }
                $edit_url = function_exists('get_edit_post_link') ? get_edit_post_link($post_id, 'raw') : '';
                if (empty($edit_url) && function_exists('admin_url')) {
                    $edit_url = admin_url('post.php?post=' . $post_id . '&action=edit');
                }
                $result['posts'][] = array(
                    'id'       => $post_id,
                    'title'    => $title,
                    'status'   => function_exists('get_post_status') ? get_post_status($post_id) : (isset($details['post_status']) ? $details['post_status'] : 'draft'),
                    'type'     => function_exists('get_post_type') ? get_post_type($post_id) : (isset($details['post_type']) ? $details['post_type'] : 'post'),
                    'edit_url' => $edit_url,
                    'view_url' => function_exists('get_permalink') ? get_permalink($post_id) : '',
                );
            }
        };
        add_action('postie_post_after', $posts_hook);

        $comments_hook = function($comment, $post_id) use (&$result) {
            $comment_id = is_numeric($post_id) ? $post_id : (isset($comment['comment_ID']) ? $comment['comment_ID'] : 0);
            $target_post_id = isset($comment['comment_post_ID']) ? $comment['comment_post_ID'] : 0;
            $result['comments'][] = array(
                'id'       => $comment_id,
                'post_id'  => $target_post_id,
                'author'   => isset($comment['comment_author']) ? $comment['comment_author'] : '',
                'view_url' => function_exists('get_comment_link') ? get_comment_link($comment_id) : '',
            );
        };
        add_action('postie_comment_after', $comments_hook, 10, 2);

        DebugEcho("fetch_mail: Connecting to $server:$port ($protocol)");

        $config = postie_config_read();

        try {
            $mailbox = $this->get_mailbox($server, $port, $email, $password, $protocol, $config);
            $messages = $mailbox->listMessages($maxemails);
            $result['total'] = count($messages);

            /* translators: %d: number of messages */
            DebugEcho(sprintf(__("fetch_mail: There are %d messages to process", 'postie'), count($messages)));

            DebugDump($messages);

            $message_number = 0;
            foreach ($messages as $message) {
                $config = postie_config_read(); //refresh config in case PostieMessage altered it

                $message_number++;
                DebugEcho("fetch_mail: $message_number: ------------------------------------");
                DebugEcho("fetch_mail: fetch {$message['uid']}");

                $email = null;
                try {
                    $email = new PostieMessage($mailbox->fetchMessage($message['uid']), $config);

                    if ($email->is_email_empty()) {
                        $message = __('Dang, message is empty!', 'postie');
                        EchoError("fetch_mail: $message_number: ");
                        DebugDump($message);
                        $result['skipped']++;
                        continue;
                    } else if ($email->is_email_read()) {
                        $message = __("Message is already marked 'read'.", 'postie');
                        DebugEcho("fetch_mail: $message_number");
                        DebugDump($message);
                        $result['skipped']++;
                        continue;
                    }

                    $email->preprocess();
                    $email->process();
                    $email->postprocess();

                    $result['processed']++;
                    DebugEcho("fetch_mail: $message_number: processed");

                    if ($deleteMessages) {
                        DebugEcho("fetch_mail: deleting {$message['uid']}");
                        $mailbox->deleteMessages($message['uid']);
                    }
                } catch (Throwable $e) {
                    EchoError("fetch_mail message error: " . $e->getMessage());
                    $result['errors'][] = $e->getMessage();
                    $this->log_trace($e, $password, 'fetch_mail message error');
                    if ($email !== null) {
                        $email->cleanup_after_error();
                    }
                }
            }

            DebugEcho("fetch_mail: closing connection");
            $mailbox->close();

            DebugEcho("Mail fetch complete, $message_number emails");
        } catch (Throwable $e) {
            EchoError("fetch_mail: " . $e->getMessage());
            $result['success'] = false;
            $result['errors'][] = $e->getMessage();
        }

        remove_action('postie_post_after', $posts_hook);
        remove_action('postie_comment_after', $comments_hook, 10);

        $this->last_run_result = $result;
        return $result;
    }

    function postie_environment_encoding($force_display = false) {
        $default_charset = ini_get('default_charset');
        if (version_compare(phpversion(), '5.6.0', '<')) {
            if (empty($default_charset)) {
                DebugEcho("default_charset: WARNING no default_charset set see http://php.net/manual/en/ini.core.php#ini.default-charset", true);
            } else {
                DebugEcho("default_charset: $default_charset", $force_display);
            }
        } else {
            if (empty($default_charset)) {
                DebugEcho("default_charset: UTF-8 (default)", $force_display);
            } else {
                DebugEcho("default_charset: $default_charset", $force_display);
            }
        }

        if (defined('DB_CHARSET')) {
            DebugEcho("DB_CHARSET: " . DB_CHARSET, $force_display);
        } else {
            DebugEcho("DB_CHARSET: undefined (utf8)", $force_display);
        }

        if (defined('DB_COLLATE')) {
            $db_collate = DB_COLLATE;
            if (empty($db_collate)) {
                DebugEcho("DB_COLLATE: database default", $force_display);
            } else {
                DebugEcho("DB_COLLATE: " . DB_COLLATE, $force_display);
            }
        }

        DebugEcho("WordPress encoding: " . esc_attr(get_option('blog_charset')), $force_display);
    }

    function postie_environment($force_display = false) {
        global $g_postie_init;
        global $wpdb;

        DebugEcho("OS: " . PHP_OS, $force_display);
        DebugEcho("PHP version: " . phpversion(), $force_display);
        DebugEcho("PHP error_log: " . ini_get('error_log'), $force_display);
        DebugEcho("PHP log_errors: " . (ini_get('log_errors') ? 'On' : 'Off'), $force_display);
        DebugEcho("PHP get_temp_dir: " . get_temp_dir(), $force_display);
        DebugEcho("PHP disable_functions: " . ini_get('disable_functions'), $force_display);
        if (function_exists('curl_version')) {
            $cv = curl_version();
            DebugEcho("PHP cURL version: " . $cv['version'], $force_display);
        }

        DebugEcho("PHP Multibyte String support: " . (function_exists('mb_detect_encoding') ? 'yes' : 'No'), $force_display);

        DebugEcho("MySQL Version: " . $wpdb->db_version(), $force_display);
        DebugEcho("MySQL client: " . ($wpdb->use_mysqli ? 'mysqli' : 'mysql'), $force_display);
        $charset = $wpdb->get_col_charset($wpdb->posts, 'post_content');
        DebugEcho("$wpdb->posts charset: $charset", $force_display);

        DebugEcho("WordPress Version: " . get_bloginfo('version'), $force_display);
        if (defined('MULTISITE') && MULTISITE) {
            DebugEcho("WordPress Multisite", $force_display);
            DebugEcho("network_home_url(): " . network_home_url(), $force_display);
        } else {
            DebugEcho("WordPress Singlesite", $force_display);
        }
        DebugEcho("WP_TEMP_DIR: " . (defined('WP_TEMP_DIR') ? WP_TEMP_DIR : '(none)'), $force_display);
        DebugEcho("WP_HOME: " . (defined('WP_HOME') ? WP_HOME : '(none)'), $force_display);
        DebugEcho("home_url(): " . home_url(), $force_display);
        DebugEcho("WP_SITEURL: " . (defined('WP_SITEURL') ? WP_SITEURL : '(none)'), $force_display);
        DebugEcho("site_url(): " . site_url(), $force_display);

        if (defined('WP_DEBUG')) {
            DebugEcho("WP_DEBUG: " . (WP_DEBUG === true ? 'On' : 'Off'), $force_display);
        } else {
            DebugEcho("WP_DEBUG: Off", $force_display);
        }

        if (defined('WP_DEBUG_DISPLAY')) {
            if (null == WP_DEBUG_DISPLAY) {
                DebugEcho("WP_DEBUG_DISPLAY: null", $force_display);
            } else {
                DebugEcho("WP_DEBUG_DISPLAY: " . (WP_DEBUG_DISPLAY === true ? 'On' : 'Off'), $force_display);
            }
        } else {
            DebugEcho("WP_DEBUG_DISPLAY: Off", $force_display);
        }

        if (defined('WP_DEBUG_LOG')) {
            DebugEcho("WP_DEBUG_LOG: " . (WP_DEBUG_LOG === true ? 'On' : 'Off'), $force_display);
        } else {
            DebugEcho("WP_DEBUG_LOG: Off", $force_display);
        }

        if (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON) {
            DebugEcho("Alternate cron is enabled", $force_display);
        }

        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            DebugEcho("WordPress cron is disabled. Postie will not run unless you have an external cron set up.", $force_display);
        }

        if (defined('WP_MAX_MEMORY_LIMIT')) {
            DebugEcho("WP_MAX_MEMORY_LIMIT: " . WP_MAX_MEMORY_LIMIT, $force_display);
        } else {
            DebugEcho("WP_MAX_MEMORY_LIMIT: 256M (default)", $force_display);
        }

        if (defined('WP_MEMORY_LIMIT')) {
            DebugEcho("WP_MEMORY_LIMIT: " . WP_MEMORY_LIMIT, $force_display);
        } else {
            DebugEcho("WP_MEMORY_LIMIT: 32M (default)", $force_display);
        }

        DebugEcho("imagick version: " . phpversion('imagick'), $force_display);
        DebugEcho("gd version: " . phpversion('gd'), $force_display);

        if (file_exists(ABSPATH . WPINC . '/class-wp-image-editor.php')) {
            require_once ABSPATH . WPINC . '/class-wp-image-editor.php';
        }
        if (file_exists(ABSPATH . WPINC . '/class-wp-image-editor-gd.php')) {
            require_once ABSPATH . WPINC . '/class-wp-image-editor-gd.php';
        }
        if (file_exists(ABSPATH . WPINC . '/class-wp-image-editor-imagick.php')) {
            require_once ABSPATH . WPINC . '/class-wp-image-editor-imagick.php';
        }
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        $implementations = apply_filters('wp_image_editors', array('WP_Image_Editor_Imagick', 'WP_Image_Editor_GD'));
        foreach ($implementations as $implementation) {
            if (!class_exists($implementation) || !call_user_func(array($implementation, 'test'))) {
                DebugEcho("image editor not supported: $implementation", $force_display);
                continue;
            } else {
                DebugEcho("image editor supported: $implementation", $force_display);

                foreach (array('image/jpeg', 'image/png', 'image/gif') as $mtype) {
                    if (!call_user_func(array($implementation, 'supports_mime_type'), $mtype)) {
                        DebugEcho("$implementation does not support: $mtype", $force_display);
                    } else {
                        DebugEcho("$implementation supports: $mtype", $force_display);
                    }
                }
            }
        }

        DebugEcho("Registered image sizes", $force_display);
        DebugDump(get_intermediate_image_sizes());
        $this->show_filters_for('image_downsize');
        $this->show_filters_for('wp_handle_upload');
        $this->show_filters_for('wp_get_attachment_thumb_file');
        $this->show_filters_for('wp_handle_upload_prefilter');
        $this->show_filters_for('wp_handle_sideload_prefilter');
        $this->show_filters_for('pre_move_uploaded_file');

        $max_memory = defined('WP_MAX_MEMORY_LIMIT') ? WP_MAX_MEMORY_LIMIT : '256M';
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        DebugEcho("image memory limit: " . apply_filters('image_memory_limit', $max_memory), $force_display);

        DebugEcho("DISABLE_WP_CRON: " . (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === true ? 'On' : 'Off'), $force_display);
        DebugEcho("ALTERNATE_WP_CRON: " . (defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON === true ? 'On' : 'Off'), $force_display);

        if (defined('WP_CRON_LOCK_TIMEOUT')) {
            DebugEcho("WP_CRON_LOCK_TIMEOUT: " . WP_CRON_LOCK_TIMEOUT, $force_display);
        }

        if ($g_postie_init->postie_IsIconvInstalled()) {
            DebugEcho("iconv: present", $force_display);
        } else {
            DebugEcho("iconv: missing", $force_display);
        }

        DebugEcho('Active plugins', $force_display);
        $plugins = get_option('active_plugins');
        foreach ($plugins as $plugin) {
            $plugin_data = get_file_data(WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin, array('Version' => 'Version'), false);
            $plugin_version = $plugin_data['Version'];
            DebugEcho("     $plugin => $plugin_version", $force_display);
        }

        DebugEcho("Postie is in " . plugin_dir_path(__FILE__), $force_display);
        DebugEcho("Postie Version: " . POSTIE_VERSION, $force_display);
        DebugEcho("POSTIE_DEBUG: " . (self::is_debugmode() ? 'On' : 'Off'), $force_display);

        $this->show_filters_for('postie_filter_email');
        $this->show_filters_for('postie_filter_email2');
        $this->show_filters_for('postie_filter_email3');
        $this->show_filters_for('postie_author');
        $this->show_filters_for('postie_post_before');
        $this->show_filters_for('postie_post_after');
        $this->show_filters_for('postie_file_added');
        $this->show_filters_for('postie_gallery');
        $this->show_filters_for('postie_comment_before');
        $this->show_filters_for('postie_comment_after');
        $this->show_filters_for('postie_category_default');
        $this->show_filters_for('postie_log_debug');
        $this->show_filters_for('postie_log_error');
        $this->show_filters_for('postie_session_start');
        $this->show_filters_for('postie_session_end');
        $this->show_filters_for('postie_preconnect');
        $this->show_filters_for('postie_post_pre');
        $this->show_filters_for('postie_email_reject_recipients');
        $this->show_filters_for('postie_email_notify_recipients');
        $this->show_filters_for('postie_email_reject_subject');
        $this->show_filters_for('postie_email_notify_subject');
        $this->show_filters_for('postie_email_reject_body');
        $this->show_filters_for('postie_place_media');
        $this->show_filters_for('postie_place_media_before');
        $this->show_filters_for('postie_place_media_after');
        $this->show_filters_for('postie_raw');
        $this->show_filters_for('postie_bare_link');
        $this->show_filters_for('postie_category');
        $this->show_filters_for('postie_file_added_pre');
        $this->show_filters_for('postie_include_attachment');
    }

    public static function is_debugmode() {
        return defined('POSTIE_DEBUG') && POSTIE_DEBUG;
    }

    function setup_logging($config) {
        if ($config->postie_log_error || self::is_debugmode()) {
            add_action('postie_log_error', array($this, 'log_error'));
        }
        if ($config->postie_log_debug && !self::is_debugmode()) {
            define('POSTIE_DEBUG', true);
        }
        if ($config->postie_log_debug || self::is_debugmode()) {
            add_action('postie_log_debug', array($this, 'log_debug'));
        }
    }

    function log_onscreen($data) {
        if (php_sapi_name() == 'cli') {
            echo esc_html($data) . "\n";
        } else {
            echo "<pre>" . esc_html($data) . "</pre>\n";
        }
    }

    public static function redact_trace(Throwable $e, $password = '') {
        $trace = $e->getTraceAsString();
        if (empty($password)) {
            $config = postie_config_read();
            if (!empty($config->mail_password)) {
                $password = $config->mail_password;
            }
        }
        if (!empty($password) && is_string($password)) {
            $trace = str_replace($password, '********', $trace);
            $max_len = intval(ini_get('zend.exception_string_param_max_len'));
            if ($max_len <= 0) {
                $max_len = 15;
            }
            if (strlen($password) > $max_len) {
                $trace = str_replace(substr($password, 0, $max_len) . '...', '********', $trace);
            }
            if (strlen($password) > 15) {
                $trace = str_replace(substr($password, 0, 15) . '...', '********', $trace);
            }
        }
        return $trace;
    }

    function log_trace(Throwable $e, $password = '', $context = '') {
        $trace = self::redact_trace($e, $password);
        $prefix = !empty($context) ? "Postie [error]: $context trace:\n" : "Postie [error]: Exception trace:\n";
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log($prefix . $trace);
    }

    function log_error($v) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log("Postie [error]: $v");
        $this->log_onscreen($v);
    }

    function log_debug($data) {
        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log("Postie [debug]: $data");
    }

    function connection_info($config) {
        $conninfo = array();
        $conninfo['mail_server'] = $config->mail_server;
        $conninfo['mail_port'] = $config->mail_server_port;
        $conninfo['mail_user'] = $config->mail_userid;
        $conninfo['mail_password'] = $config->mail_password;
        $conninfo['mail_protocol'] = $config->input_protocol;
        $conninfo['mail_tls'] = $config->email_tls;
        $conninfo['email_delete_after_processing'] = $config->delete_mail_after_processing;
        $conninfo['email_max'] = $config->maxemails;
        $conninfo['email_ignore_state'] = $config->ignore_mail_state;

        return apply_filters('postie_preconnect', $conninfo);
    }

    function test_config() {

        wp_get_current_user();

        if (!current_user_can('manage_options')) {
            DebugEcho('non-admin tried to set options');
            echo '<h2> Sorry only admin can run this file</h2>';
            exit();
        }

        $config = postie_config_read();
        $this->setup_logging($config);
        ?>
        <div class="wrap"> 
            <h1>Postie Configuration Test</h1>
            <?php
            $this->postie_environment(true);
            ?>

            <h2>Clock</h2>
            <p>This shows what time it would be if you posted right now</p>
            <?php
            $wptz = get_option('gmt_offset');
            $wptzs = get_option('timezone_string');
            DebugEcho("Wordpress timezone: $wptzs ($wptz)", true);
            DebugEcho("Current time: " . current_time('mysql'), true);
            DebugEcho("Current time (gmt): " . current_time('mysql', 1), true);
            DebugEcho("Postie time correction: {$config->time_offset}", true);
            $offsetdate = strtotime(current_time('mysql')) + ($config->use_time_offset ? $config->time_offset * 3600 : 0);

            DebugEcho("Post time: " . gmdate('Y-m-d H:i:s', $offsetdate), true);
            ?>
            <h2>Encoding</h2>
            <?php
            $this->postie_environment_encoding(true);
            ?>

            <h2>Connect to Mail Host</h2>
            <?php
            DebugEcho("Postie connection: " . $config->input_connection, true);
            DebugEcho("Postie protocol: " . $config->input_protocol, true);
            DebugEcho("Postie server: " . $config->mail_server, true);
            DebugEcho("Postie port: " . $config->mail_server_port, true);

            $this->run_config_test();
            ?>
        </div>
        <?php
        DebugEcho("Test complete");
    }

    function run_config_test() {
        $config = postie_config_read();
        $this->setup_logging($config);

        $wptz = get_option('gmt_offset');
        $wptzs = get_option('timezone_string');
        $offsetdate = strtotime(current_time('mysql')) + ($config->use_time_offset ? $config->time_offset * 3600 : 0);

        $result = array(
            'connect_success' => false,
            'connection_msg'  => '',
            'message_count'   => 0,
            'server_settings' => array(
                'server'     => $config->mail_server,
                'port'       => $config->mail_server_port,
                'user'       => $config->mail_userid,
                'protocol'   => $config->input_protocol,
                'connection' => $config->input_connection,
            ),
            'clock' => array(
                'timezone'     => $wptzs ? "$wptzs ($wptz)" : "$wptz",
                'current_time' => current_time('mysql'),
                'gmt_time'     => current_time('mysql', 1),
                'offset'       => $config->time_offset,
                'post_time'    => gmdate('Y-m-d H:i:s', $offsetdate),
            ),
            'environment' => array(
                'os'          => PHP_OS,
                'php_version' => phpversion(),
                'wp_version'  => get_bloginfo('version'),
                'charset'     => ini_get('default_charset') ? ini_get('default_charset') : 'UTF-8',
            ),
            'errors' => array(),
        );

        if (!$config->mail_server || !$config->mail_server_port || !$config->mail_userid) {
            $msg = __("Server settings not complete. Please check server, port, and user ID.", 'postie');
            EchoError("FAIL - $msg");
            $result['errors'][] = $msg;
            return $result;
        }

        $conninfo = $this->connection_info($config);
        if (self::is_debugmode()) {
            fCore::enableDebugging(true);
            fCore::registerDebugCallback('DebugEcho');
        }

        switch (strtolower($config->input_protocol)) {
            case 'imap':
            case 'imap-ssl':
                try {
                    if ($config->input_connection == 'curl') {
                        $conn = new pCurlConnection('imap', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'));
                    } else {
                        $conn = new pSocketConnection('imap', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'));
                    }
                    $srv = new pImapMailServer($conn);
                    $mailbox = new fMailbox('imap', $conn, $srv);
                    $m = $mailbox->countMessages();
                    $result['connect_success'] = true;
                    $result['message_count'] = $m;
                    $result['connection_msg'] = sprintf(__("Successful %s connection on port %s", 'postie'), strtoupper($config->input_protocol), $config->mail_server_port);
                    DebugEcho($result['connection_msg'], true);
                    DebugEcho(sprintf(__("# of waiting messages: %d", 'postie'), $m), true);
                    $mailbox->close();
                } catch (Throwable $e) {
                    $msg = sprintf(__("Unable to connect. The server said: %s", 'postie'), $e->getMessage());
                    EchoError($msg);
                    $result['errors'][] = $msg;
                }
                break;

            case 'pop3':
            case 'pop3-ssl':
                try {
                    if ($config->input_connection == 'curl') {
                        $conn = new pCurlConnection('pop3', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'), 30);
                    } else {
                        $conn = new pSocketConnection('pop3', $conninfo['mail_server'], $conninfo['mail_user'], $conninfo['mail_password'], $conninfo['mail_port'], ($conninfo['mail_protocol'] == 'imap-ssl' || $conninfo['mail_protocol'] == 'pop3-ssl'));
                    }
                    $srv = new pPop3MailServer($conn);
                    $mailbox = new fMailbox('pop3', $conn, $srv);
                    $m = $mailbox->countMessages();
                    $result['connect_success'] = true;
                    $result['message_count'] = $m;
                    $result['connection_msg'] = sprintf(__("Successful %s connection on port %s", 'postie'), strtoupper($config->input_protocol), $config->mail_server_port);
                    DebugEcho($result['connection_msg'], true);
                    DebugEcho(sprintf(__("# of waiting messages: %d", 'postie'), $m), true);
                    $mailbox->close();
                } catch (Throwable $e) {
                    $msg = sprintf(__("Unable to connect. The server said: %s", 'postie'), $e->getMessage());
                    EchoError($msg);
                    $result['errors'][] = $msg;
                }
                break;

            default:
                $msg = __("Unable to connect. Invalid setup", 'postie');
                EchoError($msg);
                $result['errors'][] = $msg;
                break;
        }

        return $result;
    }
}

global $g_postie; //need to declare as global for wp cli
$g_postie = new Postie();
