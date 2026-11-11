<?php
/**
 * Postie Process Email Modal View
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<!-- Process Email Modal -->
<div id="postie-modal-backdrop" class="postie-modal-backdrop" style="display:none;" aria-hidden="true">
    <div id="postie-modal" class="postie-modal" role="dialog" aria-modal="true" aria-labelledby="postie-modal-title">
        <div class="postie-modal-header">
            <h3 id="postie-modal-title" class="postie-modal-title"><?php esc_html_e( 'Process Email', 'postie' ); ?></h3>
            <button type="button" class="postie-modal-close" aria-label="<?php esc_attr_e( 'Close', 'postie' ); ?>">&times;</button>
        </div>
        <div class="postie-modal-body" id="postie-modal-body">
            <!-- Dynamically populated via postie-admin.js -->
        </div>
        <div class="postie-modal-footer" id="postie-modal-footer">
            <button type="button" class="button button-secondary postie-modal-close-btn"><?php esc_html_e( 'Close', 'postie' ); ?></button>
        </div>
    </div>
</div>
