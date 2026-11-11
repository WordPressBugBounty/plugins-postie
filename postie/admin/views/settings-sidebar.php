<?php
/**
 * Postie Settings Sidebar View
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="postbox-container-1" class="postbox-container">
    <div class="meta-box-sortables ui-sortable">

        <div class="postbox">
            <h3 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Actions', 'postie' ); ?></span></h3>
            <div class="inside">
                <div class="submitbox">
                    <p><?php esc_html_e( 'To run the check mail script manually', 'postie' ); ?></p>
                    <form name="postie-options" method='post'> 
                        <?php wp_nonce_field('postie_action_nonce_action', 'postie_action_nonce'); ?>
                        <input type="hidden" name="action" value="runpostie" />
                        <input id="postie-run-manual" name="Submit" value="<?php esc_attr_e( 'Process Email', 'postie' ); ?>" type="submit" class='button postie-run-btn'>
                    </form>

                    <p><?php esc_html_e( 'To run the check mail script manually with full debug output', 'postie' ); ?></p>
                    <form name="postie-options" method='post'> 
                        <?php wp_nonce_field('postie_action_nonce_action', 'postie_action_nonce'); ?>
                        <input type="hidden" name="action" value="runpostie-debug" />
                        <input id="postie-run-debug" name="Submit" value="<?php esc_attr_e( 'Debug', 'postie' ); ?>" type="submit" class='button postie-run-btn'>
                    </form>

                    <p><?php esc_html_e( 'Test your configuration (save first)', 'postie' ); ?></p>
                    <form name="postie-options" method="post">
                        <?php wp_nonce_field('postie_action_nonce_action', 'postie_action_nonce'); ?>
                        <input type="hidden" name="action" value="test" />
                        <input id="postie-run-test" name="Submit" value="<?php esc_attr_e( 'Test Config', 'postie' ); ?>" type="submit" class='button postie-run-btn'>
                    </form>
                </div>
            </div>
        </div>

        <div class="postbox">
            <h3 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Getting Started', 'postie' ); ?></span></h3>
            <div class="inside">
                <p><?php echo wp_kses_post( sprintf( __( 'Be sure and check out the <a href="%s" target="_blank">getting started</a> guide.', 'postie' ), 'http://postieplugin.com/getting-started/' ) ); ?></p>
                <p><?php echo wp_kses_post( sprintf( __( 'Please use the Postie <a href="%s" target="_blank">support forums</a> if you need help.', 'postie' ), 'https://wordpress.org/support/plugin/postie' ) ); ?></p>
            </div>
        </div>

        <div class="postbox">
            <h3 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'AddOns', 'postie' ); ?></span></h3>
            <div class="inside">
                <p><?php echo wp_kses_post( sprintf( __( 'There are a number of different AddOns available to extend Postie\'s functionality. See <a href="%s" target="_blank">the list</a> for more information.', 'postie' ), 'http://postieplugin.com/add-ons/' ) ); ?></p>
            </div>
        </div>

        <div class="postbox">
            <h3 class="hndle ui-sortable-handle"><span><?php esc_html_e( 'Donations', 'postie' ); ?></span></h3>
            <div class="inside">
                <p style="font-weight: bolder; margin-top: 0px; margin-bottom: 2px;"><?php esc_html_e( 'Please Donate, Every $ Helps!', 'postie' ); ?></p>
                <p style="margin-top: 0;margin-bottom: 2px;"><?php esc_html_e( 'Your generous donation allows me to continue developing Postie for the WordPress community.', 'postie' ); ?></p>
                <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
                    <input type="hidden" name="cmd" value="_s-xclick">
                    <input type="hidden" name="hosted_button_id" value="HPK99BJ88V4C2">
                    <div style="text-align:center;">
                        <input style="border: none; margin: 0;" type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="<?php esc_attr_e( 'PayPal - The safer, easier way to pay online!', 'postie' ); ?>">
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
