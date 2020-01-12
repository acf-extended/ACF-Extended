<?php

if(!defined('ABSPATH'))
    exit;

if(version_compare($GLOBALS['wp_version'], '4.9', '<'))
    return;

if(!class_exists('acfe_field_code_editor')):

class acfe_field_code_editor extends acf_field{
    
    function __construct(){
        
        $this->name = 'acfe_code_editor';
        $this->label = __('Code Editor', 'acfe');
        $this->category = 'content';
        $this->defaults = array(
            'default_value'	=> '',
			'placeholder'   => '',
			'mode'          => 'text/html',
			'lines'         => true,
			'indent_unit'   => 4,
			'maxlength'		=> '',
			'rows'			=> ''
        );
        
        $this->textarea = acf_get_field_type('textarea');
        
        parent::__construct();
        
    }

    function render_field($field){
        
        $wrapper = array(
            'class'             => 'acf-input-wrap acfe-field-code-editor',
            'data-mode'         => $field['mode'],
            'data-lines'        => $field['lines'],
            'data-indent_unit'  => $field['indent_unit'],
        );
        
        $field['type'] = 'textarea';
        
        ?>
        <div <?php acf_esc_attr_e($wrapper); ?>>
            <?php $this->textarea->render_field($field); ?>
        </div>
        <?php
        
    }
    
    function render_field_settings($field){
        
        // default_value
        acf_render_field_setting($field, array(
            'label'			=> __('Default Value','acfe'),
            'instructions'	=> __('Appears when creating a new post','acfe'),
            'type'			=> 'acfe_code_editor',
            'name'			=> 'default_value',
        ));
        
        // placeholder
        acf_render_field_setting($field, array(
            'label'			=> __('Placeholder','acfe'),
            'instructions'	=> __('Appears within the input','acfe'),
            'type'			=> 'acfe_code_editor',
            'name'			=> 'placeholder',
        ));
        
        // Mode
        acf_render_field_setting($field, array(
            'label'			=> __('Editor mode','acfe'),
            'instructions'	=> __('Appears within the input','acfe'),
            'type'          => 'select',
            'name'			=> 'mode',
            'choices'       => array(
                'text/html'                 => __('Text/HTML', 'acfe'),
                'javascript'                => __('JavaScript', 'acfe'),
                'css'                       => __('CSS', 'acfe'),
                'application/x-httpd-php'   => __('PHP (mixed)', 'acfe'),
                'text/x-php'                => __('PHP (plain)', 'acfe'),
            )
        ));
        
        // Lines
        acf_render_field_setting($field, array(
            'label'			=> __('Show Lines', 'acfe'),
            'instructions'	=> __('Whether to show line numbers to the left of the editor', 'acfe'),
            'type'			=> 'true_false',
            'name'			=> 'lines',
            'ui'            => true,
        ));
        
        // Indent Unit
        acf_render_field_setting($field, array(
            'label'			=> __('Indent Unit', 'acfe'),
            'instructions'	=> __('How many spaces a block (whatever that means in the edited language) should be indented', 'acfe'),
            'type'			=> 'number',
            'min'			=> 0,
            'name'			=> 'indent_unit',
        ));
        
        // maxlength
        acf_render_field_setting($field, array(
            'label'			=> __('Character Limit','acfe'),
            'instructions'	=> __('Leave blank for no limit','acfe'),
            'type'			=> 'number',
            'name'			=> 'maxlength',
        ));
        
        // rows
        acf_render_field_setting($field, array(
            'label'			=> __('Rows','acfe'),
            'instructions'	=> __('Sets the textarea height','acfe'),
            'type'			=> 'number',
            'name'			=> 'rows',
            'placeholder'	=> 8
        ));
        
    }
    
    function input_admin_enqueue_scripts(){
    
        wp_enqueue_script('code-editor');
        wp_enqueue_style('code-editor');
        
    }
    
    function validate_value($valid, $value, $field, $input){
        
        return $this->textarea->validate_value($valid, $value, $field, $input);
        
	}

}

// initialize
acf_register_field_type('acfe_field_code_editor');

endif;
