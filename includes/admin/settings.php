<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_admin_settings')):

class acfe_admin_settings{
    
    // vars
    public $defaults = array();
    public $updated = array();
    public $fields = array();
    
    /**
     * construct
     */
    function __construct(){
    
        add_action('acf/init', array($this, 'acf_pre_init'), 1);
        add_action('acf/init', array($this, 'acf_post_init'), 100);
        
        $this->register_fields();
        
    }
    
    
    /**
     * acf_pre_init
     */
    function acf_pre_init(){
        $this->defaults = acf()->settings;
    }
    
    
    /**
     * acf_post_init
     */
    function acf_post_init(){
        
        $settings = acf_get_array(acf()->settings);
        
        foreach($settings as $name => $value){
            
            // pass thru acf/settings filter
            $this->updated[ $name ] = acf_get_setting($name, $value);
        }
        
    }
    
    
    /**
     * register_fields
     */
    function register_fields(){
    
        $this->fields = array(
        
            // ACF
            'acf' => array(
            
                array(
                    'label'         => 'Path',
                    'name'          => 'path',
                    'type'          => 'text',
                    'description'   => 'Absolute path to ACF plugin folder including trailing slash.<br />Defaults to plugin_dir_path',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'URL',
                    'name'          => 'url',
                    'type'          => 'text',
                    'description'   => 'URL to ACF plugin folder including trailing slash. Defaults to plugin_dir_url',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Show admin',
                    'name'          => 'show_admin',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide ACF menu item. Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Strip slashes',
                    'name'          => 'stripslashes',
                    'type'          => 'true_false',
                    'description'   => 'Runs the function stripslashes on all $_POST data. Some servers / WP instals may require this extra functionality. Defaults to false',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Local',
                    'name'          => 'local',
                    'type'          => 'true_false',
                    'description'   => 'Enable/Disable local (PHP/json) fields. Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Json',
                    'name'          => 'json',
                    'type'          => 'true_false',
                    'description'   => 'Enable/Disable json fields. Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Json folder (save)',
                    'name'          => 'save_json',
                    'type'          => 'text',
                    'description'   => 'Absolute path to folder where json files will be created when field groups are saved.<br />Defaults to ‘acf-json’ folder within current theme',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Json folder (load)',
                    'name'          => 'load_json',
                    'type'          => 'text',
                    'description'   => 'Array of absolutes paths to folders where field group json files can be read.<br />Defaults to an array containing at index 0, the ‘acf-json’ folder within current theme',
                    'category'      => 'acf',
                    'format'        => 'array',
                ),
                array(
                    'label'         => 'Default language',
                    'name'          => 'default_language',
                    'type'          => 'true_false',
                    'description'   => 'Language code of the default language. Defaults to ”.<br />If WPML is active, ACF will default this to the WPML default language setting',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Current language',
                    'name'          => 'current_language',
                    'type'          => 'true_false',
                    'description'   => 'Language code of the current post’s language. Defaults to ”.<br />If WPML is active, ACF will default this to the WPML current language',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Capability',
                    'name'          => 'capability',
                    'type'          => 'text',
                    'description'   => 'Capability used for ACF post types and if the current user can see the ACF menu item.<br />Defaults to ‘manage_options’.',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Show updates',
                    'name'          => 'show_updates',
                    'type'          => 'true_false',
                    'description'   => 'Enable/Disable updates to appear in plugin list and show/hide the ACF updates admin page.<br />Defaults to true.',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Auto load',
                    'name'          => 'autoload',
                    'type'          => 'true_false',
                    'description'   => 'Sets the text domain used when translating field and field group settings.<br />Defaults to ”. Strings will not be translated if this setting is empty',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'l10n',
                    'name'          => 'l10n',
                    'type'          => 'true_false',
                    'description'   => 'Allows ACF to translate field and field group settings using the __() function.<br />Defaults to true. Useful to override translation without modifying the textdomain',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'l10n textdomain',
                    'name'          => 'l10n_textdomain',
                    'type'          => 'text',
                    'description'   => 'Sets the text domain used when translating field and field group settings.<br />Defaults to ”. Strings will not be translated if this setting is empty',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Google API key',
                    'name'          => 'google_api_key',
                    'type'          => 'text',
                    'description'   => 'Specify a Google Maps API authentication key to prevent usage limits.<br />Defaults to ”',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Google API client',
                    'name'          => 'google_api_client',
                    'type'          => 'text',
                    'description'   => 'Specify a Google Maps API Client ID to prevent usage limits.<br />Not needed if using <code>google_api_key</code>. Defaults to ”',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Enqueue Google Maps',
                    'name'          => 'enqueue_google_maps',
                    'type'          => 'true_false',
                    'description'   => 'Allows ACF to enqueue and load the Google Maps API JS library.<br />Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Enqueue Select2',
                    'name'          => 'enqueue_select2',
                    'type'          => 'true_false',
                    'description'   => 'Allows ACF to enqueue and load the Select2 JS/CSS library.<br />Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Select2 version',
                    'name'          => 'select2_version',
                    'type'          => 'text',
                    'description'   => 'Defines which version of Select2 library to enqueue. Either 3 or 4.<br />Defaults to 4 since ACF 5.6.0',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Enqueue Date picker',
                    'name'          => 'enqueue_datepicker',
                    'type'          => 'true_false',
                    'description'   => 'Allows ACF to enqueue and load the WP datepicker JS/CSS library.<br />Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Enqueue Date Time picker',
                    'name'          => 'enqueue_datetimepicker',
                    'type'          => 'true_false',
                    'description'   => 'Allows ACF to enqueue and load the datetimepicker JS/CSS library.<br />Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Row index offset',
                    'name'          => 'row_index_offset',
                    'type'          => 'text',
                    'description'   => 'Defines the starting index used in all ‘loop’ and ‘row’ functions.<br />Defaults to 1 (1 is the first row), can be changed to 0 (0 is the first row)',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Remove WP meta box',
                    'name'          => 'remove_wp_meta_box',
                    'type'          => 'true_false',
                    'description'   => 'Allows ACF to remove the default WP custom fields metabox. Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Rest API enabled',
                    'name'          => 'rest_api_enabled',
                    'type'          => 'true_false',
                    'description'   => 'Enables/disables the ACF REST API integration.. Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Rest API format',
                    'name'          => 'rest_api_format',
                    'type'          => 'text',
                    'description'   => 'Defines how ACF formats field values in the REST API. Defaults to light',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Rest API Embed Links',
                    'name'          => 'rest_api_embed_links',
                    'type'          => 'true_false',
                    'description'   => 'Enables/disables embed links for ACF fields in the REST API. Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Preload Blocks',
                    'name'          => 'preload_blocks',
                    'type'          => 'true_false',
                    'description'   => 'Allows ACF to preload the initial render html of ACF Blocks into the block editor. Defaults to true',
                    'category'      => 'acf',
                ),
                array(
                    'label'         => 'Enable Shortcode',
                    'name'          => 'enable_shortcode',
                    'type'          => 'true_false',
                    'description'   => 'Enable the ACF shortcode. Defaults to true',
                    'category'      => 'acf',
                ),
        
            ),
        
        
            // ACFE
            'acfe' => array(
            
                array(
                    'label'         => 'Theme Folder',
                    'name'          => 'acfe/theme_folder',
                    'type'          => 'text',
                    'description'   => 'Detected Theme Folder',
                    'category'      => 'acfe',
                ),
                array(
                    'label'         => 'Theme Path',
                    'name'          => 'acfe/theme_path',
                    'type'          => 'text',
                    'description'   => 'Detected Theme Path',
                    'category'      => 'acfe',
                ),
                array(
                    'label'         => 'Theme URL',
                    'name'          => 'acfe/theme_url',
                    'type'          => 'text',
                    'description'   => 'Detected Theme URL',
                    'category'      => 'acfe',
                ),
        
            ),
        
            // AutoSync
            'autosync' => array(
            
                array(
                    'label'         => 'Json',
                    'name'          => 'acfe/json',
                    'type'          => 'true_false',
                    'description'   => 'Whenever Json AutoSync is enabled',
                    'category'      => 'autosync',
                ),
                array(
                    'label'         => 'Json: Load',
                    'name'          => 'acfe/json_load',
                    'type'          => 'text',
                    'description'   => 'Json AutoSync load paths (array)',
                    'category'      => 'autosync',
                    'format'        => 'array',
                ),
                array(
                    'label'         => 'Json: Save',
                    'name'          => 'acfe/json_save',
                    'type'          => 'text',
                    'description'   => 'Json AutoSync saving path',
                    'category'      => 'autosync',
                ),
                array(
                    'label'         => 'PHP',
                    'name'          => 'acfe/php',
                    'type'          => 'true_false',
                    'description'   => 'Whenever PHP AutoSync is enabled',
                    'category'      => 'autosync',
                ),
                array(
                    'label'         => 'PHP: Load',
                    'name'          => 'acfe/php_load',
                    'type'          => 'text',
                    'description'   => 'PHP AutoSync load paths (array)',
                    'category'      => 'autosync',
                    'format'        => 'array',
                ),
                array(
                    'label'         => 'PHP: Save',
                    'name'          => 'acfe/php_save',
                    'type'          => 'text',
                    'description'   => 'PHP AutoSync saving path',
                    'category'      => 'autosync',
                ),
        
            ),
        
            // Modules
            'modules' => array(
            
                array(
                    'label'         => 'Author',
                    'name'          => 'acfe/modules/author',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Author module. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Block Types',
                    'name'          => 'acfe/modules/block_types',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Block Types module. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Categories',
                    'name'          => 'acfe/modules/categories',
                    'type'          => 'true_false',
                    'description'   => 'Enable/disable the Field Group Categories taxonomy. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Developer mode',
                    'name'          => 'acfe/dev',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the advanced WP post meta box. Defaults to false',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Forms',
                    'name'          => 'acfe/modules/forms',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Forms module. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Forms: Top Level',
                    'name'          => 'acfe/modules/forms/top_level',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Forms module as top level menu. Defaults to false',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Multilangual',
                    'name'          => 'acfe/modules/multilang',
                    'type'          => 'true_false',
                    'description'   => 'Enable/disable Multilang compatibility module for WPML & Polylang. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Options',
                    'name'          => 'acfe/modules/options',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Options module. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Options Pages',
                    'name'          => 'acfe/modules/options_pages',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Options Pages module. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Performance',
                    'name'          => 'acfe/modules/performance',
                    'type'          => 'text',
                    'description'   => 'Enable/disable Performance module. Defaults to false',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Post Types',
                    'name'          => 'acfe/modules/post_types',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Post Types module. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'Taxonomies',
                    'name'          => 'acfe/modules/taxonomies',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the Taxonomies module. Defaults to true',
                    'category'      => 'modules',
                ),
                array(
                    'label'         => 'UI Enhancements',
                    'name'          => 'acfe/modules/ui',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide the UI enhancements module. Defaults to true',
                    'category'      => 'modules',
                ),
        
            ),
        
            // Fields
            'fields' => array(
            
                array(
                    'label'         => 'reCaptcha: Secret key',
                    'name'          => 'acfe/field/recaptcha/secret_key',
                    'type'          => 'text',
                    'description'   => 'The default reCaptcha secret key',
                    'category'      => 'fields',
                ),
                array(
                    'label'         => 'reCaptcha: Site key',
                    'name'          => 'acfe/field/recaptcha/site_key',
                    'type'          => 'text',
                    'description'   => 'The default reCaptcha site key',
                    'category'      => 'fields',
                ),
                array(
                    'label'         => 'reCaptcha: Version',
                    'name'          => 'acfe/field/recaptcha/version',
                    'type'          => 'text',
                    'description'   => 'The default reCaptcha version',
                    'category'      => 'fields',
                ),
                array(
                    'label'         => 'reCaptcha: V2 size',
                    'name'          => 'acfe/field/recaptcha/v2/size',
                    'type'          => 'text',
                    'description'   => 'The default reCaptcha v2 size',
                    'category'      => 'fields',
                ),
                array(
                    'label'         => 'reCaptcha: V2 theme',
                    'name'          => 'acfe/field/recaptcha/v2/theme',
                    'type'          => 'text',
                    'description'   => 'The default reCaptcha v2 theme',
                    'category'      => 'fields',
                ),
                array(
                    'label'         => 'reCaptcha: V3 hide logo',
                    'name'          => 'acfe/field/recaptcha/v3/hide_logo',
                    'type'          => 'true_false',
                    'description'   => 'Show/hide reCaptcha v3 logo',
                    'category'      => 'fields',
                ),
        
            ),
    
        );
        
    }
    
}

// instantiate
acf_new_instance('acfe_admin_settings');

endif;

if(!class_exists('acfe_admin_settings_ui')):

class acfe_admin_settings_ui{
    
    // vars
    public $defaults = array();
    public $updated = array();
    public $fields = array();
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('admin_menu',                array($this, 'admin_menu'));
        add_action('acfe/admin_settings/load',  array($this, 'load'));
        add_action('acfe/admin_settings/html',  array($this, 'html'));
    
    }
    
    
    /**
     * admin_menu
     */
    function admin_menu(){
        
        if(!acf_get_setting('show_admin')){
            return;
        }
    
        $page = add_submenu_page('edit.php?post_type=acf-field-group', __('Settings'), __('Settings'), acf_get_setting('capability'), 'acfe-settings', array($this, 'menu_html'));
        
        add_action("load-{$page}", array($this, 'menu_load'));
        
    }
    
    
    /**
     * menu_load
     */
    function menu_load(){
        do_action('acfe/admin_settings/load');
    }
    
    
    /**
     * menu_html
     */
    function menu_html(){
        do_action('acfe/admin_settings/html');
    }
    
    
    /**
     * load
     */
    function load(){
    
        $acfe_admin_settings = acf_get_instance('acfe_admin_settings');
        
        $this->defaults = $acfe_admin_settings->defaults;
        $this->updated = $acfe_admin_settings->updated;
        $this->fields = $acfe_admin_settings->fields;
        
        // Enqueue
        acf_enqueue_scripts();
        
        add_action('admin_footer', array($this, 'admin_footer'));
        
    }
    
    
    /**
     * admin_footer
     */
    function admin_footer(){
        ?>
        <script type="text/javascript">
        (function($) {
            $('body').removeClass('post-type-acf-field-group');
        })(jQuery);
        </script>
        <?php
    }
    
    
    /**
     * prepare_setting
     *
     * @param $setting
     *
     * @return array|false
     */
    function prepare_setting($setting){
    
        $setting = wp_parse_args($setting, array(
            'label'         => '',
            'name'          => '',
            'type'          => '',
            'description'   => '',
            'category'      => '',
            'format'        => '',
            'default'       => '',
            'updated'       => '',
            'diff'          => false,
        ));
        
        $name = $setting['name'];
        $type = $setting['type'];
        
        // setting doesn't exist in default acf settings
        // probably an older version of acf
        if(!isset($this->defaults[ $name ])){
            return false;
        }
        
        $format = $setting['format'];
        $default = $this->defaults[ $name ];
        $updated = $this->updated[ $name ];
        
        $vars = array(
            'default' => $this->defaults[ $name ],
            'updated' => $this->updated[ $name ]
        );
    
        foreach($vars as $v => $var){
        
            $result = $var;
        
            if($type === 'true_false'){
            
                $result = $var ? '<span class="dashicons dashicons-saved"></span>' : '<span class="dashicons dashicons-no-alt"></span>';
            
            }elseif($type === 'text'){
            
                $result = '<span class="dashicons dashicons-no-alt"></span>';
    
                if($format === 'array' && empty($var) && $v === 'updated' && $default !== $updated){
                    $var = array('(empty)');
                }
            
                if(!empty($var)){
                
                    if(!is_array($var)){
                        $var = explode(',', $var);
                    }
                
                    foreach($var as $k => &$r){
                        if(is_array($r)){
                            $encode = json_encode($r);
                            $r = '<div class="acfe-settings-text"><code>' . $encode . '</code></div>';
                        }else{
                            
                            if(!is_numeric($k)){
                                $r = "{$k} = {$r}";
                            }
                            
                            $r = '<div class="acf-js-tooltip acfe-settings-text" title="' . $r . '"><code>' . $r . '</code></div>';
                        }
                    }
                
                    $result = implode('', $var);
                
                }
            
            }
        
            $setting[$v] = $result;
        
        }
    
        // Local Changes
        if($default !== $updated){
        
            $setting['updated'] .= '<span style="color:#888; margin-left:7px;vertical-align: 6px;font-size:11px;">(Local code)</span>';
            $setting['diff'] = true;
        
        }
        
        return $setting;
        
    }
    
    
    /**
     * html
     */
    function html(){
        ?>
        <div class="wrap" id="acfe-admin-settings">

            <h1><?php _e('Settings'); ?></h1>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    
                    <!-- Metabox -->
                    <div id="postbox-container-2" class="postbox-container">
        
                        <div class="postbox acf-postbox">
                            <div class="postbox-header">
                                <h2 class="hndle ui-sortable-handle"><span><?php _e('Settings'); ?></span></h2>
                            </div>
                            <div class="inside acf-fields -left">
                            
                                <?php $this->render_fields(); ?>
        
                                <script type="text/javascript">
                                    if(typeof acf !== 'undefined'){
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
            </div>
            
        </div>
        <?php
    }
    
    
    /**
     * render_fields
     */
    function render_fields(){
        
        foreach(array('ACF', 'ACFE', 'AutoSync', 'Modules', 'Fields') as $tab){
            
            // Category
            $category = sanitize_title($tab);
            
            if(isset($this->fields[$category])){
    
                $fields = array();
                $count = 0;
    
                foreach($this->fields[$category] as $field){
                    
                    $field = $this->prepare_setting($field);
                    
                    // make sure the setting exists
                    if($field){
                        $fields[] = $field;
                    }
        
                }
    
                foreach($fields as $field){
        
                    if(!$field['diff']) continue;
                    $count++;
        
                }
    
                $class = $count > 0 ? 'acfe-tab-badge' : 'acfe-tab-badge acf-hidden';
                $tab .= ' <span class="' . $class . '">' . $count . '</span>';
    
                // Tab
                acf_render_field_wrap(array(
                    'type'  => 'tab',
                    'label' => $tab,
                    'key'   => 'field_acfe_settings_tabs',
                    'wrapper' => array(
                        'data-no-preference' => true,
                    ),
                ));
    
                // Thead
                acf_render_field_wrap(array(
                    'type'  => 'acfe_dynamic_render',
                    'label' => '',
                    'key'   => 'field_acfe_settings_thead_' . $category,
                    'wrapper' => array(
                        'class' => 'acfe-settings-thead'
                    ),
                    'render' => function($field){
                        ?>
                        <div>Default</div>
                        <div>Registered</div>
                        <?php
                    }
                ));
                
                $icon = acf_version_compare('wp', '>=', '5.5') ? 'dashicons-info-outline' : 'dashicons-info';
        
                foreach($fields as $field){ ?>

                    <div class="acf-field">
                        <div class="acf-label">
                            <span class="acfe-field-tooltip acf-js-tooltip dashicons <?php echo $icon; ?>" title="<?php echo $field['name']; ?>"></span>
                            <label><?php echo $field['label']; ?></label>
                            <?php if($field['description']){ ?>
                                <p class="description"><?php echo $field['description']; ?></p>
                            <?php } ?>
                        </div>
                        <div class="acf-input">

                            <div><?php echo $field['default']; ?></div>
                            <div><?php echo $field['updated']; ?></div>

                        </div>
                    </div>
            
                    <?php
                }
        
            }
            
        }
        
    }
    
}

acf_new_instance('acfe_admin_settings_ui');

endif;