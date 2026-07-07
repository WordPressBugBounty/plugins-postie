<?php

/**
 * Strongly-Typed Configuration Data Transfer Object (DTO) for Postie Settings.
 *
 * NOTE ON BACKWARDS COMPATIBILITY:
 * Although 100% of internal codebase configuration accesses have been migrated to the
 * modern strongly-typed property syntax (e.g. `$config->post_type`), this class MUST continue 
 * to implement \ArrayAccess.
 *
 * Postie passes this `$config` object as an argument into numerous external WordPress filters
 * and action hooks (e.g., `postie_post_before`, `postie_post_after`, and attachment filters).
 * User-defined custom filters (e.g., in `filterPostie.php`) or third-party add-ons expect 
 * to read these configurations using legacy array lookups (e.g., `$config['prefer_text_type']`).
 * Removing this interface would cause fatal runtime type errors in those custom extensions.
 */
class PostieSettings implements \ArrayAccess {
    public string $add_meta = 'no';
    public string $admin_username = 'admin';
    public bool $allow_html_in_body = true;
    public bool $allow_html_in_subject = true;
    public bool $allow_subject_in_mail = true;
    public string $audiotemplate = '';
    public array $audiotypes = array('m4a', 'mp3', 'ogg', 'wav', 'mpeg');
    public array $authorized_addresses = array();
    public array $banned_files_list = array();
    public string $confirmation_email = '';
    public bool $convertnewline = false;
    public bool $converturls = true;
    public bool $custom_image_field = false;
    public mixed $default_post_category = null;
    public bool $category_match = true;
    public array $default_post_tags = array();
    public string $default_title = "Live From The Field";
    public bool $delete_mail_after_processing = true;
    public bool $drop_signature = true;
    public bool $filternewlines = true;
    public bool $forward_rejected_mail = true;
    public string $icon_set = 'silver';
    public int $icon_size = 32;
    public bool $auto_gallery = false;
    public bool $image_new_window = false;
    public string $image_placeholder = '#img%#';
    public mixed $images_append = true;
    public string $imagetemplate = '';
    public array $imagetemplates = array();
    public string $input_protocol = 'pop3';
    public string $input_connection = 'sockets';
    public string $interval = 'twiceperhour';
    public bool $keep_unknown_emails_as_draft = true;
    public ?string $mail_server = null;
    public int $mail_server_port = 110;
    public ?string $mail_userid = null;
    public ?string $mail_password = null;
    public int $maxemails = 0;
    public string $message_start = '';
    public string $message_end = '';
    public string $message_encoding = 'UTF-8';
    public bool $message_dequote = true;
    public string $post_status = 'publish';
    public string $prefer_text_type = 'plain';
    public bool $return_to_sender = false;
    public array $role_access = array();
    public string $selected_audiotemplate = 'simple_link';
    public string $selected_imagetemplate = 'wordpress_default';
    public string $selected_video1template = 'vshortcode';
    public string $selected_video2template = 'simple_link';
    public bool $shortcode = false;
    public array $sig_pattern_list = array('--\s?[\r\n]?', '--\s', '--', '---');
    public array $smtp = array();
    public bool $start_image_count_at_zero = false;
    public array $supported_file_types = array('application');
    public bool $turn_authorization_off = false;
    public float $time_offset = 0.0;
    public string $video1template = '';
    public array $video1types = array('mp4', 'mpeg4', '3gp', '3gpp', '3gpp2', '3gp2', 'mov', 'mpeg', 'quicktime');
    public string $video2template = '';
    public array $video2types = array('x-flv');
    public array $video1templates = array();
    public array $video2templates = array();
    public string $wrap_pre = 'no';
    public bool $featured_image = false;
    public bool $include_featured_image = true;
    public bool $email_tls = false;
    public string $post_format = 'standard';
    public string $post_type = 'post';
    public array $generaltemplates = array();
    public string $generaltemplate = '';
    public string $selected_generaltemplate = 'postie_default';
    public bool $generate_thumbnails = true;
    public bool $reply_as_comment = true;
    public bool $force_user_login = false;
    public string $auto_gallery_link = 'default';
    public bool $ignore_mail_state = false;
    public bool $strip_reply = true;
    public bool $postie_log_error = true;
    public bool $postie_log_debug = false;
    public bool $category_colon = true;
    public bool $category_dash = true;
    public bool $category_bracket = true;
    public bool $prefer_text_convert = true;
    public bool $category_remove = true;
    public bool $ignore_email_date = false;
    public bool $use_time_offset = false;
    public string $postie_log_error_notify = '(All Admins)';
    public bool $image_resize = true;
    public bool $duplicate_comments = true;
    public bool $legacy_commands = true;
    public bool $add_wrapper_div = true;

    public function __construct(array $data) {
        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $this->castValue($key, $val);
            }
        }
    }

    private function castValue(string $key, $value) {
        if ($value === null) {
            if (in_array($key, array('default_post_category', 'mail_server', 'mail_userid', 'mail_password'))) {
                return null;
            }
        }

        $boolKeys = array(
            'allow_html_in_body', 'allow_html_in_subject', 'allow_subject_in_mail', 'convertnewline', 'converturls',
            'custom_image_field', 'category_match', 'delete_mail_after_processing', 'drop_signature', 'filternewlines',
            'forward_rejected_mail', 'auto_gallery', 'image_new_window', 'message_dequote', 'return_to_sender',
            'shortcode', 'start_image_count_at_zero', 'turn_authorization_off', 'featured_image', 'include_featured_image',
            'email_tls', 'generate_thumbnails', 'reply_as_comment', 'force_user_login', 'ignore_mail_state', 'strip_reply',
            'postie_log_error', 'postie_log_debug', 'category_colon', 'category_dash', 'category_bracket',
            'prefer_text_convert', 'category_remove', 'ignore_email_date', 'use_time_offset', 'image_resize',
            'duplicate_comments', 'legacy_commands', 'add_wrapper_div', 'keep_unknown_emails_as_draft'
        );

        $intKeys = array('icon_size', 'mail_server_port', 'maxemails');
        $floatKeys = array('time_offset');
        $arrayKeys = array(
            'audiotypes', 'authorized_addresses', 'banned_files_list', 'default_post_tags', 'imagetemplates',
            'role_access', 'sig_pattern_list', 'smtp', 'supported_file_types', 'video1types', 'video2types',
            'video1templates', 'video2templates', 'generaltemplates'
        );

        if (in_array($key, $boolKeys)) {
            if ($value === 'yes' || $value === '1' || $value === 1 || $value === true) {
                return true;
            }
            if ($value === 'no' || $value === '0' || $value === 0 || $value === false) {
                return false;
            }
            return (bool)$value;
        }

        if (in_array($key, $intKeys)) {
            return (int)$value;
        }

        if (in_array($key, $floatKeys)) {
            return (float)$value;
        }

        if (in_array($key, $arrayKeys)) {
            return (array)$value;
        }

        return (string)$value;
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset): bool {
        return property_exists($this, $offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset) {
        if (property_exists($this, $offset)) {
            return $this->$offset;
        }
        return null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value): void {
        if (property_exists($this, $offset)) {
            $this->$offset = $this->castValue($offset, $value);
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset): void {
        // Strongly typed properties are not unset.
    }

    public function toArray(): array {
        return get_object_vars($this);
    }
}

class PostieConfig {

    /**
     *
     * @var PostieSettings
     */
    private $config;

    function __construct() {
        $this->config = $this->config_fetch();
    }

    function config_fetch() {
        $config = get_option('postie-settings');
        return new PostieSettings($this->normalize_settings($config));
    }

    /**
     * Alias to support legacy registered validation callback in postie.php
     */
    function config_ValidateSettings($in) {
        return $this->validate_settings($in);
    }

    /**
     * 
     * @return PostieSettings
     */
    function config_read() {
        return $this->config;
    }

    /**
     * normalizes and sanitizes settings to be used in the PostieSettings DTO
     */
    function normalize_settings($in) {
        $out = array();

        // use the default as a template: 
        // if a field is present in the defaults, we want to store it; otherwise we discard it
        $allowed_keys = $this->defaults();
        foreach ($allowed_keys as $key => $default) {
            if (is_array($in)) {
                $out[$key] = array_key_exists($key, $in) ? $in[$key] : $default;
            } else {
                $out[$key] = $default;
            }
        }

        // some fields are always forced to lower case:
        $lowercase = array('authorized_addresses', 'smtp', 'supported_file_types', 'video1types', 'video2types', 'audiotypes');
        foreach ($lowercase as $field) {
            $out[$field] = ( is_array($out[$field]) ) ? array_map("strtolower", $out[$field]) : strtolower($out[$field]);
        }
        $arrays = $this->arrayed_settings();

        foreach ($arrays as $sep => $fields) {
            foreach ($fields as $field) {
                if (!is_array($out[$field])) {
                    $out[$field] = explode($sep, trim($out[$field]));
                }
                foreach ($out[$field] as $key => $val) {
                    $tst = trim($val);
                    if (empty($tst)) {
                        unset($out[$field][$key]);
                    } else {
                        $out[$field][$key] = $tst;
                    }
                }
            }
        }

        $out['message_encoding'] = 'UTF-8'; //force to UTF-8;
        return $out;
    }

    /**
     * validates the config form output, fills in any gaps by using the defaults,
     * and ensures that arrayed items are stored as such, while triggering necessary
     * side effects (permissions and cron) only on save.
     */
    function validate_settings($in) {
        //DebugEcho("config_ValidateSettings");
        $out = $this->normalize_settings($in);
        $this->fix_permission_cron($out);
        return $out;
    }

    /**
     * This function used to handle updating the configuration.
     * @return boolean
     */
    function fix_permission_cron($data) {
        $this->update_permissions($data['role_access']);
        // We also update the cron settings
        PostieInit::postie_cron_hook($data['interval']);
    }

    /**
     * This function handles setting up the basic permissions
     */
    function update_permissions($role_access) {
        global $wp_roles;
        if (is_object($wp_roles)) {
            $admin = $wp_roles->get_role('administrator');
            if (!empty($admin)) {
                $admin->add_cap('config_postie');
                $admin->add_cap('post_via_postie');

                if (!is_array($role_access)) {
                    $role_access = array();
                }
                foreach ($wp_roles->role_names as $roleId => $name) {
                    $role = $wp_roles->get_role($roleId);
                    if ($roleId != 'administrator') {
                        if (array_key_exists($roleId, $role_access)) {
                            $role->add_cap('post_via_postie');
                            //DebugEcho("added $roleId");
                        } else {
                            $role->remove_cap('post_via_postie');
                            //DebugEcho("removed $roleId");
                        }
                    }
                }
            }
        }
    }

    /**
     * Returns a list of config keys that should be arrays
     * @return array
     */
    function arrayed_settings() {
        return array(
            ', ' => array('audiotypes', 'video1types', 'video2types', 'default_post_tags'),
            "\n" => array('smtp', 'authorized_addresses', 'supported_file_types', 'banned_files_list', 'sig_pattern_list'));
    }

    /**
     * return an array of the config defaults
     */
    function defaults() {
        include('templates/audio_templates.php');
        include('templates/image_templates.php');
        include('templates/video1_templates.php');
        include('templates/video2_templates.php');
        include 'templates/general_template.php';
        return array(
            'add_meta' => 'no',
            'admin_username' => 'admin',
            'allow_html_in_body' => true,
            'allow_html_in_subject' => true,
            'allow_subject_in_mail' => true,
            'audiotemplate' => $simple_link,
            'audiotypes' => array('m4a', 'mp3', 'ogg', 'wav', 'mpeg'),
            'authorized_addresses' => array(),
            'banned_files_list' => array(),
            'confirmation_email' => '',
            'convertnewline' => false,
            'converturls' => true,
            'custom_image_field' => false,
            'default_post_category' => NULL,
            'category_match' => true,
            'default_post_tags' => array(),
            'default_title' => "Live From The Field",
            'delete_mail_after_processing' => true,
            'drop_signature' => true,
            'filternewlines' => true,
            'forward_rejected_mail' => true,
            'icon_set' => 'silver',
            'icon_size' => 32,
            'auto_gallery' => false,
            'image_new_window' => false,
            'image_placeholder' => '#img%#',
            'images_append' => true,
            'imagetemplate' => $wordpress_default,
            'imagetemplates' => $imageTemplates,
            'input_protocol' => 'pop3',
            'input_connection' => 'sockets',
            'interval' => 'twiceperhour',
            'mail_server' => NULL,
            'mail_server_port' => 110,
            'mail_userid' => NULL,
            'mail_password' => NULL,
            'maxemails' => 0,
            'message_start' => '',
            'message_end' => '',
            'message_encoding' => 'UTF-8',
            'message_dequote' => true,
            'post_status' => 'publish',
            'prefer_text_type' => 'plain',
            'return_to_sender' => false,
            'role_access' => array(),
            'selected_audiotemplate' => 'simple_link',
            'selected_imagetemplate' => 'wordpress_default',
            'selected_video1template' => 'vshortcode',
            'selected_video2template' => 'simple_link',
            'shortcode' => false,
            'sig_pattern_list' => array('--\s?[\r\n]?', '--\s', '--', '---'),
            'smtp' => array(),
            'start_image_count_at_zero' => false,
            'supported_file_types' => array('application'),
            'turn_authorization_off' => false,
            'time_offset' => get_option('gmt_offset'),
            'video1template' => $simple_link,
            'video1types' => array('mp4', 'mpeg4', '3gp', '3gpp', '3gpp2', '3gp2', 'mov', 'mpeg', 'quicktime'),
            'video2template' => $simple_link,
            'video2types' => array('x-flv'),
            'video1templates' => $video1Templates,
            'video2templates' => $video2Templates,
            'wrap_pre' => 'no',
            'featured_image' => false,
            'include_featured_image' => true,
            'email_tls' => false,
            'post_format' => 'standard',
            'post_type' => 'post',
            'generaltemplates' => $generalTemplates,
            'generaltemplate' => $postie_default,
            'selected_generaltemplate' => 'postie_default',
            'generate_thumbnails' => true,
            'reply_as_comment' => true,
            'force_user_login' => false,
            'auto_gallery_link' => 'default',
            'ignore_mail_state' => false,
            'strip_reply' => true,
            'postie_log_error' => true,
            'postie_log_debug' => false,
            'category_colon' => true,
            'category_dash' => true,
            'category_bracket' => true,
            'prefer_text_convert' => true,
            'category_remove' => true,
            'ignore_email_date' => false,
            'use_time_offset' => false,
            'postie_log_error_notify' => '(All Admins)',
            'image_resize' => true,
            'duplicate_comments' => true,
            'legacy_commands' => true,
            'add_wrapper_div' => true,
            'keep_unknown_emails_as_draft' => true
        );
    }

    /**
     * This function resets all the configuration options to the default
     */
    function reset_to_default() {
        $newconfig = $this->defaults();
        $config = get_option('postie-settings');
        $save_keys = array('mail_password', 'mail_server', 'mail_server_port', 'mail_userid', 'input_protocol', 'input_connection');
        foreach ($save_keys as $key) {
            $newconfig[$key] = $config[$key];
        }
        update_option('postie-settings', $newconfig);
        $this->fix_permission_cron($newconfig);
        return $newconfig;
    }
}
