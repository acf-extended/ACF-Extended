<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_taxonomies'))
    return;

if(!class_exists('ACFE_Admin_Tool_Import_DT')):

class ACFE_Admin_Tool_Import_DT extends ACF_Admin_Tool{

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_dt_import';
        $this->title = __('Import Taxonomies');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        ?>
        <p><?php _e('Import Taxonomies', 'acf'); ?></p>
        
        <div class="acf-fields">
            <?php 
			
			acf_render_field_wrap(array(
				'label'		=> __('Select File', 'acf'),
				'type'		=> 'file',
				'name'		=> 'acf_import_file',
				'value'		=> false,
				'uploader'	=> 'basic',
			));
			
			?>
        </div>
        
        <p class="acf-submit">
            <button type="submit" name="action" class="button button-primary"><?php _e('Import File'); ?></button>
        </p>
        <?php
        
    }
    
    function submit(){
        
        // Check file size.
		if(empty($_FILES['acf_import_file']['size']))
			return acf_add_admin_notice(__("No file selected", 'acf'), 'warning');
		
		// Get file data.
		$file = $_FILES['acf_import_file'];
		
		// Check errors.
		if($file['error'])
			return acf_add_admin_notice(__("Error uploading file. Please try again", 'acf'), 'warning');
		
		// Check file type.
		if(pathinfo($file['name'], PATHINFO_EXTENSION) !== 'json')
			return acf_add_admin_notice(__("Incorrect file type", 'acf'), 'warning');
		
		// Read JSON.
		$json = file_get_contents($file['tmp_name']);
		$json = json_decode($json, true);
		
		// Check if empty.
    	if(!$json || !is_array($json))
    		return acf_add_admin_notice(__("Import file empty", 'acf'), 'warning');
    	
    	$ids = array();
        
        $dynamic_taxonomies = get_option('acfe_dynamic_taxonomies', array());
    	
    	// Loop over json
    	foreach($json as $taxonomy_name => $args){
            
            // Check if already exists
            if(isset($dynamic_taxonomies[$taxonomy_name])){
                
                acf_add_admin_notice(__("Taxonomy {$dynamic_taxonomies[$taxonomy_name]['label']} already exists. Import aborted."), 'warning');
                continue;
                
            }
            
            // Vars
            $title = $args['label'];
            $name = $taxonomy_name;
            
            // Insert post
            $post_id = wp_insert_post(array(
                'post_title'    => $title,
                'post_name'     => $name,
                'post_type'     => 'acfe-dt',
                'post_status'   => 'publish'
            ));
            
            // Insert error
            if(is_wp_error($post_id)){
                
                acf_add_admin_notice(__("Something went wrong with the taxonomy {$title}. Import aborted."), 'warning');
                continue;
                
            }
            
            
            // Register Args
            update_field('acfe_dt_name', $taxonomy_name, $post_id);
            update_field('label', $args['label'], $post_id);
            update_field('description', $args['description'], $post_id);
            update_field('hierarchical', $args['hierarchical'], $post_id);
            update_field('post_types', $args['post_types'], $post_id);
            update_field('public', $args['public'], $post_id);
            update_field('publicly_queryable', $args['publicly_queryable'], $post_id);
            update_field('update_count_callback', $args['update_count_callback'], $post_id);
            update_field('sort', $args['sort'], $post_id);
            
            // Meta box callback
            if(!isset($args['meta_box_cb']) || $args['meta_box_cb'] === null){
                
                update_field('meta_box_cb', 'null', $post_id);
                update_field('meta_box_cb_custom', '', $post_id);
                
            }
            
            elseif($args['meta_box_cb'] === false){
                
                update_field('meta_box_cb', 'false', $post_id);
                update_field('meta_box_cb_custom', '', $post_id);
                
            }
                
            elseif(empty($args['meta_box_cb']) || is_string($args['meta_box_cb'])){
                
                update_field('meta_box_cb', 'custom', $post_id);
                update_field('meta_box_cb_custom', $args['meta_box_cb'], $post_id);
                
            }
            
            // Labels
            if(!empty($args['labels'])){
                
                foreach($args['labels'] as $label_key => $label_value){
                    
                    update_field('labels_' . $label_key, $label_value, $post_id);
                    
                }
                
            }
            
            // Menu
            update_field('show_ui', $args['show_ui'], $post_id);
            update_field('show_in_menu', $args['show_in_menu'], $post_id);
            update_field('show_in_nav_menus', $args['show_in_nav_menus'], $post_id);
            update_field('show_tagcloud', $args['show_tagcloud'], $post_id);
            update_field('show_in_quick_edit', $args['show_in_quick_edit'], $post_id);
            update_field('show_admin_column', $args['show_admin_column'], $post_id);
            
            // Capability
            if(isset($args['capabilities']))
                update_field('capabilities', acf_encode_choices($args['capabilities'], false), $post_id);
            
            // Single
            update_field('acfe_dt_single_template', $args['acfe_single_template'], $post_id);
            update_field('acfe_dt_single_posts_per_page', $args['acfe_single_ppp'], $post_id);
            update_field('acfe_dt_single_orderby', $args['acfe_single_orderby'], $post_id);
            update_field('acfe_dt_single_order', $args['acfe_single_order'], $post_id);
            update_field('rewrite', $args['rewrite'], $post_id);
            
            // Admin
            update_field('acfe_dt_admin_terms_per_page', $args['acfe_admin_ppp'], $post_id);
            update_field('acfe_dt_admin_orderby', $args['acfe_admin_orderby'], $post_id);
            update_field('acfe_dt_admin_order', $args['acfe_admin_order'], $post_id);
            
            // REST
            update_field('show_in_rest', $args['show_in_rest'], $post_id);
            update_field('rest_base', $args['rest_base'], $post_id);
            update_field('rest_controller_class', $args['rest_controller_class'], $post_id);
            
            // Rewrite: override
            if($args['rewrite'] && is_array($args['rewrite'])){
                
                update_field('rewrite', true, $post_id);
                
                update_field('rewrite_args_select', true, $post_id);
                
                update_field('rewrite_args_acfe_dt_rewrite_slug', $args['rewrite']['slug'], $post_id);
                update_field('rewrite_args_acfe_dt_rewrite_with_front', $args['rewrite']['with_front'], $post_id);
                update_field('rewrite_args_hierarchical', $args['rewrite']['hierarchical'], $post_id);
                
            }
            
            // Create ACFE option
            $dynamic_taxonomies[$taxonomy_name] = $args;
            
            // Sort keys ASC
            ksort($dynamic_taxonomies);
            
            // Update ACFE option
            update_option('acfe_dynamic_taxonomies', $dynamic_taxonomies);
	    	
	    	// append message
	    	$ids[] = $post_id;
            
    	}
        
        if(empty($ids))
            return;
    	
    	// Count total
		$total = count($ids);
		
		// Generate text
		$text = sprintf(_n('1 taxonomy imported', '%s taxonomies imported', $total, 'acf'), $total);		
		
		// Add links to text
		$links = array();
		foreach($ids as $id){
            
			$links[] = '<a href="' . get_edit_post_link($id) . '">' . get_the_title($id) . '</a>';
            
		}
        
		$text .= ': ' . implode(', ', $links);
		
		// Add notice
		acf_add_admin_notice($text, 'success');
        
        // Flush permalinks
        flush_rewrite_rules();
        
    }
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Import_DT');

endif;