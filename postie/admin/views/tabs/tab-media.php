<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
    <table class='form-table'>
        <tr>
            <th scope='row'><?php esc_html_e('Video template 1', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-selected_video1template' name='postie-settings[selected_video1template]'
                       value="<?php echo esc_attr($settings->selected_video1template) ?>" />
                <select name='video1templateselect' id='video1templateselect'>
                    <?php
                    include(POSTIE_ROOT . '/templates/video1_templates.php');
                    echo PostieAdmin::template_options_html($video1Templates, $settings->selected_video1template, $settings->video1template);
                    ?>
                </select>
                <p class='description'><?php esc_html_e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php esc_html_e('Note that this template are only used if the video is not "inline" or the "Preferred Text Type" setting is set to "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;"><?php esc_html_e('Preview', 'postie'); ?></div>
                <div id='video1TemplatePreview'></div>
                <textarea cols='70' rows='7' id='postie-settings-video1template' name='postie-settings[video1template]'><?php echo esc_textarea($settings->video1template); ?></textarea>
            </td>
        </tr>
        <tr> 
            <th scope="row"><?php esc_html_e('Video 1 file extensions', 'postie') ?></th> 
            <td>
                <br/><input name='postie-settings[video1types]' type="text" id='postie-settings-video1types'
                            value="<?php echo esc_attr(is_array($settings->video1types) ? implode(', ', $settings->video1types) : $settings->video1types); ?>" size="40" />                
                <p class='description'>
                    <?php esc_html_e('Use video template 1 for files with these extensions (separated by commas)', 'postie') ?></p>
            </td> 
        </tr> 
        <tr><td colspan="2"><hr /></td></tr>
        <tr>
            <th scope='row'><?php esc_html_e('Video template 2', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-selected_video2template' name='postie-settings[selected_video2template]'
                       value="<?php echo esc_attr($settings->selected_video2template) ?>" />
                <select name='video2templateselect' id='video2templateselect'>
                    <?php
                    include(POSTIE_ROOT . '/templates/video2_templates.php');
                    echo PostieAdmin::template_options_html($video2Templates, $settings->selected_video2template, $settings->video2template);
                    ?>
                </select>
                <p class='description'><?php esc_html_e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php esc_html_e('Note that this template are only used if the video is not "inline" or the "Preferred Text Type" setting is set to "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;"><?php esc_html_e('Preview', 'postie'); ?></div>
                <div id='video2TemplatePreview'></div>
                <textarea cols='70' rows='7' id='postie-settings-video2template' name='postie-settings[video2template]'><?php echo esc_textarea($settings->video2template); ?></textarea>
            </td>
        </tr>
        <tr> 
            <th scope="row"><?php esc_html_e('Video 2 file extensions', 'postie') ?></th> 
            <td>
                <br/><input name='postie-settings[video2types]' type="text" id='postie-settings-video2types'
                            value="<?php echo esc_attr(is_array($settings->video2types) ? implode(', ', $settings->video2types) : $settings->video2types); ?>" size="40" />                
                <p class='description'>
                    <?php esc_html_e('Use video template 2 for files with these extensions (separated by commas)', 'postie') ?></p>
            </td> 
        </tr> 
        <tr><td colspan="2"><hr /></td></tr>
        <tr>
            <th scope='row'><?php esc_html_e('Audio template', 'postie') ?></th>
            <td>
                <input type='hidden' id='postie-settings-selected_audiotemplate' name='postie-settings[selected_audiotemplate]'
                       value="<?php echo esc_attr($settings->selected_audiotemplate) ?>" />
                <select name='audiotemplateselect' id='audiotemplateselect'>
                    <?php
                    include(POSTIE_ROOT . '/templates/audio_templates.php');
                    echo PostieAdmin::template_options_html($audioTemplates, $settings->selected_audiotemplate, $settings->audiotemplate);
                    ?>
                </select>
                <p class='description'><?php esc_html_e('Choose a default template, then customize to your liking in the text box', 'postie') ?></p>
                <p class='description'><?php esc_html_e('Note that this template are only used if the "Preferred Text Type" setting is set to "plain"', 'postie'); ?></p>

                <div style="margin-top: 10px; font-weight: bold;"><?php esc_html_e('Preview', 'postie'); ?></div>
                <div id='audioTemplatePreview'></div>
                <textarea cols='70' rows='7' id='postie-settings-audiotemplate' name='postie-settings[audiotemplate]'><?php echo esc_textarea($settings->audiotemplate); ?></textarea>
            </td>
        </tr>
        <tr> 
            <th scope="row"><?php esc_html_e('Audio file extensions', 'postie') ?></th> 
            <td>
                <input name='postie-settings[audiotypes]' type="text" id='postie-settings-audiotypes' value="<?php echo esc_attr(is_array($settings->audiotypes) ? implode(', ', $settings->audiotypes) : $settings->audiotypes); ?>" size="40" />
                <p class='description'><?php esc_html_e('Use the audio template for files with these extensions (separated by commas)', 'postie') ?></p>
            </td> 
        </tr> 
    </table>
