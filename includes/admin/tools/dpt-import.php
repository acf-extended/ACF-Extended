<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_post_types'))
    return;

if(!class_exists('ACFE_Admin_Tool_Import_DPT')):

class ACFE_Admin_Tool_Import_DPT extends ACF_Admin_Tool{

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_dpt_import';
        $this->title = __('Import Post Types');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        ?>
        <p><?php _e('Import Post Types', 'acf'); ?></p>
        
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
	
	    $dynamic_post_types = acfe_settings('modules.dynamic_post_type.data');
    	
    	// Loop over json
    	foreach($json as $post_type_name => $args){
            
            // Check if already exists
            if(isset($dynamic_post_types[$post_type_name])){
                
                acf_add_admin_notice(__("Post type {$dynamic_post_types[$post_type_name]['label']} already exists. Import aborted."), 'warning');
                continue;
                
            }
            
            // Vars
            $title = $args['label'];
            $name = $post_type_name;
            
            // Insert post
            $post_id = wp_insert_post(array(
                'post_title'    => $title,
                'post_name'     => $name,
                'post_type'     => 'acfe-dpt',
                'post_status'   => 'publish'
            ));
            
            // Insert error
            if(is_wp_error($post_id)){
                
                acf_add_admin_notice(__("Something went wrong with the post type {$title}. Import aborted."), 'warning');
                continue;
                
            }
            
            // Register Args
            update_field('acfe_dpt_name', $post_type_name, $post_id);
            update_field('label', $args['label'], $post_id);
            update_field('description', $args['description'], $post_id);
            update_field('hierarchical', $args['hierarchical'], $post_id);
            update_field('supports', $args['supports'], $post_id);
            update_field('taxonomies', $args['taxonomies'], $post_id);
            update_field('public', $args['public'], $post_id);
            update_field('exclude_from_search', $args['exclude_from_search'], $post_id);
            update_field('publicly_queryable', $args['publicly_queryable'], $post_id);
            update_field('can_export', $args['can_export'], $post_id);
            update_field('delete_with_user', $args['delete_with_user'], $post_id);
            
            // Labels
            if(!empty($args['labels'])){
                
                foreach($args['labels'] as $label_key => $label_value){
                    
                    update_field('labels_' . $label_key, $label_value, $post_id);
                    
                }
                
            }
            
            // Menu
            update_field('menu_position', $args['menu_position'],         $post_id);
            update_field('menu_icon', $args['menu_icon'], $post_id);
            update_field('show_ui', $args['show_ui'], $post_id);
            update_field('show_in_menu', $args['show_in_menu'], $post_id);
            update_field('show_in_nav_menus', $args['show_in_nav_menus'], $post_id);
            update_field('show_in_admin_bar', $args['show_in_admin_bar'], $post_id);
            
            // Capability
            update_field('capability_type', acf_encode_choices($args['capability_type'], false), $post_id);
            update_field('map_meta_cap', $args['map_meta_cap'], $post_id);
            
            if(isset($args['capabilities']))
                update_field('capabilities', acf_encode_choices($args['capabilities'], false), $post_id);
            
            // Archive
            update_field('acfe_dpt_archive_template', $args['acfe_archive_template'], $post_id);
            update_field('acfe_dpt_archive_posts_per_page', $args['acfe_archive_ppp'], $post_id);
            update_field('acfe_dpt_archive_orderby', $args['acfe_archive_orderby'], $post_id);
            update_field('acfe_dpt_archive_order', $args['acfe_archive_order'], $post_id);
            update_field('has_archive', $args['has_archive'], $post_id);
            
            // Single
            update_field('acfe_dpt_single_template', $args['acfe_single_template'], $post_id);
            update_field('rewrite', $args['rewrite'], $post_id);
            
            // Admin
            update_field('acfe_dpt_admin_posts_per_page', $args['acfe_admin_ppp'], $post_id);
            update_field('acfe_dpt_admin_orderby', $args['acfe_admin_orderby'], $post_id);
            update_field('acfe_dpt_admin_order', $args['acfe_admin_order'], $post_id);
            
            // REST
            update_field('show_in_rest', $args['show_in_rest'], $post_id);
            update_field('rest_base', $args['rest_base'], $post_id);
            update_field('rest_controller_class', $args['rest_controller_class'], $post_id);
            
            // Has archive: override
            if($args['has_archive'])
                update_field('has_archive_slug', $args['has_archive'], $post_id);
            
            // Rewrite: override
            if($args['rewrite'] && is_array($args['rewrite'])){
                
                update_field('rewrite', true, $post_id);
                
                update_field('rewrite_args_select', true, $post_id);
                
                update_field('rewrite_args_acfe_dpt_rewrite_slug', $args['rewrite']['slug'], $post_id);
                update_field('rewrite_args_acfe_dpt_rewrite_with_front', $args['rewrite']['with_front'], $post_id);
                update_field('rewrite_args_feeds', $args['rewrite']['feeds'], $post_id);
                update_field('rewrite_args_pages', $args['rewrite']['pages'], $post_id);
                
            }
            
            // Show in menu (text)
            if($args['show_in_menu'] && is_string($args['show_in_menu']))
                update_field('show_in_menu_text', $args['show_in_menu'], $post_id);
            
            // Map meta cap
            if($args['map_meta_cap'] === false)
                update_field('map_meta_cap', 'false', $post_id);
            
            elseif($args['map_meta_cap'] === true)
                update_field('map_meta_cap', 'true', $post_id);
            
            // Create ACFE option
            $dynamic_post_types[$post_type_name] = $args;
            
            // Sort keys ASC
            ksort($dynamic_post_types);
            
            // Update ACFE option
		    acfe_settings('modules.dynamic_post_type.data', $dynamic_post_types, true);
	    	
	    	// append message
	    	$ids[] = $post_id;
            
    	}
        
        if(empty($ids))
            return;
    	
    	// Count total
		$total = count($ids);
		
		// Generate text
		$text = sprintf(_n('1 post type imported', '%s post types imported', $total, 'acf'), $total);		
		
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

acf_register_admin_tool('ACFE_Admin_Tool_Import_DPT');

endif;