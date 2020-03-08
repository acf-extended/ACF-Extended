<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_block_types'))
    return;

if(!class_exists('ACFE_Admin_Tool_Import_DBT')):

class ACFE_Admin_Tool_Import_DBT extends ACF_Admin_Tool{

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_dbt_import';
        $this->title = __('Import Block Types');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        ?>
        <p><?php _e('Import Block Types', 'acf'); ?></p>
        
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
	
	    $dynamic_block_types = acfe_settings('modules.dynamic_block_type.data');
    	
    	// Loop over json
    	foreach($json as $block_type_name => $args){
            
            // Check if already exists
            if(isset($dynamic_block_types[$block_type_name])){
                
                acf_add_admin_notice(__("Block type {$dynamic_block_types[$block_type_name]['title']} already exists. Import aborted."), 'warning');
                continue;
                
            }
            
            // Vars
            $title = $args['title'];
            $name = $block_type_name;
            
            // Insert post
            $post_id = wp_insert_post(array(
                'post_title'    => $title,
                'post_name'     => $name,
                'post_type'     => 'acfe-dbt',
                'post_status'   => 'publish'
            ));
            
            // Insert error
            if(is_wp_error($post_id)){
                
                acf_add_admin_notice(__("Something went wrong with the block type {$title}. Import aborted."), 'warning');
                continue;
                
            }
            
            // Register Args
            update_field('name', $name, $post_id);
            update_field('title', $args['title'], $post_id);
            update_field('description', $args['description'], $post_id);
            update_field('category', $args['category'], $post_id);
            update_field('keywords', acf_encode_choices($args['keywords'], false), $post_id);
            update_field('post_types', $args['post_types'], $post_id);
            update_field('mode', $args['mode'], $post_id);
            update_field('align', $args['align'], $post_id);
            update_field('render_callback', $args['render_callback'], $post_id);
            update_field('enqueue_assets', $args['enqueue_assets'], $post_id);
            
            // Render Template
            if(!empty($args['render_template']))
                update_field('render_template', str_replace(ACFE_THEME_PATH . '/', '', $args['render_template']), $post_id);
            
            // Enqueue Style
            if(!empty($args['enqueue_style']))
                update_field('enqueue_style', str_replace(ACFE_THEME_URL . '/', '', $args['enqueue_style']), $post_id);
            
            // Enqueue Script
            if(!empty($args['enqueue_script']))
                update_field('enqueue_script', str_replace(ACFE_THEME_URL . '/', '', $args['enqueue_script']), $post_id);
            
            // Align
            if(empty($args['align']))
                update_field('align', 'none', $post_id);
            
            // Icon
            if(!empty($args['icon'])){
                
                // Simple
                if(is_string($args['icon'])){
                    
                    update_field('icon_type', 'simple', $post_id);
                    
                    update_field('icon_text', $args['icon'], $post_id);
                    
                }
                
                // Colors
                elseif(is_array($args['icon'])){
                    
                    update_field('icon_type', 'colors', $post_id);
                    
                    update_field('icon_background', $args['icon']['background'], $post_id);
                    update_field('icon_foreground', $args['icon']['foreground'], $post_id);
                    update_field('icon_src', $args['icon']['src'], $post_id);
                    
                }
                
            }
            
            // Supports: Align
            update_field('supports_align', $args['supports']['align'], $post_id);
            
            if(is_array($args['supports']['align'])){
                
                update_field('supports_align_args', acf_encode_choices($args['supports']['align'], false), $post_id);
                
            }
            
            // Supports: Mode
            update_field('supports_mode', $args['supports']['mode'], $post_id);
            
            // Supports: Multiple
            update_field('supports_multiple', $args['supports']['multiple'], $post_id);
            
            // Create ACFE option
            $dynamic_block_types[$block_type_name] = $args;
            
            // Sort keys ASC
            ksort($dynamic_block_types);
            
            // Update ACFE option
		    acfe_settings('modules.dynamic_block_type.data', $dynamic_block_types, true);
	    	
	    	// append message
	    	$ids[] = $post_id;
            
    	}
        
        if(empty($ids))
            return;
    	
    	// Count total
		$total = count($ids);
		
		// Generate text
		$text = sprintf(_n('1 block type imported', '%s block types imported', $total, 'acf'), $total);		
		
		// Add links to text
		$links = array();
		foreach($ids as $id){
            
			$links[] = '<a href="' . get_edit_post_link($id) . '">' . get_the_title($id) . '</a>';
            
		}
        
		$text .= ': ' . implode(', ', $links);
		
		// Add notice
		acf_add_admin_notice($text, 'success');
        
    }
    
}

acf_register_admin_tool('ACFE_Admin_Tool_Import_DBT');

endif;