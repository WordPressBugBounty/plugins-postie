<?php
/**
 * Backwards compatibility shim for Postie admin settings page.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __FILE__ ) . '/admin/views/settings-page.php';
