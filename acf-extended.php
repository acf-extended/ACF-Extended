<?php
/**
 * Plugin Name: Advanced Custom Fields: Extended
 * Description: All-in-one enhancement suite that improves WordPress & Advanced Custom Fields.
 * Version:     0.8.8.7
 * Author:      ACF Extended
 * Author URI:  https://www.acf-extended.com
 * Text Domain: acfe
 * Domain Path: /lang
 */

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE')):

class ACFE{
    
    // Vars
    var $version = '0.8.8.7';
    
    /*
     * Construct
     */
    function __construct(){
        // ...
    }
    
    /*
     * Initialize
     */
    function initialize(){
        
        // Constants
        $this->constants(array(
            'ACFE'          => true,
            'ACFE_FILE'     => __FILE__,
            'ACFE_PATH'     => plugin_dir_path(__FILE__),
            'ACFE_VERSION'  => $this->version,
            'ACFE_BASENAME' => plugin_basename(__FILE__),
        ));
        
        // Init
        include_once(ACFE_PATH . 'includes/init.php');
        
        // Functions
        acfe_include('includes/acfe-field-functions.php');
        acfe_include('includes/acfe-field-group-functions.php');
        acfe_include('includes/acfe-file-functions.php');
        acfe_include('includes/acfe-form-functions.php');
        acfe_include('includes/acfe-helper-functions.php');
        acfe_include('includes/acfe-meta-functions.php');
        acfe_include('includes/acfe-post-functions.php');
        acfe_include('includes/acfe-screen-functions.php');
        acfe_include('includes/acfe-template-functions.php');
        acfe_include('includes/acfe-term-functions.php');
        acfe_include('includes/acfe-user-functions.php');
        acfe_include('includes/acfe-wp-functions.php');
        
        // Compatibility
        acfe_include('includes/compatibility.php');
    
        // Load
        add_action('acf/include_field_types', array($this, 'load'));
        
    }
    
    /*
     * Load
     */
    function load(){
        
        if(!$this->acf()) return;
        
        // Vars
        $theme_path = acf_get_setting('acfe/theme_path', get_stylesheet_directory());
        $theme_url = acf_get_setting('acfe/theme_url', get_stylesheet_directory_uri());
        $reserved_post_types = array('acf-field', 'acf-field-group', 'acfe-dbt', 'acfe-form', 'acfe-dop', 'acfe-dpt', 'acfe-dt');
        $reserved_taxonomies = array('acf-field-group-category');
        $reserved_field_groups = array(
            'group_acfe_dynamic_block_type',
            'group_acfe_dynamic_form',
            'group_acfe_dynamic_options_page',
            'group_acfe_dynamic_post_type',
            'group_acfe_dynamic_taxonomy',
        );
        
        // Settings
        $this->settings(array(
            
            // General
            'url'                           => plugin_dir_url(__FILE__),
            'theme_path'                    => $theme_path,
            'theme_url'                     => $theme_url,
            'theme_folder'                  => parse_url($theme_url, PHP_URL_PATH),
            'reserved_post_types'           => $reserved_post_types,
            'reserved_taxonomies'           => $reserved_taxonomies,
            'reserved_field_groups'         => $reserved_field_groups,
            
            // Php
            'php'                           => true,
            'php_save'                      => "{$theme_path}/acfe-php",
            'php_load'                      => array("{$theme_path}/acfe-php"),
            'php_found'                     => false,
            
            // Json
            'json'                          => acf_get_setting('json'),
            'json_save'                     => acf_get_setting('save_json'),
            'json_load'                     => acf_get_setting('load_json'),
            'json_found'                    => false,
            
            // Modules
            'dev'                           => false,
            'modules/author'                => true,
            'modules/categories'            => true,
            'modules/block_types'           => true,
            'modules/forms'                 => true,
            'modules/options_pages'         => true,
            'modules/post_types'            => true,
            'modules/taxonomies'            => true,
            'modules/multilang'             => true,
            'modules/options'               => true,
            'modules/single_meta'           => false,
            'modules/ui'                    => true,
            
            // Fields
            'field/recaptcha/site_key'      => null,
            'field/recaptcha/secret_key'    => null,
            'field/recaptcha/version'       => null,
            'field/recaptcha/v2/theme'      => null,
            'field/recaptcha/v2/size'       => null,
            'field/recaptcha/v3/hide_logo'  => null,
            
        ));
    
        // Load textdomain file
        acfe_load_textdomain();
        
        // Includes
        add_action('acf/init',                  array($this, 'init'), 99);
        add_action('acf/include_fields',        array($this, 'include_fields'), 5);
        add_action('acf/include_field_types',   array($this, 'include_field_types'), 99);
        add_action('acf/include_admin_tools',   array($this, 'include_admin_tools'));
        add_action('acf/include_admin_tools',   array($this, 'include_admin_tools_late'), 20);
        
        // Admin
        acfe_include('includes/admin/menu.php');
        acfe_include('includes/admin/plugins.php');
        acfe_include('includes/admin/settings.php');
    
        // Core
        acfe_include('includes/local-meta.php');
        acfe_include('includes/multilang.php');
        acfe_include('includes/settings.php');
        acfe_include('includes/upgrades.php');
        
        // Forms
        acfe_include('includes/forms/form-attachment.php');
        acfe_include('includes/forms/form-options-page.php');
        acfe_include('includes/forms/form-post.php');
        acfe_include('includes/forms/form-settings.php');
        acfe_include('includes/forms/form-taxonomy.php');
        acfe_include('includes/forms/form-user.php');

    }
    
    /*
     * Init
     */
    function init(){
        
        /*
         * Action
         */
        do_action('acfe/init');
        
        /*
         * Core
         */
        acfe_include('includes/assets.php');
        acfe_include('includes/hooks.php');
        
        /*
         * Admin
         */
        acfe_include('includes/admin/admin.php');
        acfe_include('includes/admin/plugins.php');
        
        /*
         * Fields
         */
        acfe_include('includes/fields/field-checkbox.php');
        acfe_include('includes/fields/field-clone.php');
        acfe_include('includes/fields/field-file.php');
        acfe_include('includes/fields/field-flexible-content.php');
        acfe_include('includes/fields/field-group.php');
        acfe_include('includes/fields/field-image.php');
        acfe_include('includes/fields/field-post-object.php');
        acfe_include('includes/fields/field-repeater.php');
        acfe_include('includes/fields/field-select.php');
        acfe_include('includes/fields/field-textarea.php');
        acfe_include('includes/fields/field-wysiwyg.php');
        
        /*
         * Fields settings
         */
        acfe_include('includes/fields-settings/bidirectional.php');
        acfe_include('includes/fields-settings/data.php');
        acfe_include('includes/fields-settings/instructions.php');
        acfe_include('includes/fields-settings/permissions.php');
        acfe_include('includes/fields-settings/settings.php');
        acfe_include('includes/fields-settings/validation.php');
        
        /*
         * Field Groups
         */
        acfe_include('includes/field-groups/field-group.php');
        acfe_include('includes/field-groups/field-group-advanced.php');
        acfe_include('includes/field-groups/field-group-category.php');
        acfe_include('includes/field-groups/field-group-display-title.php');
        acfe_include('includes/field-groups/field-group-hide-on-screen.php');
        acfe_include('includes/field-groups/field-group-instruction-placement.php');
        acfe_include('includes/field-groups/field-group-meta.php');
        acfe_include('includes/field-groups/field-group-permissions.php');
        acfe_include('includes/field-groups/field-groups.php');
        acfe_include('includes/field-groups/field-groups-local.php');
        
        /*
         * Locations
         */
        acfe_include('includes/locations/post-type-all.php');
        acfe_include('includes/locations/post-type-archive.php');
        acfe_include('includes/locations/post-type-list.php');
        acfe_include('includes/locations/taxonomy-list.php');
        
        /*
         * Modules
         */
        acfe_include('includes/modules/module.php');
        acfe_include('includes/modules/author.php');
        acfe_include('includes/modules/dev.php');
        acfe_include('includes/modules/block-types.php');
        acfe_include('includes/modules/forms.php');
        acfe_include('includes/modules/options.php');
        acfe_include('includes/modules/options-pages.php');
        acfe_include('includes/modules/post-types.php');
        acfe_include('includes/modules/taxonomies.php');
        acfe_include('includes/modules/single-meta.php');
        acfe_include('includes/modules/ui.php');
        acfe_include('includes/modules/ui-settings.php');
        acfe_include('includes/modules/ui-term.php');
        acfe_include('includes/modules/ui-user.php');
        
    }
    
    /*
     * Incldude Fields
     */
    function include_fields(){
        
        // AutoSync
        acfe_include('includes/modules/autosync.php');
        
    }
    
    /*
     * Include Field Types
     */
    function include_field_types(){
        
        acfe_include('includes/fields/field-advanced-link.php');
        acfe_include('includes/fields/field-button.php');
        acfe_include('includes/fields/field-code-editor.php');
        acfe_include('includes/fields/field-column.php');
        acfe_include('includes/fields/field-dynamic-render.php');
        acfe_include('includes/fields/field-forms.php');
        acfe_include('includes/fields/field-hidden.php');
        acfe_include('includes/fields/field-post-statuses.php');
        acfe_include('includes/fields/field-post-types.php');
        acfe_include('includes/fields/field-recaptcha.php');
        acfe_include('includes/fields/field-slug.php');
        acfe_include('includes/fields/field-taxonomies.php');
        acfe_include('includes/fields/field-taxonomy-terms.php');
        acfe_include('includes/fields/field-user-roles.php');
        
    }
    
    /*
     * Include Admin Tools
     */
    function include_admin_tools(){
        
        // Modules
        acfe_include('includes/admin/tools/module-export.php');
        acfe_include('includes/admin/tools/module-import.php');
        
        acfe_include('includes/admin/tools/post-types-export.php');
        acfe_include('includes/admin/tools/post-types-import.php');
        acfe_include('includes/admin/tools/taxonomies-export.php');
        acfe_include('includes/admin/tools/taxonomies-import.php');
        acfe_include('includes/admin/tools/options-pages-export.php');
        acfe_include('includes/admin/tools/options-pages-import.php');
        acfe_include('includes/admin/tools/block-types-export.php');
        acfe_include('includes/admin/tools/block-types-import.php');
        acfe_include('includes/admin/tools/forms-export.php');
        acfe_include('includes/admin/tools/forms-import.php');
        
    }
    
    /*
     * Include Admin Tools Late
     */
    function include_admin_tools_late(){
        
        // Field Groups
        acfe_include('includes/admin/tools/field-groups-local.php');
        acfe_include('includes/admin/tools/field-groups-export.php');
        
    }

    /*
     * Set Constants
     */
    function constants($array = array()){
    
        foreach($array as $name => $value){
        
            if(defined($name)) continue;
            
            define($name, $value);
        
        }
        
    }
    
    /*
     * Set Settings
     */
    function settings($array = array()){
        
        foreach($array as $name => $value){
        
            // update
            acf_update_setting("acfe/{$name}", $value);
        
            add_filter("acf/settings/acfe/{$name}", function($value) use($name){
            
                return apply_filters("acfe/settings/{$name}", $value);
            
            }, 5);
        
        }
        
    }
    
    /*
     * ACF
     */
    function acf(){
        
        return class_exists('ACF') && defined('ACF_PRO') && defined('ACF_VERSION') && version_compare(ACF_VERSION, '5.8', '>=');
        
    }
    
}

function acfe(){
    
    global $acfe;
    
    if(!isset($acfe)){
        
        $acfe = new ACFE();
        $acfe->initialize();
        
    }
    
    return $acfe;
    
}

acfe();

endif;