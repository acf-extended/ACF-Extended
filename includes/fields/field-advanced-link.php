<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_field_advanced_link')):

class acfe_field_advanced_link extends acf_field{
    
    public $post_object = '';
    
    function __construct(){

        $this->name = 'acfe_advanced_link';
        $this->label = __('Advanced Link', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'post_type' => array(),
			'taxonomy'  => array(),
        );
        
        add_action('wp_ajax_acfe/fields/advanced_link/post_query',			array($this, 'ajax_query'));
		add_action('wp_ajax_nopriv_acfe/fields/advanced_link/post_query',	array($this, 'ajax_query'));
        
        $this->post_object = acf_get_field_type('post_object');
        remove_action('acf/render_field/type=post_object',                  array($this->post_object, 'render_field'), 9);
        
        add_action('acf/render_field/type=post_object',                     array($this, 'post_object_render_field'), 9);

        parent::__construct();

    }
    
    function post_object_render_field($field){
		
		// Change Field into a select
		$field['type'] = 'select';
		$field['ui'] = 1;
		$field['ajax'] = 1;
		$field['choices'] = array();
		
		// load posts
		$posts = $this->post_object->get_posts( $field['value'], $field );
		
		if($posts){
				
			foreach( array_keys($posts) as $i ) {
				
				// vars
				$post = acf_extract_var( $posts, $i );
				
				
				// append to choices
				$field['choices'][ $post->ID ] = $this->post_object->get_post_title( $post, $field );
				
			}
			
		}
        
        if(!is_array($field['value']) && !is_numeric($field['value'])){
            
            $post_type = $field['value'];
            $post_type_label = acf_get_post_type_label($post_type);
            
            $field['choices'][$field['value']] = $post_type_label . ' Archive';
            
        }
		
		// render
		acf_render_field( $field );
		
	}
    
    function ajax_query(){
        
        // validate
		if(!acf_verify_ajax())
            die();
		
		// get choices
		$response = $this->post_object->get_ajax_query($_POST);
        
        $options = acf_parse_args($_POST, array(
			'post_id'		=> 0,
			's'				=> '',
			'field_key'		=> '',
			'paged'			=> 1
		));
        
        $field = acf_get_field($options['field_key']);
		if(!$field)
            return false;
        
        if($options['paged'] > 1)
            acf_send_ajax_results($response);
        
        // init archives
        $s = false;
        $is_search = false;
        
        if($options['s'] !== ''){
            
			$s = wp_unslash(strval($options['s']));
			$is_search = true;
			
		}
        
        if(!empty($field['post_type'])){
		
			$post_types = acf_get_array($field['post_type']);
			
		}else{
			
			$post_types = acf_get_post_types();
			
		}
        
        $post_types_archives = array();
        
        foreach($post_types as $post_type){
            
            $post_type_obj = get_post_type_object($post_type);
            
            $has_archive = false;
            
            if($post_type === 'post' || $post_type_obj->has_archive){
                
                $has_archive = true;
                
            }
            
            if(!$has_archive)
                continue;
            
            $post_type_label = acf_get_post_type_label($post_type);
            
            if($is_search && stripos($post_type_label, $s) === false)
                continue;
            
            $post_types_archives[] = array(
                'id' => $post_type,
                'text' => $post_type_label . ' Archive'
            );
            
        }
        
        if(!empty($post_types_archives)){
            
            if(!isset($response['results'])){
                
                $response['results'] = array();
                
            }
        
            array_unshift($response['results'], array(
                'text' => 'Archives',
                'children' => $post_types_archives
            ));
        
        }
		
		// return
		acf_send_ajax_results($response);
        
    }
    
    function render_field_settings($field){
        
        // Filter Post Type
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
        
		// Filter Taxonomy
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
        Add your own sub fields using the following hook:<br /><br />
<pre>
add_filter('acfe/fields/advanced_link/sub_fields/name=<?php echo $field_name; ?>', 'my_acf_advanced_link_sub_fields', 10, 3);
function my_acf_advanced_link_sub_fields($sub_fields, $field, $value){
    
    /**
     * @array $sub_fields   Sub fields array
     * @array $field        Advanced Link field
     * @array $value        Advanced Link values
     */
    
    $sub_fields[] = array(
        'name'      => 'my_field',
        'label'     => 'My field',
        'type'      => 'true_false',
        'ui'        => true
    );
    
    return $sub_fields;
    
}
</pre>
        <?php
        
        $message = ob_get_clean();
        
        // field_type
        acf_render_field_setting($field, array(
            'label'			=> __('Custom sub fields','acf'),
            'instructions'	=> '',
            'type'			=> 'message',
            'name'			=> 'instructions',
            'message'       => $message,
            'new_lines'     => false
        ));
        
    }
    
    function get_value($value = array()){
		
		// vars
        $value = wp_parse_args($value, array(
            'type'      => 'url',
            'post'      => '',
            'term'      => '',
			'title'     => '',
            'url'       => false,
            'url_title' => '',
			'target'    => false,
		));
        
        $value['url_title'] = $value['url'];
        
        // Post
        if($value['type'] === 'post' && !empty($value['post'])){
            
            if(is_numeric($value['post'])){
                
                $value['url'] = get_permalink($value['post']);
                $value['url_title'] = get_the_title($value['post']);
                
            }else{
                
                $post_type = $value['post'];
                
                $value['url'] = get_post_type_archive_link($post_type);
                $value['url_title'] = acf_get_post_type_label($post_type) . ' Archive';
                
            }
        
        // Term
        }elseif($value['type'] === 'term' && !empty($value['term'])){
            
            $term = get_term(intval($value['term']));
            
            $value['url'] = get_term_link($term);
            $value['url_title'] = $term->name;
            
        }
        
        // Target
        if(!empty($value['target'])){
            
            $value['target'] = '_blank';
            
        }
        
		// return
		return $value;
		
	}
    
    function render_field($field){
        
        // vars
		$div = array(
			'id'	=> $field['id'],
			'class'	=> $field['class'] . ' acf-link',
		);
		
		// get link
		$value = $this->get_value($field['value']);
		
		// classes
		if($value['url']){
            
            $div['class'] .= ' -value';
            
        }
		
		if($value['target'] === '_blank'){
            
            $div['class'] .= ' -external';
            
        }
        
        $sub_fields = array(
        
            array(
                'name'		=> 'type',
                'key'		=> 'type',
                'label'		=> __('Type', 'acf'),
                'type'		=> 'radio',
                'required'	=> false,
                'class'     => 'input-type',
                'choices'   => array(
                    'url'   => __('URL', 'acf'),
                    'post'  => __('Post', 'acf'),
                    'term'  => __('Term', 'acf'),
                ),
            ),
            
            array(
                'name'		=> 'url',
                'key'		=> 'url',
                'label'		=> __('URL', 'acf'),
                'type'		=> 'text',
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
                'name'          => 'post',
                'key'           => 'post',
                'label'         => __('Post', 'acf'),
                'type'          => 'post_object',
                'required'      => false,
                'class'         => 'input-post',
                'allow_null'    => 0,
                'ajax_action'   => 'acfe/fields/advanced_link/post_query',
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
                'name'          => 'term',
                'key'           => 'term',
                'label'         => __('Term', 'acf'),
                'type'          => 'acfe_taxonomy_terms',
                'required'      => false,
                'class'         => 'input-term',
                'allow_null'    => 1,
                'field_type'    => 'select',
                'return_format' => 'id',
                'ui'            => 1,
                'allow_null'    => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field'     => 'type',
                            'operator'  => '==',
                            'value'     => 'term',
                        )
                    )
                )
            ),
            
            array(
                'name'		=> 'title',
                'key'		=> 'title',
                'label'		=> __('Link text', 'acf'),
                'type'		=> 'text',
                'required'	=> false,
                'class'     => 'input-title',
            ),
            
            array(
                'name'		=> 'target',
                'key'		=> 'target',
                'label'		=> __('Target', 'acf'),
                'type'		=> 'true_false',
                'message'   => __('Open in an new window', 'acf'),
                'required'	=> false,
                'class'     => 'input-target',
            ),
            
        );
        
        // Deprecated
        $sub_fields = apply_filters('acfe/fields/advanced_link/fields',                         $sub_fields, $field, $value);
        $sub_fields = apply_filters('acfe/fields/advanced_link/fields/name=' . $field['_name'], $sub_fields, $field, $value);
        $sub_fields = apply_filters('acfe/fields/advanced_link/fields/key=' . $field['key'],    $sub_fields, $field, $value);
        
        // Sub Fields Fitlers
        $sub_fields = apply_filters('acfe/fields/advanced_link/sub_fields',                         $sub_fields, $field, $value);
        $sub_fields = apply_filters('acfe/fields/advanced_link/sub_fields/name=' . $field['_name'], $sub_fields, $field, $value);
        $sub_fields = apply_filters('acfe/fields/advanced_link/sub_fields/key=' . $field['key'],    $sub_fields, $field, $value);
        
        foreach($sub_fields as &$sub_field){
            
            $sub_field['prefix'] = $field['name'];
            
            $sub_field['value'] = isset($value[$sub_field['name']]) ? $value[$sub_field['name']] : '';
            
            $sub_field = acf_validate_field($sub_field);
            
            $sub_field = acf_prepare_field($sub_field);
            
        }
		
        ?>
        
        <div <?php acf_esc_attr_e($div); ?>>
        
            <div class="acfe-modal" data-modal-title="<?php echo $field['label']; ?>">
                <div class="acfe-modal-wrapper">
                    <div class="acfe-modal-content">
                    
                    <div class="acf-fields acf-form-fields -left">
                    
                        <?php acf_render_fields($sub_fields, false, 'div', 'label'); ?>
                        
                    </div>
                        
                    </div>
                </div>
            </div>
            
            <a href="#" class="button" data-name="add" target=""><?php _e('Select Link', 'acf'); ?></a>
            
            <div class="link-wrap">
                <span class="link-title"><?php echo esc_html($value['title']); ?></span>
                <a class="link-url" href="<?php echo esc_url($value['url']); ?>" target="_blank"><?php echo esc_html($value['url_title']); ?></a>
                <i class="acf-icon -link-ext acf-js-tooltip" title="<?php _e('Opens in a new window/tab', 'acf'); ?>"></i><?php
                ?><a class="acf-icon -pencil -clear acf-js-tooltip" data-name="edit" href="#" title="<?php _e('Edit', 'acf'); ?>"></a><?php
                ?><a class="acf-icon -cancel -clear acf-js-tooltip" data-name="remove" href="#" title="<?php _e('Remove', 'acf'); ?>"></a>
            </div>
            
        </div>
        <?php
        
    }
    
    function format_value($value, $post_id, $field){
		
		// get value
		$value = $this->get_value($value);
        
        // clean
        unset($value['type']);
        unset($value['post']);
        unset($value['term']);
        unset($value['url_title']);

		return $value;
		
	}
    
	function validate_value($valid, $value, $field, $input){
		
		// bail early if not required
		if(!$field['required'])
            return $valid;
        
        // URL is required
        if(empty($value))
            return false;
        
        if((acf_maybe_get($value, 'post') || acf_maybe_get($value, 'term')) && !acf_maybe_get($value, 'url'))
            return false;
        
		// return
		return $valid;
		
	}
    
}

// initialize
acf_register_field_type('acfe_field_advanced_link');

endif;