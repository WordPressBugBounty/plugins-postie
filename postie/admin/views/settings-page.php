<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if (!current_user_can('manage_options')) {
    wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'postie'));
}
require_once dirname(dirname(__FILE__)) . '/postie-admin.class.php';

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

if (!isset($config['maxemails'])) {
    DebugEcho(__("New setting: maxemails", 'postie'));
    $config['maxemails'] = 0;
}
if (!isset($config['category_match'])) {
    $config['category_match'] = true;
}

$settings = new PostieSettings($config);

if ($settings->interval == 'manual') {
    wp_clear_scheduled_hook('check_postie_hook');
}

$messages[1] = __("Configuration successfully updated!", 'postie');
$messages[2] = __("Error - unable to save configuration", 'postie');

$views_dir = dirname(__FILE__);
$tabs_dir  = $views_dir . '/tabs';

$default_tabs = array(
    'server'      => array(
        'label' => __('Mailserver', 'postie'),
        'view'  => $tabs_dir . '/tab-server.php',
    ),
    'user'        => array(
        'label' => __('User', 'postie'),
        'view'  => $tabs_dir . '/tab-user.php',
    ),
    'message'     => array(
        'label' => __('Message', 'postie'),
        'view'  => $tabs_dir . '/tab-message.php',
    ),
    'image'       => array(
        'label' => __('Image', 'postie'),
        'view'  => $tabs_dir . '/tab-image.php',
    ),
    'media'       => array(
        'label' => __('Video and Audio', 'postie'),
        'view'  => $tabs_dir . '/tab-media.php',
    ),
    'attachments' => array(
        'label' => __('Attachments', 'postie'),
        'view'  => $tabs_dir . '/tab-attachments.php',
    ),
    'support'     => array(
        'label' => __('Support', 'postie'),
        'view'  => $tabs_dir . '/tab-support.php',
    ),
);

$filtered_tabs = apply_filters('postie_admin_tabs', $default_tabs);
$tabs = (is_array($filtered_tabs) && !empty($filtered_tabs)) ? $filtered_tabs : $default_tabs;
?>
<div class="wrap"> 
    <h2>
        <a style='text-decoration:none' href='admin.php?page=postie-settings'>
            <?php
            echo '<img src="' . esc_url(POSTIE_URL . 'images/mail.png') . '" alt="postie" />';
            esc_html_e('Postie Settings', 'postie');
            ?>
        </a>
        <span class="description">(v<?php echo esc_html(POSTIE_VERSION); ?>)</span>
    </h2>

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
                            <div id="simpleTabs">
                                <h2 class="nav-tab-wrapper">
                                    <?php
                                    $tab_index = 1;
                                    foreach ($tabs as $tab_key => $tab_data) :
                                        if (!is_array($tab_data)) {
                                            continue;
                                        }
                                        $clean_key = sanitize_key($tab_key);
                                        $tab_label = !empty($tab_data['label']) ? $tab_data['label'] : ucfirst($clean_key);
                                        $is_active = ($tab_index === 1) ? ' nav-tab-active' : '';
                                        ?>
                                        <a href="#tab-<?php echo esc_attr($clean_key); ?>" id="simpleTabs-nav-<?php echo (int)$tab_index; ?>" data-tab="<?php echo esc_attr($clean_key); ?>" data-tab-index="<?php echo (int)$tab_index; ?>" class="nav-tab<?php echo $is_active; ?>"><?php echo esc_html($tab_label); ?></a>
                                        <?php
                                        $tab_index++;
                                    endforeach;
                                    ?>
                                </h2>

                                <?php
                                $tab_index = 1;
                                foreach ($tabs as $tab_key => $tab_data) :
                                    if (!is_array($tab_data)) {
                                        continue;
                                    }
                                    $clean_key = sanitize_key($tab_key);
                                    ?>
                                    <div id="postie-tab-content-<?php echo esc_attr($clean_key); ?>" class="simpleTabs-content" data-tab-content="<?php echo esc_attr($clean_key); ?>" data-tab-index="<?php echo (int)$tab_index; ?>"<?php if ($tab_index !== 1) echo ' style="display:none;"'; ?>>
                                        <?php
                                        if (!empty($tab_data['view']) && is_string($tab_data['view']) && file_exists($tab_data['view']) && pathinfo($tab_data['view'], PATHINFO_EXTENSION) === 'php') {
                                            include $tab_data['view'];
                                        } elseif (isset($tab_data['callback']) && is_callable($tab_data['callback'])) {
                                            call_user_func($tab_data['callback']);
                                        }
                                        do_action('postie_admin_tab_' . $clean_key);
                                        ?>
                                    </div>
                                    <?php
                                    $tab_index++;
                                endforeach;
                                ?>
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
            <?php include dirname(__FILE__) . '/settings-sidebar.php'; ?>
            <br class="clear">
        </div>
    </div>

    <!-- Process Email Modal -->
    <?php include dirname(__FILE__) . '/modal-process.php'; ?>
</div>
