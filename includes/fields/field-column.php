<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_column')):

class acfe_field_column extends acfe_field{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'acfe_column';
        $this->label = __('Column', 'acfe');
        $this->category = 'layout';
        $this->defaults = array(
            'columns'       => '6/12',
            'endpoint'      => false,
        );
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // columns
        acf_render_field_setting($field, array(
            'label'         => __('Columns', 'acfe'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'columns',
            'choices'       => array(
                '1/12' => '1/12',
                '2/12' => '2/12',
                '3/12' => '3/12',
                '4/12' => '4/12',
                '5/12' => '5/12',
                '6/12' => '6/12',
                '7/12' => '7/12',
                '8/12' => '8/12',
                '9/12' => '9/12',
                '10/12' => '10/12',
                '11/12' => '11/12',
                '12/12' => '12/12',
            ),
            'class' => 'acfe-field-columns',
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
        acf_render_field_setting($field, array(
            'label'         => __('Endpoint','acf'),
            'instructions'  => __('Define an endpoint for the previous columns to stop.', 'acf'),
            'name'          => 'endpoint',
            'type'          => 'true_false',
            'ui'            => 1,
            'class'         => 'acfe-field-columns-endpoint',
        ));
        
    }
    
    
    /**
     * load_field
     *
     * @param $field
     *
     * @return mixed
     */
    function load_field($field){
        
        $columns = '';
        
        if($field['columns']){
            $columns = ucfirst($field['columns']);
        }
        
        if($field['endpoint']){
            $columns = 'Endpoint';
        }
        
        $field['label'] = '(Column ' . $columns . ')';
        $field['name'] = '';
        $field['instructions'] = '';
        $field['required'] = 0;
        $field['value'] = false;
        
        return $field;
        
    }
    
    
    /**
     * prepare_field
     *
     * @param $field
     *
     * @return false
     */
    function prepare_field($field){
    
        global $pagenow;
        
        // do not render on User/Term views without Enhanced UI module (because of Table render)
        if((acf_is_screen(array('profile', 'user-edit')) || (acf_is_screen('user') && !is_multisite()) || $pagenow === 'term.php') && !acf_get_setting('acfe/modules/ui')){
            return false;
        }
        
        // do not render on New Term page (forced to left)
        if($pagenow === 'edit-tags.php'){
            return false;
        }
        
        // hide label
        $field['label'] = false;
        
        // return
        return $field;
        
    }
    
    
    /**
     * field_wrapper_attributes
     *
     * @param $wrapper
     * @param $field
     *
     * @return mixed
     */
    function field_wrapper_attributes($wrapper, $field){
        
        if($field['endpoint']){
            $wrapper['data-endpoint'] = $field['endpoint'];
            
        }elseif($field['columns']){
            $wrapper['data-columns'] = $field['columns'];
        }
        
        return $wrapper;
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
        
        // vars
        $atts = array(
            'class' => 'acf-fields',
        );
        
        ?>
        <div <?php echo acf_esc_atts($atts); ?>></div>
        <?php
        
    }

}

// initialize
acf_register_field_type('acfe_field_column');

endif;