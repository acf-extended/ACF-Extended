<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/single_meta'))
    return;

// Register notices store.
acf_register_store('acfe/meta')->prop('multisite', true);

if(!class_exists('acfe_single_meta')):

class acfe_single_meta{
    
    public $data = array();
    public $restricted = array();
    public $post_types = array();
    public $taxonomies = array();
    
	function __construct(){
        
        $this->restricted = array('acf-field-group', 'acf-field', 'attachment', 'acfe-dbt', 'acfe-dop', 'acfe-dpt', 'acfe-dt', 'acfe-form');
        
        $this->post_types = apply_filters('acfe/single_meta/post_types', array());
        $this->taxonomies = apply_filters('acfe/single_meta/taxonomies', array());
        
        // Values
        add_filter('acf/pre_load_metadata',     array($this, 'load_metadata'), 		10, 4);
        add_filter('acf/pre_update_metadata',   array($this, 'update_metadata'),   999, 5);
		add_filter('acf/pre_delete_metadata',   array($this, 'delete_metadata'),   999, 4);
		
		// Save Post
		add_action('acf/save_post',             array($this, 'save_post'),      999);
        
        // Settings
        add_action('acf/render_field_settings', array($this, 'field_setting'));
        
        // Post
        add_action('load-post.php',         	array($this, 'load_post'));
		add_action('load-post-new.php',     	array($this, 'load_post'));
        
        // Term
        add_action('load-edit-tags.php',    	array($this, 'load_term'));
        add_action('load-term.php',         	array($this, 'load_term'));
        
        // User
        add_action('load-user-new.php',     	array($this, 'load_user'));
        add_action('load-user-edit.php',    	array($this, 'load_user'));
        add_action('load-profile.php',      	array($this, 'load_user'));
        
	}
	
	/*
     * Pre Load Value
     */
	function load_metadata($return, $post_id, $name, $hidden){
		
		if($name === 'acf')
			return $return;
		
		// Validate Post ID
		$validate = $this->validate_post_id($post_id);
		
		if(!$validate)
			return $return;
		
		// Check store.
		$store = acf_get_store('acfe/meta');
		
		// Store found
		if($store->has("$post_id:acf")){
			
			// Get Store: ACF meta
			$acf = $store->get("$post_id:acf");
			
		// Store not found
		}else{
			
			// Get ACF meta
			$acf = acf_get_metadata($post_id, 'acf');
			
			// Set Store: ACF meta
			$store->set("$post_id:acf", $acf);
			
		}
		
		if(empty($acf))
			return $return;
		
		$prefix = $hidden ? '_' : '';
		
		if(isset($acf["{$prefix}{$name}"])){
			
			$return = $acf["{$prefix}{$name}"];
			
		}
		
		return $return;
		
	}
    
    /*
     * Update Value
     */
    function update_metadata($return, $post_id, $name, $value, $hidden){
	
		if($name === 'acf')
			return $return;
        
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
        
        if(!$validate)
            return $return;
        
        $is_save_post = false;
        
        // Submitting acf/save_post
        if(acf_maybe_get_POST('acf'))
            $is_save_post = true;
        
        // Get store
        $store = acf_get_store('acfe/meta');
        
        // Store found
        if($store->has("$post_id:acf")){
            
            // Get Store: ACF meta
            $acf = $store->get("$post_id:acf");
        
        // Store not found
        }else{
            
			// Get ACF meta
			$acf = acf_get_metadata($post_id, 'acf');
            
            // Set Store: ACF meta
            $store->set("$post_id:acf", $acf);
            
        }
	
		$prefix = $hidden ? '_' : '';
		
        $acf["{$prefix}{$name}"] = $value;
	
		// Set Store: ACF meta
		$store->set("$post_id:acf", $acf);
        
        // Single field update: Save to ACF meta
        if(!$is_save_post){
	
			acf_update_metadata($post_id, 'acf', $acf);
        
        }
        
        // Field Setting: Save individually
        /*
		if(acf_maybe_get($field, 'acfe_save_meta'))
            return $return;
        */
        
        // Do not save as individual meta
        return $value;
        
    }
    
    function delete_metadata($return, $post_id, $name, $hidden){
	
		if($name === 'acf')
			return $return;
	
		// Validate Post ID
		$validate = $this->validate_post_id($post_id);
	
		if(!$validate)
			return $return;
	
		// Check store.
		$store = acf_get_store('acfe/meta');
	
		// Store found
		if($store->has("$post_id:acf")){
		
			// Get Store: ACF meta
			$acf = $store->get("$post_id:acf");
		
			// Store not found
		}else{
		
			// Get ACF meta
			$acf = acf_get_metadata($post_id, 'acf');
		
			// Set Store: ACF meta
			$store->set("$post_id:acf", $acf);
		
		}
	
		if(empty($acf))
			return $return;
	
		$prefix = $hidden ? '_' : '';
	
		if(isset($acf["{$prefix}{$name}"])){
		
			unset($acf["{$prefix}{$name}"]);
			
			// Save to ACF meta
			acf_update_metadata($post_id, 'acf', $acf);
			
			// Set Store: ACF meta
			$store->set("$post_id:acf", $acf);
			
		
		}
	
		return $return;
    
    }

    /*
     * Delete orphan meta
     */
    function save_post($post_id = 0){
        
        // Validate Post ID
        $validate = $this->validate_post_id($post_id);
        
        if(!$validate)
            return;
        
        // Check store.
        $store = acf_get_store('acfe/meta');
        
        // Store found
        if(!$store->has("$post_id:acf"))
            return;
        
        // Get Store: ACF meta
        $acf = $store->get("$post_id:acf");
        
        // Save to ACF meta
        acf_update_metadata($post_id, 'acf', $acf);
        
        if(acf_maybe_get_POST('acfe_clean_meta')){
            
            $meta = acf_get_meta($post_id);
            
            if(empty($meta))
                return;
            
            foreach($meta as $key => $value){
                
                // bail if reference key does not exist
                if(!isset($meta["_$key"]))
                    continue;
                
                if(isset($acf[$key]))
                    continue;
                
                acf_delete_metadata($post_id, $key);
                acf_delete_metadata($post_id, $key, true);
                
            }
        
        }
        
    }
	
	function validate_post_id($post_id){
		
		// Type + ID
		extract(acf_decode_post_id($post_id));
		
		// Validate ID
		if(!$id)
			return false;
		
		// Exclude options
		if($type === 'option')
			return false;
		
		// Get store
		$store = acf_get_store('acfe/meta');
		
		// Restrict post type
		if($type === 'post'){
			
			$allowed = false;
			
			// Allowed found
			if($store->has("$post_id:allowed")){
				
				// Get Store: Allowed
				$allowed = $store->get("$post_id:allowed");
				
				// Allowed not found
			}else{
				
				$post_type = get_post_type($id);
				
				if(!in_array($post_type, $this->restricted)){
					
					$allowed = true;
					
				}
				
				$store->set("$post_id:allowed", $allowed);
				
			}
			
			if(!$allowed)
				return false;
			
		}
		
		return true;
		
	}
    
    /*
     * Field Setting
     */
    function field_setting($field){
        
        // Settings
        acf_render_field_setting($field, array(
            'label'             => __('Save as meta'),
            'key'               => 'acfe_save_meta',
            'name'              => 'acfe_save_meta',
            'instructions'      => __('Save the field an individual meta (useful for WP_Query).'),
            'type'              => 'true_false',
            'required'          => false,
            'conditional_logic' => false,
            'wrapper' => array(
                'width' => '',
                'class' => '',
                'id'    => '',
            ),
            'message'           => '',
            'default_value'     => false,
            'ui'                => true,
            'ui_on_text'        => '',
            'ui_off_text'       => '',
        ));
        
    }

    function load_post(){
        
        // globals
		global $typenow;
		
		// restrict specific post types
		$restricted = array('acf-field-group', 'attachment', 'acfe-dbt', 'acfe-dop', 'acfe-dpt', 'acfe-dt', 'acfe-form');
        
		if(in_array($typenow, $restricted))
			return;
        
        // actions
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
        
    }
    
    function add_meta_boxes($post_type, $post){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), $post_type, 'side', 'core');
        
    }
    
    function load_term(){
        
        $screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
        
        // actions
        add_action("{$taxonomy}_edit_form", array($this, 'edit_term'), 20, 2);
        
    }
    
    function edit_term($term, $taxonomy){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), 'edit-' . $term->taxonomy, 'side', 'core');
        
    }
    
    function load_user(){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), 'edit-user', 'normal', 'default');
        
    }

    function render_metabox($post, $metabox){
        
        $field = array(
            'key' => false,
			'label' => false,
			'name' => 'acfe_clean_meta',
			'prefix' => false,
			'type' => 'true_false',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'message' => 'Clean orphan meta data',
			'default_value' => 0,
			'ui' => 0,
			'ui_on_text' => '',
			'ui_off_text' => '',
        );
        
        acf_render_field_wrap($field);
        
        ?>
        <script type="text/javascript">
        if( typeof acf !== 'undefined' ) {
                
            acf.newPostbox({
                'id': 'acfe-clean-meta',
                'label': 'top'
            });	

        }
        </script>
        <?php
        
    }
    
}

new acfe_single_meta();

endif;