<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_flexible_content_controls')):

class acfe_field_flexible_content_controls{
    
    function __construct(){
    
        // Hooks
        add_filter('acfe/flexible/defaults_field',                  array($this, 'defaults_field'), 1);
        add_action('acfe/flexible/render_field_settings',           array($this, 'render_field_settings'), 1);
    
        add_filter('acfe/flexible/wrapper_attributes',              array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/prepare_layout',                  array($this, 'prepare_layout'), 20, 5);
        add_filter('acfe/flexible/action_wrapper',                  array($this, 'action_wrapper'), 10, 2);
        add_filter('acfe/flexible/action_button',                   array($this, 'action_button'), 10, 2);
        add_filter('acfe/flexible/action_button_secondary',         array($this, 'action_button_secondary'), 10, 2);
        
        add_filter('acf/fields/flexible_content/no_value_message',  array($this, 'no_value_message'), 1, 2);
        
    }
    
    function action_wrapper($wrapper, $field){
    
        if($field['acfe_flexible_stylised_button']){
            $wrapper['class'] = ' acfe-flexible-stylised-button';
        }
        
        return $wrapper;
        
    }
    
    function action_button($button, $field){
    
        if(!$field['acfe_flexible_stylised_button']){
            $button['class'] .= ' button-primary';
        }
        
        return $button;
        
    }
    
    function action_button_secondary($button, $field){
    
        if(!$field['acfe_flexible_stylised_button']){
            $button['class'] .= ' button-primary';
        }
        
        return $button;
        
    }
    
    function defaults_field($field){
        
        $field['acfe_flexible_advanced'] = false;
        $field['acfe_flexible_stylised_button'] = false;
        $field['acfe_flexible_hide_empty_message'] = false;
        $field['acfe_flexible_empty_message'] = '';
        
        return $field;
        
    }
    
    function render_field_settings($field){
    
        // Advanced settings
        acf_render_field_setting($field, array(
            'label'         => __('Advanced Flexible Content'),
            'name'          => 'acfe_flexible_advanced',
            'key'           => 'acfe_flexible_advanced',
            'instructions'  => __('Show advanced Flexible Content settings'),
            'type'              => 'true_false',
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
        ));
    
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
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'acfe_flexible_remove_add_button',
                        'operator'  => '!=',
                        'value'     => '1',
                    ),
                )
            )
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
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
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
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
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
        
    }
    
    function wrapper_attributes($wrapper, $field){
    
        // Stylised button
        if($field['acfe_flexible_stylised_button']){
            $wrapper['data-acfe-flexible-stylised-button'] = 1;
        }
    
        // Hide Empty Message
        if($field['acfe_flexible_hide_empty_message'] || $field['acfe_flexible_stylised_button']){
            $wrapper['data-acfe-flexible-hide-empty-message'] = 1;
        }
        
        return $wrapper;
        
    }
    
    function prepare_layout($layout, $field, $i, $value, $prefix){
        
        // Vars
        $name = $field['_name'];
        $key = $field['key'];
        $l_name = $layout['name'];
        
        // Default
        $icons = array(
            'add'       => '<a class="acf-icon -plus small light acf-js-tooltip" href="#" data-name="add-layout" title="' . __('Add layout','acf') . '"></a>',
            'duplicate' => '<a class="acf-icon -duplicate small light acf-js-tooltip" href="#" data-name="duplicate-layout" title="' . __('Duplicate layout','acf') . '"></a>',
            'delete'    => '<a class="acf-icon -minus small light acf-js-tooltip" href="#" data-name="remove-layout" title="' . __('Remove layout','acf') . '"></a>',
            'collapse'  => '<a class="acf-icon -collapse small acf-js-tooltip" href="#" data-name="collapse-layout" title="' . __('Click to toggle','acf') . '"></a>'
        );
        
        // Previous ACF Version
        if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
            acfe_unset($icons, 'duplicate');
        }
        
        // Filters
        $icons = apply_filters("acfe/flexible/layouts/icons",                               $icons, $layout, $field);
        $icons = apply_filters("acfe/flexible/layouts/icons/name={$name}",                  $icons, $layout, $field);
        $icons = apply_filters("acfe/flexible/layouts/icons/key={$key}",                    $icons, $layout, $field);
        $icons = apply_filters("acfe/flexible/layouts/icons/name={$name}&layout={$l_name}", $icons, $layout, $field);
        $icons = apply_filters("acfe/flexible/layouts/icons/key={$key}&layout={$l_name}",   $icons, $layout, $field);
        
        if(!empty($icons)){ ?>
            <div class="acf-fc-layout-controls">
                <?php foreach($icons as $icon){ ?>
                    <?php echo $icon; ?>
                <?php } ?>
            </div>
        <?php }
        
        return $layout;
        
    }
    
    function no_value_message($message, $field){
    
        if(!empty($field['acfe_flexible_empty_message']))
            $message = $field['acfe_flexible_empty_message'];
        
        return $message;
        
    }
    
}

acf_new_instance('acfe_field_flexible_content_controls');

endif;