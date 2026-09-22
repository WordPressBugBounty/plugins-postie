<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="simpleTabs-content-1" class="simpleTabs-content">

    <table class='form-table' role='presentation'>
        <tbody>
            <tr>
                <th scope="row"><lable for="postie-settings-input_connection"><?php esc_html_e('Connection', 'postie') ?></lable></th>
        <td>
            <select name='postie-settings[input_connection]' id='postie-settings-input_connection'>
                <option value="sockets"  <?php echo (($input_connection == "socket") ? " selected='selected' " : "") ?>>Sockets</option>
                <?php if (function_exists('curl_version')) { ?>
                    <option value="curl" <?php echo ($input_connection == "curl") ? "selected='selected' " : "" ?>>cURL</option>
                <?php } ?>
            </select>
            <p class='description'><?php esc_html_e("Sockets is preferred, but doesn't work with some hosts.", 'postie'); ?></p>
        </td>
        </tr>

        <tr>
            <th scope="row"><lable for="postie-settings-input_protocol"><?php esc_html_e('Mail Protocol', 'postie') ?></lable></th>
        <td>
            <select name='postie-settings[input_protocol]' id='postie-settings-input_protocol'>
                <option value="pop3"  <?php echo (($input_connection == "pop3") ? " selected='selected' " : "") ?>>POP3</option>
                <option value="imap" <?php echo ($input_connection == "imap") ? "selected='selected' " : "" ?>>IMAP</option>
                <option value="pop3-ssl" <?php echo ($input_connection == "pop3-ssl") ? "selected='selected' " : "" ?>>POP3-SSL</option>
                <option value="imap-ssl" <?php echo ($input_connection == "imap-ssl") ? "selected='selected' " : "" ?>>IMAP-SSL</option>
            </select>
            <p class='description'><?php
                if (!extension_loaded('openssl')) {
                    esc_html_e("OpenSSL has not been enabled. POP3-SSL and IMAP-SSL will not work as expected.", 'postie');
                }
                ?></p>
        </td>
        </tr>

        <tr>
            <th scope="row"><label for="postie-settings-mail_server_port"><?php esc_html_e('Port', 'postie') ?></label></th>
            <td valign="top">
                <input name='postie-settings[mail_server_port]' style="width: 70px;" type="number" min="0" id='postie-settings-mail_server_port' value="<?php echo esc_attr($mail_server_port); ?>" size="6" />
                <p class='description'><?php esc_html_e("Standard Ports:", 'postie'); ?><br />
                    <?php esc_html_e("POP3", 'postie'); ?>: 110<br />
                    <?php esc_html_e("IMAP", 'postie'); ?>: 143<br />
                    <?php esc_html_e("IMAP-SSL", 'postie'); ?>: 993 <br />
                    <?php esc_html_e("POP3-SSL", 'postie'); ?>: 995 <br />
                </p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Mail Server', 'postie') ?></th>
            <td><input name='postie-settings[mail_server]' type="text" id='postie-settings-mail_server' value="<?php echo esc_attr($mail_server); ?>" size="40" />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Mail Userid', 'postie') ?></th>
            <td>
                <input name='postie-settings[mail_userid]' type="text" id='postie-settings-mail_userid' autocomplete='new-password' value="<?php echo esc_attr($mail_userid); ?>" size="40" />
                <p class='description'><?php esc_html_e("Note that Postie will read and DELETE all the email in this account. Typically your full email address.", 'postie'); ?><br />
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Mail Password', 'postie') ?></th>
            <td>
                <input name='postie-settings[mail_password]' type="password" id='postie-settings-mail_password' autocomplete='new-password' value="<?php echo esc_attr($mail_password); ?>" size="40" />
            </td>
        </tr>

        <?php echo PostieAdmin::boolean_select_html(__("Ignore Email Date", 'postie'), 'postie-settings[ignore_email_date]', $ignore_email_date, __("If set to 'Yes' the email date will be ignored and the post will be published with the system time. If set to 'No' the date in the email will be used as the post date.", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo PostieAdmin::boolean_select_html(__("Use Postie Time Correction", 'postie'), 'postie-settings[use_time_offset]', $use_time_offset, __("If set to 'Yes' adjust the time according to Postie Time Correction otherwise use the date provided by the email.", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <tr>
            <th scope="row"><?php esc_html_e('Postie Time Correction', 'postie') ?></th>
            <td><input style="width: 70px;" name='postie-settings[time_offset]' type="number" step="0.5" id='postie-settings-time_offset' size="2" value="<?php echo esc_attr($time_offset); ?>" /> 
                <?php
                esc_html_e('hours', 'postie');
                ?> 
                <p class='description'>
                    <?php
                    echo wp_kses_post( sprintf(
                        /* translators: 1: blog timezone, 2: blog offset */
                        __( 'Should be the same as your normal offset, but this lets you adjust it in cases where that doesn\'t work.<br>Blog timezone is: %1$s<br>Blog offset: %2$d', 'postie' ),
                        esc_html( get_option('timezone_string') == '' ? 'GMT+0' : get_option('timezone_string') ),
                        (int) get_option('gmt_offset')
                    ) );
                    ?>
                </p>
            </td>
        </tr>
        <tr>
            <th>
                <?php esc_html_e('Check for mail every', 'postie') ?>:
            </th>
            <td>
                <select name='postie-settings[interval]' id='postie-settings-interval'>
                    <option value="weekly" <?php selected($interval, "weekly"); ?>><?php esc_html_e('Once weekly', 'postie') ?>
                    </option>

                    <option value="daily" <?php selected($interval, "daily"); ?>><?php esc_html_e('daily', 'postie') ?>
                    </option>

                    <option value="twohours" <?php selected($interval, "twohours"); ?>><?php esc_html_e('every 2 hours', 'postie') ?>
                    </option>

                    <option value="hourly" <?php selected($interval, "hourly"); ?>><?php esc_html_e('hourly', 'postie') ?>
                    </option>

                    <option value="twiceperhour" <?php selected($interval, "twiceperhour"); ?>><?php esc_html_e('every 30 minutes', 'postie') ?>
                    </option>

                    <option value="tenminutes" <?php selected($interval, "tenminutes"); ?>><?php esc_html_e('every 10 minutes', 'postie') ?>
                    </option>

                    <option value="fiveminutes" <?php selected($interval, "fiveminutes"); ?>><?php esc_html_e('every 5 minutes', 'postie') ?>
                    </option>

                    <option value="oneminute" <?php selected($interval, "oneminute"); ?>><?php esc_html_e('every 1 minute', 'postie') ?>
                    </option>

                    <option value="thirtyseconds" <?php selected($interval, "thirtyseconds"); ?>><?php esc_html_e('every 30 seconds', 'postie') ?>
                    </option>

                    <option value="fifteenseconds" <?php selected($interval, "fifteenseconds"); ?>><?php esc_html_e('every 15 seconds', 'postie') ?>
                    </option>

                    <option value="manual" <?php selected($interval, "manual"); ?>><?php esc_html_e('check manually', 'postie') ?>
                    </option>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <?php esc_html_e('Maximum number of emails to process', 'postie'); ?>
            </th>
            <td>
                <select name='postie-settings[maxemails]' id='postie-settings-maxemails'>
                    <option value="0" <?php selected($maxemails, '0'); ?>><?php esc_html_e('All', 'postie'); ?></option>
                    <option value="1" <?php selected($maxemails, '1'); ?>>1</option>
                    <option value="2" <?php selected($maxemails, '2'); ?>>2</option>
                    <option value="5" <?php selected($maxemails, '5'); ?>>5</option>
                    <option value="10" <?php selected($maxemails, '10'); ?>>10</option>
                    <option value="25" <?php selected($maxemails, '25'); ?>>25</option>
                    <option value="50" <?php selected($maxemails, '50'); ?>>50</option>
                </select>
            </td>
        </tr>
        <?php
        echo PostieAdmin::boolean_select_html(__("Delete email after posting", 'postie'), 'postie-settings[delete_mail_after_processing]', $delete_mail_after_processing, __("Only set to no for testing purposes", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
//echo PostieAdmin::BuildBooleanSelect(__("Ignore mail state", 'postie'), 'postie-settings[ignore_mail_state]', $ignore_mail_state, __("Ignore whether the mails is 'read' or 'unread' If 'No' then only unread messages are processed. IMAP only", 'postie')); 

        echo PostieAdmin::boolean_select_html(__("Enable Error Logging", 'postie'), 'postie-settings[postie_log_error]', $postie_log_error, __("Log error messages to the web server error log.", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        $notification_options = array('(Nobody)', '(All Admins)');
        foreach (get_users(array('role' => 'administrator')) as $user) {
            $notification_options[] = $user->user_login;
        }
        echo PostieAdmin::select_html(__('Notify on Error', 'postie'), 'postie-settings[postie_log_error_notify]', $postie_log_error_notify, $notification_options); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        echo PostieAdmin::boolean_select_html(__("Enable Debug Logging", 'postie'), 'postie-settings[postie_log_debug]', $postie_log_debug, __("Log debug messages to the web server error log.", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        ?>
        </tbody>
    </table>
</div>