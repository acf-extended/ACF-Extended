<?php
/**
 * Plugin Name: Advanced Custom Fields: Extended
 * Description: Enhancement Suite which improves Advanced Custom Fields administration
 * Version:     0.8.5.5
 * Author:      ACF Extended
 * Author URI:  https://www.acf-extended.com
 * Text Domain: acfe
 */

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE')):

class ACFE{
    
    // Version
    var $version = '0.8.5.5';
    
    // Settings
    var $settings = array();
    
    // ACF
    var $acf = false;
    
    /**
     * ACFE: Construct
     */
    function __construct(){
        // ...
    }
    
    /**
     * ACFE: Initialize
     */
    function initialize(){
        
        // Constants
        $this->define('ACFE',               true);
        $this->define('ACFE_FILE',          __FILE__);
        $this->define('ACFE_PATH',          plugin_dir_path(__FILE__));
        $this->define('ACFE_VERSION',       $this->version);
        $this->define('ACFE_BASENAME',      plugin_basename(__FILE__));
        $this->define('ACFE_THEME_PATH',    get_stylesheet_directory());
        $this->define('ACFE_THEME_URL',     get_stylesheet_directory_uri());
        
        // Define settings
        $this->settings = array(
            'acfe/url'                              => plugin_dir_url(__FILE__),
            'acfe/php'                              => true,
            'acfe/php_save'                         => ACFE_THEME_PATH . '/acfe-php',
            'acfe/php_load'                         => array(ACFE_THEME_PATH . '/acfe-php'),
            'acfe/php_found'                        => false,
            'acfe/json_found'                       => false,
            'acfe/dev'                              => false,
            'acfe/modules/author'                   => true,
            'acfe/modules/dynamic_block_types'      => true,
            'acfe/modules/dynamic_forms'            => true,
            'acfe/modules/dynamic_options_pages'    => true,
            'acfe/modules/dynamic_post_types'       => true,
            'acfe/modules/dynamic_taxonomies'       => true,
            'acfe/modules/options'                  => true,
            'acfe/modules/single_meta'              => false,
            'acfe/modules/taxonomies'               => true,
        );
        
        // Init
        include_once(ACFE_PATH . 'init.php');
        
        // Load
        add_action('acf/include_field_types', array($this, 'load'));
        
    }
    
    /**
     * ACFE: Load
     */
    function load(){
        
        if(!$this->has_acf())
            return;
        
        // Settings
        foreach($this->settings as $name => $value){
            
            acf_update_setting($name, $value);
            
        }
        
        // Load
        add_action('acf/init',                  array($this, 'includes'), 99);
        
        // AutoSync
        add_action('acf/include_fields',        array($this, 'autosync'), 5);
        
        // Fields
        add_action('acf/include_field_types',   array($this, 'fields'), 99);
        
        // Tools
        add_action('acf/include_admin_tools',   array($this, 'tools'));
        
        // Additional
        acfe_include('includes/core/settings.php');
        acfe_include('includes/core/compatibility.php');
	    acfe_include('includes/core/upgrades.php');

    }
    
    /**
     * ACFE: includes
     */
    function includes(){
        
        /**
         * Core
         */
        acfe_include('includes/core/enqueue.php');
        acfe_include('includes/core/helpers.php');
        acfe_include('includes/core/menu.php');
        
        /**
         * Admin Pages
         */
        acfe_include('includes/admin/options.php');
        acfe_include('includes/admin/plugins.php');
        acfe_include('includes/admin/settings.php');
        
        /**
         * Fields
         */
        acfe_include('includes/fields/field-clone.php');
        acfe_include('includes/fields/field-file.php');
        acfe_include('includes/fields/field-flexible-content.php');
        acfe_include('includes/fields/field-group.php');
        acfe_include('includes/fields/field-image.php');
        acfe_include('includes/fields/field-post-object.php');
        acfe_include('includes/fields/field-repeater.php');
        acfe_include('includes/fields/field-select.php');
        acfe_include('includes/fields/field-textarea.php');
        
        /**
         * Fields settings
         */
        acfe_include('includes/fields-settings/bidirectional.php');
        acfe_include('includes/fields-settings/data.php');
        acfe_include('includes/fields-settings/fields.php');
        acfe_include('includes/fields-settings/permissions.php');
        acfe_include('includes/fields-settings/settings.php');
        acfe_include('includes/fields-settings/validation.php');
        
        /**
         * Field Groups
         */
        acfe_include('includes/field-groups/field-group.php');
        acfe_include('includes/field-groups/field-group-category.php');
        acfe_include('includes/field-groups/field-groups.php');
        acfe_include('includes/field-groups/field-groups-local.php');
        
        /**
         * Locations
         */
        acfe_include('includes/locations/post-type-all.php');
        acfe_include('includes/locations/post-type-archive.php');
        acfe_include('includes/locations/post-type-list.php');
        acfe_include('includes/locations/taxonomy-list.php');
        
        /**
         * Modules
         */
        acfe_include('includes/modules/author.php');
        acfe_include('includes/modules/dev.php');
        acfe_include('includes/modules/dynamic-block-type.php');
        acfe_include('includes/modules/dynamic-form.php');
        acfe_include('includes/modules/dynamic-options-page.php');
        acfe_include('includes/modules/dynamic-post-type.php');
        acfe_include('includes/modules/dynamic-taxonomy.php');
        acfe_include('includes/modules/single-meta.php');
        acfe_include('includes/modules/taxonomy.php');
        
    }
    
    /**
     * ACFE: AutoSync
     */
    function autosync(){
        
        acfe_include('includes/modules/autosync.php');
        
    }
    
    /**
     * ACFE: Fields
     */
    function fields(){
        
        acfe_include('includes/fields/field-advanced-link.php');
        acfe_include('includes/fields/field-button.php');
        acfe_include('includes/fields/field-code-editor.php');
        acfe_include('includes/fields/field-column.php');
        acfe_include('includes/fields/field-dynamic-message.php');
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
    
    /**
     * ACFE: Tools
     */
    function tools(){
        
        acfe_include('includes/admin/tools/dbt-export.php');
        acfe_include('includes/admin/tools/dbt-import.php');
        acfe_include('includes/admin/tools/dpt-export.php');
        acfe_include('includes/admin/tools/dpt-import.php');
        acfe_include('includes/admin/tools/dt-export.php');
        acfe_include('includes/admin/tools/dt-import.php');
        acfe_include('includes/admin/tools/dop-export.php');
        acfe_include('includes/admin/tools/dop-import.php');
        
        acfe_include('includes/admin/tools/form-export.php');
        acfe_include('includes/admin/tools/form-import.php');
        acfe_include('includes/admin/tools/fg-local.php');
        acfe_include('includes/admin/tools/fg-export.php');
        
    }

	/**
	 * ACFE: Define
	 *
	 * @param $name
	 * @param bool $value
	 */
    function define($name, $value = true){
        
        if(!defined($name))
            define($name, $value);
        
    }
    
    /**
     * ACFE: Has ACF
     */
    function has_acf(){
        
        if($this->acf)
            return true;
        
        $this->acf = class_exists('ACF') && defined('ACF_PRO') && defined('ACF_VERSION') && version_compare(ACF_VERSION, '5.7.10', '>=');
        
        return $this->acf;
        
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

// Instantiate.
acfe();

endif;