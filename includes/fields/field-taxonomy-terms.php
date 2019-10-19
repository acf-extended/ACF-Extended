<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_taxonomy_terms extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_taxonomy_terms';
        $this->label = __('Taxonomy Terms', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'taxonomy'      => array(),
            'field_type'    => 'checkbox',
            'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'choices'		=> array(),
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
            'layout'        => '',
			'toggle'        => 0,
			'allow_custom'  => 0,
			'return_format' => 'id',
        );
        
        parent::__construct();
        
    }

    function prepare_field($field){
        
        $field['choices'] = acfe_get_taxonomy_terms_ids($field['taxonomy']);
        
        // Set Field Type
        $field['type'] = $field['field_type'];
        
        return $field;
        
    }
    
    function render_field_settings($field){
        
        if(isset($field['default_value']))
            $field['default_value'] = acf_encode_choices($field['default_value'], false);
        
        // Allow Taxonomy
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomy_labels(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All taxonomies",'acf'),
		));
        
        // field_type
        acf_render_field_setting($field, array(
            'label'			=> __('Appearance','acf'),
            'instructions'	=> __('Select the appearance of this field', 'acf'),
            'type'			=> 'select',
            'name'			=> 'field_type',
            'optgroup'		=> true,
            'choices'		=> array(
                'checkbox'  => __('Checkbox', 'acf'),
                'select'    => _x('Select', 'noun', 'acf')
            )
        ));
        
        // default_value
		acf_render_field_setting( $field, array(
			'label'			=> __('Default Value','acf'),
			'instructions'	=> __('Enter each default value on a new line','acf'),
			'name'			=> 'default_value',
			'type'			=> 'textarea',
		));
        
        // return_format
        acf_render_field_setting($field, array(
            'label'			=> __('Return Value', 'acf'),
            'instructions'	=> '',
            'type'			=> 'radio',
            'name'			=> 'return_format',
            'choices'		=> array(
                'object'    =>	__('Term object', 'acfe'),
                'name'      =>	__('Term name', 'acfe'),
                'id'      =>	__('Term ID', 'acfe'),
            ),
            'layout'	=>	'horizontal',
        ));
        
		// Select: allow_null
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Null?','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_null',
			'type'			=> 'true_false',
			'ui'			=> 1,
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
        
        // placeholder
        acf_render_field_setting($field, array(
            'label'			=> __('Placeholder Text','acf'),
            'instructions'	=> __('Appears within the input','acf'),
            'type'			=> 'text',
            'name'			=> 'placeholder',
            'placeholder'   => _x('Select', 'verb', 'acf'),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'select',
                    ),
                    array(
                        'field'     => 'allow_null',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                    
                )
            )
        ));
        
        // Select: multiple
		acf_render_field_setting( $field, array(
			'label'			=> __('Select multiple values?','acf'),
			'instructions'	=> '',
			'name'			=> 'multiple',
			'type'			=> 'true_false',
			'ui'			=> 1,
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
		acf_render_field_setting( $field, array(
			'label'			=> __('Stylised UI','acf'),
			'instructions'	=> '',
			'name'			=> 'ui',
			'type'			=> 'true_false',
			'ui'			=> 1,
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
		acf_render_field_setting( $field, array(
			'label'			=> __('Use AJAX to lazy load choices?','acf'),
			'instructions'	=> '',
			'name'			=> 'ajax',
			'type'			=> 'true_false',
			'ui'			=> 1,
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
        
        // Checkbox: layout
		acf_render_field_setting( $field, array(
			'label'			=> __('Layout','acf'),
			'instructions'	=> '',
			'type'			=> 'radio',
			'name'			=> 'layout',
			'layout'		=> 'horizontal', 
			'choices'		=> array(
				'vertical'		=> __("Vertical",'acf'), 
				'horizontal'	=> __("Horizontal",'acf')
			),
            'conditions' => array(
                array(
                    array(
                        'field'     => 'field_type',
                        'operator'  => '==',
                        'value'     => 'checkbox',
                    ),
                ),
            )
		));
        
        // Checkbox: toggle
        acf_render_field_setting( $field, array(
			'label'			=> __('Toggle','acf'),
			'instructions'	=> __('Prepend an extra checkbox to toggle all choices','acf'),
			'name'			=> 'toggle',
			'type'			=> 'true_false',
			'ui'			=> 1,
            'conditions' => array(
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
		acf_render_field_setting( $field, array(
			'label'			=> __('Allow Custom','acf'),
			'instructions'	=> '',
			'name'			=> 'allow_custom',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Allow 'custom' values to be added", 'acf'),
            'conditions' => array(
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
		acf_render_field_setting( $field, array(
			'label'			=> __('Save Custom','acf'),
			'instructions'	=> '',
			'name'			=> 'save_custom',
			'type'			=> 'true_false',
			'ui'			=> 1,
			'message'		=> __("Save 'custom' values to the field's choices", 'acf'),
            'conditions' => array(
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
    
    function format_value($value, $post_id, $field){
        
        if(empty($value))
            return $value;
        
        // Return: object
		if($field['return_format'] === 'object' || $field['return_format'] === 'name'){
            
            // array
            if(acf_is_array($value)){
                
                foreach($value as $i => $v){
                    
                    $term = get_term($v);
                    
                    if($field['return_format'] === 'object'){
                        
                        $value[$i] = $term;
                        
                    }elseif($field['return_format'] === 'name'){
                        
                        $value[$i] = $term->name;
                        
                    }
                    
                }
            
            // string
            }else{
                
                $term = get_term($value);
                
                if($field['return_format'] === 'object'){
                    
                    $value = $term;
                    
                }elseif($field['return_format'] === 'name'){
                    
                    $value = $term->name;
                    
                }
                
            }
        
		}
        
		// return
		return $value;
        
    }

}

new acfe_field_taxonomy_terms();