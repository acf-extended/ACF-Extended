<?php

if(!defined('ABSPATH'))
    exit;

add_action('admin_menu', 'acfe_admin_settings_menu');
function acfe_admin_settings_menu(){
    
    if(!acf_get_setting('show_admin'))
        return;
    
    $submenu_page = add_submenu_page('edit.php?post_type=acf-field-group', __('Settings'), __('Settings'), acf_get_setting('capability'), 'acfe-settings', 'acfe_admin_settings_html');
    
    add_action('admin_print_scripts-' . $submenu_page, function(){
        acf_enqueue_scripts();
    });
    
}

function acfe_admin_settings_html(){
?>
<div class="wrap" id="acfe-admin-settings">
    
    <h1><?php _e('Settings'); ?></h1>
    
    <div id="poststuff">
        
        <div class="postbox acf-postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php _e('Settings'); ?></span></h2>
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
                        'label' => 'Path',
                        'value' => '<code>' . acf_get_setting('path') . '</code>',
                        'description' => 'Absolute path to ACF plugin folder including trailing slash.<br />Defaults to plugin_dir_path'
                    ),
                    array(
                        'name'  => 'dir',
                        'label' => 'Directory',
                        'value' => '<code>' . acf_get_setting('dir') . '</code>',
                        'description' => 'URL to ACF plugin folder including trailing slash. Defaults to plugin_dir_url'
                    ),
                    array(
                        'name'  => 'show_admin',
                        'label' => 'Show menu',
                        'value' => '<code>' . (acf_get_setting('show_admin') ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide ACF menu item. Defaults to true'
                    ),
                    array(
                        'name'  => 'stripslashes',
                        'label' => 'Strip slashes',
                        'value' => '<code>' . (acf_get_setting('stripslashes') ? __('True'): __('False')) . '</code>',
                        'description' => 'Runs the function stripslashes on all $_POST data. Some servers / WP instals may require this extra functioanlity. Defaults to false'
                    ),
                    array(
                        'name'  => 'local',
                        'label' => 'PHP/Json',
                        'value' => '<code>' . (acf_get_setting('local') ? __('True'): __('False')) . '</code>',
                        'description' => 'Enable/Disable local (PHP/json) fields. Defaults to true'
                    ),
                    array(
                        'name'  => 'json',
                        'label' => 'Json',
                        'value' => '<code>' . (acf_get_setting('json') ? __('True'): __('False')) . '</code>',
                        'description' => 'Enable/Disable json fields. Defaults to true'
                    ),
                    array(
                        'name'  => 'save_json',
                        'label' => 'Json folder (save)',
                        'value' => '<code>' . acf_get_setting('save_json') . '</code>',
                        'description' => 'Absolute path to folder where json files will be created when field groups are saved.<br />Defaults to ‘acf-json’ folder within current theme'
                    ),
                    array(
                        'name'  => 'load_json',
                        'label' => 'Json folder (load)',
                        'value' => '<code>' . $load_json_text . '</code>',
                        'description' => 'Array of absolutes paths to folders where field group json files can be read.<br />Defaults to an array containing at index 0, the ‘acf-json’ folder within current theme'
                    ),
                    array(
                        'name'  => 'default_language',
                        'label' => 'Default language',
                        'value' => '<code>' . acf_get_setting('default_language') . '</code>',
                        'description' => 'Language code of the default language. Defaults to ”.<br />If WPML is active, ACF will default this to the WPML default language setting'
                    ),
                    array(
                        'name'  => 'current_language',
                        'label' => 'Current language',
                        'value' => '<code>' . acf_get_setting('current_language') . '</code>',
                        'description' => 'Language code of the current post’s language. Defaults to ”.<br />If WPML is active, ACF will default this to the WPML current language'
                    ),
                    array(
                        'name'  => 'capability',
                        'label' => 'Capability',
                        'value' => '<code>' . acf_get_setting('capability') . '</code>',
                        'description' => 'Capability used for ACF post types and if the current user can see the ACF menu item.<br />Defaults to ‘manage_options’.'
                    ),
                    array(
                        'name'  => 'show_updates',
                        'label' => 'Show updates',
                        'value' => '<code>' . (acf_get_setting('show_updates') ? __('True'): __('False')) . '</code>',
                        'description' => 'Enable/Disable updates to appear in plugin list and show/hide the ACF updates admin page.<br />Defaults to true.'
                    ),
                    array(
                        'name'  => 'export_textdomain',
                        'label' => 'Export textdomain',
                        'value' => '<code>' . (acf_get_setting('export_textdomain') ? __('True'): __('False')) . '</code>',
                        'description' => 'Array of keys used during the ‘Export to PHP’ feature to wrap strings within the __() function.<br />Defaults to array(’title’, ’label’, ’instructions’). Depreciated in v5.3.4 – please see l10n_field and l10n_field_group'
                    ),
                    array(
                        'name'  => 'export_translate',
                        'label' => 'Export translate',
                        'value' => '<code>' . print_r(acf_get_setting('export_translate'), true) . '</code>',
                        'description' => 'Used during the ‘Export to PHP’ feature to wrap strings within the __() function.<br />Depreciated in v5.4.4 – please see l10n_textdomain'
                    ),
                    array(
                        'name'  => 'autoload',
                        'label' => 'Auto load',
                        'value' => '<code>' . (acf_get_setting('autoload') ? __('True'): __('False')) . '</code>',
                        'description' => 'Sets the text domain used when translating field and field group settings.<br />Defaults to ”. Strings will not be translated if this setting is empty'
                    ),
                    array(
                        'name'  => 'l10n',
                        'label' => 'l10n',
                        'value' => '<code>' . (acf_get_setting('l10n') ? __('True'): __('False')) . '</code>',
                        'description' => 'Allows ACF to translate field and field group settings using the __() function.<br />Defaults to true. Useful to override translation without modifying the textdomain'
                    ),
                    array(
                        'name'  => 'l10n_textdomain',
                        'label' => 'l10n Textdomain',
                        'value' => '<code>' . (acf_get_setting('l10n') ? __('True'): __('False')) . '</code>',
                        'description' => 'Sets the text domain used when translating field and field group settings.<br />Defaults to ”. Strings will not be translated if this setting is empty'
                    ),
                    array(
                        'name'  => 'l10n_field',
                        'label' => 'l10n Field',
                        'value' => '<code>' . print_r(acf_get_setting('l10n_field'), true) . '</code>',
                        'description' => 'An array of settings to translate when loading and exporting a field.<br />Defaults to array(’label’, ’instructions’). Depreciated in v5.3.6 – please see acf/translate_field filter'
                    ),
                    array(
                        'name'  => 'l10n_field_group',
                        'label' => 'l10n Field group',
                        'value' => '<code>' . print_r(acf_get_setting('l10n_field_group'), true) . '</code>',
                        'description' => 'An array of settings to translate when loading and exporting a field group.<br />Defaults to array(’title’). Depreciated in v5.3.6 – please see acf/translate_field_group filter'
                    ),
                    array(
                        'name'  => 'google_api_key',
                        'label' => 'Google API Key',
                        'value' => '<code>' . acf_get_setting('google_api_key') . '</code>',
                        'description' => 'Specify a Google Maps API authentication key to prevent usage limits.<br />Defaults to ”'
                    ),
                    array(
                        'name'  => 'google_api_client',
                        'label' => 'Google API Key',
                        'value' => '<code>' . acf_get_setting('google_api_client') . '</code>',
                        'description' => 'Specify a Google Maps API Client ID to prevent usage limits.<br />Not needed if using google_api_key. Defaults to ”'
                    ),
                    array(
                        'name'  => 'enqueue_google_maps',
                        'label' => 'Enqueue Google Maps',
                        'value' => '<code>' . (acf_get_setting('enqueue_google_maps') ? __('True'): __('False')) . '</code>',
                        'description' => 'Allows ACF to enqueue and load the Google Maps API JS library.<br />Defaults to true'
                    ),
                    array(
                        'name'  => 'enqueue_select2',
                        'label' => 'Enqueue Select2',
                        'value' => '<code>' . (acf_get_setting('enqueue_select2') ? __('True'): __('False')) . '</code>',
                        'description' => 'Allows ACF to enqueue and load the Select2 JS/CSS library.<br />Defaults to true'
                    ),
                    array(
                        'name'  => 'select2_version',
                        'label' => 'Select2 version',
                        'value' => '<code>' . acf_get_setting('select2_version') . '</code>',
                        'description' => 'Defines which version of Select2 library to enqueue. Either 3 or 4.<br />Defaults to 4 since ACF 5.6.0'
                    ),
                    array(
                        'name'  => 'enqueue_datepicker',
                        'label' => 'Enqueue Datepicker',
                        'value' => '<code>' . (acf_get_setting('enqueue_datepicker') ? __('True'): __('False')) . '</code>',
                        'description' => 'Allows ACF to enqueue and load the WP datepicker JS/CSS library.<br />Defaults to true'
                    ),
                    array(
                        'name'  => 'enqueue_datetimepicker',
                        'label' => 'Enqueue Date/timepicker',
                        'value' => '<code>' . (acf_get_setting('enqueue_datetimepicker') ? __('True'): __('False')) . '</code>',
                        'description' => 'Allows ACF to enqueue and load the datetimepicker JS/CSS library.<br />Defaults to true'
                    ),
                    array(
                        'name'  => 'row_index_offset',
                        'label' => 'Row index offset',
                        'value' => '<code>' . acf_get_setting('row_index_offset') . '</code>',
                        'description' => 'Defines the starting index used in all ‘loop’ and ‘row’ functions.<br />Defaults to 1 (1 is the first row), can be changed to 0 (0 is the first row)'
                    ),
                    array(
                        'name'  => 'remove_wp_meta_box',
                        'label' => 'Remove WP meta box',
                        'value' => '<code>' . (acf_get_setting('remove_wp_meta_box') ? __('True'): __('False')) . '</code>',
                        'description' => 'Allows ACF to remove the default WP custom fields metabox. Defaults to true'
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
                        'label' => 'Module: Author',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/author', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Author module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_block_types',
                        'label' => 'Module: Dynamic Block Types',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_block_types', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Block Types module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_forms',
                        'label' => 'Module: Dynamic Forms',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_forms', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Forms module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_post_types',
                        'label' => 'Module: Dynamic Post Types',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_post_types', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Post Types module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_taxonomies',
                        'label' => 'Module: Dynamic Taxonomies',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_taxonomies', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Taxonomies module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/modules/dynamic_options_pages',
                        'label' => 'Module: Dynamic Options Pages',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/dynamic_options_pages', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Options Pages module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/modules/options',
                        'label' => 'Module: Options',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/options', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Options module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/modules/taxonomies',
                        'label' => 'Module: Taxonomies Enhancements',
                        'value' => '<code>' . (acf_get_setting('acfe/modules/taxonomies', true) ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the Taxonomies enhancements module. Defaults to true'
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/site_key',
                        'label' => 'Field: reCaptcha site key',
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/site_key') . '</code>',
                        'description' => 'The default reCaptcha site key'
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/secret_key',
                        'label' => 'Field: reCaptcha secret key',
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/secret_key') . '</code>',
                        'description' => 'The default reCaptcha secret key'
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/version',
                        'label' => 'Field: reCaptcha version',
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/version', 'v2') . '</code>',
                        'description' => 'The default reCaptcha version'
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/v2/theme',
                        'label' => 'Field: reCaptcha v2 theme',
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/v2/theme', 'light') . '</code>',
                        'description' => 'The default reCaptcha v2 theme'
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/v2/size',
                        'label' => 'Field: reCaptcha v2 size',
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/v2/size', 'normal') . '</code>',
                        'description' => 'The default reCaptcha v2 size'
                    ),
                    array(
                        'name'  => 'acfe/field/recaptcha/v3/hide_logo',
                        'label' => 'Field: reCaptcha v3 hide logo',
                        'value' => '<code>' . acf_get_setting('acfe/field/recaptcha/v3/hide_logo') . '</code>',
                        'description' => 'Show/hide reCaptcha v3 logo'
                    ),
                    array(
                        'name'  => 'acfe/dev',
                        'label' => 'Dev mode',
                        'value' => '<code>' . (acf_get_setting('acfe/dev') ? __('True'): __('False')) . '</code>',
                        'description' => 'Show/hide the advanced WP post meta box. Defaults to false'
                    ),
                    array(
                        'name'  => 'acfe/php',
                        'label' => 'PHP',
                        'value' => '<code>' . (acf_get_setting('acfe/php') ? __('True'): __('False')) . '</code>',
                        'description' => 'Allow PHP Sync'
                    ),
                    array(
                        'name'  => 'acfe/php_found',
                        'label' => 'PHP: Found',
                        'value' => '<code>' . (acf_get_setting('acfe/php_found') ? __('True'): __('False')) . '</code>',
                        'description' => 'Found PHP Sync load folder'
                    ),
                    array(
                        'name'  => 'acfe/php_save',
                        'label' => 'PHP: Save',
                        'value' => '<code>' . acf_get_setting('acfe/php_save') . '</code>',
                        'description' => 'Found PHP Sync save folder'
                    ),
                    array(
                        'name'  => 'acfe/php_load',
                        'label' => 'PHP: Load',
                        'value' => '<code>' . $load_php_text . '</code>',
                        'description' => 'PHP Sync Load path'
                    ),
                    array(
                        'name'  => 'acfe/json_found',
                        'label' => 'Json: Found',
                        'value' => '<code>' . (acf_get_setting('acfe/json_found') ? __('True'): __('False')) . '</code>',
                        'description' => 'Found Json Sync load folder'
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
                        'label': 'left'
                    });	

                }
                </script>
            </div>
        </div>
    </div>
    
</div>
<?php
}