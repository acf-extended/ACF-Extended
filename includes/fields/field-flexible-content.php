<?php

if(!defined('ABSPATH'))
    exit;

/**
 * Add Settings
 */
add_action('acf/render_field_settings/type=flexible_content', 'acfe_flexible_settings', 0);
function acfe_flexible_settings($field){
    
    // Stylised button
    acf_render_field_setting($field, array(
        'label'         => __('Stylised Button'),
        'name'          => 'acfe_flexible_stylised_button',
        'key'           => 'acfe_flexible_stylised_button',
        'instructions'  => __('Better actions buttons integration'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Hide Empty Message
    acf_render_field_setting($field, array(
        'label'         => __('Hide Empty Message'),
        'name'          => 'acfe_flexible_hide_empty_message',
        'key'           => 'acfe_flexible_hide_empty_message',
        'instructions'  => __('Hide the empty message box'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
        'conditional_logic' => array(
            array(
                array(
                    'field'     => 'acfe_flexible_stylised_button',
                    'operator'  => '!=',
                    'value'     => '1',
                ),
            )
        )
    ));
    
    // Empty Message
    acf_render_field_setting($field, array(
        'label'         => __('Empty Message'),
        'name'          => 'acfe_flexible_empty_message',
        'key'           => 'acfe_flexible_empty_message',
        'instructions'  => __('Text displayed when the flexible field is empty'),
        'type'          => 'text',
        'placeholder'   => __('Click the "Add Row" button below to start creating your layout'),
        'conditional_logic' => array(
            array(
                array(
                    'field'     => 'acfe_flexible_stylised_button',
                    'operator'  => '!=',
                    'value'     => '1',
                ),
                array(
                    'field'     => 'acfe_flexible_hide_empty_message',
                    'operator'  => '!=',
                    'value'     => '1',
                )
            )
        )
    ));
    
    // Layouts thumbnails
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Thumbnails'),
        'name'          => 'acfe_flexible_layouts_thumbnails',
        'key'           => 'acfe_flexible_layouts_thumbnails',
        'instructions'  => __('Set a thumbnail for each layouts. You must save the field group to apply this setting'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Layouts settings
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Settings'),
        'name'          => 'acfe_flexible_layouts_settings',
        'key'           => 'acfe_flexible_layouts_settings',
        'instructions'  => __('Choose a field group to clone and to be used as a configuration modal'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Layouts ajax
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Asynchronous'),
        'name'          => 'acfe_flexible_layouts_ajax',
        'key'           => 'acfe_flexible_layouts_ajax',
        'instructions'  => __('Add layouts using Ajax method. This setting increase performance on complex Flexible Content'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Layouts: Render
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Render'),
        'name'          => 'acfe_flexible_layouts_templates',
        'key'           => 'acfe_flexible_layouts_templates',
        'instructions'  => __('Set template, style & javascript files for each layouts. This setting is mandatory in order to use <code style="font-size:11px;">get_flexible()</code> function. You must save the field group to apply this setting'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Layouts: Preview
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Dynamic Preview'),
        'name'          => 'acfe_flexible_layouts_previews',
        'key'           => 'acfe_flexible_layouts_previews',
        'instructions'  => __('Use layouts render settings to display a dynamic preview in the post administration'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
        'conditional_logic' => array(
            array(
                array(
                    'field'     => 'acfe_flexible_layouts_templates',
                    'operator'  => '==',
                    'value'     => '1',
                )
            )
        )
    ));
    
    // Layouts: Placholder
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Placeholder'),
        'name'          => 'acfe_flexible_layouts_placeholder',
        'key'           => 'acfe_flexible_layouts_placeholder',
        'instructions'  => __('Display a placeholder with a pencil icon, making edition easier'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
        'conditional_logic' => array(
            array(
                array(
                    'field'     => 'acfe_flexible_layouts_previews',
                    'operator'  => '==',
                    'value'     => '',
                )
            )
        )
    ));
    
    // Disable Legacy Title Ajax
    acf_render_field_setting($field, array(
        'label'         => __('Disable Legacy Layout Title Ajax'),
        'name'          => 'acfe_flexible_disable_ajax_title',
        'key'           => 'acfe_flexible_disable_ajax_title',
        'instructions'  => __('Disable the additional ACF Layout Title Ajax call. If you don\'t perform operations using <code>acf/fields/flexible_content/layout_title</code> you can turn this setting on. <br /><br />More informations can be found on the <a href="https://www.advancedcustomfields.com/resources/acf-fields-flexible_content-layout_title/" target="_blank">ACF documentation</a>.'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Layouts: Close Button
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Close Button'),
        'name'          => 'acfe_flexible_close_button',
        'key'           => 'acfe_flexible_close_button',
        'instructions'  => __('Display a close button to collapse the layout'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Layouts: Title Edition
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Title Edition'),
        'name'          => 'acfe_flexible_title_edition',
        'key'           => 'acfe_flexible_title_edition',
        'instructions'  => __('Allow layout title edition'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Layouts: Copy/Paste
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Copy/Paste'),
        'name'          => 'acfe_flexible_copy_paste',
        'key'           => 'acfe_flexible_copy_paste',
        'instructions'  => __('Allow copy/paste layouts functions'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Modal: Edition
    acf_render_field_setting($field, array(
        'label'         => __('Layouts Modal: Edition'),
        'name'          => 'acfe_flexible_modal_edition',
        'key'           => 'acfe_flexible_modal_edition',
        'instructions'  => __('Edit layout content in a modal'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
    ));
    
    // Modal: Selection
    acf_render_field_setting($field, array(
        'label'         => __('Layouts Modal: Selection'),
        'name'          => 'acfe_flexible_modal',
        'key'           => 'acfe_flexible_modal',
        'instructions'  => __('Select layouts in a modal'),
        'type'          => 'group',
        'layout'        => 'block',
        'sub_fields'    => array(
            array(
                'label'             => '',
                'name'              => 'acfe_flexible_modal_enabled',
                'key'               => 'acfe_flexible_modal_enabled',
                'type'              => 'true_false',
                'instructions'      => '',
                'required'          => false,
                'wrapper'           => array(
                    'width' => '',
                    'class' => '',
                    'id'    => '',
                ),
                'message'           => '',
                'default_value'     => false,
                'ui'                => true,
                'ui_on_text'        => '',
                'ui_off_text'       => '',
                'conditional_logic' => false,
            ),
            array(
                'label'         => '',
                'name'          => 'acfe_flexible_modal_title',
                'key'           => 'acfe_flexible_modal_title',
                'type'          => 'text',
                'prepend'       => __('Modal Title'),
                'placeholder'   => 'Add Row',
                'instructions'  => false,
                'required'      => false,
                'wrapper'       => array(
                    'width' => '35',
                    'class' => '',
                    'id'    => '',
                ),
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'acfe_flexible_modal_enabled',
                            'operator'  => '==',
                            'value'     => '1',
                        )
                    )
                )
            ),
            array(
                'label'         => '',
                'name'          => 'acfe_flexible_modal_col',
                'key'           => 'acfe_flexible_modal_col',
                'type'          => 'select',
                'prepend'       => '',
                'instructions'  => false,
                'required'      => false,
                'choices'       => array(
                    '1' => '1 column',
                    '2' => '2 columns',
                    '3' => '3 columns',
                    '4' => '4 columns',
                    '5' => '5 columns',
                    '6' => '6 columns',
                ),
                'default_value' => '4',
                'wrapper'       => array(
                    'width' => '15',
                    'class' => '',
                    'id'    => '',
                ),
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'acfe_flexible_modal_enabled',
                            'operator'  => '==',
                            'value'     => '1',
                        )
                    )
                )
            ),
            array(
                'label'         => '',
                'name'          => 'acfe_flexible_modal_categories',
                'key'           => 'acfe_flexible_modal_categories',
                'type'          => 'true_false',
                'message'       => __('Categories'),
                'instructions'  => false,
                'required'      => false,
                'wrapper'       => array(
                    'width' => '25',
                    'class' => '',
                    'id'    => '',
                ),
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'acfe_flexible_modal_enabled',
                            'operator'  => '==',
                            'value'     => '1',
                        )
                    )
                )
            ),
        )
    ));
    
    // Layouts: Force State
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Force State'),
        'name'          => 'acfe_flexible_layouts_state',
        'key'           => 'acfe_flexible_layouts_state',
        'instructions'  => __('Force layouts to be collapsed or opened'),
        'type'          => 'select',
        'placeholder'   => __('Default', 'acfe'),
        'allow_null'    => true,
        'choices'       => array(
            'collapse'  => 'Collapsed',
            'open'      => 'Opened',
        ),
        'conditional_logic' => array(
            array(
                array(
                    'field'     => 'acfe_flexible_modal_edition',
                    'operator'  => '!=',
                    'value'     => '1',
                )
            )
        )
    ));
    
    // Layouts: Remove Collapse
    acf_render_field_setting($field, array(
        'label'         => __('Layouts: Remove Collapse'),
        'name'          => 'acfe_flexible_layouts_remove_collapse',
        'key'           => 'acfe_flexible_layouts_remove_collapse',
        'instructions'  => __('Remove collapse action'),
        'type'              => 'true_false',
        'message'           => '',
        'default_value'     => false,
        'ui'                => true,
        'ui_on_text'        => '',
        'ui_off_text'       => '',
        'conditional_logic' => array(
            array(
                array(
                    'field'     => 'acfe_flexible_modal_edition',
                    'operator'  => '!=',
                    'value'     => '1',
                ),
                array(
                    'field'     => 'acfe_flexible_layouts_state',
                    'operator'  => '==',
                    'value'     => 'open',
                )
            )
        )
    ));
    
}

add_action('acf/render_field', 'acfe_flexible_layouts_settings_before', 0);
function acfe_flexible_layouts_settings_before($field){
    
    if($field['_name'] !== 'label' || stripos($field['name'], '[layouts]') === false)
        return;
    
    echo '</li>';
    
    acf_render_field_wrap(array(
        'label' => __('Settings'),
        'type'  => 'hidden',
        'name'  => 'acfe_flexible_settings_label'
    ), 'ul');
    
    echo '<li>';
    
}

add_action('acf/render_field', 'acfe_flexible_layouts_settings', 10);
function acfe_flexible_layouts_settings($field){
    
    if($field['_name'] !== 'max' || stripos($field['name'], '[layouts]') === false)
        return;
    
    $layout_prefix = $field['prefix'];

    parse_str($layout_prefix, $output);
    $keys = acfe_array_keys_r($output);

    $_field_id = $keys[1];
    $_layout_key = $keys[3];

    $field_flexible = acf_get_field($_field_id);
    $layout = $field_flexible['layouts'][$_layout_key];
    
    $is_flexible_layouts_thumbnails = isset($field_flexible['acfe_flexible_layouts_thumbnails']) && !empty($field_flexible['acfe_flexible_layouts_thumbnails']);
    $is_flexible_layouts_settings = isset($field_flexible['acfe_flexible_layouts_settings']) && !empty($field_flexible['acfe_flexible_layouts_settings']);
    $is_flexible_layouts_templates = isset($field_flexible['acfe_flexible_layouts_templates']) && !empty($field_flexible['acfe_flexible_layouts_templates']);
    $is_flexible_modal_enabled = isset($field_flexible['acfe_flexible_modal']['acfe_flexible_modal_enabled']) && !empty($field_flexible['acfe_flexible_modal']['acfe_flexible_modal_enabled']);
    $is_flexible_modal_categories = isset($field_flexible['acfe_flexible_modal']['acfe_flexible_modal_categories']) && !empty($field_flexible['acfe_flexible_modal']['acfe_flexible_modal_categories']);
    
    // Category
    if($is_flexible_modal_enabled && $is_flexible_modal_categories){
        
        $acfe_flexible_category = isset($layout['acfe_flexible_category']) ? $layout['acfe_flexible_category'] : '';

        acf_render_field_wrap(array(
            'prepend'       => __('Category'),
            'name'          => 'acfe_flexible_category',
            'type'          => 'text',
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $layout_prefix,
            'value'         => $acfe_flexible_category,
            'placeholder'   => __('Multiple categories can be set using "|"')
            
            /*
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_modal_enabled',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_modal_categories',
                        'operator'  => '==',
                        'value'     => '1',
                    )
                )
            )
            */
            
        ), 'ul');
    
    }
    
    // Template
    if($is_flexible_layouts_templates){
        
        $acfe_flexible_render_template = isset($layout['acfe_flexible_render_template']) ? $layout['acfe_flexible_render_template'] : '';
        
        acf_render_field_wrap(array(
            'label'         => __('Render'),
            'prepend'       => str_replace(home_url(), '', ACFE_THEME_URL) . '/',
            'name'          => 'acfe_flexible_render_template',
            'type'          => 'text',
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $layout_prefix,
            'value'         => $acfe_flexible_render_template,
            'placeholder'   => 'template.php',
            
            /*
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_layouts_templates',
                        'operator'  => '==',
                        'value'     => '1',
                    )
                )
            )
            */
            
        ), 'ul');
        
        $acfe_flexible_render_style = isset($layout['acfe_flexible_render_style']) ? $layout['acfe_flexible_render_style'] : '';
        
        acf_render_field_wrap(array(
            'prepend'       => str_replace(home_url(), '', ACFE_THEME_URL) . '/',
            'name'          => 'acfe_flexible_render_style',
            'type'          => 'text',
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $layout_prefix,
            'value'         => $acfe_flexible_render_style,
            'placeholder'   => 'style.css',
            
            /*
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_layouts_templates',
                        'operator'  => '==',
                        'value'     => '1',
                    )
                )
            )
            */
            
        ), 'ul');
        
        $acfe_flexible_render_script = isset($layout['acfe_flexible_render_script']) ? $layout['acfe_flexible_render_script'] : '';

        acf_render_field_wrap(array(
            'prepend'       => str_replace(home_url(), '', ACFE_THEME_URL) . '/',
            'name'          => 'acfe_flexible_render_script',
            'type'          => 'text',
            'class'         => 'acf-fc-meta-name',
            'prefix'        => $layout_prefix,
            'value'         => $acfe_flexible_render_script,
            'placeholder'   => 'script.js',
            
            /*
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_layouts_templates',
                        'operator'  => '==',
                        'value'     => '1',
                    )
                )
            )
            */
            
        ), 'ul');
    
    }
    
    // Thumbnail
    if($is_flexible_layouts_thumbnails){
        
        $acfe_flexible_thumbnail = isset($layout['acfe_flexible_thumbnail']) ? $layout['acfe_flexible_thumbnail'] : '';
        
        acf_render_field_wrap(array(
            'label'         => __('Thumbnail'),
            'name'          => 'acfe_flexible_thumbnail',
            'type'          => 'image',
            'class'         => '',
            'prefix'        => $layout_prefix,
            'value'         => $acfe_flexible_thumbnail,
            'return_format' => 'array',
            'preview_size'  => 'thumbnail',
            'library'       => 'all',
            
            /*
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_layouts_thumbnails',
                        'operator'  => '==',
                        'value'     => '1',
                    )
                )
            )
            */
            
        ), 'ul');
    
    }
    
    // Settings
    if($is_flexible_layouts_settings){
        
        $acfe_flexible_settings = isset($layout['acfe_flexible_settings']) ? $layout['acfe_flexible_settings'] : '';
        
        acf_disable_filters();
        
        $choices = array();
        
        $field_groups = acf_get_field_groups();
        if(!empty($field_groups)){
            
            foreach($field_groups as $field_group){
                
                $choices[$field_group['key']] = $field_group['title'];
                
            }
            
        }
        
        acf_enable_filters();
        
        acf_render_field_wrap(array(
            'label'         => __('Configuration modal'),
            'name'          => 'acfe_flexible_settings',
            'type'          => 'select',
            'class'         => '',
            'prefix'        => $layout_prefix,
            'value'         => $acfe_flexible_settings,
            'choices'       => $choices,
            'allow_null'    => 1,
            'multiple'      => 0,
            'ui'            => 1,
            'ajax'          => 0,
            'return_format' => 0,
        ), 'ul');
        
    }
    
}

add_filter('acfe/field_wrapper_attributes/type=flexible_content', 'acfe_flexible_wrapper', 10, 2);
function acfe_flexible_wrapper($wrapper, $field){
    
    // Stylised button
    if(acf_maybe_get($field, 'acfe_flexible_stylised_button')){
        
        $wrapper['data-acfe-flexible-stylised-button'] = 1;
        
    }
    
    // Hide Empty Message
    if(acf_maybe_get($field, 'acfe_flexible_hide_empty_message') || acf_maybe_get($field, 'acfe_flexible_stylised_button')){
        
        $wrapper['data-acfe-flexible-hide-empty-message'] = 1;
        
    }
    
    // Ajax
    if(acf_maybe_get($field, 'acfe_flexible_layouts_ajax')){
        
        $wrapper['data-acfe-flexible-ajax'] = 1;
        
    }
    
    // Modal: Edition
    if(acf_maybe_get($field, 'acfe_flexible_modal_edition')){
        
        $wrapper['data-acfe-flexible-modal-edition'] = 1;
    
    }
    
    // Modal: Selection
    if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_enabled']) && !empty($field['acfe_flexible_modal']['acfe_flexible_modal_enabled'])){
        
        $wrapper['data-acfe-flexible-modal'] = 1;
        
        // Columns
        if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_col']) && !empty($field['acfe_flexible_modal']['acfe_flexible_modal_col']))
            $wrapper['data-acfe-flexible-modal-col'] = $field['acfe_flexible_modal']['acfe_flexible_modal_col'];
        
        // Title
        if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_title']) && !empty($field['acfe_flexible_modal']['acfe_flexible_modal_title']))
            $wrapper['data-acfe-flexible-modal-title'] = $field['acfe_flexible_modal']['acfe_flexible_modal_title'];
    
    }
    
    // Layouts: Title Edition
    if(acf_maybe_get($field, 'acfe_flexible_title_edition')){
        
        $wrapper['data-acfe-flexible-title-edition'] = 1;
        
    }
    
    // Layouts: Close Button
    if(acf_maybe_get($field, 'acfe_flexible_close_button')){
        
        $wrapper['data-acfe-flexible-close-button'] = 1;
        
    }
    
    // Layouts: Copy/paste
    if(acf_maybe_get($field, 'acfe_flexible_copy_paste')){
        
        $wrapper['data-acfe-flexible-copy-paste'] = 1;
        
    }
    
    // Layouts: State
    if(!acf_maybe_get($field, 'acfe_flexible_modal_edition')){
        
        if(acf_maybe_get($field, 'acfe_flexible_layouts_state')){
            
            // Collapse
            if($field['acfe_flexible_layouts_state'] === 'collapse'){
                
                $wrapper['data-acfe-flexible-close'] = 1;
                
            }
            
            // Open
            elseif($field['acfe_flexible_layouts_state'] === 'open'){
                
                $wrapper['data-acfe-flexible-open'] = 1;
                
            }
            
        }
    
    }
    
    // Layouts Placeholder
    if(acf_maybe_get($field, 'acfe_flexible_layouts_placeholder')){
        
        $wrapper['data-acfe-flexible-placeholder'] = 1;
        
    }
    
    // Layouts Previews
    if(acf_maybe_get($field, 'acfe_flexible_layouts_templates') && acf_maybe_get($field, 'acfe_flexible_layouts_previews')){
        
        $wrapper['data-acfe-flexible-preview'] = 1;
        
    }
    
    // Placeholder Icon
    $layout_placeholder_icon = false;
    $layout_placeholder_icon = apply_filters('acfe/flexible/placeholder/icon',                          $layout_placeholder_icon, $field);
    $layout_placeholder_icon = apply_filters('acfe/flexible/placeholder/icon/name=' . $field['_name'],  $layout_placeholder_icon, $field);
    $layout_placeholder_icon = apply_filters('acfe/flexible/placeholder/icon/key=' . $field['key'],     $layout_placeholder_icon, $field);
    
    if(!empty($layout_placeholder_icon)){
        
        $wrapper['data-acfe-flexible-placeholder-icon'] = $layout_placeholder_icon;
        
    }
    
    // Lock sortable
    $acfe_flexible_lock_sortable = false;
    $acfe_flexible_lock_sortable = apply_filters('acfe/flexible/lock',                          $acfe_flexible_lock_sortable, $field);
    $acfe_flexible_lock_sortable = apply_filters('acfe/flexible/lock/name=' . $field['_name'],  $acfe_flexible_lock_sortable, $field);
    $acfe_flexible_lock_sortable = apply_filters('acfe/flexible/lock/key=' . $field['key'],     $acfe_flexible_lock_sortable, $field);
    
    if($acfe_flexible_lock_sortable){
        
        $wrapper['data-acfe-flexible-lock'] = 1;
        
    }
    
    // Remove ajax 'layout_title' call
    $acfe_flexible_remove_ajax_title = acf_maybe_get($field, 'acfe_flexible_disable_ajax_title', false);
    $acfe_flexible_remove_ajax_title = apply_filters('acfe/flexible/remove_ajax_title',                           $acfe_flexible_remove_ajax_title, $field);
    $acfe_flexible_remove_ajax_title = apply_filters('acfe/flexible/remove_ajax_title/name=' . $field['_name'],   $acfe_flexible_remove_ajax_title, $field);
    $acfe_flexible_remove_ajax_title = apply_filters('acfe/flexible/remove_ajax_title/key=' . $field['key'],      $acfe_flexible_remove_ajax_title, $field);
    
    if($acfe_flexible_remove_ajax_title){
        
        $wrapper['data-acfe-flexible-remove-ajax-title'] = 1;
        
    }
    
    return $wrapper;
    
}

add_filter('acf/fields/flexible_content/no_value_message', 'acfe_flexible_empty_message', 10, 2);
function acfe_flexible_empty_message($message, $field){
    
    if(!acf_maybe_get($field, 'acfe_flexible_empty_message'))
        return $message;
    
    return $field['acfe_flexible_empty_message'];
    
}

add_filter('acf/prepare_field/type=flexible_content', 'acfe_flexible_layout_title_prepare');
function acfe_flexible_layout_title_prepare($field){
    
    if(empty($field['layouts']))
        return $field;
    
    foreach($field['layouts'] as $k => &$layout){
        
        // vars
        $thumbnail = false;
        $span_class = false;
        
        // thumbnail
        if(acf_maybe_get($field, 'acfe_flexible_layouts_thumbnails')){
            
            $class = $style = array();
            $class[] = 'acfe-flexible-layout-thumbnail';
            
            // Modal disabled
            if(!isset($field['acfe_flexible_modal']['acfe_flexible_modal_enabled']) || empty($field['acfe_flexible_modal']['acfe_flexible_modal_enabled']))
                $class[] = 'acfe-flexible-layout-thumbnail-no-modal';
            
            // Thumbnail is set
            $thumbnail_found = false;
            
            $acfe_flexible_thumbnail = false;
            if(acf_maybe_get($layout, 'acfe_flexible_thumbnail'))
                $acfe_flexible_thumbnail = $layout['acfe_flexible_thumbnail'];
            
            // Filter: acfe/flexible/layout/thumbnail/name={field:flexible:name}&layout={field:flexible:layout_name}
            // Flexible Thumbnails
            $acfe_flexible_thumbnail = apply_filters('acfe/flexible/thumbnail/name=' . $field['_name'], $acfe_flexible_thumbnail, $field, $layout);
            $acfe_flexible_thumbnail = apply_filters('acfe/flexible/thumbnail/key=' . $field['key'], $acfe_flexible_thumbnail, $field, $layout);
    
            $acfe_flexible_thumbnail = apply_filters('acfe/flexible/layout/thumbnail/layout=' . $layout['name'], $acfe_flexible_thumbnail, $field, $layout);
            $acfe_flexible_thumbnail = apply_filters('acfe/flexible/layout/thumbnail/name=' . $field['_name'] . '&layout=' . $layout['name'], $acfe_flexible_thumbnail, $field, $layout);
            $acfe_flexible_thumbnail = apply_filters('acfe/flexible/layout/thumbnail/key=' . $field['key'] . '&layout=' . $layout['name'], $acfe_flexible_thumbnail, $field, $layout);
            
            if(!empty($acfe_flexible_thumbnail)){
            
                // Thumbnail ID
                if(is_numeric($acfe_flexible_thumbnail)){
                    
                    if($thumbnail_src = wp_get_attachment_url($acfe_flexible_thumbnail)){
                        
                        $thumbnail_found = true;
                        $style[] = 'background-image:url(' . $thumbnail_src . ');';
                        
                    }
                    
                }
                
                // Thumbnail URL
                else{
                    
                    $thumbnail_found = true;
                    $style[] = 'background-image:url(' . $acfe_flexible_thumbnail . ');';
                    
                }
            
            }
            
            // Thumbnail not found
            if(!$thumbnail_found){
                
                $class[] = 'acfe-flexible-layout-thumbnail-not-found';
                
            }
            
            $thumbnail = '<div class="' . implode(' ', $class) . '" style="' . implode(' ', $style) . '"></div>';
            
        }
        
        // No Thumbnails
        else{
            
            $span_class = 'class="no-thumbnail"';
            
        }
        
        // Category
        $category = '';
        if(isset($field['acfe_flexible_modal']['acfe_flexible_modal_categories']) && !empty($field['acfe_flexible_modal']['acfe_flexible_modal_categories']) && acf_maybe_get($layout, 'acfe_flexible_category')){
            
            $category = 'data-acfe-flexible-category="' . trim($layout['acfe_flexible_category']) . '"';
            
        }
        
        $layout['label'] = $thumbnail . '<span '.$category.' ' . $span_class . '>' . $layout['label'] . '</span>';
        
    }
    
    return $field;
    
}

add_filter('acf/fields/flexible_content/layout_title', 'acfe_flexible_layout_title_ajax', 0, 4);
function acfe_flexible_layout_title_ajax($title, $field, $layout, $i){
    
    // Remove thumbnail
    $title = preg_replace('#<div class="acfe-flexible-layout-thumbnail(.*?)</div>#', '', $title);
    
    // Title Edition
    if(acf_maybe_get($field, 'acfe_flexible_title_edition')){
        
        // Get Layout Title
        $acfe_flexible_layout_title = get_sub_field('acfe_flexible_layout_title');
        if(!empty($acfe_flexible_layout_title))
            $title = wp_unslash($acfe_flexible_layout_title);
        
        // Return
        return '<span class="acfe-layout-title acf-js-tooltip" title="' . __('Layout', 'acfe') . ': ' . esc_attr(strip_tags($layout['label'])) . '"><span class="acfe-layout-title-text">' . $title . '</span></span>';
        
    }
    
    // Return
    return '<span class="acfe-layout-title-text">' . $title . '</span></span>';
    
}

add_action('acf/render_field/type=flexible_content', 'acfe_flexible_render_field');
function acfe_flexible_render_field($field){
    
    if(!acf_maybe_get($field, 'acfe_flexible_layouts_templates') || !acf_maybe_get($field, 'acfe_flexible_layouts_previews') || empty($field['layouts']))
        return;
    
    // Vars
    global $is_preview;
    $is_preview = true;
    
    // Actions
    do_action('acfe/flexible/enqueue', $field, $is_preview);
    do_action('acfe/flexible/enqueue/name=' . $field['_name'], $field, $is_preview);
    do_action('acfe/flexible/enqueue/key=' . $field['key'], $field, $is_preview);
    
    // Layouts Previews
    foreach($field['layouts'] as $layout_key => $layout){
        
        // Render: Enqueue
        acfe_flexible_render_layout_enqueue($layout, $field);
        
    }
    
}

add_action('wp_ajax_acfe/flexible/layout_preview', 'acfe_flexible_layout_preview');
function acfe_flexible_layout_preview($args = array()){
    
    $options = $args;
    
    if(empty($options)){
    
        // Options
        $options = acf_parse_args($_POST, array(
            'post_id'		=> 0,
            'i'				=> 0,
            'field_key'		=> '',
            'nonce'			=> '',
            'layout'		=> '',
            'value'			=> array()
        ));
    
    }
    
    // Load field
    $field = acf_get_field($options['field_key']);
    if(!$field)
        die;
    
    // Get Flexible
    $flexible = acf_get_field_type('flexible_content');
    
    // Vars
    $layout = $flexible->get_layout($options['layout'], $field);
    if(!$layout)
        die;
    
    if(!acf_maybe_get($layout, 'acfe_flexible_thumbnail'))
        $layout['acfe_flexible_thumbnail'] = false;
    
    // Flexible Thumbnails
    $layout['acfe_flexible_thumbnail'] = apply_filters('acfe/flexible/thumbnail/name=' . $field['_name'], $layout['acfe_flexible_thumbnail'], $field, $layout);
    $layout['acfe_flexible_thumbnail'] = apply_filters('acfe/flexible/thumbnail/key=' . $field['key'], $layout['acfe_flexible_thumbnail'], $field, $layout);
    
    // Layout Thumbnails
    $layout['acfe_flexible_thumbnail'] = apply_filters('acfe/flexible/layout/thumbnail/layout=' . $layout['name'], $layout['acfe_flexible_thumbnail'], $field, $layout);
    $layout['acfe_flexible_thumbnail'] = apply_filters('acfe/flexible/layout/thumbnail/name=' . $field['_name'] . '&layout=' . $layout['name'], $layout['acfe_flexible_thumbnail'], $field, $layout);
    $layout['acfe_flexible_thumbnail'] = apply_filters('acfe/flexible/layout/thumbnail/key=' . $field['key'] . '&layout=' . $layout['name'], $layout['acfe_flexible_thumbnail'], $field, $layout);
    
    $meta = array($options['field_key'] => array(
        wp_unslash($options['value'])
    ));
    
    acf_setup_meta($meta, $options['field_key'] . '_' . $options['i'], true);
    
    if(have_rows($options['field_key'])):
        while(have_rows($options['field_key'])): the_row();
        
            global $post;
            $_post = $post;
            
            // Flexible Preview
            do_action('acfe/flexible/preview/name=' . $field['_name'], $field, $layout);
            do_action('acfe/flexible/preview/key=' . $field['key'], $field, $layout);
            
            // Flexible Layout Preview
            do_action('acfe/flexible/layout/preview/layout=' . $layout['name'], $field, $layout);
            do_action('acfe/flexible/layout/preview/name=' . $field['_name'] . '&layout=' . $layout['name'], $field, $layout);
            do_action('acfe/flexible/layout/preview/key=' . $field['key'] . '&layout=' . $layout['name'], $field, $layout);
            
            // ACFE: All Flexible Preview
            do_action('acfe/flexible/preview', $field, $layout);
            
            $post = $_post;
            
        endwhile;
    endif;
    
    acf_reset_meta($options['field_key'] . '_' . $options['i']);
    
    if(wp_doing_ajax()){
        die;
    }
    
}

add_action('acfe/flexible/preview', 'acfe_flexible_layout_preview_render', 99, 2);
function acfe_flexible_layout_preview_render($field, $layout){
    
    global $is_preview;
    
    $is_preview = true;
    
    acfe_flexible_render_layout_template($layout, $field);
    
}

add_filter('acfe/flexible/render/template', 'acfe_flexible_layout_render_template_setting', 0, 4);
function acfe_flexible_layout_render_template_setting($return, $field, $layout, $is_preview){
    
    if(acf_maybe_get($layout, 'acfe_flexible_render_template'))
        $return = $layout['acfe_flexible_render_template'];
    
    return $return;
    
}

add_filter('acfe/flexible/render/style', 'acfe_flexible_layout_render_style_setting', 0, 4);
function acfe_flexible_layout_render_style_setting($return, $field, $layout, $is_preview){
    
    if(acf_maybe_get($layout, 'acfe_flexible_render_style'))
        $return = $layout['acfe_flexible_render_style'];
    
    return $return;
    
}

add_filter('acfe/flexible/render/script', 'acfe_flexible_layout_render_script_setting', 0, 4);
function acfe_flexible_layout_render_script_setting($return, $field, $layout, $is_preview){
    
    if(acf_maybe_get($layout, 'acfe_flexible_render_script'))
        $return = $layout['acfe_flexible_render_script'];
    
    return $return;
    
}

if(!class_exists('acfe_field_flexible_content')):

class acfe_field_flexible_content extends acf_field_flexible_content{
    
    public $flexible = '';
    public $state = false;
    public $placeholder = false;
    public $preview = false;
    public $modal_edition = false;
    public $title_edition = false;
    public $copy_paste = false;
    public $remove_collapse = false;
    public $close_button = false;
    public $stylised_button = false;
    public $remove_actions = false;
    
    function __construct(){
        
        parent::initialize();
        
        // Retrieve Flexible Content Class
        $this->flexible = acf_get_field_type('flexible_content');
        
        // Remove Inherit Render Field
        remove_action('acf/render_field/type=flexible_content',                     array($this->flexible, 'render_field'), 9);
        
        // Field Action
        $this->add_field_action('acf/render_field',                                 array($this, 'render_field'), 9);
        
        // General Filters
        $this->add_filter('acfe/fields/flexible_content/layouts/icons',             array($this, 'add_layout_icons'), 10, 3);
        $this->add_filter('acfe/fields/flexible_content/layouts/div',               array($this, 'add_layout_div'), 10, 3);
        $this->add_filter('acfe/fields/flexible_content/layouts/handle',            array($this, 'add_layout_handle'), 10, 3);
        
        $this->add_filter('acf/load_fields',                                        array($this, 'load_fields'), 10, 2);
        
        // General Actions
        $this->add_action('wp_ajax_acfe/advanced_flexible_content/models',          array($this, 'ajax_layout_model'));
        $this->add_action('wp_ajax_nopriv_acfe/advanced_flexible_content/models',   array($this, 'ajax_layout_model'));
        
    }
    
    function load_fields($fields, $field){
        
        if(!isset($field['type']) || $field['type'] !== 'flexible_content')
            return $fields;
        
        if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group', 'acf_page_acf-tools')))
            return $fields;
        
        if(!acf_maybe_get($field, 'layouts'))
            return $fields;
        
        $settings = acf_maybe_get($field, 'acfe_flexible_layouts_settings');
        $title_edition = acf_maybe_get($field, 'acfe_flexible_title_edition');
        
        // Settings OR Title Edition
        if(!$settings && !$title_edition)
            return $fields;
        
        foreach($field['layouts'] as $layout_key => $layout){
            
            // Settings
            if($settings_key = acf_maybe_get($layout, 'acfe_flexible_settings')){
                
                $field_group = acf_get_field_group($settings_key);
                
                if(!empty($field_group)){
                    
                    $style = $field_group['label_placement'] === 'left' ? 'row' : 'block';
                    
                    acf_add_local_field(array(
                        'label'                 => false,
                        'key'                   => 'field_acfe_' . $layout['key'] . '_settings',
                        'name'                  => 'layout_settings',
                        'type'                  => 'clone',
                        'clone'                 => array($settings_key),
                        'display'               => 'group',
                        'acfe_seamless_style'   => true,
                        'layout'                => $style,
                        'prefix_label'          => 0,
                        'prefix_name'           => 1,
                        'parent_layout'         => $layout['key'],
                        'parent'                => $field['key']
                    ));
                    
                    $clone = acf_get_field('field_acfe_' . $layout['key'] . '_settings');
                    
                    array_unshift($fields, $clone);
                
                }
            
            }
            
            // Title Edition
            if($title_edition){
                
                acf_add_local_field(array(
                    'label'                 => false,
                    'key'                   => 'field_acfe_' . $layout['key'] . '_title',
                    'name'                  => 'acfe_flexible_layout_title',
                    'type'                  => 'text',
                    'required'              => false,
                    'maxlength'             => false,
                    'default_value'         => $layout['label'],
                    'placeholder'           => $layout['label'],
                    'parent_layout'         => $layout['key'],
                    'parent'                => $field['key']
                ));
                
                $title = acf_get_field('field_acfe_' . $layout['key'] . '_title');
                
                array_unshift($fields, $title);
            
            }
            
        }
        
        return $fields;
        
    }
    
    function ajax_layout_model(){
        
        // options
        $options = acf_parse_args($_POST, array(
            'field_key' => '',
            'layout'    => '',
        ));
        
        $field = acf_get_field($options['field_key']);
        if(!$field)
            die;
        
        $field = acf_prepare_field($field);
        
        foreach($field['layouts'] as $k => $layout){
            
            if($layout['name'] !== $options['layout'])
                continue;
            
            $this->render_layout($field, $layout, 'acfcloneindex', array());
            
            die;
            
        }
        
        die;
        
    }
    
    function render_field($field){
        
        // settings
        $stylised_button = acf_maybe_get($field, 'acfe_flexible_stylised_button');
        $copy_paste = acf_maybe_get($field, 'acfe_flexible_copy_paste');
        $ajax = acf_maybe_get($field, 'acfe_flexible_layouts_ajax');
        
        // Remove actions
        $remove_actions = false;
        $remove_actions = apply_filters('acfe/flexible/remove_actions',                           $remove_actions, $field);
        $remove_actions = apply_filters('acfe/flexible/remove_actions/name=' . $field['_name'],   $remove_actions, $field);
        $remove_actions = apply_filters('acfe/flexible/remove_actions/key=' . $field['key'],      $remove_actions, $field);
        
        // defaults
        if(empty($field['button_label'])){
        
            $field['button_label'] = $this->defaults['button_label'];
            
        }
        
        // sort layouts into names
        $layouts = array();
        
        foreach($field['layouts'] as $k => $layout){
        
            $layouts[$layout['name']] = $layout;
            
        }
        
        // vars
        $div = array(
            'class'		=> 'acf-flexible-content',
            'data-min'	=> $field['min'],
            'data-max'	=> $field['max']
        );
        
        // empty
        if(empty($field['value'])){
            $div['class'] .= ' -empty';
        }
        
        // no value message
        $no_value_message = __('Click the "%s" button below to start creating your layout','acf');
        $no_value_message = apply_filters('acf/fields/flexible_content/no_value_message', $no_value_message, $field);

    ?>
    <div <?php acf_esc_attr_e( $div ); ?>>

        <?php acf_hidden_input(array('name' => $field['name'])); ?>

        <div class="no-value-message">
            <?php printf($no_value_message, $field['button_label']); ?>
        </div>

        <div class="clones">
            <?php foreach($layouts as $layout):
            
                // Ajax
                if($ajax){
                
                    $div = array(
                        'class'			=> 'layout acf-clone',
                        'data-id'		=> 'acfcloneindex',
                        'data-layout'	=> $layout['name']
                    );
                    
                    
                    echo '<div ' . acf_esc_attr($div) . '></div>';
                
                // No ajax
                }else{
                    
                    $this->render_layout($field, $layout, 'acfcloneindex', array());
                    
                }
                
            endforeach; ?>
        </div>

        <div class="values">
            <?php if(!empty($field['value'])): 
                
                foreach($field['value'] as $i => $value):
                    
                    // validate
                    if(empty($layouts[$value['acf_fc_layout']]))
                        continue;
                    
                    
                    // render
                    $this->render_layout($field, $layouts[ $value['acf_fc_layout'] ], $i, $value);
                    
                endforeach;
                
            endif; ?>
        </div>

        <?php if(!$remove_actions){
        
        $button = array(
            'class'     => 'acf-button button',
            'href'      => '#',
            'data-name' => 'add-layout',
        );
        
        if(!$stylised_button){
            
            $button['class'] .= ' button-primary';
            
        }
        
        if($stylised_button){ ?>
            <div class="acfe-flexible-stylised-button">
        <?php } ?>
        
        <div class="acf-actions">
            <a <?php echo acf_esc_attr($button); ?>><?php echo $field['button_label']; ?></a>
            
            <?php if($copy_paste){ ?>
            
                <?php
                
                $button_secondary = array(
                    'class'     => 'button',
                    'style'     => 'padding-left:5px;padding-right:5px; margin-left:3px;',
                    'href'      => '#',
                    'data-name' => 'acfe-flexible-control-button',
                );
                
                if(!$stylised_button){
                    
                    $button_secondary['class'] .= ' button-primary';
                    
                }
                ?>
            
                <a <?php echo acf_esc_attr($button_secondary); ?>>
                   <span class="dashicons dashicons-arrow-down-alt2" style="vertical-align:text-top;width:auto;height:auto;font-size:13px;line-height:20px;"></span>
                </a>
                
                <script type="text-html" class="tmpl-acfe-flexible-control-popup">
                   <ul>
                       <li><a href="#" data-acfe-flexible-control-action="copy">Copy layouts</a></li>
                       <li><a href="#" data-acfe-flexible-control-action="paste">Paste layouts</a></li>
                   </ul>
                </script>
            
            <?php } ?>
            
        </div>
        
        <?php if($stylised_button){ ?>
            </div>
        <?php } ?>

        <script type="text-html" class="tmpl-popup">
            <ul>
            <?php foreach( $layouts as $layout ): 
                
                $atts = array(
                    'href'			=> '#',
                    'data-layout'	=> $layout['name'],
                    'data-min' 		=> $layout['min'],
                    'data-max' 		=> $layout['max'],
                );
                
                ?><li><a <?php acf_esc_attr_e($atts); ?>><?php echo $layout['label']; ?></a></li><?php 
            
            endforeach; ?>
            </ul>
        </script>
        
        <?php } ?>

    </div>
    <?php
    
    }
    
	function render_layout($field, $layout, $i, $value){
        
        // settings
        $this->state = acf_maybe_get($field, 'acfe_flexible_layouts_state');
        $this->placeholder = acf_maybe_get($field, 'acfe_flexible_layouts_placeholder');
        $this->preview = acf_maybe_get($field, 'acfe_flexible_layouts_previews');
        $this->modal_edition = acf_maybe_get($field, 'acfe_flexible_modal_edition');
        $this->title_edition = acf_maybe_get($field, 'acfe_flexible_title_edition');
        $this->copy_paste = acf_maybe_get($field, 'acfe_flexible_copy_paste');
        $this->remove_collapse = acf_maybe_get($field, 'acfe_flexible_layouts_remove_collapse');
        $this->close_button = acf_maybe_get($field, 'acfe_flexible_close_button');
        $this->stylised_button = acf_maybe_get($field, 'acfe_flexible_stylised_button');
        $this->settings = acf_maybe_get($layout, 'acfe_flexible_settings');
        
        // Remove actions
        $this->remove_actions = apply_filters('acfe/flexible/remove_actions',                           $this->remove_actions, $field);
        $this->remove_actions = apply_filters('acfe/flexible/remove_actions/name=' . $field['_name'],   $this->remove_actions, $field);
        $this->remove_actions = apply_filters('acfe/flexible/remove_actions/key=' . $field['key'],      $this->remove_actions, $field);
        
		// vars
		$sub_fields = $layout['sub_fields'];
		$id = ($i === 'acfcloneindex') ? 'acfcloneindex' : "row-$i";
		$prefix = $field['name'] . '[' . $id .  ']';
		
		// div
		$div = array(
			'class'			=> 'layout',
			'data-id'		=> $id,
			'data-layout'	=> $layout['name']
		);
		
		// clone
		if(!is_numeric($i)){
			
			$div['class'] .= ' acf-clone';
			
		}
		
        // div
        $div = $this->get_layout_div($div, $layout, $field);
        
        // handle
        $handle = $this->get_layout_handle($layout, $field);
		
        // title
		$title = $this->get_layout_title($field, $layout, $i, $value);
        
		// remove row
		reset_rows();
        
        ?>
        <div <?php echo acf_esc_attr($div); ?>>
                    
            <?php acf_hidden_input(array( 'name' => $prefix.'[acf_fc_layout]', 'value' => $layout['name'] )); ?>
            
            <div <?php echo acf_esc_attr($handle); ?>><?php echo $title; ?></div>
            
            <?php 
            
            // Title Edition
            $this->render_layout_title_edition($sub_fields, $value, $prefix);
            
            // Icons
            $this->render_layout_icons($layout, $field);
            
            // Placeholder
            $this->render_layout_placeholder($value, $layout, $field, $i);
            
            
            add_filter('acf/prepare_field/type=wysiwyg', array($this, 'acfe_flexible_editor_delay'));
            
                // Fields
                $this->render_layout_fields($layout, $sub_fields, $value, $prefix);
            
            remove_filter('acf/prepare_field/type=wysiwyg', array($this, 'acfe_flexible_editor_delay'));
            
            ?>

        </div>
        <?php
		
	}
    
    function get_layout_div($div, $layout, $field){
        
        $div = apply_filters('acfe/fields/flexible_content/layouts/div',                                                         $div, $layout, $field);
        $div = apply_filters('acfe/fields/flexible_content/layouts/div/name=' . $field['_name'],                                 $div, $layout, $field);
        $div = apply_filters('acfe/fields/flexible_content/layouts/div/key=' . $field['key'],                                    $div, $layout, $field);
        $div = apply_filters('acfe/fields/flexible_content/layouts/div/name=' . $field['_name'] . '&layout=' . $layout['name'],  $div, $layout, $field);
        $div = apply_filters('acfe/fields/flexible_content/layouts/div/key=' . $field['key'] . '&layout=' . $layout['name'],     $div, $layout, $field);
        
        return $div;
        
    }
    
    function get_layout_handle($layout, $field){
        
        $handle = array(
            'class'     => 'acf-fc-layout-handle',
            'title'     => __('Drag to reorder','acf'),
            'data-name' => 'collapse-layout',
        );
        
        if($this->remove_collapse){
            
            unset($handle['data-name']);
            
        }
        
        $handle = apply_filters('acfe/fields/flexible_content/layouts/handle',                                                         $handle, $layout, $field);
        $handle = apply_filters('acfe/fields/flexible_content/layouts/handle/name=' . $field['_name'],                                 $handle, $layout, $field);
        $handle = apply_filters('acfe/fields/flexible_content/layouts/handle/key=' . $field['key'],                                    $handle, $layout, $field);
        $handle = apply_filters('acfe/fields/flexible_content/layouts/handle/name=' . $field['_name'] . '&layout=' . $layout['name'],  $handle, $layout, $field);
        $handle = apply_filters('acfe/fields/flexible_content/layouts/handle/key=' . $field['key'] . '&layout=' . $layout['name'],     $handle, $layout, $field);
        
        return $handle;
        
    }
    
    function render_layout_icons($layout, $field){
        
        // icons
        $icons = array(
            'add'       => '<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-layout" title="' . __('Add layout','acf') . '"></a>',
            'remove'    => '<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-layout" title="' . __('Remove layout','acf') . '"></a>',
            'collapse'  => '<a class="acf-icon -collapse small acf-js-tooltip" href="#" data-name="collapse-layout" title="' . __('Click to toggle','acf') . '"></a>'
        );
        
        $icons = apply_filters('acfe/fields/flexible_content/layouts/icons',                                                         $icons, $layout, $field);
        $icons = apply_filters('acfe/fields/flexible_content/layouts/icons/name=' . $field['_name'],                                 $icons, $layout, $field);
        $icons = apply_filters('acfe/fields/flexible_content/layouts/icons/key=' . $field['key'],                                    $icons, $layout, $field);
        $icons = apply_filters('acfe/fields/flexible_content/layouts/icons/name=' . $field['_name'] . '&layout=' . $layout['name'],  $icons, $layout, $field);
        $icons = apply_filters('acfe/fields/flexible_content/layouts/icons/key=' . $field['key'] . '&layout=' . $layout['name'],     $icons, $layout, $field);
        
        if(!empty($icons)){ ?>
        
            <div class="acf-fc-layout-controls">
            
                <?php foreach($icons as $icon){ ?>
                
                    <?php echo $icon; ?>
                    
                <?php } ?>
                
            </div>
            
        <?php }
        
    }
    
    function render_layout_title_edition(&$sub_fields, $value, $prefix){
        
        if(!$this->title_edition || empty($sub_fields))
            return false;
        
        if($sub_fields[0]['name'] !== 'acfe_flexible_layout_title')
            return false;
        
        // Extract
        $title = acf_extract_var($sub_fields, 0);
        
        // Reset key 0
        $sub_fields = array_values($sub_fields);
        
        // add value
        if( isset($value[ $title['key'] ]) ) {
            
            // this is a normal value
            $title['value'] = $value[ $title['key'] ];
            
        } elseif( isset($title['default_value']) ) {
            
            // no value, but this sub field has a default value
            $title['value'] = $title['default_value'];
            
        }
        
        // update prefix to allow for nested values
        $title['prefix'] = $prefix;
        
        $title['class'] = 'acfe-flexible-control-title';
        $title['data-acfe-flexible-control-title-input'] = 1;
        
        $title = acf_validate_field($title);
        $title = acf_prepare_field($title);
        
        $input_attrs = array();
        foreach( array( 'type', 'id', 'class', 'name', 'value', 'placeholder', 'maxlength', 'pattern', 'readonly', 'disabled', 'required', 'data-acfe-flexible-control-title-input' ) as $k ) {
            if( isset($title[ $k ]) ) {
                $input_attrs[ $k ] = $title[ $k ];
            }
        }
        
        // render input
        echo acf_get_text_input(acf_filter_attrs($input_attrs));
        
    }
    
    function render_layout_placeholder($value, $layout, $field, $i){
        
        if(!$this->placeholder && !$this->preview)
            return false;
        
        $placeholder = array(
            'class' => 'acfe-fc-placeholder',
            'title' => __('Edit layout', 'acfe'),
        );

        if(!$this->modal_edition && !$this->preview && $this->state !== 'collapse'){
            
            $placeholder['class'] .= ' acf-hidden';
            
        }
        
        $preview_html = false;
        
        if($this->preview){
            
            if(!empty($value)){
                
                ob_start();
                
                acfe_flexible_layout_preview(array(
                    'post_id'   => acf_get_valid_post_id(),
                    'i'         => $i,
                    'field_key' => $field['key'],
                    'layout'    => $layout['name'],
                    'value'     => $value,
                ));
                
                $preview_html = ob_get_clean();
                
                if(strlen($preview_html) > 0){
                    
                    $placeholder['class'] .= ' acfe-fc-preview';
                    
                }
                
            }
            
        }
        
        if($this->modal_edition){
            
            $placeholder['data-action'] = 'acfe-flexible-modal-edit';
            
        }
        
        ?>
        
        <div <?php echo acf_esc_attr($placeholder); ?>>
        
            <a href="#" class="button">
                <span class="dashicons dashicons-edit"></span>
            </a>
            
            <div class="acfe-fc-overlay"></div>
            
            <div class="acfe-flexible-placeholder -preview">
                <?php echo $preview_html; ?>
            </div>
            
        </div>
        
        <?php
        
    }
    
    function render_layout_fields($layout, $sub_fields, $value, $prefix){
        
        $modal_edition = $this->modal_edition;
        $close_button = $this->close_button;
        
        if(empty($sub_fields))
            return;
        
        if($sub_fields[0]['name'] === 'layout_settings'){
            
            $sub_field = acf_extract_var($sub_fields, 0);
            ?>
            
            <div class="acfe-modal -settings">
            <div class="acfe-modal-wrapper">
            <div class="acfe-modal-content">
            
                <div class="acf-fields -top">
                
                    <?php
                        
                    // add value
                    if( isset($value[ $sub_field['key'] ]) ) {
                        
                        // this is a normal value
                        $sub_field['value'] = $value[ $sub_field['key'] ];
                        
                    } elseif( isset($sub_field['default_value']) ) {
                        
                        // no value, but this sub field has a default value
                        $sub_field['value'] = $sub_field['default_value'];
                        
                    }
                    
                    
                    // update prefix to allow for nested values
                    $sub_field['prefix'] = $prefix;
                    
                    
                    // render input
                    acf_render_field_wrap($sub_field, 'div');
                    
                    ?>
                
                </div>
            
            </div>
            </div>
            </div>
            
            <?php
            
        }
        
        // el
        $el = 'div';
        
		if($layout['display'] == 'table'){
			
			$el = 'td';
			
		}
    
        if($modal_edition){ ?>
        
            <div class="acfe-modal -fields">
            <div class="acfe-modal-wrapper">
            <div class="acfe-modal-content">
        
        <?php } ?>
    
        <?php if( $layout['display'] == 'table' ): ?>
        <table class="acf-table">
            
            <thead>
                <tr>
                    <?php foreach( $sub_fields as $sub_field ): 
                        
                        // prepare field (allow sub fields to be removed)
                        $sub_field = acf_prepare_field($sub_field);
                        
                        
                        // bail ealry if no field
                        if( !$sub_field ) continue;
                        
                        
                        // vars
                        $atts = array();
                        $atts['class'] = 'acf-th';
                        $atts['data-name'] = $sub_field['_name'];
                        $atts['data-type'] = $sub_field['type'];
                        $atts['data-key'] = $sub_field['key'];
                        
                        
                        // Add custom width
                        if( $sub_field['wrapper']['width'] ) {
                        
                            $atts['data-width'] = $sub_field['wrapper']['width'];
                            $atts['style'] = 'width: ' . $sub_field['wrapper']['width'] . '%;';
                            
                        }
                        
                        ?>
                        <th <?php echo acf_esc_attr( $atts ); ?>>
                            <?php echo acf_get_field_label( $sub_field ); ?>
                            <?php if( $sub_field['instructions'] ): ?>
                                <p class="description"><?php echo $sub_field['instructions']; ?></p>
                            <?php endif; ?>
                        </th>
                        
                    <?php endforeach; ?> 
                </tr>
            </thead>
            
            <tbody>
                <tr class="acf-row">
        <?php else: ?>
        <div class="acf-fields <?php if($layout['display'] == 'row'): ?>-left<?php endif; ?>">
        <?php endif; ?>
        
            <?php
                
            // loop though sub fields
            foreach( $sub_fields as $sub_field ) {
                
                // add value
                if( isset($value[ $sub_field['key'] ]) ) {
                    
                    // this is a normal value
                    $sub_field['value'] = $value[ $sub_field['key'] ];
                    
                } elseif( isset($sub_field['default_value']) ) {
                    
                    // no value, but this sub field has a default value
                    $sub_field['value'] = $sub_field['default_value'];
                    
                }
                
                
                // update prefix to allow for nested values
                $sub_field['prefix'] = $prefix;
                
                
                // render input
                acf_render_field_wrap( $sub_field, $el );
            
            }
            
            ?>
            
            <?php if(!$modal_edition && $close_button){ ?>
            
                <div class="acfe-flexible-opened-actions"><a href="javascript:void(0);" class="button"><?php _e('Close', 'acf'); ?></button></a></div>
            
            <?php } ?>
                
        <?php if( $layout['display'] == 'table' ): ?>
                </tr>
            </tbody>
        </table>
        <?php else: ?>
        </div>
        <?php endif; ?>
        
        <?php if($modal_edition){ ?>
        
            </div>
            </div>
            </div>
        
        <?php }
        
    }
    
    function acfe_flexible_editor_delay($field){
        
        $field['delay'] = 1;
        
        return $field;
        
    }
    
    function add_layout_icons($icons, $layout, $field){
        
        // Settings
        if($this->settings){
            
            $new_icons = array(
                'settings' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-admin-generic" href="#" title="Settings" data-acfe-flexible-settings="' . $layout['name'] . '"></a>'
            );
            
            $icons = array_merge($icons, $new_icons);
            
        }
        
        // Copy/Paste
        if($this->copy_paste){
            
            $new_icons = array(
                'copy' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-category" href="#" title="Copy layout" data-acfe-flexible-control-copy="' . $layout['name'] . '"></a>'
            );
            
            $icons = array_merge($new_icons, $icons);
            
        }
        
        // Clone
        $new_icons = array(
            'clone' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-admin-page" href="#" title="Clone layout" data-acfe-flexible-control-clone="' . $layout['name'] . '"></a>'
        );
        
        $icons = array_merge($new_icons, $icons);
        
        // Remove Toggle
        if(($this->modal_edition || $this->remove_collapse) && isset($icons['collapse'])){
            
            unset($icons['collapse']);
            
        }
        
        if($this->remove_actions){
            
            // Add
            if(isset($icons['add']))    unset($icons['add']);
            
            // Remove
            if(isset($icons['remove'])) unset($icons['remove']);
            
            // Clone
            if(isset($icons['clone']))  unset($icons['clone']);
            
            // Copy/Paste
            if(isset($icons['copy']))  unset($icons['copy']);
            
        }
        
        return $icons;
        
    }
    
    function add_layout_div($div, $layout, $field){
        
        // Class
        if($this->state === 'collapse' || $this->preview || $this->modal_edition){
            
            $div['class'] .= ' -collapsed';
            
        }
        
        return $div;
        
    }
    
    function add_layout_handle($handle, $layout, $field){
        
        // Data
        if($this->modal_edition){
        
            $handle['data-action'] = 'acfe-flexible-modal-edit';
            
        }
        
        return $handle;
        
    }
    
}

acf_register_field_type('acfe_field_flexible_content');

endif;