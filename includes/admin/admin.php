<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_admin')):

class acfe_admin{
    
    /**
     * construct
     */
    function __construct(){

        // acf-updates & acf-tools pages
        add_action('admin_menu',                                    array($this, 'admin_menu'));
        
        // acf-field-groups (ACF 6.0)
        add_action('acfe/load_posts/post_type=acf-field-group',     array($this, 'load_posts'));
        add_action('acfe/load_post/post_type=acf-field-group',      array($this, 'load_post'));
        
        // acf-post-type (ACF 6.1)
        add_action('acfe/load_posts/post_type=acf-post-type',       array($this, 'load_posts'));
        add_action('acfe/load_post/post_type=acf-post-type',        array($this, 'load_post'));
        
        // acf-taxonomy (ACF 6.1)
        add_action('acfe/load_posts/post_type=acf-taxonomy',        array($this, 'load_posts'));
        add_action('acfe/load_post/post_type=acf-taxonomy',         array($this, 'load_post'));
        
        // acf-ui-options-page (ACF 6.2)
        add_action('acfe/load_posts/post_type=acf-ui-options-page', array($this, 'load_posts'));
        add_action('acfe/load_post/post_type=acf-ui-options-page',  array($this, 'load_post'));
        
        // additional hooks
        add_action('current_screen',                                array($this, 'current_screen'));
        add_filter('acf/validate_field',                            array($this, 'validate_field'));
        
    }
    
    
    /**
     * admin_menu
     */
    function admin_menu(){
        
        // get updates/tools pages
        $updates = get_plugin_page_hookname('acf-settings-updates', 'edit.php?post_type=acf-field-group');
        $tools = get_plugin_page_hookname('acf-tools', 'edit.php?post_type=acf-field-group');
        
        // actions
        add_action("load-{$updates}", array($this, 'load_acf_page'));
        add_action("load-{$tools}", array($this, 'load_acf_page'));
        
    }
    
    
    /**
     * load_acf_page
     */
    function load_acf_page(){

        // add marker for acfe-admin-input assets
        if(acfe_is_acf('6.0')){
            acf_enable_filter('acfe/acf_internal_page');
        }

        add_filter('admin_body_class', array($this, 'admin_body_class'));
    }
    
    
    /**
     * load_posts
     */
    function load_posts(){

        // add marker for acfe-admin-input assets
        if(acfe_is_acf('6.0')){
            acf_enable_filter('acfe/acf_internal_page');
        }

        add_filter('admin_body_class', array($this, 'admin_body_class'));
    }
    
    
    /**
     * load_post
     */
    function load_post(){

        // add marker for acfe-admin-input assets
        if(acfe_is_acf('6.0')){
            acf_enable_filter('acfe/acf_internal_page');
        }

        add_filter('admin_body_class',     array($this, 'admin_body_class'));
        add_action('acf/input/admin_head', array($this, 'admin_head'), 20);

    }
    
    
    /**
     * admin_body_class
     *
     * Adds acfe-acf-6-0 class to body
     */
    function admin_body_class($classes){

        // filter
        $new_class = apply_filters('acfe/acf_admin_body_class', 'acfe-acf-6-0');

        // append class
        if(!empty($new_class)){
            $classes .= ' ' . $new_class;
        }

        // return
        return $classes;
    }
    
    
    /**
     * admin_head
     *
     */
    function admin_head(){
        
        // remove forced 1 column on 'screen_layout' options
        acfe_remove_filter('get_user_option_screen_layout_acf-field-group',     array('acf_admin_field_group', 'screen_layout'));
        acfe_remove_filter('get_user_option_screen_layout_acf-post-type',       array('ACF_Admin_Post_type', 'screen_layout'));
        acfe_remove_filter('get_user_option_screen_layout_acf-taxonomy',        array('ACF_Admin_Taxonomy', 'screen_layout'));
        acfe_remove_filter('get_user_option_screen_layout_acf-ui-options-page', array('ACF_Admin_UI_Options_Page', 'screen_layout'));
    
        // base url
        $default_icon = acf_get_url('assets/images/icons/icon-fields.svg');
    
        // generate default field type missing icon
        ?>
        <style>
            .field-type-icon:before{
                -webkit-mask-image: url(<?php echo $default_icon; ?>);
                mask-image: url(<?php echo $default_icon; ?>);
            }
        </style>
        <?php
        
    }
    
    
    /**
     * current_screen
     *
     * Remove ACF Title header bar on ACFE modules
     *
     * @param $screen
     */
    function current_screen($screen){
        
        // allowed screens
        $allowed = array(
            'edit-acf-field-group-category',
            'edit-acfe-dbt',
            'acfe-dbt',
            'edit-acfe-template',
            'acfe-template',
            'edit-acfe-form',
            'acfe-form'
        );
        
        // check screen
        if(acfe_get($screen, 'post_type') === 'acf-field-group' || acf_is_screen($allowed)){

            // add top menu icons
            add_action('admin_head', array($this, 'admin_head_navigation'));

            // remove the topbar "white banner" with the page title
            if(acf_is_screen($allowed)){
                global $acf_page_title;
                $acf_page_title = '';
            }
            
        }
        
        // acf 6.1 removed topbar for third party submenu
        // checking $screen[post_type] to avoid adding the global navigation twice (throws: Cannot redeclare acf_print_menu_section())
        // this fix an issue when visiting custom admin url like: edit-tags.php?taxonomy=acf-field-group-category&post_type=acf-field-group
        if(acf_is_screen($allowed) && acfe_get($screen, 'post_type') !== 'acf-field-group'){
            add_action('in_admin_header', array($this, 'in_admin_header'));
        }
        
    }


    /**
     * in_admin_header
     *
     * @return void
     */
    function in_admin_header(){

        // safeguard: bail early in case global navigation is already loaded
        if(function_exists('acf_print_menu_section')){
            return;
        }

        // load view
        acf_get_view(apply_filters('acfe/acf_admin_navigation_page', 'global/navigation'));

    }
    
    
    /**
     * admin_head_navigation
     *
     * ACF >= 6.0 && <= 6.1 Add ACF admin head navigation icons to ACFE modules
     * Starting ACF 6.1, ACF add custom submenus into a "More" menu, which doesn't show icons anymore
     */
    function admin_head_navigation(){
        
        // base url
        $base_url = acf_get_url('assets/images/');
        
        // pages rules
        $pages = array(
            'categories'                                   => 'field-type-icons/icon-field-taxonomy.svg',
            'edit-tagsphptaxonomyacf-field-group-category' => 'field-type-icons/icon-field-taxonomy.svg',
            'block-types'                                  => 'icons/icon-fields.svg',
            'acfe-dbt'                                     => 'icons/icon-fields.svg',
            'forms'                                        => 'field-type-icons/icon-field-post-object.svg',
            'acfe-form'                                    => 'field-type-icons/icon-field-post-object.svg',
            'options-pages'                                => 'field-type-icons/icon-field-group.svg',
            'acfe-dop'                                     => 'field-type-icons/icon-field-group.svg',
            'settings'                                     => 'icons/icon-settings.svg',
            'acfe-settings'                                => 'icons/icon-settings.svg',
            'templates'                                    => 'field-type-icons/icon-field-wysiwyg.svg',
            'acfe-template'                                => 'field-type-icons/icon-field-wysiwyg.svg',
        );
        
        // generate css
        ?>
        <style>
            <?php foreach($pages as $page => $icon): ?>
            .acf-admin-toolbar .acf-header-tab-<?php echo $page; ?> i.acf-icon{
                display: inline-flex;
                -webkit-mask-image: url(<?php echo $base_url . $icon; ?>);
                mask-image: url(<?php echo $base_url . $icon; ?>);
            }
            <?php endforeach; ?>
            
            .acf-icon.acf-icon-plus{
                -webkit-mask-image: url(<?php echo $base_url; ?>icons/icon-add.svg);
                mask-image: url(<?php echo $base_url; ?>icons/icon-add.svg);
            }
        </style>
        <?php
        
    }
    
    /**
     * validate_field
     *
     * Change instructions to hint for appended field settings
     *
     * @param $field
     *
     * @return mixed
     */
    function validate_field($field){
    
        if(acfe_get($field, '_appended') && acfe_get($field, 'instructions')){
            $field['hint'] = $field['instructions'];
            $field['instructions'] = '';
        }
        
        return $field;
    
    }
    
}

acf_new_instance('acfe_admin');

endif;