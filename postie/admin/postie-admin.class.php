<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PostieAdmin {

    /**
     * Normalizes a form field name into a valid, CSS/JS-friendly HTML element ID without brackets.
     * E.g. "postie-settings[my_field]" becomes "postie-settings-my_field".
     *
     * @param string $name
     * @return string
     */
    static function normalize_id($name) {
        return str_replace(array('[', ']'), array('-', ''), rtrim((string)$name, ']'));
    }

    /**
     * Takes a value and builds a simple yes/no select box
     */
    static function boolean_select_html($label, $id, $current_value, $recommendation = NULL, $options = null) {
        $element_id = self::normalize_id($id);

        $html = "<tr>\n\t<th scope='row'><label for='" . esc_attr($element_id) . "'>" . esc_html($label) . "</label>";

        if (!(is_array($options) && count($options) == 2)) {
            $options = array(__('Yes', 'postie'), __('No', 'postie'));
        }

        $html .= "</th>\n\t<td><select name='" . esc_attr($id) . "' id='" . esc_attr($element_id) . "'>
            <option value='1'>" . esc_html($options[0]) . "</option>
            <option value='0' " . (!$current_value ? "selected='selected'" : "") . ">" . esc_html($options[1]) . '</option>
        </select>';
        if (!empty($recommendation)) {
            $html .= '<p class="description">' . $recommendation . '</p>';
        }
        $html .= "</td>\n</tr>";

        return $html;
    }

    /**
     * This takes an array or newline string and displays a textarea for editing
     */
    static function textarea_html($label, $id, $current_value, $recommendation = NULL) {
        $element_id = self::normalize_id($id);

        $html = "<tr><th scope='row'><label for='" . esc_attr($element_id) . "'>" . esc_html($label) . "</label></th>";

        $html .= "<td><br /><textarea cols=40 rows=3 name='" . esc_attr($id) . "' id='" . esc_attr($element_id) . "'>";
        if (is_array($current_value)) {
            $current_value = implode("\n", $current_value);
        }
        $trimmed = trim((string)$current_value);
        if ($trimmed !== '') {
            $items = preg_split("/[\r\n]+/", esc_attr($trimmed));
            if (is_array($items)) {
                foreach ($items as $item) {
                    $item = trim($item);
                    if ($item !== '') {
                        $html .= "$item\n";
                    }
                }
            }
        }
        $html .= "</textarea>";
        if ($recommendation) {
            $html .= "<p class='description'>" . $recommendation . "</p>";
        }
        $html .= "</td></tr>";
        return $html;
    }

    /**
     * Takes an associative array of key => label options and builds a select dropdown
     */
    static function select_html2($label, $id, $current_value, $options, $recommendation = NULL) {
        $element_id = self::normalize_id($id);

        $html = "<tr>\n\t<th scope='row'><label for='" . esc_attr($element_id) . "'>" . esc_html($label) . "</label></th>\n";

        $html .= "<td><select name='" . esc_attr($id) . "' id='" . esc_attr($element_id) . "'>\n";
        foreach ($options as $key => $value) {
            $html .= "<option value='" . esc_attr($key) . "' " . ($key == $current_value ? "selected='selected'" : "") . ">" . esc_html($value) . "</option>\n";
        }
        $html .= '</select>';
        if (!empty($recommendation)) {
            $html .= '<p class="description">' . $recommendation . '</p>';
        }
        $html .= "</td>\n</tr>\n";

        return $html;
    }

    /**
     * Takes a list of options and builds a select dropdown (delegates to select_html2)
     */
    static function select_html($label, $id, $current_value, $options, $recommendation = NULL) {
        $assoc_options = array_combine($options, $options);
        return self::select_html2($label, $id, $current_value, $assoc_options, $recommendation);
    }

    /**
     * Generates HTML <option> tags for attachment template dropdowns
     *
     * @param array  $templates      Key => template content array
     * @param string $selected_key   Currently selected template key
     * @param string $custom_content Content to inject if key is 'custom'
     * @return string
     */
    static function template_options_html($templates, $selected_key, $custom_content = '') {
        $html = '';
        foreach ($templates as $key => $value) {
            if ($key === 'selected') {
                continue;
            }
            if ($key === 'custom') {
                $value = $custom_content;
            }
            $html .= '<option' . selected($key, $selected_key, false) . ' value="' . esc_attr($value) . '">' . esc_html($key) . '</option>';
        }
        return $html;
    }

}
