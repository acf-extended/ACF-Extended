<?php 

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_options_pages'))
    return;

if(!class_exists('ACFE_Admin_Tool_Import_DOP')):

class ACFE_Admin_Tool_Import_DOP extends ACF_Admin_Tool{

    function initialize(){
        
        // vars
        $this->name = 'acfe_tool_dop_import';
        $this->title = __('Import Options Pages');
        $this->icon = 'dashicons-upload';
        
    }
    
    function html(){
        
        ?>
        <p><?php _e('Import Options Pages', 'acf'); ?></p>
        
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
        
        $dynamic_options_pages = get_option('acfe_dynamic_options_pages', array());
        
        $dynamic_options_sub_pages = array();
    	
    	// Loop over json
    	foreach($json as $options_page_name => $args){
            
            // Check if already exists
            if(isset($dynamic_options_pages[$options_page_name])){
                
                acf_add_admin_notice(__("Options page {$dynamic_options_pages[$options_page_name]['page_title']} already exists. Import aborted."), 'warning');
                continue;
                
            }
            
            // Vars
            $title = $args['page_title'];
            $name = $options_page_name;
            
            // Insert post
            $post_id = wp_insert_post(array(
                'post_title'    => $title,
                'post_name'     => $name,
                'post_type'     => 'acfe-dop',
                'post_status'   => 'publish'
            ));
            
            // Insert error
            if(is_wp_error($post_id)){
                
                acf_add_admin_notice(__("Something went wrong with the options page {$title}. Import aborted."), 'warning');
                continue;
                
            }
            
            // Register Args
            update_field('page_title', $args['page_title'], $post_id);
            update_field('acfe_dop_name', $name, $post_id);
            update_field('menu_title', $args['menu_title'], $post_id);
            update_field('menu_slug', $args['menu_slug'], $post_id);
            update_field('capability', $args['capability'], $post_id);
            update_field('position', $args['position'], $post_id);
            update_field('parent_slug', $args['parent_slug'], $post_id);
            update_field('icon_url', $args['icon_url'], $post_id);
            update_field('redirect', $args['redirect'], $post_id);
            update_field('post_id', $args['post_id'], $post_id);
            update_field('autoload', $args['autoload'], $post_id);
            update_field('update_button', $args['update_button'], $post_id);
            update_field('updated_message', $args['updated_message'], $post_id);
            
            // Create ACFE option
            $dynamic_options_pages[$options_page_name] = $args;
            
            // Sort keys ASC
            ksort($dynamic_options_pages);
            
            // Update ACFE option
            update_option('acfe_dynamic_options_pages', $dynamic_options_pages);
	    	
	    	// Append message
	    	$ids[] = $post_id;
            
            // Add Sub Page
            if(isset($args['parent_slug']) && !empty($args['parent_slug']))
                $dynamic_options_sub_pages[$post_id] = $args;
            
    	}
        
        // Check if pages have been added
        if(empty($ids))
            return;
        
        // Update Options Sub Pages
        if(!empty($dynamic_options_sub_pages)){
            
            foreach($dynamic_options_sub_pages as $post_id => $args){
                
                // Get possible parent options pages
                $get_dop_parent = get_posts(array(
                    'post_type'         => 'acfe-dop',
                    'posts_per_page'    => 1,
                    'fields'            => 'ids',
                    'meta_query'        => array(
                        array(
                            'key'   => 'menu_slug',
                            'value' => $args['parent_slug']
                        )
                    )
                ));
                
                if(empty($get_dop_parent))
                    continue;
                
                $parent = $get_dop_parent[0];
                
                // Update sub page post
                wp_update_post(array(
                    'ID'            => $post_id,
                    'post_parent'   => $parent,
                ));
                
            }
            
        }
    	
    	// Count total
		$total = count($ids);
		
		// Generate text
		$text = sprintf(_n('1 options page imported', '%s options pages imported', $total, 'acf'), $total);		
		
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

acf_register_admin_tool('ACFE_Admin_Tool_Import_DOP');

endif;