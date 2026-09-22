<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/* this function is necessary for wildcard matching on non-posix systems */
if (!function_exists('fnmatch')) {

    function fnmatch($pattern, $string) {
        $pattern = strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.', '\[' => '[', '\]' => ']'));
        return preg_match('/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
    }

}

// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
if (!function_exists('mb_str_replace')) {
    if (function_exists('mb_split')) {

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
        function mb_str_replace($search, $replace, $subject, &$count = 0) {
            if (!is_array($subject)) {
                // Normalize $search and $replace so they are both arrays of the same length
                $searches = is_array($search) ? array_values($search) : array($search);
                $replacements = array_pad(is_array($replace) ? array_values($replace) : array($replace), count($searches), '');

                foreach ($searches as $key => $search) {
                    $parts = mb_split(preg_quote($search), $subject);
                    $count += count($parts) - 1;
                    $subject = implode($replacements[$key], $parts);
                }
            } else {
                // Call mb_str_replace for each subject in array, recursively
                foreach ($subject as $key => $value) {
                    $subject[$key] = mb_str_replace($search, $replace, $value, $count);
                }
            }

            return $subject;
        }

    } else {

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
        function mb_str_replace($search, $replace, $subject, &$count = null) {
            return str_replace($search, $replace, $subject, $count);
        }

    }
}

if (!function_exists('boolval')) {

    function boolval($val) {
        return (bool) $val;
    }

}

if (!defined('DEFAULT_TARGET_CHARSET')) {
    define('DEFAULT_TARGET_CHARSET', 'UTF-8');
}
if (!defined('DEFAULT_BR_TEXT')) {
    define('DEFAULT_BR_TEXT', "\r\n");
}
if (!defined('DEFAULT_SPAN_TEXT')) {
    define('DEFAULT_SPAN_TEXT', " ");
}

if (!function_exists('str_get_html')) {
    function str_get_html($str, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
        return \voku\helper\HtmlDomParser::str_get_html($str, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
    }
}

if (!function_exists('file_get_html')) {
    function file_get_html($url, $use_include_path = false, $context = null, $offset = -1, $maxLen = -1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
        return \voku\helper\HtmlDomParser::file_get_html($url, $use_include_path, $context, $offset, $maxLen, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
    }
}