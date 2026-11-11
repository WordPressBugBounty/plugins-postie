<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
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

class PostieCompatibility {

    public static function fnmatch($pattern, $string) {
        if (function_exists('fnmatch')) {
            return fnmatch($pattern, $string);
        }
        $pattern = strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.', '\[' => '[', '\]' => ']'));
        return preg_match('/^' . strtr(addcslashes($pattern, '/\\.+^$(){}=!<>|'), array('*' => '.*', '?' => '.?')) . '$/i', $string);
    }

    public static function mb_str_replace($search, $replace, $subject, &$count = 0) {
        if (function_exists('mb_str_replace')) {
            return mb_str_replace($search, $replace, $subject, $count);
        }
        if (function_exists('mb_split')) {
            if (!is_array($subject)) {
                $searches = is_array($search) ? array_values($search) : array($search);
                $replacements = array_pad(is_array($replace) ? array_values($replace) : array($replace), count($searches), '');

                foreach ($searches as $key => $search_item) {
                    $parts = mb_split(preg_quote($search_item), $subject);
                    $count += count($parts) - 1;
                    $subject = implode($replacements[$key], $parts);
                }
            } else {
                foreach ($subject as $key => $value) {
                    $subject[$key] = self::mb_str_replace($search, $replace, $value, $count);
                }
            }

            return $subject;
        } else {
            return str_replace($search, $replace, $subject, $count);
        }
    }

    public static function boolval($val) {
        if (function_exists('boolval')) {
            return boolval($val);
        }
        return (bool) $val;
    }

    public static function str_get_html($str, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
        return \voku\helper\HtmlDomParser::str_get_html($str, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
    }

    public static function file_get_html($url, $use_include_path = false, $context = null, $offset = -1, $maxLen = -1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
        return \voku\helper\HtmlDomParser::file_get_html($url, $use_include_path, $context, $offset, $maxLen, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
    }

}
