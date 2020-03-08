<?php

if(!class_exists('acfe_upgrades')):

class acfe_upgrades{

	function __construct(){
		
		// General: Version
		$this->upgrade_version();
		
		// ACF Extended: 0.8.5
		$this->upgrade_0_8_5();

	}
	
	function upgrade_version(){
		
		$version = acfe_settings('version');
		
		if(!$version || acf_version_compare($version, '!=', ACFE_VERSION)){
			
			acfe_settings('version', ACFE_VERSION, true);
			
		}
		
	}
	
	function upgrade_0_8_5(){
		
		$todo = acfe_settings('upgrades.0_8_5.todo');
		
		if(!$todo)
			return;
		
		$tasks = acfe_settings('upgrades.0_8_5.tasks');
		
		foreach($tasks as $task => $todo){
			
			if(!$todo)
				continue;
			
			/*
			 * Forms
			 */
			if($task === 'dynamic_form'){
				
				acf_log('ACFE 0.8.5: Upgrading forms');
				
				// Retrieve all forms posts
				$get_forms = get_posts(array(
					'post_type'         => 'acfe-form',
					'posts_per_page'    => -1,
					'fields'            => 'ids',
					'post_status'       => 'any'
				));
				
				// Bail early if no form found
				if(empty($get_forms)){
					
					// Upgrade done
					acfe_settings('upgrades.0_8_5.tasks.dynamic_form', false, true);
					
					continue;
					
				}
				
				global $wpdb;
				
				foreach($get_forms as $post_id){
					
					// init
					$wp_meta = array();
					$acf_meta = array();
					
					// Retrieve meta
					$get_meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d ", $post_id));
					
					// Sort
					usort($get_meta, function($a, $b){
						return strcmp($a->meta_key, $b->meta_key);
					});
					
					// Store
					foreach($get_meta as $meta){
						
						$wp_meta[$meta->meta_key] = $meta->meta_value;
						
					}
					
					// Check if is acf meta
					foreach($wp_meta as $key => $value){
						
						// ACF Meta
						if(isset($wp_meta["_$key"])){
							
							$acf_meta[] = array(
								'key'   => $key,
								'value' => $wp_meta[$key],
							);
							
						}
						
					}
					
					// Define script rules
					$rules = array(
						
						// Post: title
						array(
							'group'             => 'acfe_form_post_save_post_title_group',
							'sub_field'         => 'acfe_form_post_save_post_title_group_acfe_form_post_save_post_title',
							'sub_field_custom'  => 'acfe_form_post_save_post_title_group_acfe_form_post_save_post_title_custom',
							'new_field'         => 'acfe_form_post_save_post_title',
						),
						
						// Post: name
						array(
							'group'             => 'acfe_form_post_save_post_name_group',
							'sub_field'         => 'acfe_form_post_save_post_name_group_acfe_form_post_save_post_name',
							'sub_field_custom'  => 'acfe_form_post_save_post_name_group_acfe_form_post_save_post_name_custom',
							'new_field'         => 'acfe_form_post_save_post_name',
						),
						
						// Term: name
						array(
							'group'             => 'acfe_form_term_save_name_group',
							'sub_field'         => 'acfe_form_term_save_name_group_acfe_form_term_save_name',
							'sub_field_custom'  => 'acfe_form_term_save_name_group_acfe_form_term_save_name_custom',
							'new_field'         => 'acfe_form_term_save_name',
						),
						
						// Term: slug
						array(
							'group'             => 'acfe_form_term_save_slug_group',
							'sub_field'         => 'acfe_form_term_save_slug_group_acfe_form_term_save_slug',
							'sub_field_custom'  => 'acfe_form_term_save_slug_group_acfe_form_term_save_slug_custom',
							'new_field'         => 'acfe_form_term_save_slug',
						),
						
						// User: e-mail
						array(
							'group'             => 'acfe_form_user_save_email_group',
							'sub_field'         => 'acfe_form_user_save_email_group_acfe_form_user_save_email',
							'sub_field_custom'  => 'acfe_form_user_save_email_group_acfe_form_user_save_email_custom',
							'new_field'         => 'acfe_form_user_save_email',
						),
						
						// User: username
						array(
							'group'             => 'acfe_form_user_save_username_group',
							'sub_field'         => 'acfe_form_user_save_username_group_acfe_form_user_save_username',
							'sub_field_custom'  => 'acfe_form_user_save_username_group_acfe_form_user_save_username_custom',
							'new_field'         => 'acfe_form_user_save_username',
						),
						
						// User: password
						array(
							'group'             => 'acfe_form_user_save_password_group',
							'sub_field'         => 'acfe_form_user_save_password_group_acfe_form_user_save_password',
							'sub_field_custom'  => 'acfe_form_user_save_password_group_acfe_form_user_save_password_custom',
							'new_field'         => 'acfe_form_user_save_password',
						),
						
						// User: first name
						array(
							'group'             => 'acfe_form_user_save_first_name_group',
							'sub_field'         => 'acfe_form_user_save_first_name_group_acfe_form_user_save_first_name',
							'sub_field_custom'  => 'acfe_form_user_save_first_name_group_acfe_form_user_save_first_name_custom',
							'new_field'         => 'acfe_form_user_save_first_name',
						),
						
						// User: last name
						array(
							'group'             => 'acfe_form_user_save_last_name_group',
							'sub_field'         => 'acfe_form_user_save_last_name_group_acfe_form_user_save_last_name',
							'sub_field_custom'  => 'acfe_form_user_save_last_name_group_acfe_form_user_save_last_name_custom',
							'new_field'         => 'acfe_form_user_save_last_name',
						),
						
						// User: nickname
						array(
							'group'             => 'acfe_form_user_save_nickname_group',
							'sub_field'         => 'acfe_form_user_save_nickname_group_acfe_form_user_save_nickname',
							'sub_field_custom'  => 'acfe_form_user_save_nickname_group_acfe_form_user_save_nickname_custom',
							'new_field'         => 'acfe_form_user_save_nickname',
						),
						
						// User: display name
						array(
							'group'             => 'acfe_form_user_save_display_name_group',
							'sub_field'         => 'acfe_form_user_save_display_name_group_acfe_form_user_save_display_name',
							'sub_field_custom'  => 'acfe_form_user_save_display_name_group_acfe_form_user_save_display_name_custom',
							'new_field'         => 'acfe_form_user_save_display_name',
						),
						
						// User: website
						array(
							'group'             => 'acfe_form_user_save_website_group',
							'sub_field'         => 'acfe_form_user_save_website_group_acfe_form_user_save_website',
							'sub_field_custom'  => 'acfe_form_user_save_website_group_acfe_form_user_save_website_custom',
							'new_field'         => 'acfe_form_user_save_website',
						),
					
					);
					
					// Prefix
					$prefix = 'acfe_form_actions';
					
					// Process rules
					foreach($rules as $rule){
						
						$final = array();
						
						foreach($acf_meta as $acf){
							
							// Starts with prefix
							if(strpos($acf['key'], $prefix) !== 0)
								continue;
							
							if(preg_match('/^' . $prefix . '_([0-9]+)_' . $rule['group'] . '$/', $acf['key'], $match)){
								
								$final[$rule['new_field']][$match[1]]['group'] = array(
									'key'   => $acf['key'],
									'value' => $acf['value'],
								);
								
							}elseif(preg_match('/^' . $prefix . '_([0-9]+)_' . $rule['sub_field'] . '$/', $acf['key'], $match)){
								
								$final[$rule['new_field']][$match[1]]['sub_field'] = array(
									'key'   => $acf['key'],
									'value' => $acf['value'],
								);
								
							}elseif(preg_match('/^' . $prefix . '_([0-9]+)_' . $rule['sub_field_custom'] . '$/', $acf['key'], $match)){
								
								$final[$rule['new_field']][$match[1]]['sub_field_custom'] = array(
									'key'   => $acf['key'],
									'value' => $acf['value'],
								);
								
							}
							
						}
						
						if(empty($final))
							continue;
						
						// Update meta
						foreach($final as $new_field => $data){
							
							foreach($data as $i => $row){
								
								$group = acf_maybe_get($row, 'group');
								$sub_field = acf_maybe_get($row, 'sub_field');
								$sub_field_custom = acf_maybe_get($row, 'sub_field_custom');
								
								if($sub_field){
									
									$new_field_name = "{$prefix}_{$i}_{$new_field}";
									
									// update field
									if($sub_field['value'] === 'custom'){
										
										update_post_meta($post_id, $new_field_name, $sub_field_custom['value']);
										
										//acf_log('update:', $new_field_name, $sub_field_custom['value'] . ' custom');
										
									}else{
										
										update_post_meta($post_id, $new_field_name, $sub_field['value']);
										
										//acf_log('update:', $new_field_name, $sub_field['value']);
										
									}
									
									// update reference
									update_post_meta($post_id, '_' . $new_field_name, 'field_' . $new_field);
									
									//acf_log('update:', '_' . $new_field_name, 'field_' . $new_field);
									
								}
								
								// Delete old group
								delete_post_meta($post_id, $group['key']);
								delete_post_meta($post_id, $sub_field['key']);
								delete_post_meta($post_id, $sub_field_custom['key']);
								
								/*acf_log('delete:', $group['key']);
								acf_log('delete:', $sub_field['key']);
								acf_log('delete:', $sub_field_custom['key']);*/
								
							}
							
						}
						
					}
					
				}
				
				// Upgrade done
				acfe_settings('upgrades.0_8_5.tasks.dynamic_form', false, true);
				
			}
			
			/*
			 * Post Types
			 */
			elseif($task === 'dynamic_post_type'){
				
				acf_log('ACFE 0.8.5: Upgrading post types');
				
				// Old Post Types
				$old_post_types = get_option('acfe_dynamic_post_types', array());
				
				// New Post Types
				$new_post_types = acfe_settings('modules.dynamic_post_type.data');
				
				$merged_post_types = array_merge($old_post_types, $new_post_types);
				
				// Update Post Types
				acfe_settings('modules.dynamic_post_type.data', $merged_post_types, true);
				
				// Delete Old Post Types
				delete_option('acfe_dynamic_post_types');
				
				// Upgrade done
				acfe_settings('upgrades.0_8_5.tasks.dynamic_post_type', false, true);
				
			}
			
			/*
			 * Taxonomies
			 */
			elseif($task === 'dynamic_taxonomy'){
				
				acf_log('ACFE 0.8.5: Upgrading taxonomies');
				
				// Old Taxonomies
				$old_taxonomies = get_option('acfe_dynamic_taxonomies', array());
				
				// New Taxonomies
				$new_taxonomies = acfe_settings('modules.dynamic_taxonomy.data');
				
				$merged_taxonomies = array_merge($old_taxonomies, $new_taxonomies);
				
				// Update Taxonomies
				acfe_settings('modules.dynamic_taxonomy.data', $merged_taxonomies, true);
				
				// Delete Old Taxonomies
				delete_option('acfe_dynamic_taxonomies');
				
				// Upgrade done
				acfe_settings('upgrades.0_8_5.tasks.dynamic_taxonomy', false, true);
				
			}
			
			/*
			 * Block Types
			 */
			elseif($task === 'dynamic_block_type'){
				
				acf_log('ACFE 0.8.5: Upgrading block types');
				
				// Old Block Types
				$old_block_types = get_option('acfe_dynamic_block_types', array());
				
				// New Block Types
				$new_block_types = acfe_settings('modules.dynamic_block_type.data');
				
				$merged_block_types = array_merge($old_block_types, $new_block_types);
				
				// Update Block Types
				acfe_settings('modules.dynamic_block_type.data', $merged_block_types, true);
				
				// Delete Old Block Types
				delete_option('acfe_dynamic_block_types');
				
				// Upgrade done
				acfe_settings('upgrades.0_8_5.tasks.dynamic_block_type', false, true);
				
			}
			
			/*
			 * Option Pages
			 */
			elseif($task === 'dynamic_option'){
				
				acf_log('ACFE 0.8.5: Upgrading option pages');
				
				// Old Options
				$old_options = get_option('acfe_dynamic_options_pages', array());
				
				// New Options
				$new_options = acfe_settings('modules.dynamic_option.data');
				
				$merged_options = array_merge($old_options, $new_options);
				
				// Update Options
				acfe_settings('modules.dynamic_option.data', $merged_options, true);
				
				// Delete Old Options
				delete_option('acfe_dynamic_options_pages');
				
				// Upgrade done
				acfe_settings('upgrades.0_8_5.tasks.dynamic_option', false, true);
				
			}
			
		}
		
		$tasks = acfe_settings('upgrades.0_8_5.tasks');
		$finished = true;
		
		foreach($tasks as $todo){
			
			if($todo)
				$finished = false;
			
		}
		
		// Upgrade todo
		if($finished){
			
			acfe_settings('upgrades.0_8_5.todo', false, true);
			
		}
		
	}
	
}

new acfe_upgrades();

endif;