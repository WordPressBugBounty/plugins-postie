<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
    <table class='form-table'>
        <?php echo PostieAdmin::textarea_html(__("Supported MIME Types", 'postie'), "postie-settings[supported_file_types]", $settings->supported_file_types, __("Add just the type (not the subtype). Text, Video, Audio, Image and Multipart are always supported. Put each type on a single line", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        <?php echo PostieAdmin::textarea_html(__("Banned File Names", 'postie'), "postie-settings[banned_files_list]", $settings->banned_files_list, __("Put each file name on a single line. Files matching this list will never be posted to your blog. You can use wildcards such as *.xls, or *.* for all files", 'postie')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <tr>
            <th scope='row'><?php esc_html_e('Attachment icon set', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-icon_set' name='postie-settings[icon_set]'
                       value="<?php echo esc_attr($settings->icon_set) ?>" />

                <?php
                $icon_sets = array('silver', 'black', 'white', 'metro', 'custom', 'none');
                $icon_sizes = array(32, 48, 64);
                ?>
                <select name='icon_set_select' id='icon_set_select'>
                    <?php
                    $styleOptions = $icon_sets;
                    $selected = $settings->icon_set;
                    foreach ($styleOptions as $key) {
                        if ($key != 'selected') {
                            echo '<option ' . selected($key, $selected, false) . ' value="' . esc_attr($key) . '" >' . esc_html($key) . '</option>';
                        }
                    }
                    ?>
                </select>
                <div id='postie-settings-attachment_preview'></div>
            </td>
        </tr>
        <tr>
            <th scope='row'><?php esc_html_e('Attachment icon size (in pixels)', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-icon_size' name='postie-settings[icon_size]'
                       value="<?php echo esc_attr($settings->icon_size) ?>" />
                <select name='icon_size_select' id='icon_size_select'>
                    <?php
                    $styleOptions = $icon_sizes;
                    $selected = $settings->icon_size;
                    foreach ($styleOptions as $key) {
                        if ($key != 'selected') {
                            echo '<option ' . selected($key, $selected, false) . ' value="' . esc_attr($key) . '" >' . esc_html($key) . '</option>';
                        }
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope='row'><?php esc_html_e('Attachment template', 'postie') ?>:<br />
            </th>
            <td>
                <input type='hidden' id='postie-settings-selected_generaltemplate' name='postie-settings[selected_generaltemplate]'
                       value="<?php echo esc_attr($settings->selected_generaltemplate) ?>" />
                <select name='generaltemplateselect' id='generaltemplateselect'>
                    <?php
                    include(POSTIE_ROOT . '/templates/general_template.php');
                    echo PostieAdmin::template_options_html($generalTemplates, $settings->selected_generaltemplate, $settings->generaltemplate);
                    ?>
                </select>
                <p class='description'><?php esc_html_e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php esc_html_e('Note that this template are only used if the attachment is not "inline" or if the email type is "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;">
                    <?php esc_html_e('Preview', 'postie'); ?>
                </div>
                <div id='generalTemplatePreview'></div>
                <textarea cols='70' rows='7' 
                          id='postie-settings-generaltemplate'
                          name='postie-settings[generaltemplate]'><?php echo esc_textarea($settings->generaltemplate); ?></textarea>
            </td>
        </tr>
    </table>
