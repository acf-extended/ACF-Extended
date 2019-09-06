<?php
/**
 * Plugin Name: Advanced Custom Fields: Extended
 * Description: Enhancement Suite which improves Advanced Custom Fields administration
 * Version:     0.7.9.9.9
 * Author: 		ACF Extended
 * Author URI:  https://www.acf-extended.com
 * Text Domain: acfe
 */

if(!defined('ABSPATH'))
    exit;

/**
 * ACFE: Constants
 */
if(!defined('ACFE_FILE'))       define('ACFE_FILE', __FILE__);
if(!defined('ACFE_PATH'))       define('ACFE_PATH', plugin_dir_path(__FILE__));
if(!defined('ACFE_URL'))        define('ACFE_URL', plugin_dir_url(__FILE__));
if(!defined('ACFE_VERSION'))    define('ACFE_VERSION', '0.7.9.9.9');
if(!defined('ACFE_BASENAME'))   define('ACFE_BASENAME', plugin_basename(__FILE__));
if(!defined('ACFE_THEME_PATH')) define('ACFE_THEME_PATH', get_stylesheet_directory());
if(!defined('ACFE_THEME_URL'))  define('ACFE_THEME_URL', get_stylesheet_directory_uri());

/**
 * ACFE: Init
 */
require_once(ACFE_PATH . 'init.php');

/**
 * ACFE: Load
 */
add_action('acf/init', 'acfe_load', 99);
function acfe_load(){
    
    if(!acfe_is_acf_pro())
        return;
    
    /**
     * Settings
     */
    acf_update_setting('acfe_php', true);
    acf_update_setting('acfe_php_save', ACFE_THEME_PATH . '/acfe-php');
    acf_update_setting('acfe_php_load', array(ACFE_THEME_PATH . '/acfe-php'));
    acf_update_setting('acfe_php_found', false);
    
    /**
     * Core
     */
    require_once(ACFE_PATH . 'includes/core/compatibility.php');
    require_once(ACFE_PATH . 'includes/core/enqueue.php');
    require_once(ACFE_PATH . 'includes/core/helpers.php');
    require_once(ACFE_PATH . 'includes/core/menu.php');
    
    /**
     * Admin Pages
     */
    require_once(ACFE_PATH . 'includes/admin/options.php');
    require_once(ACFE_PATH . 'includes/admin/plugins.php');
    require_once(ACFE_PATH . 'includes/admin/settings.php');
    
    
    /**
     * Fields settings
     */
    require_once(ACFE_PATH . 'includes/fields-settings/bidirectional.php');
    require_once(ACFE_PATH . 'includes/fields-settings/data.php');
    require_once(ACFE_PATH . 'includes/fields-settings/flexible-content.php');
    require_once(ACFE_PATH . 'includes/fields-settings/image.php');
    require_once(ACFE_PATH . 'includes/fields-settings/permissions.php');
    require_once(ACFE_PATH . 'includes/fields-settings/thumbnail.php');
    require_once(ACFE_PATH . 'includes/fields-settings/update.php');
    require_once(ACFE_PATH . 'includes/fields-settings/validation.php');
    
    /**
     * Field Groups
     */
    require_once(ACFE_PATH . 'includes/field-groups/field-group.php');
    require_once(ACFE_PATH . 'includes/field-groups/field-group-category.php');
    require_once(ACFE_PATH . 'includes/field-groups/field-groups.php');
    require_once(ACFE_PATH . 'includes/field-groups/field-groups-third-party.php');
    
    /**
     * Locations
     */
    require_once(ACFE_PATH . 'includes/locations/post-type-all.php');
    require_once(ACFE_PATH . 'includes/locations/post-type-archive.php');
    require_once(ACFE_PATH . 'includes/locations/taxonomy-archive.php');
    
    /**
     * Modules
     */
    require_once(ACFE_PATH . 'includes/modules/author.php');
    require_once(ACFE_PATH . 'includes/modules/autosync.php');
    require_once(ACFE_PATH . 'includes/modules/dynamic-block-type.php');
    require_once(ACFE_PATH . 'includes/modules/dynamic-options-page.php');
    require_once(ACFE_PATH . 'includes/modules/dynamic-post-type.php');
    require_once(ACFE_PATH . 'includes/modules/dynamic-taxonomy.php');
    require_once(ACFE_PATH . 'includes/modules/taxonomy.php');
    
}

/**
 * ACFE: Fields
 */
add_action('acf/include_field_types', 'acfe_fields');
function acfe_fields(){
    
    if(!acfe_is_acf_pro())
        return;
    
    require_once(ACFE_PATH . 'includes/fields/field-button.php');
    require_once(ACFE_PATH . 'includes/fields/field-dynamic-message.php');
    require_once(ACFE_PATH . 'includes/fields/field-post-types.php');
    require_once(ACFE_PATH . 'includes/fields/field-slug.php');
    require_once(ACFE_PATH . 'includes/fields/field-taxonomies.php');

}

/**
 * ACFE: Tools
 */
add_action('acf/include_admin_tools', 'acfe_tools');
function acfe_tools(){
    
    if(!acfe_is_acf_pro())
        return;
    
    require_once(ACFE_PATH . 'includes/admin/tools/dbt-export.php');
    require_once(ACFE_PATH . 'includes/admin/tools/dbt-import.php');
    require_once(ACFE_PATH . 'includes/admin/tools/dpt-export.php');
    require_once(ACFE_PATH . 'includes/admin/tools/dpt-import.php');
    require_once(ACFE_PATH . 'includes/admin/tools/dt-export.php');
    require_once(ACFE_PATH . 'includes/admin/tools/dt-import.php');
    require_once(ACFE_PATH . 'includes/admin/tools/dop-export.php');
    require_once(ACFE_PATH . 'includes/admin/tools/dop-import.php');
    
}