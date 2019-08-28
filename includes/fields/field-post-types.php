<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_post_types extends acf_field{
    
    function __construct(){
        $this->name = 'acfe_post_types';
        $this->label = __('Post types', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'field_type'    => 'checkbox',
            'return_format' => 'name',
        );
        
        parent::__construct();
    }

    function render_field($field){
        
        // force value to array
        $field['value'] = acf_get_array($field['value']);
        
        if($field['field_type'] === 'select'){
            
            $this->render_field_select($field);
        
        }
        
        elseif($field['field_type'] === 'radio'){
            
            $this->render_field_checkbox($field);
            
        }
        
        elseif($field['field_type'] === 'checkbox'){
        
            $this->render_field_checkbox($field);
            
        }
        
    }
    
    function render_field_select($field){
        
        // Change Field into a select
        $field['type'] = 'select';
        $field['ui'] = 0;
        $field['ajax'] = 0;
        $field['allow_null'] = 0;
        $field['multiple'] = 0;
        $field['choices'] = get_post_types(array(
            'public' => true, 
            'show_ui' => true
        ), 'names');
        
        acf_render_field($field);
        
    }
    
    function render_field_checkbox($field){
        
        acf_hidden_input(array(
            'type'	=> 'hidden',
            'name'	=> $field['name'],
        ));
        
        if($field['field_type'] === 'checkbox')
            $field['name'] .= '[]';
        
        $taxonomies = get_post_types(array(
            'public' => true, 
            'show_ui' => true
        ), 'objects');
        
        ?>
        <div class="categorychecklist-holder">
            <ul class="acf-checkbox-list acf-bl">
            
                <?php if(!empty($taxonomies)){ ?>
                    <?php foreach($taxonomies as $taxonomy){ ?>
                        <?php $selected = in_array($taxonomy->name, $field['value']); ?>
                        <li>
                            <label <?php echo $selected ? 'class="selected"' : ''; ?>>
                                <input type="<?php echo $field['field_type']; ?>" name="<?php echo $field['name']; ?>" value="<?php echo $taxonomy->name; ?>" <?php echo $selected ? 'checked="checked"' : ''; ?> /> 
                                <span><?php echo $taxonomy->label; ?></span>
                            </label>
                        </li>
                        
                    <?php } ?>
                <?php } ?>
                
            </ul>
        </div>
        <?php
        
    }
    
    function render_field_settings($field){
        
        // field_type
        acf_render_field_setting( $field, array(
            'label'			=> __('Appearance','acf'),
            'instructions'	=> __('Select the appearance of this field', 'acf'),
            'type'			=> 'select',
            'name'			=> 'field_type',
            'optgroup'		=> true,
            'choices'		=> array(
                'checkbox'  => __('Checkbox', 'acf'),
                'radio'     => __('Radio Buttons', 'acf'),
                'select'    => _x('Select', 'noun', 'acf')
            )
        ));
        
        // return_format
        acf_render_field_setting( $field, array(
            'label'			=> __('Return Value', 'acf'),
            'instructions'	=> '',
            'type'			=> 'radio',
            'name'			=> 'return_format',
            'choices'		=> array(
                'object'    =>	__("Post Type Object", 'acfe'),
                'name'      =>	__("Post Type Name", 'acfe')
            ),
            'layout'	=>	'horizontal',
        ));
        
    }
    
    function format_value($value, $post_id, $field){
        
        if(empty($value))
            return false;
        
        // force value to array
        $value = acf_get_array($value);
        
        // format = name
        if($field['return_format'] === 'name'){
            
            if($field['field_type'] === 'select' || $field['field_type'] === 'radio')
                return array_shift($value);
            
            return $value;
            
        }
        
        // format = object
        elseif($field['return_format'] === 'object'){
            
            $post_types = array();
            
            foreach($value as $post_type){
                $post_types[] = get_post_type_object($post_type);
            }
            
            if($field['field_type'] === 'select' || $field['field_type'] === 'radio')
                return array_shift($post_types);
            
            return $post_types;
            
        }
        
        // return
        return $value;
        
    }

}

new acfe_field_post_types();