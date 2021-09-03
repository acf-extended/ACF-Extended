<?php

use WPGraphQL\AppContext;
use WPGraphQL\Model\Term;

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_compatibility')):

class acfe_compatibility{
    
    function __construct(){
    
        add_action('acf/init',                                      array($this, 'init'), 98);
        add_action('after_plugin_row_' . ACFE_BASENAME,             array($this, 'plugin_row'), 5, 3);
        
        add_filter('acfe/form/import_args',                         array($this, 'acfe_form_import_compatibility'), 10, 3);
        add_filter('pto/posts_orderby/ignore',                      array($this, 'pto_acf_field_group'), 10, 3);
        add_filter('pto/get_options',                               array($this, 'pto_options_acf_field_group'));
        
        add_action('admin_menu',                                    array($this, 'cotto_submenu'), 999);
        add_filter('rank_math/metabox/priority',                    array($this, 'rankmath_metaboxes_priority'));
        add_filter('wpseo_metabox_prio',                            array($this, 'yoast_metaboxes_priority'));
        add_filter('pll_get_post_types',                            array($this, 'polylang'), 10, 2);
        add_action('elementor/documents/register_controls',         array($this, 'elementor'));
        add_filter('wpgraphql_acf_supported_fields',                array($this, 'wpgraphql_supported_fields'));
        add_filter('wpgraphql_acf_register_graphql_field',          array($this, 'wpgraphql_register_field'), 10, 4);
        
    }
    
    function plugin_row($plugin_file, $plugin_data, $status){
    
        // Bail early
        if(acfe()->acf()) return;
    
        // Check WP version
        $colspan = version_compare($GLOBALS['wp_version'], '5.5', '<') ? 3 : 4;
    
        ?>
        <style>
            .plugins tr[data-plugin='<?php echo ACFE_BASENAME; ?>'] th,
            .plugins tr[data-plugin='<?php echo ACFE_BASENAME; ?>'] td{
                box-shadow:none;
            }
        
            <?php if(isset($plugin_data['update']) && !empty($plugin_data['update'])){ ?>

            .plugins tr.acfe-plugin-tr td{
                box-shadow:none !important;
            }

            .plugins tr.acfe-plugin-tr .update-message{
                margin-bottom:0;
            }
        
            <?php } ?>
        </style>
    
        <tr class="plugin-update-tr active acfe-plugin-tr">
            <td colspan="<?php echo $colspan; ?>" class="plugin-update colspanchange">
                <div class="update-message notice inline notice-error notice-alt">
                    <p><?php _e('ACF Extended requires <a href="https://www.advancedcustomfields.com/pro/" target="_blank">Advanced Custom Fields PRO</a> (minimum: 5.8).', 'acfe'); ?></p>
                </div>
            </td>
        </tr>
        <?php
        
    }
    
    function init(){
    
        $this->update_settings();
        
        add_filter('acf/validate_field_group',                      array($this, 'field_group_location_list'), 20);
        add_filter('acf/validate_field',                            array($this, 'field_acfe_update'), 20);
        
        add_filter('acf/validate_field/type=group',                 array($this, 'field_seamless_style'), 20);
        add_filter('acf/validate_field/type=clone',                 array($this, 'field_seamless_style'), 20);
        add_filter('acf/validate_field/type=acfe_dynamic_message',  array($this, 'field_dynamic_message'), 20);
        add_filter('acfe/load_fields/type=flexible_content',        array($this, 'field_flexible_settings_title'), 20, 2);
        
        add_filter('acf/prepare_field/name=acfe_flexible_category', array($this, 'field_flexible_layout_categories'), 10, 2);
        
    }
    
    /**
     * ACF Extended: Settings
     */
    function update_settings(){
        
        // ACF Extended: 0.8.8 - 'acfe/modules/taxonomies' is now used for the old 'acfe/modules/dynamic_taxonomies'
        // ACF Extended: 0.8.6.3 - Renamed 'acfe/modules/taxonomies' to 'acfe/modules/ui'
        //if(acf_get_setting('acfe/modules/taxonomies') !== null){
        //    acf_update_setting('acfe/modules/ui', acf_get_setting('acfe/modules/taxonomies'));
        //}
        
        // ACF Extended: 0.8 - Renamed 'acfe_php*' to 'acfe/php*'
        if(acf_get_setting('acfe_php') !== null){
            acf_update_setting('acfe/php', acf_get_setting('acfe_php'));
        }
    
        if(acf_get_setting('php_save') !== null){
            acf_update_setting('acfe/php_save', acf_get_setting('php_save'));
        }
    
        if(acf_get_setting('php_load') !== null){
            acf_update_setting('acfe/php_load', acf_get_setting('php_load'));
        }
    
        if(acf_get_setting('php_found') !== null){
            acf_update_setting('acfe/php_found', acf_get_setting('php_found'));
        }
        
        // ACF Extended: 0.8.8 - renamed modules
        if(acf_get_setting('acfe/modules/dynamic_block_types') !== null){
            acf_update_setting('acfe/modules/block_types', acf_get_setting('acfe/modules/dynamic_block_types'));
        }
        
        if(acf_get_setting('acfe/modules/dynamic_forms') !== null){
            acf_update_setting('acfe/modules/forms', acf_get_setting('acfe/modules/dynamic_forms'));
        }
        
        if(acf_get_setting('acfe/modules/dynamic_options_pages') !== null){
            acf_update_setting('acfe/modules/options_pages', acf_get_setting('acfe/modules/dynamic_options_pages'));
        }
        
        if(acf_get_setting('acfe/modules/dynamic_post_types') !== null){
            acf_update_setting('acfe/modules/post_types', acf_get_setting('acfe/modules/dynamic_post_types'));
        }
        
        if(acf_get_setting('acfe/modules/dynamic_taxonomies') !== null){
            acf_update_setting('acfe/modules/taxonomies', acf_get_setting('acfe/modules/dynamic_taxonomies'));
        }
        
    }

    /**
     * ACF Extended: 0.8
     * Field Group Location: Archive renamed to List
     */
    function field_group_location_list($field_group){
        
        if(!acf_maybe_get($field_group, 'location'))
            return $field_group;
        
        foreach($field_group['location'] as &$or){
            
            foreach($or as &$and){
                
                if(!isset($and['value']))
                    continue;
                
                // Post Type List
                if($and['param'] === 'post_type' && acfe_ends_with($and['value'], '_archive')){
                
                    $and['param'] = 'post_type_list';
                    $and['value'] = substr_replace($and['value'], '', -8);
                
                }
                
                // Taxonomy List
                elseif($and['param'] === 'taxonomy' && acfe_ends_with($and['value'], '_archive')){
                    
                    $and['param'] = 'taxonomy_list';
                    $and['value'] = substr_replace($and['value'], '', -8);
                    
                }
                
            }
            
        }
        
        return $field_group;
        
    }

    /**
     * ACF Extended: 0.8
     * Field Filter Value: Removed from this version
     */
    function field_acfe_update($field){
        
        if(!acf_maybe_get($field, 'acfe_update'))
            return $field;
        
        unset($field['acfe_update']);
        
        return $field;
        
    }

    /**
     * ACF Extended: 0.8.5
     * Field Group/Clone: Fixed typo "Seamless"
     */
    function field_seamless_style($field){
        
        if($seamless = acf_maybe_get($field, 'acfe_seemless_style', false)){
            
            $field['acfe_seamless_style'] = $seamless;
            
        }
        
        return $field;
        
    }
    
    /**
     * ACF Extended: 0.8.8.5
     * Renamed Dynamic Message to Dynamic Render
     */
    function field_dynamic_message($field){
        
        $field['type'] = 'acfe_dynamic_render';
        
        return $field;
        
    }

    /**
     * ACF Extended: 0.8.4.5
     * Field Flexible Content: Fix duplicated "layout_settings" & "layout_title"
     */
    function field_flexible_settings_title($fields, $parent){
        
        // Check if is tool screen
        if(!acf_is_screen(acfe_get_acf_screen_id('acf-tools')))
            return $fields;
        
        foreach($fields as $_k => $_field){
            
            // field name
            $_field_name = acf_maybe_get($_field, 'name');
            
            // check 'acfe_flexible_layout_title' & 'layout_settings'
            if($_field_name !== 'acfe_flexible_layout_title' && $_field_name !== 'layout_settings')
                continue;
            
            // unset
            unset($fields[$_k]);
            
        }
        
        return $fields;
        
    }
    
    /**
     * ACF Extended: 0.8.6.7
     * Field Flexible Content: Compatibility for Layout Categories
     */
    function field_flexible_layout_categories($field){
        
        $value = acf_maybe_get($field, 'value');
    
        if(empty($value))
            return $field;
    
        if(is_string($value)){
        
            $explode = explode('|', $value);
        
            $choices = array();
        
            foreach($explode as $v){
            
                $v = trim($v);
                $choices[$v] = $v;
            
            }
        
            $field['choices'] = $choices;
            $field['value'] = $choices;
        
        }
    
        return $field;
    
    }
    
    /**
     * ACF Extended: 0.8.5
     * Module Dynamic Forms: Upgrade previous versions
     */
    function acfe_form_import_compatibility($args, $name, $post_id){
        
        // ACF Extended: 0.8.5 Compatibility - Step 1
        // Groups upgrade
        $has_upgraded = false;
        
        $rules = array(
            
            // Post: title
            array(
                'group'             => 'field_acfe_form_post_save_post_title_group',
                'sub_field'         => 'field_acfe_form_post_save_post_title',
                'sub_field_custom'  => 'field_acfe_form_post_save_post_title_custom',
            ),
            
            // Post: name
            array(
                'group'             => 'field_acfe_form_post_save_post_name_group',
                'sub_field'         => 'field_acfe_form_post_save_post_name',
                'sub_field_custom'  => 'field_acfe_form_post_save_post_name_custom',
            ),
            
            // Term: name
            array(
                'group'             => 'field_acfe_form_term_save_name_group',
                'sub_field'         => 'field_acfe_form_term_save_name',
                'sub_field_custom'  => 'field_acfe_form_term_save_name_custom',
            ),
            
            // Term: slug
            array(
                'group'             => 'field_acfe_form_term_save_slug_group',
                'sub_field'         => 'field_acfe_form_term_save_slug',
                'sub_field_custom'  => 'field_acfe_form_term_save_slug_custom',
            ),
            
            // User: e-mail
            array(
                'group'             => 'field_acfe_form_user_save_email_group',
                'sub_field'         => 'field_acfe_form_user_save_email',
                'sub_field_custom'  => 'field_acfe_form_user_save_email_custom',
            ),
            
            // User: username
            array(
                'group'             => 'field_acfe_form_user_save_username_group',
                'sub_field'         => 'field_acfe_form_user_save_username',
                'sub_field_custom'  => 'field_acfe_form_user_save_username_custom',
            ),
            
            // User: password
            array(
                'group'             => 'field_acfe_form_user_save_password_group',
                'sub_field'         => 'field_acfe_form_user_save_password',
                'sub_field_custom'  => 'field_acfe_form_user_save_password_custom',
            ),
            
            // User: first name
            array(
                'group'             => 'field_acfe_form_user_save_first_name_group',
                'sub_field'         => 'field_acfe_form_user_save_first_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_first_name_custom',
            ),
            
            // User: last name
            array(
                'group'             => 'field_acfe_form_user_save_last_name_group',
                'sub_field'         => 'field_acfe_form_user_save_last_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_last_name_custom',
            ),
            
            // User: nickname
            array(
                'group'             => 'field_acfe_form_user_save_nickname_group',
                'sub_field'         => 'field_acfe_form_user_save_nickname',
                'sub_field_custom'  => 'field_acfe_form_user_save_nickname_custom',
            ),
            
            // User: display name
            array(
                'group'             => 'field_acfe_form_user_save_display_name_group',
                'sub_field'         => 'field_acfe_form_user_save_display_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_display_name_custom',
            ),
            
            // User: website
            array(
                'group'             => 'field_acfe_form_user_save_website_group',
                'sub_field'         => 'field_acfe_form_user_save_website',
                'sub_field_custom'  => 'field_acfe_form_user_save_website_custom',
            ),
        
        );
        
        foreach($args['acfe_form_actions'] as &$row){
            
            foreach($rules as $rule){
                
                if(!acf_maybe_get($row, $rule['group']))
                    continue;
                
                $value = null;
                $group = $row[$rule['group']];
                
                if(acf_maybe_get($group, $rule['sub_field']) === 'custom'){
                    
                    $value = acf_maybe_get($group, $rule['sub_field_custom']);
                    
                }else{
                    
                    $value = acf_maybe_get($group, $rule['sub_field']);
                    
                }
                
                unset($row[$rule['group']]);
                
                $row[$rule['sub_field']] = $value;
                
                $has_upgraded = true;
                
            }
            
        }
        
        // ACF Extended: 0.8.5 Compatibility - Step 2
        // Field mapping upgrade
        if($has_upgraded){
            
            // Rules
            $rules = array(
                
                array(
                    'load_values' => 'field_acfe_form_post_load_values',
                    'fields' => array(
                        'field_acfe_form_post_map_post_type'       => 'field_acfe_form_post_save_post_type',
                        'field_acfe_form_post_map_post_status'     => 'field_acfe_form_post_save_post_status',
                        'field_acfe_form_post_map_post_title'      => 'field_acfe_form_post_save_post_title',
                        'field_acfe_form_post_map_post_name'       => 'field_acfe_form_post_save_post_name',
                        'field_acfe_form_post_map_post_content'    => 'field_acfe_form_post_save_post_content',
                        'field_acfe_form_post_map_post_author'     => 'field_acfe_form_post_save_post_author',
                        'field_acfe_form_post_map_post_parent'     => 'field_acfe_form_post_save_post_parent',
                        'field_acfe_form_post_map_post_terms'      => 'field_acfe_form_post_save_post_terms',
                    )
                ),
                
                array(
                    'load_values' => 'field_acfe_form_term_load_values',
                    'fields' => array(
                        'field_acfe_form_term_map_name'            => 'field_acfe_form_term_save_name',
                        'field_acfe_form_term_map_slug'            => 'field_acfe_form_term_save_slug',
                        'field_acfe_form_term_map_taxonomy'        => 'field_acfe_form_term_save_taxonomy',
                        'field_acfe_form_term_map_parent'          => 'field_acfe_form_term_save_parent',
                        'field_acfe_form_term_map_description'     => 'field_acfe_form_term_save_description',
                    )
                ),
                
                array(
                    'load_values' => 'field_acfe_form_user_load_values',
                    'fields' => array(
                        'field_acfe_form_user_map_email'        => 'field_acfe_form_user_save_email',
                        'field_acfe_form_user_map_username'     => 'field_acfe_form_user_save_username',
                        'field_acfe_form_user_map_password'     => 'field_acfe_form_user_save_password',
                        'field_acfe_form_user_map_first_name'   => 'field_acfe_form_user_save_first_name',
                        'field_acfe_form_user_map_last_name'    => 'field_acfe_form_user_save_last_name',
                        'field_acfe_form_user_map_nickname'     => 'field_acfe_form_user_save_nickname',
                        'field_acfe_form_user_map_display_name' => 'field_acfe_form_user_save_display_name',
                        'field_acfe_form_user_map_website'      => 'field_acfe_form_user_save_website',
                        'field_acfe_form_user_map_description'  => 'field_acfe_form_user_save_description',
                        'field_acfe_form_user_map_role'         => 'field_acfe_form_user_save_role',
                    )
                ),
            
            );
            
            foreach($args['acfe_form_actions'] as &$row){
                
                foreach($rules as $rule){
                    
                    $load_values = acf_maybe_get($row, $rule['load_values']);
                    $fields = $rule['fields'];
                    
                    if(!empty($load_values))
                        continue;
                    
                    foreach($fields as $map => $save){
                        
                        $map_value = acf_maybe_get($row, $map);
                        
                        if(empty($map_value))
                            continue;
                        
                        if($save === 'field_acfe_form_post_save_post_content'){
                            
                            $row['field_acfe_form_post_save_post_content_group'][$save] = $map_value;
                            
                        }
                        
                        elseif($save === 'field_acfe_form_term_save_description'){
                            
                            $row['field_acfe_form_term_save_description_group'][$save] = $map_value;
                            
                        }
                        
                        elseif($save === 'field_acfe_form_user_save_description'){
                            
                            $row['field_acfe_form_user_save_description_group'][$save] = $map_value;
                            
                        }
                        
                        else{
                            
                            $row[$save] = $map_value;
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        return $args;
        
    }

    /**
     * Plugin: Post Types Order
     * https://wordpress.org/plugins/post-types-order/
     * The plugin apply custom order to ACF Field Group Post Type. We have to fix this
     */
    function pto_acf_field_group($ignore, $orderby, $query){
        
        if(is_admin() && $query->is_main_query() && $query->get('post_type') === 'acf-field-group')
            $ignore = true;

        return $ignore;
        
    }
    
    /**
     * Plugin: Post Types Order
     * https://wordpress.org/plugins/post-types-order/
     * The plugin apply a drag & drop UI on ACF Field Group UI. We have to fix this
     */
    function pto_options_acf_field_group($options){
        
        $options['show_reorder_interfaces']['acf-field-group'] = 'hide';
        
        return $options;
        
    }
    
    /**
     * Plugin: Category Order and Taxonomy Terms Order
     * https://wordpress.org/plugins/taxonomy-terms-order/
     * The plugin add a submenu to 'Custom Fields' to order Field Group Categories. It's unecessary
     */
    function cotto_submenu(){
        
        remove_submenu_page('edit.php?post_type=acf-field-group', 'to-interface-acf-field-group');
        
    }
    
    /**
     * Plugin: Rank Math SEO
     * https://wordpress.org/plugins/seo-by-rank-math/
     * Fix the plugin post metabox which is always above ACF metaboxes
     */
    function rankmath_metaboxes_priority(){
        
        return 'default';
        
    }
    
    /**
     * Plugin: YOAST SEO
     * https://wordpress.org/plugins/wordpress-seo/
     * Fix the plugin post metabox which is always above ACF metaboxes
     */
    function yoast_metaboxes_priority(){
        
        return 'default';
        
    }
    
    /**
     * ACF Extended: 0.8.3
     * Modules: Enable PolyLang Translation for ACFE Form Module
     * https://polylang.pro/doc/filter-reference/
     */
    function polylang($post_types, $is_settings){
        
        if($is_settings){
            
            unset($post_types['acfe-form']);
            unset($post_types['acfe-template']);
            
        }else{
            
            $post_types['acfe-form'] = 'acfe-form';
            $post_types['acfe-template'] = 'acfe-template';
            
        }
        
        return $post_types;
        
    }
    
    /*
     * ACF Extended: 0.8.8
     * Elementor Pro
     * Fix Elementor listing all private ACF Extended Field Groups in Dynamic ACF Tags options list
     */
    function elementor(){
        
        add_filter('acf/load_field_groups', function($field_groups){
            
            // Hidden Local Field Groups
            $hidden = acfe_get_setting('reserved_field_groups', array());
            
            foreach($field_groups as $i => $field_group){
                
                if(!in_array($field_group['key'], $hidden))
                    continue;
                
                unset($field_groups[$i]);
                
            }
    
            $field_groups = array_values($field_groups);
            
            return $field_groups;
            
        }, 25);
        
    }
    
    /*
     * ACF Extended: 0.8.8.2
     * WP GraphQL ACF Supported Fields
     */
    function wpgraphql_supported_fields($fields){
        
        $acfe_fields = array(
            'acfe_advanced_link',
            'acfe_code_editor',
            'acfe_forms',
            'acfe_hidden',
            'acfe_post_statuses',
            'acfe_post_types',
            'acfe_slug',
            'acfe_taxonomies',
            'acfe_taxonomy_terms',
            'acfe_user_roles',
        );
        
        return array_merge($fields, $acfe_fields);
        
    }
    
    /*
     * ACF Extended: 0.8.8.4
     * WP GraphQL ACF Register Field
     */
    function wpgraphql_register_field($field_config, $type_name, $field_name, $config){
    
        $acf_field = isset( $config['acf_field'] ) ? $config['acf_field'] : null;
        $acf_type  = isset( $acf_field['type'] ) ? $acf_field['type'] : null;
        
        if($acf_type === 'acfe_advanced_link'){
    
            $field_config['type'] = array('list_of' => 'String');
            
        }elseif($acf_type === 'acfe_code_editor'){
    
            $field_config['type'] = 'String';
            
        }elseif($acf_type === 'acfe_forms'){
    
            $field_config['type'] = array('list_of' => 'String');
            
        }elseif($acf_type === 'acfe_hidden'){
    
            $field_config['type'] = 'String';
            
        }elseif($acf_type === 'acfe_post_statuses'){
    
            $field_config['type'] = array('list_of' => 'String');
            
        }elseif($acf_type === 'acfe_post_types'){
    
            $field_config['type'] = array('list_of' => 'String');
            
        }elseif($acf_type === 'acfe_slug'){
    
            $field_config['type'] = 'String';
            
        }elseif($acf_type === 'acfe_taxonomies'){
    
            $field_config['type'] = array('list_of' => 'String');
            
        }elseif($acf_type === 'acfe_taxonomy_terms'){
    
            $field_config['type'] = array('list_of' => 'String');
            
        }elseif($acf_type === 'acfe_user_roles'){
    
            $field_config['type'] = array('list_of' => 'String');
            
        }
        
        return $field_config;
        
    }
    
}

new acfe_compatibility();

endif;