<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'postie'));
}
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "postie-admin.class.php");
?>
<div class="wrap"> 
    <h2>
        <a style='text-decoration:none' href='admin.php?page=postie-settings'>
            <?php
            echo '<img src="' . esc_url(plugins_url('images/mail.png', __FILE__)) . '" alt="postie" />';
            esc_html_e('Postie Settings', 'postie');
            ?>
        </a>
        <span class="description">(v<?php echo esc_html(POSTIE_VERSION); ?>)</span>
    </h2>

    <?php
    if (isset($_POST['action'])) {
        $postie_nonce = isset($_POST['postie_action_nonce']) ? sanitize_key(wp_unslash($_POST['postie_action_nonce'])) : '';
        if (empty($postie_nonce) || !wp_verify_nonce($postie_nonce, 'postie_action_nonce_action')) {
            wp_die(esc_html__('Security check failed. Please refresh the page and try again.', 'postie'));
        }
        switch ($_POST['action']) {
            case 'reset':
                $pconfig = new PostieConfig();
                $pconfig->reset_to_default();
                $message = 1;
                break;
            case 'cronless':
                postie_check();
                $message = 1;
                break;
            case 'test':
                $g_postie->test_config();
                exit;
                break;
            case 'runpostie':
                DebugEcho(__("Checking for mail manually", 'postie'));
                postie_get_mail();
                exit;
                break;
            case 'runpostie-debug':
                DebugEcho(__("Checking for mail manually with debug output", 'postie'));
                if (!defined('POSTIE_DEBUG')) {
                    define('POSTIE_DEBUG', true);
                }
                postie_get_mail();
                exit;
                break;
            default:
                $message = 2;
                break;
        }
    }
    global $wpdb, $wp_roles; //don't remove - used in included files

    $pconfig = new PostieConfig();
    $config_dto = $pconfig->config_read();
    $config = $config_dto ? $config_dto->toArray() : array();
    if (empty($config)) {
        $config = $pconfig->reset_to_default();
    }

    $arrays = $pconfig->arrayed_settings();
    // some fields are stored as arrays, because that makes back-end processing much easier
    // and we need to convert those fields to strings here, for the options form
    foreach ($arrays as $sep => $fields) {
        foreach ($fields as $field) {
            $config[$field] = implode($sep, $config[$field]);
        }
    }
    extract($config);
    if (!isset($maxemails)) {
        DebugEcho(__("New setting: maxemails", 'postie'));
        $maxemails = 0;
    }
    if (!isset($category_match)) {
        $category_match = true;
    }

    if ($interval == 'manual') {
        wp_clear_scheduled_hook('check_postie_hook');
    }

    $messages[1] = __("Configuration successfully updated!", 'postie');
    $messages[2] = __("Error - unable to save configuration", 'postie');
    ?>
    <?php if (isset($_GET['message']) && array_key_exists((int)$_GET['message'], $messages)) : ?>
        <div class="updated"><p><?php echo esc_html($messages[(int)$_GET['message']]); ?></p></div>
    <?php endif; ?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!-- main content -->
            <div id="post-body-content" style="position: relative;">
                <div class="meta-box-sortables ui-sortable">
                    <div class="inside">
                        <form name="postie-options" method="post" action='options.php' autocomplete="off">
                            <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                            <input style="display:none" type="text" name="fakeusernameremembered"/>
                            <input style="display:none" type="password" name="fakepasswordremembered"/>

                            <?php settings_fields('postie-settings'); ?>
                            <input type="hidden" name="action" value="config" />
                            <div id="simpleTabs">
                                <h2 class="nav-tab-wrapper">
                                    <a href="#" id="simpleTabs-nav-1" data-tab="1" class="nav-tab nav-tab-active"><?php esc_html_e('Mailserver', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-2" data-tab="2" class="nav-tab"><?php esc_html_e('User', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-3" data-tab="3" class="nav-tab"><?php esc_html_e('Message', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-4" data-tab="4" class="nav-tab"><?php esc_html_e('Image', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-5" data-tab="5" class="nav-tab"><?php esc_html_e('Video and Audio', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-6" data-tab="6" class="nav-tab"><?php esc_html_e('Attachments', 'postie') ?></a>
                                    <a href="#" id="simpleTabs-nav-7" data-tab="7" class="nav-tab"><?php esc_html_e('Support', 'postie') ?></a>
                                </h2>

                                <?php include 'config_form_server.php'; ?>

                                <?php include 'config_form_user.php'; ?>

                                <?php include 'config_form_message.php'; ?>

                                <?php include 'config_form_image.php'; ?>

                                <?php include 'config_form_video.php'; ?>

                                <?php include 'config_form_attachments.php'; ?>

                                <?php include 'config_form_support.php'; ?>
                            </div>

                            <p class="submit" style="clear: both;">
                                <input type="hidden" name="action" value="update" />
                                <input type="hidden" name="page_options" value="postie-settings" />
                                <input type="submit" name="Submit" value="<?php esc_attr_e('Save Changes', 'postie') ?>" class="button button-primary" />
                            </p>
                        </form> 
                    </div>
                </div>
            </div>

            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables ui-sortable">

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>Actions</span></h3>
                        <div class="inside">
                            <div class="submitbox">
                                <p><?php esc_html_e( 'To run the check mail script manually', 'postie' ); ?></p>
                                <form name="postie-options" method='post'> 
                                    <?php wp_nonce_field('postie_action_nonce_action', 'postie_action_nonce'); ?>
                                    <input type="hidden" name="action" value="runpostie" />
                                    <input name="Submit" value="<?php esc_attr_e( 'Process Email', 'postie' ); ?>" type="submit" class='button'>
                                </form>

                                <p><?php esc_html_e( 'To run the check mail script manually with full debug output', 'postie' ); ?></p>
                                <form name="postie-options" method='post'> 
                                    <?php wp_nonce_field('postie_action_nonce_action', 'postie_action_nonce'); ?>
                                    <input type="hidden" name="action" value="runpostie-debug" />
                                    <input name="Submit" value="<?php esc_attr_e( 'Debug', 'postie' ); ?>" type="submit" class='button'>
                                </form>

                                <p><?php esc_html_e( 'Test your configuration (save first)', 'postie' ); ?></p>
                                <form name="postie-options" method="post">
                                    <?php wp_nonce_field('postie_action_nonce_action', 'postie_action_nonce'); ?>
                                    <input type="hidden" name="action" value="test" />
                                    <input name="Submit" value="<?php esc_attr_e( 'Test Config', 'postie' ); ?>" type="submit" class='button'>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>Getting Started</span></h3>
                        <div class="inside">
                            <p>Be sure and check out the <a href="http://postieplugin.com/getting-started/" target="_blank">getting started</a> guide.</p>
                            <p>Please use the Postie <a href="https://wordpress.org/support/plugin/postie" target="_blank">support forums</a> if you need help.</p>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>AddOns</span></h3>
                        <div class="inside">
                            <p>There are a number of different AddOns available to extend Postie's functionality.
                                See <a href='http://postieplugin.com/add-ons/' target='_blank'>the list</a> for more information.</p>                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle ui-sortable-handle"><span>Donations</span></h3>

                        <div class="inside">
                            <p style="font-weight: bolder; margin-top: 0px; margin-bottom: 2px;"><?php esc_html_e( 'Please Donate, Every $ Helps!', 'postie' ); ?></p>
                            <p style="margin-top: 0;margin-bottom: 2px;"><?php esc_html_e( 'Your generous donation allows me to continue developing Postie for the WordPress community.', 'postie' ); ?></p>
                            <form style="" action="https://www.paypal.com/cgi-bin/webscr" method="post">
                                <input type="hidden" name="cmd" value="_s-xclick">
                                <input type="hidden" name="hosted_button_id" value="HPK99BJ88V4C2">
                                <div style="text-align:center;">
                                    <input style="border: none; margin: 0;" type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer, easier way to pay online!">
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            <br class="clear">
        </div>
    </div>
</div>
