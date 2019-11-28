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
        'instructions'  => __('Better layouts button integration'),
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
        
        // Layouts Remove Collapse
        if(acf_maybe_get($field, 'acfe_flexible_layouts_remove_collapse')){
            
            $wrapper['data-acfe-flexible-remove-collapse'] = 1;
            
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
    
    // Remove actions
    $acfe_flexible_remove_actions = false;
    $acfe_flexible_remove_actions = apply_filters('acfe/flexible/remove_actions',                           $acfe_flexible_remove_actions, $field);
    $acfe_flexible_remove_actions = apply_filters('acfe/flexible/remove_actions/name=' . $field['_name'],   $acfe_flexible_remove_actions, $field);
    $acfe_flexible_remove_actions = apply_filters('acfe/flexible/remove_actions/key=' . $field['key'],      $acfe_flexible_remove_actions, $field);
    
    if($acfe_flexible_remove_actions){
        
        $wrapper['data-acfe-flexible-remove-actions'] = 1;
        
    }
    
    // Remove ajax 'layout_title' call
    $acfe_flexible_remove_ajax_title = false;
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
            
            $category = 'data-acfe-flexible-category="' . $layout['acfe_flexible_category'] . '"';
            
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
function acfe_flexible_layout_preview(){
    
    // Options
    $options = acf_parse_args($_POST, array(
        'post_id'		=> 0,
        'i'				=> 0,
        'field_key'		=> '',
        'nonce'			=> '',
        'layout'		=> '',
        'value'			=> array()
    ));
    
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
    
    $get_field_object = get_field_object($options['field_key'], $options['post_id'], false, false);
    
    $preview_key = 'preview_' . $options['field_key'];
    $get_field_object['key'] = $preview_key;
    
    acf_add_local_field($get_field_object);
    
    add_filter('acf/load_value/key=' . $preview_key, function($value, $post_id, $field) use($options){
        
        $value = array();
        $value[0] = wp_unslash($options['value']);
        
        return $value;
        
    }, 10, 3);
    
    if(have_rows($preview_key)):
        while(have_rows($preview_key)): the_row();
            
            // Flexible Preview
            do_action('acfe/flexible/preview/name=' . $field['_name'], $field, $layout);
            do_action('acfe/flexible/preview/key=' . $field['key'], $field, $layout);
            
            // Flexible Layout Preview
            do_action('acfe/flexible/layout/preview/layout=' . $layout['name'], $field, $layout);
            do_action('acfe/flexible/layout/preview/name=' . $field['_name'] . '&layout=' . $layout['name'], $field, $layout);
            do_action('acfe/flexible/layout/preview/key=' . $field['key'] . '&layout=' . $layout['name'], $field, $layout);
            
            // ACFE: All Flexible Preview
            do_action('acfe/flexible/preview', $field, $layout);
            
        endwhile;
    endif;
    
    die;
    
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

add_filter('acf/load_field/type=flexible_content', 'acfe_flexible_layout_title_subfield');
function acfe_flexible_layout_title_subfield($field){
    
    global $typenow;
    
    if(acf_is_screen(array('edit-acf-field-group', 'acf-field-group')) || (isset($typenow) && $typenow === 'acf-field-group'))
        return $field;
    
    if(!acf_maybe_get($field, 'layouts'))
        return $field;
    
    if(!acf_maybe_get($field, 'acfe_flexible_title_edition'))
        return $field;
    
    foreach($field['layouts'] as $layout_key => &$layout){
        
        // Add the input as the first sub_field
        array_unshift($layout['sub_fields'] , array(
            'ID'            => false,
            'label'         => false,
			'key'           => 'field_acfe_flexible_layout_title',
			'name'          => 'acfe_flexible_layout_title',
			'_name'         => 'acfe_flexible_layout_title',
			'type'          => 'text',
			'required'      => 0,
			'maxlength'     => null,
			'parent'        => false,
			'default_value' => $layout['label'],
			'placeholder'   => $layout['label'],
            'wrapper'       => array(
                'id' => '',
                'class' => '',
                'width' => '',
            )
		));
        
    }
    
    return $field;
    
}