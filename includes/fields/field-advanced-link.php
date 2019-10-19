<?php

if(!defined('ABSPATH'))
    exit;

class acfe_field_advanced_link extends acf_field{
    
    function __construct(){

        $this->name = 'acfe_advanced_link';
        $this->label = __('Advanced Link', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            
        );
        
        acf_add_local_field(array(
            'key'       => 'acfe_advanced_link_post',
            'label'     => __('Post', 'acf'),
            'type'      => 'post_object',
            'required'  => false,
            'post_type' => false,
            'taxonomy'  => false,
        ));

        parent::__construct();

    }
    
    function get_link($value = ''){
		
		// vars
        $value = wp_parse_args($value, array(
			'acfe_advanced_link_type'   => 'url',
            'acfe_advanced_link_url'    => '',
            'acfe_advanced_link_post'   => '',
			'acfe_advanced_link_title'  => '',
			'acfe_advanced_link_target' => false,
		));
        
		$link = array(
            'type'      => 'url',
            'url'       => false,
            'post'      => '',
            'title'     => '',
            'target'    => false,
		);
        
        
        $link['type'] = $value['acfe_advanced_link_type'];
        $link['title'] = $value['acfe_advanced_link_title'];
        if($value['acfe_advanced_link_target'])
            $link['target'] = '_blank';
        
        // URL
        if($value['acfe_advanced_link_type'] === 'url'){
            
            $link['url'] = $value['acfe_advanced_link_url'];
            
        // Post
        }elseif($value['acfe_advanced_link_type'] === 'post'){
            
            $link['post'] = $value['acfe_advanced_link_post'];
            
            if(!empty($value['acfe_advanced_link_post'])){
                
                $link['url'] = get_permalink($value['acfe_advanced_link_post']);
                
            }
            
        }
        
		// return
		return $link;
		
	}
    
    function render_field($field){
        
        // vars
		$div = array(
			'id'	=> $field['id'],
			'class'	=> $field['class'] . ' acf-link',
		);
		
		// get link
		$link = $this->get_link($field['value']);
		
		// classes
		if($link['url'])
			$div['class'] .= ' -value';
		
		if($link['target'] === '_blank')
			$div['class'] .= ' -external';
        
        $link['url_title'] = '';
        
        // URL
        if($link['type'] === 'url'){
            
            $link['url_title'] = $link['url'];
            
        // Post
        }elseif($link['type'] === 'post'){
            
            if(!empty($link['post'])){
                
                $link['url_title'] = get_the_title($link['post']);
                
            }
            
        }
        
        $fields = array(
        
            array(
                'prefix'	=> $field['name'],
                'name'		=> 'type',
                'key'		=> 'acfe_advanced_link_type',
                'label'		=> __('Type', 'acf'),
                'type'		=> 'radio',
                'value'		=> $link['type'],
                'required'	=> false,
                'class'     => 'input-type',
                'choices'   => array(
                    'url'   => __('URL', 'acf'),
                    'post'  => __('Post', 'acf'),
                ),
            ),
            
            array(
                'prefix'	=> $field['name'],
                'name'		=> 'url',
                'key'		=> 'acfe_advanced_link_url',
                'label'		=> __('URL', 'acf'),
                'type'		=> 'text',
                'value'		=> $link['url'],
                'required'	=> false,
                'class'     => 'input-url',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'acfe_advanced_link_type',
                            'operator'  => '==',
                            'value'     => 'url',
                        )
                    )
                )
                
            ),
            
            array(
                'prefix'	=> $field['name'],
                'name'		=> 'post',
                'key'		=> 'acfe_advanced_link_post',
                'label'		=> __('Post', 'acf'),
                'type'		=> 'post_object',
                'value'		=> $link['post'],
                'required'	=> false,
                'class'     => 'input-post',
                'allow_null' => 1,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'acfe_advanced_link_type',
                            'operator'  => '==',
                            'value'     => 'post',
                        )
                    )
                )
            ),
            
            array(
                'prefix'	=> $field['name'],
                'name'		=> 'title',
                'key'		=> 'acfe_advanced_link_title',
                'label'		=> __('Link text', 'acf'),
                'type'		=> 'text',
                'value'		=> $link['title'],
                'required'	=> false,
                'class'     => 'input-title',
            ),
            
            array(
                'prefix'	=> $field['name'],
                'name'		=> 'target',
                'key'		=> 'acfe_advanced_link_target',
                'label'		=> __('Target', 'acf'),
                'type'		=> 'true_false',
                'value'		=> $link['target'],
                'message'   => __('Open in an new window', 'acf'),
                'required'	=> false,
                'class'     => 'input-target',
            ),
            
        );
        
        $fields = apply_filters('acfe/fields/advanced_link/fields', $fields, $field, $link);
		
        ?>
        
        <div <?php acf_esc_attr_e($div); ?>>
        
            <div class="acfe-modal" data-modal-title="<?php echo $field['label']; ?>">
                <div class="acfe-modal-wrapper">
                    <div class="acfe-modal-content">
                    
                    <div class="acf-fields acf-form-fields -left">
                    
                        <?php acf_render_fields($fields, false, 'div', 'label'); ?>
                        
                    </div>
                        
                    </div>
                </div>
            </div>
            
            <a href="#" class="button" data-name="add" target=""><?php _e('Select Link', 'acf'); ?></a>
            
            <div class="link-wrap">
                <span class="link-title"><?php echo esc_html($link['title']); ?></span>
                <a class="link-url" href="<?php echo esc_url($link['url']); ?>" target="_blank"><?php echo esc_html($link['url_title']); ?></a>
                <i class="acf-icon -link-ext acf-js-tooltip" title="<?php _e('Opens in a new window/tab', 'acf'); ?>"></i><?php
                ?><a class="acf-icon -pencil -clear acf-js-tooltip" data-name="edit" href="#" title="<?php _e('Edit', 'acf'); ?>"></a><?php
                ?><a class="acf-icon -cancel -clear acf-js-tooltip" data-name="remove" href="#" title="<?php _e('Remove', 'acf'); ?>"></a>
            </div>
            
        </div>
        <?php
        
    }
    
    function format_value($value, $post_id, $field){
		
		// bail early if no value
		if(empty($value))
            return $value;
		
		// get link
		$link = $this->get_link($value);

		// return link
		return $link;
		
	}
    
	function validate_value($valid, $value, $field, $input){
		
		// bail early if not required
		if(!$field['required'])
            return $valid;
		
		// URL is required
		if(empty($value) || (empty($value['acfe_advanced_link_url'] && empty($value['acfe_advanced_link_post']))))
			return false;
        
		// return
		return $valid;
		
	}
    
}

new acfe_field_advanced_link();