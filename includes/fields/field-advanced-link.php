<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_advanced_link')):

class acfe_field_advanced_link extends acf_field{
    
    function __construct(){

        $this->name = 'acfe_advanced_link';
        $this->label = __('Advanced Link', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'post_type' => array(),
			'taxonomy'  => array(),
        );

        parent::__construct();

    }
    
    function render_field_settings($field){
        
        // default_value
		acf_render_field_setting($field, array(
			'label'			=> __('Filter by Post Type','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'post_type',
			'choices'		=> acf_get_pretty_post_types(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All post types",'acf'),
		));
        
		// default_value
		acf_render_field_setting($field, array(
			'label'			=> __('Filter by Taxonomy','acf'),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'taxonomy',
			'choices'		=> acf_get_taxonomy_terms(),
			'multiple'		=> 1,
			'ui'			=> 1,
			'allow_null'	=> 1,
			'placeholder'	=> __("All taxonomies",'acf'),
		));
        
        $field_name = 'field_name';
        if(acf_maybe_get($field, 'name'))
            $field_name = $field['name'];
        
        ob_start();
        ?>
        Add your own fields using the following hook:<br /><br />
<pre>
add_filter('acfe/fields/advanced_link/fields/name=<?php echo $field_name; ?>', 'my_acf_advanced_link_fields', 10, 3);
function my_acf_advanced_link_fields($fields, $field, $value){
    
    /**
     * @array $fields   Sub fields array
     * @array $field    Advanced Link field
     * @array $value    The field values
     */
    
    $fields[] = array(
        'prefix'    => $field['name'],
        'name'      => 'my_field',
        'key'       => 'acfe_advanced_link_my_field',
        'label'     => 'My field',
        'type'      => 'true_false',
        'ui'        => true,
        'value'     => isset($value['my_field']) ? $value['my_field'] : ''
    );
    
    return $fields;
    
}
</pre>
        <?php
        
        $message = ob_get_clean();
        
        // field_type
        acf_render_field_setting($field, array(
            'label'			=> __('Instructions','acf'),
            'instructions'	=> '',
            'type'			=> 'message',
            'name'			=> 'instructions',
            'message'       => $message,
            'new_lines'     => false
        ));
        
    }
    
    function get_link($value = ''){
		
		// vars
        $value = wp_parse_args($value, array(
            'post'      => '',
			'type'      => 'url',
            'url'       => '',
			'title'     => '',
			'target'    => false,
		));
        
		$link = array(
            'type'      => 'url',
            'url'       => false,
            'post'      => '',
            'title'     => '',
            'target'    => false,
		);
        
        
        $link['type'] = $value['type'];
        $link['title'] = $value['title'];
        if($value['target'])
            $link['target'] = '_blank';
        
        // URL
        if($value['type'] === 'url'){
            
            $link['url'] = $value['url'];
            
        // Post
        }elseif($value['type'] === 'post'){
            
            $link['post'] = $value['post'];
            
            if(!empty($value['post'])){
                
                $link['url'] = get_permalink($value['post']);
                
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
                'key'		=> 'type',
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
                'key'		=> 'url',
                'label'		=> __('URL', 'acf'),
                'type'		=> 'text',
                'value'		=> $link['url'],
                'required'	=> false,
                'class'     => 'input-url',
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'type',
                            'operator'  => '==',
                            'value'     => 'url',
                        )
                    )
                )
                
            ),
            
            array(
                'prefix'        => $field['name'],
                'name'          => 'post',
                'key'           => 'post',
                'label'         => __('Post', 'acf'),
                'type'          => 'post_object',
                'value'         => $link['post'],
                'required'      => false,
                'class'         => 'input-post',
                'allow_null'    => 1,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'type',
                            'operator'  => '==',
                            'value'     => 'post',
                        )
                    )
                )
            ),
            
            array(
                'prefix'	=> $field['name'],
                'name'		=> 'title',
                'key'		=> 'title',
                'label'		=> __('Link text', 'acf'),
                'type'		=> 'text',
                'value'		=> $link['title'],
                'required'	=> false,
                'class'     => 'input-title',
            ),
            
            array(
                'prefix'	=> $field['name'],
                'name'		=> 'target',
                'key'		=> 'target',
                'label'		=> __('Target', 'acf'),
                'type'		=> 'true_false',
                'value'		=> $link['target'],
                'message'   => __('Open in an new window', 'acf'),
                'required'	=> false,
                'class'     => 'input-target',
            ),
            
        );
        
        $fields = apply_filters('acfe/fields/advanced_link/fields', $fields, $field, $link);
        $fields = apply_filters('acfe/fields/advanced_link/fields/name=' . $field['_name'], $fields, $field, $link);
        $fields = apply_filters('acfe/fields/advanced_link/fields/key=' . $field['key'], $fields, $field, $link);
		
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
		if(empty($value) || (empty($value['url'] && empty($value['post']))))
			return false;
        
		// return
		return $valid;
		
	}
    
}

// initialize
acf_register_field_type('acfe_field_advanced_link');

endif;