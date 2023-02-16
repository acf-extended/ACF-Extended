<?php

if(!defined('ABSPATH')){
    exit;
}

if(acf_version_compare($GLOBALS['wp_version'],  '<', '4.9')){
    return;
}

if(!class_exists('acfe_field_code_editor')):

class acfe_field_code_editor extends acf_field{
    
    // vars
    var $textarea = '';
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_code_editor';
        $this->label = __('Code Editor', 'acfe');
        $this->category = 'content';
        $this->defaults = array(
            'default_value'   => '',
            'placeholder'     => '',
            'mode'            => 'text/html',
            'lines'           => true,
            'indent_unit'     => 4,
            'maxlength'       => '',
            'rows'            => 4,
            'max_rows'        => '',
            'return_format'   => array(),
        );
        
        $this->textarea = acf_get_field_type('textarea');
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // default_value
        acf_render_field_setting($field, array(
            'label'         => __('Default Value','acf'),
            'instructions'  => __('Appears when creating a new post','acf'),
            'type'          => 'acfe_code_editor',
            'name'          => 'default_value',
            'rows'          => 4
        ));
        
        // placeholder
        acf_render_field_setting($field, array(
            'label'         => __('Placeholder','acf'),
            'instructions'  => __('Appears within the input','acf'),
            'type'          => 'acfe_code_editor',
            'name'          => 'placeholder',
            'rows'          => 4
        ));
        
        // Mode
        acf_render_field_setting($field, array(
            'label'         => __('Editor mode','acf'),
            'instructions'  => __('Choose the syntax highlight','acf'),
            'type'          => 'select',
            'name'          => 'mode',
            'choices'       => array(
                'text/html'                 => __('Text/HTML', 'acf'),
                'javascript'                => __('JavaScript', 'acf'),
                'application/x-json'        => __('Json', 'acf'),
                'css'                       => __('CSS', 'acf'),
                'application/x-httpd-php'   => __('PHP (mixed)', 'acf'),
                'text/x-php'                => __('PHP (plain)', 'acf'),
            )
        ));
        
        // Lines
        acf_render_field_setting($field, array(
            'label'         => __('Show Lines', 'acf'),
            'instructions'  => 'Whether to show line numbers to the left of the editor',
            'type'          => 'true_false',
            'name'          => 'lines',
            'ui'            => true,
        ));
        
        // Indent Unit
        acf_render_field_setting($field, array(
            'label'         => __('Indent Unit', 'acf'),
            'instructions'  => 'How many spaces a block (whatever that means in the edited language) should be indented',
            'type'          => 'number',
            'min'           => 0,
            'name'          => 'indent_unit',
        ));
        
        // maxlength
        acf_render_field_setting($field, array(
            'label'         => __('Character Limit','acf'),
            'instructions'  => __('Leave blank for no limit','acf'),
            'type'          => 'number',
            'name'          => 'maxlength',
        ));
        
        // rows
        acf_render_field_setting($field, array(
            'label'         => __('Rows','acf'),
            'instructions'  => __('Sets the textarea height','acf'),
            'type'          => 'number',
            'name'          => 'rows',
            'placeholder'   => ''
        ));
        
        // max rows
        acf_render_field_setting($field, array(
            'label'         => __('Max rows','acf'),
            'instructions'  => __('Sets the textarea max height','acf'),
            'type'          => 'number',
            'name'          => 'max_rows',
            'placeholder'   => ''
        ));
    
        // return format
        acf_render_field_setting($field, array(
            'label'         => __('Return Value', 'acf'),
            'instructions'  => '',
            'type'          => 'checkbox',
            'name'          => 'return_format',
            'layout'        => 'horizontal',
            'choices'       => array(
                'htmlentities' => __("HTML Entities", 'acfe'),
                'nl2br'        => __("New Lines to &lt;br&gt;", 'acfe'),
            ),
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
        
        $field['return_format'] = acf_get_array($field['return_format']);
        
        return $field;
        
    }
    
    
    /**
     * input_admin_enqueue_scripts
     */
    function input_admin_enqueue_scripts(){
        
        if(acfe_is_block_editor()){
    
            wp_enqueue_script('code-editor');
            wp_enqueue_style('code-editor');
            
        }
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
        
        // enqueue
        wp_enqueue_script('code-editor');
        wp_enqueue_style('code-editor');
        
        // field type
        $field['type'] = 'textarea';
        
        // wrapper
        $wrapper = array(
            'class'             => 'acf-input-wrap acfe-field-code-editor',
            'data-mode'         => $field['mode'],
            'data-lines'        => $field['lines'],
            'data-indent-unit'  => $field['indent_unit'],
            'data-rows'         => $field['rows'],
            'data-max-rows'     => $field['max_rows'],
        );
        
        ?>
        <div <?php echo acf_esc_atts($wrapper); ?>>
            <?php $this->textarea->render_field($field); ?>
        </div>
        <?php
        
    }
    
    
    /**
     * validate_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     *
     * @return mixed
     */
    function validate_value($valid, $value, $field, $input){
        return $this->textarea->validate_value($valid, $value, $field, $input);
    }
    
    
    /**
     * format_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return string
     */
    function format_value($value, $post_id, $field){
        
        // force array
        $field['return_format'] = acf_get_array($field['return_format']);
        
        // htmlentities
        if(in_array('htmlentities', $field['return_format'])){
            $value = htmlentities($value);
        }
        
        // nl2br
        if(in_array('nl2br', $field['return_format'])){
            $value = nl2br($value);
        }
        
        // return
        return $value;
        
    }

}

// initialize
acf_register_field_type('acfe_field_code_editor');

endif;