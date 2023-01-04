<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_options_page_field_groups')):

class acfe_module_options_page_field_groups{
    
    /**
     * construct
     */
    function __construct(){
        
        add_filter('acfe/module/register_field_groups/module=options_page', array($this, 'register_field_groups'), 10, 2);
        
    }
    
    
    /**
     * register_field_groups
     *
     * @param $field_groups
     * @param $module
     *
     * @return mixed
     */
    function register_field_groups($field_groups, $module){
        
        $field_groups[] = array(
            'key' => 'group_acfe_options_page',
            'title' => __('Options Page', 'acfe'),
    
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $module->post_type,
                    ),
                ),
            ),
    
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => 1,
            'description' => '',
    
            'fields' => array(
                array(
                    'key' => 'field_name',
                    'label' => 'Menu slug',
                    'name' => 'name',
                    'type' => 'acfe_slug',
                    'instructions' => __('The URL slug used to uniquely identify this options page. Defaults to a url friendly version of Menu Title', 'acfe'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_update' => array(
                        array(
                            'acfe_update_function' => 'sanitize_title',
                        ),
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_menu_title',
                    'label' => 'Menu title',
                    'name' => 'menu_title',
                    'type' => 'text',
                    'instructions' => __('The title displayed in the wp-admin sidebar. Defaults to Page Title', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_capability',
                    'label' => 'Capability',
                    'name' => 'capability',
                    'type' => 'text',
                    'instructions' => __('The capability required for this menu to be displayed to the user. Defaults to edit_posts.<br /><br />Read more about capability here: <a href="https://wordpress.org/support/article/roles-and-capabilities/" target="_blank">https://wordpress.org/support/article/roles-and-capabilities/</a>', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'edit_posts',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_position',
                    'label' => 'Position',
                    'name' => 'position',
                    'type' => 'text',
                    'instructions' => __('The position in the menu order this menu should appear. Defaults to bottom of utility menu items.<br /><br />WARNING: if two menu items use the same position attribute, one of the items may be overwritten so that only one item displays!<br />Risk of conflict can be reduced by using decimal instead of integer values, e.g. 63.3 instead of 63.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_parent_slug',
                    'label' => 'Parent slug',
                    'name' => 'parent_slug',
                    'type' => 'text',
                    'instructions' => __('The slug of another WP admin page. if set, this will become a child page.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_icon_url',
                    'label' => 'Icon url',
                    'name' => 'icon_url',
                    'type' => 'text',
                    'instructions' => __('The icon class for this menu. Defaults to default WordPress gear.<br /><br />Read more about dashicons here: <a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">https://developer.wordpress.org/resource/dashicons/</a>', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_redirect',
                    'label' => 'Redirect',
                    'name' => 'redirect',
                    'type' => 'true_false',
                    'instructions' => __('If set to true, this options page will redirect to the first child page (if a child page exists). If set to false, this parent page will appear alongside any child pages. Defaults to true', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
                array(
                    'key' => 'field_post_id',
                    'label' => 'Post ID',
                    'name' => 'post_id',
                    'type' => 'text',
                    'instructions' => __('The <code>$post_id</code> to save/load data to/from. Can be set to a numeric post ID (123), or a string (\'user_2\'). Defaults to \'options\'.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'options',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_autoload',
                    'label' => 'Autoload',
                    'name' => 'autoload',
                    'type' => 'true_false',
                    'instructions' => __('Whether to load the option (values saved from this options page) when WordPress starts up. Defaults to false.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => 'True',
                    'ui_off_text' => 'False',
                ),
                array(
                    'key' => 'field_update_button',
                    'label' => 'Update button',
                    'name' => 'update_button',
                    'type' => 'text',
                    'instructions' => __('The update button text.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'Update',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_updated_message',
                    'label' => 'Updated Message',
                    'name' => 'updated_message',
                    'type' => 'text',
                    'instructions' => __('The message shown above the form on submit.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => 'Options Updated',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
        );
        
        return $field_groups;
        
    }
    
}

acf_new_instance('acfe_module_options_page_field_groups');

endif;