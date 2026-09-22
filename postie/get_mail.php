<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
//http_response_code(403);
$raw_protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_PROTOCOL'])) : 'HTTP/1.0');
$protocol = (preg_match('/^HTTP\/[0-9.]+$/', $raw_protocol) ? $raw_protocol : 'HTTP/1.0');
header("$protocol 403 Forbidden");
$GLOBALS['http_response_code'] = 403;
?>
<html>
    <head>
        <title>Postie - Error</title>
    </head>
    <body>
        This URL is no longer supported for forcing an email check please update your cron job to 
        access http://&lt;mysite&gt;/?postie=get-mail
    </body>
</html>