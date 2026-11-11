<?php
/**
 * Backwards compatibility shim for PostieAdmin class.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once dirname( __FILE__ ) . '/admin/postie-admin.class.php';
