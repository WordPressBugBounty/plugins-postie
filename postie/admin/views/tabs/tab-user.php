<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
    <table class='form-table'>

        <tr>
            <th scope="row">
                <?php esc_html_e('Roles That Can Publish Posts', 'postie') ?><br />
            </th>
            <td>
                <table class="checkbox-table">
                    <?php
                    foreach ($wp_roles->role_names as $roleId => $name) {
                        $is_admin = ($roleId === 'administrator');
                        $checked  = $is_admin || $wp_roles->get_role($roleId)->has_cap('post_via_postie');
                        $name_attr = $is_admin ? '' : " name='postie-settings[role_access][" . esc_attr($roleId) . "]'";
                        $disabled = $is_admin ? " disabled='disabled'" : '';
                        ?>
                        <tr>
                            <td>
                                <input type='checkbox' value='1'<?php echo $name_attr . $disabled; ?><?php checked($checked); ?>>
                                <?php echo esc_html(translate_user_role($name)); ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
                <p class='description'><?php esc_html_e("This allows you to grant access to other users to publish posts if they have the proper access level. Administrators can always publish posts. Email addresses that don't exist or don't have permissions will create posts with draft status so you can review.", 'postie'); ?></p>
            </td>
        </tr>

        <?php echo PostieAdmin::textarea_html(__("Authorized Addresses", 'postie'), "postie-settings[authorized_addresses]", $settings->authorized_addresses, __("(optional) Put each email address on a single line. Posts from emails in this list will be treated as if they came from the admin. If you would prefer to have users post under their own name - create a WordPress user with the correct access level. Emails that don't match will cause the post to be created with draft status.", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <tr> 
            <th scope="row"><?php esc_html_e('Default Poster', 'postie') ?></th> 
            <td>
                <select name='postie-settings[admin_username]' id='postie-settings-admin_username'>
                    <?php
                    $adminusers = get_users('orderby=nicename&role=administrator');
                    $roles = is_array($settings->role_access) ? $settings->role_access : array();
                    foreach ($roles as $userrole => $value) {
                        $adminusers = array_merge($adminusers, get_users("orderby=nicename&role=$userrole"));
                    }
                    foreach ($adminusers as $user) {
                        echo "<option value='" . esc_attr($user->user_login) . "' " . selected($settings->admin_username, $user->user_login, false) . ">" . esc_html($user->user_nicename) . " (" . esc_html($user->user_login) . ")</option>";
                    }
                    ?>
                </select>
                <p class='description'><?php esc_html_e("This will be the poster if you allow posting from emails that are not a registered blog user.", 'postie'); ?></p>
            </td> 
        </tr> 
        <?php echo PostieAdmin::boolean_select_html(__("Force User Login", 'postie'), "postie-settings[force_user_login]", $settings->force_user_login, __("Changing this to yes will cause Postie to try and login as the 'from' user if they exist. This should be set to 'Yes' if you use custom taxonomies in the subject line.", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo PostieAdmin::boolean_select_html(__("Allow Anyone To Post Via Email", 'postie'), "postie-settings[turn_authorization_off]", $settings->turn_authorization_off, __("Changing this to yes <b style='color: red'>is not recommended</b> - anything that gets sent in will automatically be posted.", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

    </table>
