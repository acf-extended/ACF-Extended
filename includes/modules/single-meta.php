<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/single_meta'))
    return;

if(!class_exists('acfe_single_meta')):

class acfe_single_meta{
    
    public $data = array();
    
	function __construct(){
        
        // Load
        add_filter('acf/load_value',            array($this, 'load_value'),     0, 3);
        add_filter('acf/pre_load_metadata',     array($this, 'load_reference'), 10, 4);
        
        // Settings
        add_action('acf/render_field_settings', array($this, 'field_setting'));
        
        // Post
        add_action('load-post.php',         array($this, 'load_post'));
		add_action('load-post-new.php',     array($this, 'load_post'));
        
        // Term
        add_action('load-edit-tags.php',    array($this, 'load_term'));
        add_action('load-term.php',         array($this, 'load_term'));
        
        // User
        add_action('load-user-new.php',     array($this, 'load_user'));
        add_action('load-user-edit.php',    array($this, 'load_user'));
        add_action('load-profile.php',      array($this, 'load_user'));
        
	}
    
    /*
     * Save Single Meta
     */
    function save_value($value, $post_id, $field, $_value){
        
        // $type + $id
        extract(acf_decode_post_id($post_id));
        
        // Exclude option
        if($type === 'option')
            return $value;
        
        // Do not save empty values
        //if(empty($value) && !is_numeric($value) && $field['type'] !== 'flexible_content' && $field['type'] !== 'clone' && $field['type'] !== 'group')
        //    return null;
        
        $this->data['_' . $field['name']] = $field['key'];
        $this->data[$field['name']] = $value;
        
        // Save to ACF meta
        acf_update_metadata($post_id, 'acf', $this->data);
        
        // Field Setting: Save individually
        if(acf_maybe_get($field, 'acfe_save_meta'))
            return $value;
        
        // Do not save as individual meta
        return null;
        
    }

    /*
     * Load Single Meta
     */
    function load_value($value, $post_id, $field){
        
        // Value already exists
        if(!empty($value) || is_numeric($value))
            return $value;
        
        // $type + $id
        extract(acf_decode_post_id($post_id));
        
        // Exclude option
        if($type === 'option')
            return $value;
        
        // Get ACF meta
        $acf = acf_get_metadata($post_id, 'acf');
        
        // ACF meta not found
        if(empty($acf))
            return $value;
        
        $field_name = $field['name'];
        
        if(isset($acf[$field_name])){
            
            $value = $acf[$field_name];
            
        }
        
        return $value;
        
    }

    /*
     * Pre Load Get Field
     */
    function load_reference($null, $post_id, $name, $hidden){
        
        if(!$hidden)
            return $null;
        
        // $type + $id
        extract(acf_decode_post_id($post_id));
        
        // Hidden meta uses an underscore prefix.
        $prefix = $hidden ? '_' : '';
        
        // Bail early if no $id (possible during new acf_form).
        if(!$id){
            
            return $null;
            
        }
        
        // Check option.
        if($type === 'option'){
            
            return $null;
            
        // Check meta.
        } else {
            
            $acf = get_metadata($type, $id, 'acf', false);
            
            if(isset($acf[0])){
                
                $acf = $acf[0];
                
                if(isset($acf["{$prefix}{$name}"])){
                    
                    $null = $acf["{$prefix}{$name}"];
                    
                }
                
            }
            
            return $null;
            
        }
        
        return $null;
        
    }

    /*
     * Delete orphan meta
     */
    function save_post($post_id = 0){
        
        if(!acf_maybe_get_POST('acfe_clean_meta'))
            return;
        
        // $type + $id
        extract(acf_decode_post_id($post_id));
        
        // Exclude option
        if($type === 'option')
            return;
        
        $acf = acf_get_metadata($post_id, 'acf');
        if(empty($acf))
            return;
        
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
        add_filter('acf/update_value',          array($this, 'save_value'),     999, 4);
        add_action('acf/save_post',             array($this, 'save_post'),      999);
        
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2);
        
    }
    
    function add_meta_boxes($post_type, $post){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), $post_type, 'side', 'core');
        
    }
    
    function load_term(){
        
        $screen = get_current_screen();
		$taxonomy = $screen->taxonomy;
        
        // actions
        add_filter('acf/update_value',          array($this, 'save_value'),     999, 4);
        add_action('acf/save_post',             array($this, 'save_post'),      999);
        
        add_action("{$taxonomy}_edit_form", array($this, 'edit_term'), 20, 2);
        
    }
    
    function edit_term($term, $taxonomy){
        
        add_meta_box('acfe-clean-meta', 'ACF Single Meta', array($this, 'render_metabox'), 'edit-' . $term->taxonomy, 'side', 'core');
        
    }
    
    function load_user(){
        
        // actions
        add_filter('acf/update_value',          array($this, 'save_value'),     999, 4);
        add_action('acf/save_post',             array($this, 'save_post'),      999);
        
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