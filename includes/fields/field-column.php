<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_column extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_column';
        $this->label = __('Column', 'acfe');
        $this->category = 'layout';
        $this->defaults = array(
            'columns' => '3/6'
        );
        
        add_filter('acf/field_wrapper_attributes', array($this, 'field_wrapper_attributes'), 10, 2);
        
        parent::__construct();
        
    }
    
    function render_field_settings($field){
        
        // columns
        acf_render_field_setting( $field, array(
            'label'         => __('Columns', 'acfe'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'columns',
            'choices'       => array(
                '1/6' => '1/6',
                '2/6' => '2/6',
                '3/6' => '3/6',
                '4/6' => '4/6',
                '5/6' => '5/6',
                '6/6' => '6/6'
            ),
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'endpoint',
                        'operator'  => '!=',
                        'value'     => '1',
                    )
                )
            )
		));
        
        // endpoint
		acf_render_field_setting( $field, array(
			'label'			=> __('Endpoint','acf'),
			'instructions'	=> __('Define an endpoint for the previous tabs to stop. This will start a new group of columns.', 'acf'),
			'name'			=> 'endpoint',
			'type'			=> 'true_false',
			'ui'			=> 1,
		));
        
    }
    
    function field_wrapper_attributes($wrapper, $field){
        
        if($field['type'] !== 'acfe_column')
            return $wrapper;
        
        if(isset($field['endpoint']) && !empty($field['endpoint'])){
            
            $wrapper['data-endpoint'] = $field['endpoint'];
            
        }
        
        elseif(isset($field['columns']) && !empty($field['columns'])){
            
            $wrapper['data-columns'] = $field['columns'];
            
        }
        
        return $wrapper;
        
    }
    
    
    function render_field($field){
        
        // vars
		$atts = array(
			'class' => 'acf-fields',
		);
		
		?>
		<div <?php acf_esc_attr_e($atts); ?>></div>
		<?php
        
    }

    function load_field($field){
        
        $columns = '';
        if(isset($field['columns']))
            $columns = ' ' . $field['columns'];
        
        if(isset($field['endpoint']) && !empty($field['endpoint']))
            $columns = ' endpoint';
        
        $field['label'] = '(Column' . $columns .')';
        $field['name'] = '';
        $field['instructions'] = '';
        $field['required'] = 0;
        $field['value'] = false;
        
        return $field;
        
    }
    
    function prepare_field($field){
        
        $field['label'] = false;
        
        return $field;
        
    }

}

new acfe_field_column();