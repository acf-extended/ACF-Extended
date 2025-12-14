<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_forms')):

class acfe_field_forms extends acfe_field{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_forms';
        $this->label = __('Forms', 'acfe');
        $this->category = apply_filters('acfe/form_field_type_category', 'relational');
        $this->defaults = array(
            'forms'              => array(),
            'field_type'         => 'checkbox',
            'multiple'           => 0,
            'allow_null'         => 0,
            'choices'            => array(),
            'default_value'      => '',
            'ui'                 => 0,
            'ajax'               => 0,
            'placeholder'        => '',
            'search_placeholder' => '',
            'layout'             => '',
            'toggle'             => 0,
            'allow_custom'       => 0,
            'other_choice'       => 0,
            'return_format'      => 'name',
        );
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        if(isset($field['default_value'])){
            $field['default_value'] = acf_encode_choices($field['default_value'], false);
        }
        
        // Allow Form
        acf_render_field_setting($field, array(
            'label'         => __('Allow Forms','acf'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'forms',
            'choices'       => acfe_get_pretty_forms(),
            'multiple'      => 1,
            'ui'            => 1,
            'allow_null'    => 1,
            'placeholder'   => __("All forms",'acf'),
        ));
        
        // field_type
        acf_render_field_setting($field, array(
            'label'         => __('Appearance','acf'),
            'instructions'  => __('Select the appearance of this field', 'acf'),
            'type'          => 'select',
            'name'          => 'field_type',
            'optgroup'      => true,
            'choices'       => array(
                'checkbox'      => __('Checkbox', 'acf'),
                'radio'         => __('Radio Buttons', 'acf'),
                'select'        => _x('Select', 'noun', 'acf')
            )
        ));
        
        // default_value
        acf_render_field_setting($field, array(
            'label'         => __('Default Value','acf'),
            'instructions'  => __('Enter each default value on a new line','acf'),
            'name'          => 'default_value',
            'type'          => 'textarea',
        ));
        
        // return_format
        acf_render_field_setting($field, array(
            'label'         => __('Return Value', 'acf'),
            'instructions'  => '',
            'type'          => 'radio',
            'name'          => 'return_format',
            'choices'       => array(
                'id'    => __('Form ID', 'acfe'),
                'name'  => __('Form name', 'acfe')
            ),
            'layout'        => 'horizontal',
        ));
        
        // Select + Radio: allow_null
        acf_render_field_setting($field, array(
            'label'         => __('Allow Null?','acf'),
            'instructions'  => '',
            'name'          => 'allow_null',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                ),
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'radio',
                    ),
                ),
            )
        ));
    
        // Select: Placeholder
        acf_render_field_setting($field, array(
            'label'             => __('Placeholder','acf'),
            'instructions'      => __('Appears within the input','acf'),
            'type'              => 'text',
            'name'              => 'placeholder',
            'placeholder'       => _x('Select', 'verb', 'acf'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                    array(
                        'field'     => 'allow_null',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                ),
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'allow_null',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                ),
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                ),
            )
        ));
    
        // Select: Search Placeholder
        acf_render_field_setting($field, array(
            'label'             => __('Search Input Placeholder','acf'),
            'instructions'      => __('Appears within the search input','acf'),
            'type'              => 'text',
            'name'              => 'search_placeholder',
            'placeholder'       => '',
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    array(
                        'field'     => 'multiple',
                        'operator'  => '==',
                        'value'     => '0',
                    ),
                ),
            )
        ));
        
        // Select: multiple
        acf_render_field_setting($field, array(
            'label'         => __('Select multiple values?','acf'),
            'instructions'  => '',
            'name'          => 'multiple',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                ),
            )
        ));
        
        // Select: ui
        acf_render_field_setting($field, array(
            'label'         => __('Stylised UI','acf'),
            'instructions'  => '',
            'name'          => 'ui',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                ),
            )
        ));
                
        
        // Select: ajax
        acf_render_field_setting($field, array(
            'label'         => __('Use AJAX to lazy load choices?','acf'),
            'instructions'  => '',
            'name'          => 'ajax',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'ui',
                        'operator'  => '==',
                        'value'     => 1,
                    ),
                ),
            )
        ));
        
        // Radio: other_choice
        acf_render_field_setting($field, array(
            'label'         => __('Other','acf'),
            'instructions'  => '',
            'name'          => 'other_choice',
            'type'          => 'true_false',
            'ui'            => 1,
            'message'       => __("Add 'other' choice to allow for custom values", 'acf'),
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'radio',
                    ),
                ),
            )
        ));
        
        
        // Radio: save_other_choice
        acf_render_field_setting($field, array(
            'label'         => __('Save Other','acf'),
            'instructions'  => '',
            'name'          => 'save_other_choice',
            'type'          => 'true_false',
            'ui'            => 1,
            'message'       => __("Save 'other' values to the field's choices", 'acf'),
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'radio',
                    ),
                    array(
                        'field'     => 'other_choice',
                        'operator'  => '==',
                        'value'     => 1,
                    ),
                ),
            )
        ));
        
        // Checkbox: layout
        acf_render_field_setting($field, array(
            'label'         => __('Layout','acf'),
            'instructions'  => '',
            'type'          => 'radio',
            'name'          => 'layout',
            'layout'        => 'horizontal', 
            'choices'       => array(
                'vertical'      => __("Vertical",'acf'),
                'horizontal'    => __("Horizontal",'acf')
            ),
            'conditions'    => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                ),
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'radio',
                    ),
                ),
            )
        ));
        
        // Checkbox: toggle
        acf_render_field_setting($field, array(
            'label'         => __('Toggle','acf'),
            'instructions'  => __('Prepend an extra checkbox to toggle all choices','acf'),
            'name'          => 'toggle',
            'type'          => 'true_false',
            'ui'            => 1,
            'conditions'    => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                ),
            )
        ));
        
        // Checkbox: other_choice
        acf_render_field_setting($field, array(
            'label'         => __('Allow Custom','acf'),
            'instructions'  => '',
            'name'          => 'allow_custom',
            'type'          => 'true_false',
            'ui'            => 1,
            'message'       => __("Allow 'custom' values to be added", 'acf'),
            'conditions'    => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                ),
            )
        ));
        
        
        // Checkbox: save_other_choice
        acf_render_field_setting($field, array(
            'label'         => __('Save Custom','acf'),
            'instructions'  => '',
            'name'          => 'save_custom',
            'type'          => 'true_false',
            'ui'            => 1,
            'message'       => __("Save 'custom' values to the field's choices", 'acf'),
            'conditions'    => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                    array(
                        'field'     => 'allow_custom',
                        'operator'  => '==',
                        'value'     => 1,
                    ),
                ),
            )
        ));
        
    }
    
    
    /**
     * update_field
     *
     * @param $field
     *
     * @return mixed
     */
    function update_field($field){
        
        $field['default_value'] = acf_decode_choices($field['default_value'], true);
        
        if($field['field_type'] === 'radio'){
            $field['default_value'] = acfe_unarray($field['default_value']);
        }
        
        return $field;
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return mixed
     */
    function prepare_field($field){
    
        // field type
        $type = $field['type'];
        $field_type = $field['field_type'];
    
        $field['type'] = $field_type;
        $field['wrapper']['data-ftype'] = $type;
        
        // choices
        $field['choices'] = acfe_get_pretty_forms($field['forms']);
    
        // allow custom
        if($field['allow_custom']){
        
            $value = acf_maybe_get($field, 'value');
            $value = acf_get_array($value);
        
            foreach($value as $v){
            
                // append custom value to choices
                if(!isset($field['choices'][ $v ])){
                    $field['choices'][ $v ] = $v;
                    $field['custom_choices'][ $v ] = $v;
                }
            }
        
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * load_value
     *
     * Handle old values which returned form id
     * Should return form names now
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return mixed
     */
    function load_value($value, $post_id, $field){
        
        // bail early
        if(empty($value)){
            return $value;
        }
        
        // vars
        $is_array = is_array($value);
        $value = acf_get_array($value);
        
        // loop
        foreach($value as &$v){
            
            if(is_numeric($v)){
                
                // get item
                $item = acfe_get_module('form')->get_item($v);
                
                // found item by ID
                // return name
                if($item){
                    $v = $item['name'];
                }
                
            }
            
        }
        
        // check array
        if(!$is_array || $field['field_type'] === 'radio'){
            $value = acfe_unarray($value);
        }
        
        return $value;
        
    }
    
    
    /**
     * format_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array|false|mixed|string[]
     */
    function format_value($value, $post_id, $field){
    
        // bail early
        if(empty($value)){
            return $value;
        }
    
        // vars
        $is_array = is_array($value);
        $value = acf_get_array($value);
    
        // loop
        foreach($value as &$v){
        
            // get object
            $object = acfe_get_module('form')->get_item($v);
            
            if(!$object || is_wp_error($object)) continue;
        
            // return: name
            if($field['return_format'] === 'id'){
                $v = $object['ID'];
            }
        
        }
    
        // check array
        if(!$is_array){
            $value = acfe_unarray($value);
        }
    
        // return
        return $value;
        
    }
    
    
    /**
     * validate_front_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     * @param $form
     *
     * @return false
     */
    function validate_front_value($valid, $value, $field, $input, $form){
        
        // bail early
        if(!$this->pre_validate_front_value($valid, $value, $field, $form)){
            return $valid;
        }
        
        // custom value allowed
        if(!empty($field['allow_custom']) || !empty($field['other_choice'])){
            return $valid;
        }
        
        // vars
        $value = acf_get_array($value);
        $choices = acf_get_array($field['forms']);
        
        // empty choices
        if(empty($choices)){
            return $valid;
        }
        
        // check values against choices
        if(!empty(array_diff($value, $choices))){
            return false;
        }
        
        // return
        return $valid;
        
    }
    
    
    /**
     * translate_field
     *
     * @param $field
     *
     * @return mixed
     */
    function translate_field($field){
        
        $field['placeholder'] = acf_translate($field['placeholder']);
        $field['search_placeholder'] = acf_translate($field['search_placeholder']);
        
        return $field;
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_forms');

endif;