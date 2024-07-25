<?php
/**
 * Plugin Name: Advanced Custom Fields: Extended
 * Description: All-in-one enhancement suite that improves WordPress & Advanced Custom Fields.
 * Version:     0.9.0.7
 * Author:      ACF Extended
 * Plugin URI:  https://www.acf-extended.com
 * Author URI:  https://www.acf-extended.com
 * Text Domain: acfe
 * Domain Path: /lang
 */

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE')):

class ACFE{
    
    // vars
    var $version = '0.9.0.7';
    
    /**
     * construct
     */
    function __construct(){
        // ...
    }
    
    
    /**
     * initialize
     */
    function initialize(){
        
        // constants
        $this->constants(array(
            'ACFE'          => true,
            'ACFE_FILE'     => __FILE__,
            'ACFE_PATH'     => plugin_dir_path(__FILE__),
            'ACFE_VERSION'  => $this->version,
            'ACFE_BASENAME' => plugin_basename(__FILE__),
        ));
        
        // init
        include_once(ACFE_PATH . 'includes/init.php');
        
        // functions
        acfe_include('includes/acfe-deprecated-functions.php');
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
        
        // compatibility
        acfe_include('includes/compatibility.php');
        acfe_include('includes/third-party.php');
    
        // load
        add_action('acf/include_field_types', array($this, 'load'));
        
    }
    
    
    /**
     * load
     *
     * acf/include_field_types
     */
    function load(){
        
        // bail early
        if(!acfe_has_acf()){
            return;
        }
        
        // vars
        $theme_path = acf_get_setting('acfe/theme_path', get_stylesheet_directory());
        $theme_url = acf_get_setting('acfe/theme_url', get_stylesheet_directory_uri());
        
        // settings
        $this->settings(array(
            
            // general
            'url'                           => plugin_dir_url(__FILE__),
            'theme_path'                    => $theme_path,
            'theme_url'                     => $theme_url,
            'theme_folder'                  => parse_url($theme_url, PHP_URL_PATH),
            'reserved_post_types'           => array('acf-field', 'acf-field-group'),
            'reserved_taxonomies'           => array('acf-field-group-category'),
            'reserved_field_groups'         => array(),
            
            // php
            'php'                           => true,
            'php_save'                      => "{$theme_path}/acfe-php",
            'php_load'                      => array("{$theme_path}/acfe-php"),
            'php_found'                     => false,
            
            // json
            'json'                          => acf_get_setting('json'),
            'json_save'                     => acf_get_setting('save_json'),
            'json_load'                     => acf_get_setting('load_json'),
            'json_found'                    => false,
            
            // modules
            'dev'                           => false,
            'modules/author'                => true,
            'modules/categories'            => true,
            'modules/block_types'           => true,
            'modules/forms'                 => true,
            'modules/forms/top_level'       => false,
            'modules/options_pages'         => true,
            'modules/post_types'            => true,
            'modules/taxonomies'            => true,
            'modules/multilang'             => true,
            'modules/options'               => true,
            'modules/performance'           => false,
            'modules/ui'                    => true,
            
            // fields
            'field/recaptcha/site_key'      => '',
            'field/recaptcha/secret_key'    => '',
            'field/recaptcha/version'       => '',
            'field/recaptcha/v2/theme'      => '',
            'field/recaptcha/v2/size'       => '',
            'field/recaptcha/v3/hide_logo'  => '',
            
        ));
    
        // load textdomain
        acfe_load_textdomain();
        
        // includes
        add_action('acf/init',                  array($this, 'init'), 99);
        add_action('acf/include_fields',        array($this, 'include_fields'), 5);
        add_action('acf/include_field_types',   array($this, 'include_field_types'), 99);
        add_action('acf/include_admin_tools',   array($this, 'include_admin_tools'));
        add_action('acf/include_admin_tools',   array($this, 'include_admin_tools_late'), 20);
        
        // compatibility
        acfe_include('includes/compatibility-6.0.php');
        
        // admin
        acfe_include('includes/admin/menu.php');
        acfe_include('includes/admin/plugins.php');
        acfe_include('includes/admin/settings.php');
    
        // core
        acfe_include('includes/field.php');
        acfe_include('includes/field-extend.php');
        acfe_include('includes/local-meta.php');
        acfe_include('includes/multilang.php');
        acfe_include('includes/settings.php');
        acfe_include('includes/template-tags.php');
        acfe_include('includes/upgrades.php');
    
        // module
        acfe_include('includes/module.php');
        acfe_include('includes/module-acf.php');
        acfe_include('includes/module-db.php');
        acfe_include('includes/module-l10n.php');
        acfe_include('includes/module-manager.php');
        acfe_include('includes/module-post.php');
        acfe_include('includes/module-posts.php');
        acfe_include('includes/module-upgrades.php');
    
        // block type
        acfe_include('includes/modules/block-type/module-block-type.php');
        acfe_include('includes/modules/block-type/module-block-type-fields.php');
        acfe_include('includes/modules/block-type/module-block-type-upgrades.php');
        
        // form
        acfe_include('includes/modules/form/module-form.php');
        acfe_include('includes/modules/form/module-form-action.php');
        acfe_include('includes/modules/form/module-form-action-custom.php');
        acfe_include('includes/modules/form/module-form-action-email.php');
        acfe_include('includes/modules/form/module-form-action-post.php');
        acfe_include('includes/modules/form/module-form-action-redirect.php');
        acfe_include('includes/modules/form/module-form-action-term.php');
        acfe_include('includes/modules/form/module-form-action-user.php');
        acfe_include('includes/modules/form/module-form-compatibility.php');
        acfe_include('includes/modules/form/module-form-deprecated.php');
        acfe_include('includes/modules/form/module-form-fields.php');
        acfe_include('includes/modules/form/module-form-format.php');
        acfe_include('includes/modules/form/module-form-front.php');
        acfe_include('includes/modules/form/module-form-front-hooks.php');
        acfe_include('includes/modules/form/module-form-front-render.php');
        acfe_include('includes/modules/form/module-form-shortcode.php');
        acfe_include('includes/modules/form/module-form-upgrades.php');
        
        // performance
        acfe_include('includes/modules/performance/module-performance.php');
        acfe_include('includes/modules/performance/module-performance-connector.php');
        acfe_include('includes/modules/performance/module-performance-functions.php');
        acfe_include('includes/modules/performance/module-performance-ui.php');
        acfe_include('includes/modules/performance/module-performance-upgrades.php');
    
        // post type
        acfe_include('includes/modules/post-type/module-post-type.php');
        acfe_include('includes/modules/post-type/module-post-type-fields.php');
        acfe_include('includes/modules/post-type/module-post-type-features.php');
        acfe_include('includes/modules/post-type/module-post-type-upgrades.php');
    
        // taxonomy
        acfe_include('includes/modules/taxonomy/module-taxonomy.php');
        acfe_include('includes/modules/taxonomy/module-taxonomy-fields.php');
        acfe_include('includes/modules/taxonomy/module-taxonomy-features.php');
        acfe_include('includes/modules/taxonomy/module-taxonomy-upgrades.php');
    
        // options page
        acfe_include('includes/modules/options-page/module-options-page.php');
        acfe_include('includes/modules/options-page/module-options-page-fields.php');
        acfe_include('includes/modules/options-page/module-options-page-features.php');
        acfe_include('includes/modules/options-page/module-options-page-upgrades.php');
        
        // screens
        acfe_include('includes/screens/screen-attachment.php');
        acfe_include('includes/screens/screen-options-page.php');
        acfe_include('includes/screens/screen-post.php');
        acfe_include('includes/screens/screen-settings.php');
        acfe_include('includes/screens/screen-taxonomy.php');
        acfe_include('includes/screens/screen-user.php');

    }
    
    
    /**
     * init
     *
     * acf/init:99
     */
    function init(){
        
        // action
        do_action('acfe/init');
        
        // core
        acfe_include('includes/assets.php');
        acfe_include('includes/hooks.php');
        
        // fields
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
        acfe_include('includes/fields/field-taxonomy.php');
        acfe_include('includes/fields/field-wysiwyg.php');
        
        // fields settings
        acfe_include('includes/fields-settings/bidirectional.php');
        acfe_include('includes/fields-settings/data.php');
        acfe_include('includes/fields-settings/instructions.php');
        acfe_include('includes/fields-settings/permissions.php');
        acfe_include('includes/fields-settings/settings.php');
        acfe_include('includes/fields-settings/validation.php');
        
        // field groups
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
        acfe_include('includes/field-groups/field-types.php');
        
        // locations
        acfe_include('includes/locations/post-type-all.php');
        acfe_include('includes/locations/post-type-archive.php');
        acfe_include('includes/locations/post-type-list.php');
        acfe_include('includes/locations/taxonomy-list.php');
        
        // modules
        acfe_include('includes/modules/author.php');
        acfe_include('includes/modules/dev/module-dev.php');
        acfe_include('includes/modules/dev/module-dev-delete-meta.php');
        acfe_include('includes/modules/option/module-option.php');
        acfe_include('includes/modules/ui/module-ui.php');
        acfe_include('includes/modules/ui/module-ui-attachment.php');
        acfe_include('includes/modules/ui/module-ui-settings.php');
        acfe_include('includes/modules/ui/module-ui-term.php');
        acfe_include('includes/modules/ui/module-ui-user.php');
    
        // performance
        acfe_include('includes/modules/performance/module-performance-ultra.php');
        acfe_include('includes/modules/performance/module-performance-ultra-fields.php');
        acfe_include('includes/modules/performance/module-performance-ultra-revisions.php');
        
    }
    
    
    /**
     * include_fields
     *
     * acf/include_fields:5
     */
    function include_fields(){
        
        // autosync
        acfe_include('includes/modules/autosync-php.php');
        acfe_include('includes/modules/autosync-json.php');
        
    }
    
    
    /**
     * include_field_types
     *
     * acf/include_field_types:99
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
    
    
    /**
     * include_admin_tools
     *
     * acf/include_admin_tools
     */
    function include_admin_tools(){
        
        // modules
        acfe_include('includes/admin/tools/module-export.php');
        acfe_include('includes/admin/tools/module-import.php');
        
    }
    
    
    /**
     * include_admin_tools_late
     *
     * acf/include_admin_tools:99
     */
    function include_admin_tools_late(){
        
        // field groups
        acfe_include('includes/admin/tools/field-groups-local.php');
        acfe_include('includes/admin/tools/field-groups-export.php');
        
    }
    
    
    /**
     * constants
     *
     * @param $array
     */
    function constants($array = array()){
        
        foreach($array as $name => $value){
            if(!defined($name)){
                define($name, $value);
            }
        }
        
    }
    
    
    /**
     * settings
     *
     * @param $array
     */
    function settings($array = array()){
        
        foreach($array as $name => $value){
        
            // acf_update_setting
            acf_update_setting("acfe/{$name}", $value);
            
            // acf_get_setting
            add_filter("acf/settings/acfe/{$name}", function($value) use($name){
                return apply_filters("acfe/settings/{$name}", $value);
            }, 5);
        
        }
        
    }
    
}


/**
 * acfe
 *
 * @return ACFE
 */
function acfe(){
    
    global $acfe;
    
    if(!isset($acfe)){
        
        $acfe = new ACFE();
        $acfe->initialize();
        
    }
    
    return $acfe;
    
}

acfe();

else:
    
    add_action('after_plugin_row_' . plugin_basename(__FILE__), function($plugin_file, $plugin_data, $status){
        
        // vars
        $colspan = version_compare($GLOBALS['wp_version'], '5.5', '<') ? 3 : 4;
        $pro = defined('ACFE_PRO') && ACFE_PRO;
        
        // message
        $message = __('An another version of ACF Extended has been detected. Please activate only one version.', 'acfe');
        if($pro){
            $message = __('ACF Extended Pro has been detected. Please activate only one version.', 'acfe');
        }
        
        // class
        $class = 'acfe-plugin-tr';
        if(isset($plugin_data['update']) && !empty($plugin_data['update'])){
            $class .= ' acfe-plugin-tr-update';
        }
        
        ?>
        <style>
            .plugins tr[data-plugin='<?php echo $plugin_file; ?>'] th,
            .plugins tr[data-plugin='<?php echo $plugin_file; ?>'] td{
                box-shadow:none;
            }
        </style>
        
        <tr class="plugin-update-tr active <?php echo $class; ?>">
            <td colspan="<?php echo $colspan; ?>" class="plugin-update colspanchange">
                <div class="update-message notice inline notice-error notice-alt">
                    <p><?php echo $message; ?></p>
                </div>
            </td>
        </tr>
        <?php
        
    }, 5, 3);

endif;