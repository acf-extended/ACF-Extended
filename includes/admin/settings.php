<?php

if(!defined('ABSPATH'))
    exit;

add_action('admin_menu', 'acfe_admin_settings_menu');
function acfe_admin_settings_menu(){
    
    if(!acf_get_setting('show_admin'))
        return;
    
    $submenu_page = add_submenu_page('edit.php?post_type=acf-field-group', __('Settings', 'acfe'), __('Settings', 'acfe'), acf_get_setting('capability'), 'acfe-settings', 'acfe_admin_settings_html');
    
    add_action('admin_print_scripts-' . $submenu_page, function(){
        acf_enqueue_scripts();
    });
    
}

function acfe_admin_settings_html(){
?>
<div class="wrap" id="acfe-admin-settings">
    
    <h1><?php _e('Settings', 'acfe'); ?></h1>
    
    <div id="poststuff">
        
        <div class="postbox acf-postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Settings', 'acfe'); ?></span></h2>
            <div class="inside acf-fields -left">
            
                <?php 
                acf_render_field_wrap(array(
                    'type'  => 'tab',
                    'label' => 'ACF',
                ));
                ?>
                
                <?php
                
                $load_json = acf_get_setting('load_json');
                $load_json_text = '';
                
                if(!empty($load_json))
                    $load_json_text = implode("<br />", $load_json);

                $settings = array(
                    array(
                        'name'  => 'path',
                        'label' => __('Path', 'acfe'),
                        'value' => '<code>' . acf_get_setting('path') . '</code>',
                        'description' => __('Absolute path to ACF plugin folder including trailing slash.<br />Defaults to plugin_dir_path', 'acfe')
                    ),
                    array(
                        'name'  => 'dir',
                        'label' => __('Directory', 'acfe'),
                        'value' => '<code>' . acf_get_setting('dir') . '</code>',
                        'description' => __('URL to ACF plugin folder including trailing slash. Defaults to plugin_dir_url', 'acfe')
                    ),
                    array(
                        'name'  => 'show_admin',
                        'label' => __('Show menu', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('show_admin') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide ACF menu item. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'stripslashes',
                        'label' =>  __('Strip slashes', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('stripslashes') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Runs the function stripslashes on all $_POST data. Some servers / WP instals may require this extra functioanlity. Defaults to false', 'acfe')
                    ),
                    array(
                        'name'  => 'local',
                        'label' => __('PHP/Json', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('local') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Enable/Disable local (PHP/json) fields. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'json',
                        'label' => __('Json', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('json') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Enable/Disable json fields. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'save_json',
                        'label' => __('Json folder (save)', 'acfe'),
                        'value' => '<code>' . acf_get_setting('save_json') . '</code>',
                        'description' => __('Absolute path to folder where json files will be created when field groups are saved.<br />Defaults to ‘acf-json’ folder within current theme', 'acfe')
                    ),
                    array(
                        'name'  => 'load_json',
                        'label' => __('Json folder (load)', 'acfe'),
                        'value' => '<code>' . $load_json_text . '</code>',
                        'description' => __('Array of absolutes paths to folders where field group json files can be read.<br />Defaults to an array containing at index 0, the ‘acf-json’ folder within current theme', 'acfe')
                    ),
                    array(
                        'name'  => 'default_language',
                        'label' => __('Default language', 'acfe'),
                        'value' => '<code>' . acf_get_setting('default_language') . '</code>',
                        'description' => __('Language code of the default language. Defaults to ”.<br />If WPML is active, ACF will default this to the WPML default language setting', 'acfe')
                    ),
                    array(
                        'name'  => 'current_language',
                        'label' => __('Current language', 'acfe'),
                        'value' => '<code>' . acf_get_setting('current_language') . '</code>',
                        'description' => __('Language code of the current post’s language. Defaults to ”.<br />If WPML is active, ACF will default this to the WPML current language', 'acfe')
                    ),
                    array(
                        'name'  => 'capability',
                        'label' => __('Capability', 'acfe'),
                        'value' => '<code>' . acf_get_setting('capability') . '</code>',
                        'description' => __('Capability used for ACF post types and if the current user can see the ACF menu item.<br />Defaults to ‘manage_options’.', 'acfe')
                    ),
                    array(
                        'name'  => 'show_updates',
                        'label' => __('Show updates', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('show_updates') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Enable/Disable updates to appear in plugin list and show/hide the ACF updates admin page.<br />Defaults to true.', 'acfe')
                    ),
                    array(
                        'name'  => 'export_textdomain',
                        'label' => __('Export textdomain', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('export_textdomain') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Array of keys used during the ‘Export to PHP’ feature to wrap strings within the __() function.<br />Defaults to array(’title’, ’label’, ’instructions’). Depreciated in v5.3.4 – please see l10n_field and l10n_field_group', 'acfe')
                    ),
                    array(
                        'name'  => 'export_translate',
                        'label' => __('Export translate', 'acfe'),
                        'value' => '<code>' . print_r(acf_get_setting('export_translate'), true) . '</code>',
                        'description' => __('Used during the ‘Export to PHP’ feature to wrap strings within the __() function.<br />Depreciated in v5.4.4 – please see l10n_textdomain', 'acfe')
                    ),
                    array(
                        'name'  => 'autoload',
                        'label' => __('Auto load', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('autoload') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Sets the text domain used when translating field and field group settings.<br />Defaults to ”. Strings will not be translated if this setting is empty', 'acfe')
                    ),
                    array(
                        'name'  => 'l10n',
                        'label' => __('l10n', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('l10n') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Allows ACF to translate field and field group settings using the __() function.<br />Defaults to true. Useful to override translation without modifying the textdomain', 'acfe')
                    ),
                    array(
                        'name'  => 'l10n_textdomain',
                        'label' => __('l10n Textdomain', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('l10n') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Sets the text domain used when translating field and field group settings.<br />Defaults to ”. Strings will not be translated if this setting is empty', 'acfe')
                    ),
                    array(
                        'name'  => 'l10n_field',
                        'label' => __('l10n Field', 'acfe'),
                        'value' => '<code>' . print_r(acf_get_setting('l10n_field'), true) . '</code>',
                        'description' => __('An array of settings to translate when loading and exporting a field.<br />Defaults to array(’label’, ’instructions’). Depreciated in v5.3.6 – please see acf/translate_field filter', 'acfe')
                    ),
                    array(
                        'name'  => 'l10n_field_group',
                        'label' => __('l10n Field group', 'acfe'),
                        'value' => '<code>' . print_r(acf_get_setting('l10n_field_group'), true) . '</code>',
                        'description' => __('An array of settings to translate when loading and exporting a field group.<br />Defaults to array(’title’). Depreciated in v5.3.6 – please see acf/translate_field_group filter', 'acfe')
                    ),
                    array(
                        'name'  => 'google_api_key',
                        'label' => __('Google API Key', 'acfe'),
                        'value' => '<code>' . acf_get_setting('google_api_key') . '</code>',
                        'description' => __('Specify a Google Maps API authentication key to prevent usage limits.<br />Defaults to ”', 'acfe')
                    ),
                    array(
                        'name'  => 'google_api_client',
                        'label' => __('Google API Key', 'acfe'),
                        'value' => '<code>' . acf_get_setting('google_api_client') . '</code>',
                        'description' => __('Specify a Google Maps API Client ID to prevent usage limits.<br />Not needed if using google_api_key. Defaults to ”', 'acfe')
                    ),
                    array(
                        'name'  => 'enqueue_google_maps',
                        'label' => __('Enqueue Google Maps', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('enqueue_google_maps') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Allows ACF to enqueue and load the Google Maps API JS library.<br />Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'enqueue_select2',
                        'label' => __('Enqueue Select2', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('enqueue_select2') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Allows ACF to enqueue and load the Select2 JS/CSS library.<br />Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'select2_version',
                        'label' => __('Select2 version', 'acfe'),
                        'value' => '<code>' . acf_get_setting('select2_version') . '</code>',
                        'description' => __('Defines which version of Select2 library to enqueue. Either 3 or 4.<br />Defaults to 4 since ACF 5.6.0', 'acfe')
                    ),
                    array(
                        'name'  => 'enqueue_datepicker',
                        'label' => __('Enqueue Datepicker', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('enqueue_datepicker') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Allows ACF to enqueue and load the WP datepicker JS/CSS library.<br />Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'enqueue_datetimepicker',
                        'label' => __('Enqueue Date/timepicker', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('enqueue_datetimepicker') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Allows ACF to enqueue and load the datetimepicker JS/CSS library.<br />Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'row_index_offset',
                        'label' => __('Row index offset', 'acfe'),
                        'value' => '<code>' . acf_get_setting('row_index_offset') . '</code>',
                        'description' => __('Defines the starting index used in all ‘loop’ and ‘row’ functions.<br />Defaults to 1 (1 is the first row), can be changed to 0 (0 is the first row)', 'acfe')
                    ),
                    array(
                        'name'  => 'remove_wp_meta_box',
                        'label' => __('Remove WP meta box', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('remove_wp_meta_box') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Allows ACF to remove the default WP custom fields metabox. Defaults to true', 'acfe')
                    ),
                );
                
                ?>
                
                <?php foreach($settings as $setting){ ?>
                    <div class="acf-field">
                        <div class="acf-label">
                            <label><span class="acf-js-tooltip dashicons dashicons-info" style="float:right; font-size:16px; color:#ccc;" title="<?php echo $setting['description']; ?>"></span><?php echo $setting['label']; ?></label>
                            <p class="description"><code><?php echo $setting['name']; ?></code></p>
                        </div>
                        <div class="acf-input">
                            <?php echo $setting['value']; ?>
                        </div>
                    </div>
                <?php } ?>
                
                <?php 
                acf_render_field_wrap(array(
                    'type'  => 'tab',
                    'label' => 'ACF: Extended',
                ));
                ?>
                
                <?php
                
                $load_php = acf_get_setting('acfe/php_load');
                $load_php_text = '';
                
                if(!empty($load_php))
                    $load_php_text = implode("<br />", $load_php);
                
                $settings = array(
                    array(
                        'name'  => 'acfe/modules/author',
                        'label' => __('Module: Author', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/author', true) ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the Author module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_block_types',
                        'label' => __('Module: Dynamic Block Types', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_block_types', true) ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the Block Types module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_forms',
                        'label' => __('Module: Dynamic Forms', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_forms', true) ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the Forms module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_post_types',
                        'label' => __('Module: Dynamic Post Types', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_post_types', true) ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the Post Types module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_taxonomies',
                        'label' => __('Module: Dynamic Taxonomies', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_taxonomies', true) ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the Taxonomies module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_options_pages',
                        'label' => __('Module: Dynamic Options Pages', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_options_pages', true) ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the Options Pages module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/modules/options',
                        'label' => __('Module: Options', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/options', true) ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the Options module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/modules/taxonomies',
                        'label' => __('Module: Taxonomies Enhancements', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/modules/taxonomies', true) ? __('True'): __('False')) . '</code>',
                        'description' => __('Show/hide the Taxonomies enhancements module. Defaults to true', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/site_key',
                        'label' => __('Field: reCaptcha site key', 'acfe'),
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/site_key') . '</code>',
                        'description' => __('The default reCaptcha site key', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/secret_key',
                        'label' => __('Field: reCaptcha secret key', 'acfe'),
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/secret_key') . '</code>',
                        'description' => __('The default reCaptcha secret key', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/version',
                        'label' => __('Field: reCaptcha version', 'acfe'),
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/version', 'v2') . '</code>',
                        'description' => __('The default reCaptcha version', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/v2/theme',
                        'label' => __('Field: reCaptcha v2 theme', 'acfe'),
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/v2/theme', 'light') . '</code>',
                        'description' => __('The default reCaptcha v2 theme', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/v2/size',
                        'label' => __('Field: reCaptcha v2 size', 'acfe'),
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/v2/size', 'normal') . '</code>',
                        'description' => __('The default reCaptcha v2 size', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/v3/hide_logo',
                        'label' => __('Field: reCaptcha v3 hide logo', 'acfe'),
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/v3/hide_logo') . '</code>',
                        'description' => __('Show/hide reCaptcha v3 logo', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/dev',
                        'label' => __('Dev mode', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/dev') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Show/hide the advanced WP post meta box. Defaults to false', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/php',
                        'label' => __('PHP', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/php') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Allow PHP Sync', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/php_found',
                        'label' => __('PHP: Found', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/php_found') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Found PHP Sync load folder', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/php_save',
                        'label' => __('PHP: Save', 'acfe'),
                        'value' => '<code>' . acf_get_setting('acfe/php_save') . '</code>',
                        'description' => __('Found PHP Sync save folder', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/php_load',
                        'label' => __('PHP: Load', 'acfe'),
                        'value' => '<code>' . $load_php_text . '</code>',
                        'description' => __('PHP Sync Load path', 'acfe')
                    ),
                    array(
                        'name'  => 'acfe/json_found',
                        'label' => __('Json: Found', 'acfe'),
                        'value' => '<code>' . (acf_get_setting('acfe/json_found') ? __('True', 'acfe'): __('False', 'acfe')) . '</code>',
                        'description' => __('Found Json Sync load folder', 'acfe')
                    ),
                );
                ?>
                
                <?php foreach($settings as $setting){ ?>
                    <div class="acf-field">
                        <div class="acf-label">
                            <label><span class="acf-js-tooltip dashicons dashicons-info" style="float:right; font-size:16px; color:#ccc;" title="<?php echo $setting['description']; ?>"></span><?php echo $setting['label']; ?></label>
                            <p class="description"><code><?php echo $setting['name']; ?></code></p>
                        </div>
                        <div class="acf-input">
                            <?php echo $setting['value']; ?>
                        </div>
                    </div>
                <?php } ?>

                <script type="text/javascript">
                if( typeof acf !== 'undefined' ) {
                        
                    acf.newPostbox({
                        'id': 'acfe-settings',
                        'label': __('left', 'acfe')
                    });	

                }
                </script>
            </div>
        </div>
    </div>
    
</div>
<?php
}