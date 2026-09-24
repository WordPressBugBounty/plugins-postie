<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="simpleTabs-content-7" class="simpleTabs-content">
    <div style="">
        <h3>Postie Support</h3>
        <p>Please refer to the <a href="http://postieplugin.com/help/" target="_blank">Postie Help</a> page for help resolving issues.</p>
        <p>There are a number of different AddOns available to extend Postie's functionality.
            See <a href='http://postieplugin.com/add-ons/' target='_blank'>the list</a> for more information.</p>
        <div>
            <div id='postie-addons'>
                <?php
                include_once(ABSPATH . WPINC . '/feed.php');
                $any_success = false;
                
                // Fetch pages 1 to 4 of the download feed
                for ($p = 1; $p <= 4; $p++) {
                    $feed_url = "https://postieplugin.com/feed/?post_type=download&paged=" . $p;
                    $rss = fetch_feed($feed_url);
                    if (!is_wp_error($rss)) {
                        $any_success = true;
                        $maxitems = $rss->get_item_quantity(20);
                        $rss_items = $rss->get_items(0, $maxitems);
                        foreach ($rss_items as $item) {
                            $title = $item->get_title();
                            $link = esc_url($item->get_permalink());
                            $description = $item->get_description();
                            if (($i = strpos($description, '<p class="more')) !== false) {
                                $description = substr($description, 0, $i);
                            } elseif (($i = strpos($description, '<p>The post <a')) !== false) {
                                $description = substr($description, 0, $i);
                            }
                            
                            // Safe escape/sanitize output
                            $safe_description = wp_kses_post($description);
                            ?>
                            <div class="postie-addon-card">
                                <div class="postie-addon-card-content">
                                    <h4 class="title">
                                        <a href="<?php echo $link; ?>" target="_blank"><?php echo esc_html($title); ?></a>
                                    </h4>
                                    <div class="description"><?php echo $safe_description; ?></div>
                                </div>
                                <div class="postie-addon-card-footer">
                                    <a href="<?php echo $link; ?>" target="_blank" class="button button-secondary">Learn More</a>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
                
                if (!$any_success) {
                    echo '<p>' . esc_html__('Unable to load add-ons at this time.', 'postie') . '</p>';
                }
                ?>
                <div style="clear: both;"></div>
            </div>
        </div>
    </div>
</div>
