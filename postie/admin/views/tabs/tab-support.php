<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div>
    <h3><?php esc_html_e('Postie Support', 'postie'); ?></h3>
    <p><?php echo wp_kses_post( sprintf( __( 'Please refer to the <a href="%s" target="_blank">Postie Help</a> page for help resolving issues.', 'postie' ), 'http://postieplugin.com/help/' ) ); ?></p>
    <p><?php echo wp_kses_post( sprintf( __( 'There are a number of different AddOns available to extend Postie\'s functionality. See <a href="%s" target="_blank">the list</a> for more information.', 'postie' ), 'http://postieplugin.com/add-ons/' ) ); ?></p>
    <div>
        <div id='postie-addons'>
            <?php
            $addon_items = get_transient('postie_addons_feed');
            if (false === $addon_items) {
                include_once(ABSPATH . WPINC . '/feed.php');
                $addon_items = array();

                // Fetch pages 1 to 4 of the download feed
                for ($p = 1; $p <= 4; $p++) {
                    $feed_url = "https://postieplugin.com/feed/?post_type=download&paged=" . $p;
                    $rss = fetch_feed($feed_url);
                    if (is_wp_error($rss)) {
                        // If any page fails (e.g. server down or timeout), fail fast and do not hammer next pages
                        break;
                    }

                    $maxitems = $rss->get_item_quantity(20);
                    $rss_items = $rss->get_items(0, $maxitems);
                    if (empty($rss_items)) {
                        break;
                    }

                    foreach ($rss_items as $item) {
                        $addon_title = $item->get_title();
                        $addon_link = $item->get_permalink();
                        $addon_description = $item->get_description();
                        if (($i = strpos($addon_description, '<p class="more')) !== false) {
                            $addon_description = substr($addon_description, 0, $i);
                        } elseif (($i = strpos($addon_description, '<p>The post <a')) !== false) {
                            $addon_description = substr($addon_description, 0, $i);
                        }
                        $addon_items[] = array(
                            'title'       => $addon_title,
                            'link'        => $addon_link,
                            'description' => $addon_description,
                        );
                    }
                }

                // Cache successful items for 12 hours; cache failure for 15 minutes to avoid repeated page hangs
                if (!empty($addon_items)) {
                    set_transient('postie_addons_feed', $addon_items, 12 * HOUR_IN_SECONDS);
                } else {
                    set_transient('postie_addons_feed', array(), 15 * MINUTE_IN_SECONDS);
                }
            }

            if (!empty($addon_items) && is_array($addon_items)) {
                foreach ($addon_items as $addon) {
                    ?>
                    <div class="postie-addon-card">
                        <div class="postie-addon-card-content">
                            <h4 class="title">
                                <a href="<?php echo esc_url($addon['link']); ?>" target="_blank"><?php echo esc_html($addon['title']); ?></a>
                            </h4>
                            <div class="description"><?php echo wp_kses_post($addon['description']); ?></div>
                        </div>
                        <div class="postie-addon-card-footer">
                            <a href="<?php echo esc_url($addon['link']); ?>" target="_blank" class="button button-secondary"><?php esc_html_e('Learn More', 'postie'); ?></a>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>' . esc_html__('Unable to load add-ons at this time.', 'postie') . '</p>';
            }
            ?>
            <div style="clear: both;"></div>
        </div>
    </div>
</div>
