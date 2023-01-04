<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_upgrades')):

class acfe_module_form_upgrades{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_8'), 20);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_5'), 10);
        
    }
    
    
    /**
     * upgrade_0_8_8
     *
     * acfe/do_upgrade:20
     *
     * @param $db_version
     */
    function upgrade_0_8_8($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.8')){
            return;
        }
        
        acfe_delete_settings('modules.dynamic_form');
        
    }
    
    
    /**
     * upgrade_0_8_5
     *
     * acfe/do_upgrade:10
     *
     * @param $db_version
     */
    function upgrade_0_8_5($db_version){
    
        // check already done
        if(acf_version_compare($db_version, '>=', '0.8.5')){
            return;
        }
    
        // Retrieve all forms posts
        $get_forms = get_posts(array(
            'post_type'         => 'acfe-form',
            'posts_per_page'    => -1,
            'fields'            => 'ids',
            'post_status'       => 'any'
        ));
    
        // Bail early if no form found
        if(empty($get_forms)){
            return;
        }
    
        $flexible = acf_get_field_type('flexible_content');
        $field = acf_get_field('acfe_form_actions');
    
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
                $wp_meta[ $meta->meta_key ] = $meta->meta_value;
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
        
            /*
             * Step 1: Upgrade old group fields
             */
            $prefix = 'acfe_form_actions';
        
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
        
            // Process rules
            foreach($rules as $rule){
            
                $updates = array();
            
                foreach($acf_meta as $acf){
                
                    // Bail early if doesn't starts with 'acfe_form_actions'
                    if(strpos($acf['key'], $prefix) !== 0){
                        continue;
                    }
                
                    // Regex: 'acfe_form_actions_2_acfe_form_post_save_post_title_group'
                    // Match: '2'
                    if(preg_match('/^' . $prefix . '_([0-9]+)_' . $rule['group'] . '$/', $acf['key'], $match)){
                    
                        $updates[$rule['new_field']][$match[1]]['group'] = array(
                            'key'   => $acf['key'],
                            'value' => $acf['value'],
                        );
                    
                        // Regex: 'acfe_form_post_2_save_post_title_group_acfe_form_post_save_post_title'
                        // Match: '2'
                    }elseif(preg_match('/^' . $prefix . '_([0-9]+)_' . $rule['sub_field'] . '$/', $acf['key'], $match)){
                    
                        $updates[$rule['new_field']][$match[1]]['sub_field'] = array(
                            'key'   => $acf['key'],
                            'value' => $acf['value'],
                        );
                    
                        // Regex: 'acfe_form_post_2_save_post_title_group_acfe_form_post_save_post_title_custom'
                        // Match: '2'
                    }elseif(preg_match('/^' . $prefix . '_([0-9]+)_' . $rule['sub_field_custom'] . '$/', $acf['key'], $match)){
                    
                        // Generate: array[acfe_form_post_save_post_title][2]['sub_field_custom']
                        $updates[$rule['new_field']][$match[1]]['sub_field_custom'] = array(
                            'key'   => $acf['key'],
                            'value' => $acf['value'],
                        );
                    
                    }
                
                }
            
                if(!empty($updates)){
                
                    acf_log('[ACF Extended] 0.8.5 Upgrade: Forms');
                
                    // Update meta
                    foreach($updates as $new_field => $data){
                    
                        foreach($data as $i => $row){
                        
                            $group = acf_maybe_get($row, 'group');
                            $sub_field = acf_maybe_get($row, 'sub_field');
                            $sub_field_custom = acf_maybe_get($row, 'sub_field_custom');
                        
                            if($sub_field){
                            
                                $new_field_name = "{$prefix}_{$i}_{$new_field}";
                            
                                // update field
                                if($sub_field['value'] === 'custom'){
                                
                                    update_post_meta($post_id, $new_field_name, $sub_field_custom['value']);
                                
                                }else{
                                
                                    update_post_meta($post_id, $new_field_name, $sub_field['value']);
                                
                                }
                            
                                // update reference
                                update_post_meta($post_id, '_' . $new_field_name, 'field_' . $new_field);
                            
                            }
                        
                            // Delete old group
                            delete_post_meta($post_id, $group['key']);
                            delete_post_meta($post_id, $sub_field['key']);
                            delete_post_meta($post_id, $sub_field_custom['key']);
                        
                        }
                    
                    }
                
                }
            
            }
        
            /*
             * Step 2: Upgrade map fields which now require "Load values" to be enabled
             */
            if(have_rows('acfe_form_actions', $post_id)):
                while(have_rows('acfe_form_actions', $post_id)): the_row();
                
                    $layout = get_row_layout();
                    $row = get_row_index();
                    $i = $row-1;
                
                    // Post Action
                    if($layout === 'post'){
                    
                        $load_values = get_sub_field('acfe_form_post_load_values');
                    
                        $fields = array(
                            'field_acfe_form_post_save_post_type'       => get_sub_field('acfe_form_post_map_post_type', false),
                            'field_acfe_form_post_save_post_status'     => get_sub_field('acfe_form_post_map_post_status', false),
                            'field_acfe_form_post_save_post_title'      => get_sub_field('acfe_form_post_map_post_title', false),
                            'field_acfe_form_post_save_post_name'       => get_sub_field('acfe_form_post_map_post_name', false),
                            'field_acfe_form_post_save_post_content'    => get_sub_field('acfe_form_post_map_post_content', false),
                            'field_acfe_form_post_save_post_author'     => get_sub_field('acfe_form_post_map_post_author', false),
                            'field_acfe_form_post_save_post_parent'     => get_sub_field('acfe_form_post_map_post_parent', false),
                            'field_acfe_form_post_save_post_terms'      => get_sub_field('acfe_form_post_map_post_terms', false),
                        );
                    
                        if(!$load_values){
                        
                            foreach($fields as $field_key => $field_value){
                            
                                // Bail early if map field has no value
                                if(empty($field_value)){
                                    continue;
                                }
                            
                                // args
                                $update = array();
                                $update['acf_fc_layout'] = $layout;
                            
                                // Post content inside group
                                if($field_key === 'field_acfe_form_post_save_post_content'){
                                
                                    $update['field_acfe_form_post_save_post_content_group'] = array(
                                        'field_acfe_form_post_save_post_content' => $field_value
                                    );
                                
                                }else{
                                
                                    $update[$field_key] = $field_value;
                                
                                }
                            
                                // update
                                $flexible->update_row($update, $i, $field, $post_id);
                            
                            }
                        
                        }
                    
                    }
                
                    // Term Action
                    elseif($layout === 'term'){
                    
                        $load_values = get_sub_field('acfe_form_term_load_values');
                    
                        $fields = array(
                            'field_acfe_form_term_save_name'         => get_sub_field('acfe_form_term_map_name', false),
                            'field_acfe_form_term_save_slug'         => get_sub_field('acfe_form_term_map_slug', false),
                            'field_acfe_form_term_save_taxonomy'     => get_sub_field('acfe_form_term_map_taxonomy', false),
                            'field_acfe_form_term_save_parent'       => get_sub_field('acfe_form_term_map_parent', false),
                            'field_acfe_form_term_save_description'  => get_sub_field('acfe_form_term_map_description', false),
                        );
                    
                        if(!$load_values){
                        
                            foreach($fields as $field_key => $field_value){
                            
                                // Bail early if map field has no value
                                if(empty($field_value)){
                                    continue;
                                }
                            
                                // args
                                $update = array();
                                $update['acf_fc_layout'] = $layout;
                            
                                // Post content inside group
                                if($field_key === 'field_acfe_form_term_save_description'){
                                
                                    $update['field_acfe_form_term_save_description_group'] = array(
                                        'field_acfe_form_term_save_description' => $field_value
                                    );
                                
                                }else{
                                
                                    $update[$field_key] = $field_value;
                                
                                }
                            
                                // update
                                $flexible->update_row($update, $i, $field, $post_id);
                            
                            }
                        
                        }
                    
                    }
                
                    // User Action
                    elseif($layout === 'user'){
                    
                        $load_values = get_sub_field('acfe_form_user_load_values');
                    
                        $fields = array(
                            'field_acfe_form_user_save_email'           => get_sub_field('acfe_form_user_map_email', false),
                            'field_acfe_form_user_save_username'        => get_sub_field('acfe_form_user_map_username', false),
                            'field_acfe_form_user_save_password'        => get_sub_field('acfe_form_user_map_password', false),
                            'field_acfe_form_user_save_first_name'      => get_sub_field('acfe_form_user_map_first_name', false),
                            'field_acfe_form_user_save_last_name'       => get_sub_field('acfe_form_user_map_last_name', false),
                            'field_acfe_form_user_save_nickname'        => get_sub_field('acfe_form_user_map_nickname', false),
                            'field_acfe_form_user_save_display_name'    => get_sub_field('acfe_form_user_map_display_name', false),
                            'field_acfe_form_user_save_website'         => get_sub_field('acfe_form_user_map_website', false),
                            'field_acfe_form_user_save_description'     => get_sub_field('acfe_form_user_map_description', false),
                            'field_acfe_form_user_save_role'            => get_sub_field('acfe_form_user_map_role', false),
                        );
                    
                        if(!$load_values){
                        
                            foreach($fields as $field_key => $field_value){
                            
                                // Bail early if map field has no value
                                if(empty($field_value)){
                                    continue;
                                }
                            
                                // args
                                $update = array();
                                $update['acf_fc_layout'] = $layout;
                            
                                // Post content inside group
                                if($field_key === 'field_acfe_form_user_save_description'){
                                
                                    $update['field_acfe_form_user_save_description_group'] = array(
                                        'field_acfe_form_user_save_description' => $field_value
                                    );
                                
                                }else{
                                
                                    $update[$field_key] = $field_value;
                                
                                }
                            
                                // update
                                $flexible->update_row($update, $i, $field, $post_id);
                            
                            }
                        
                        }
                    
                    }
            
                endwhile;
            endif;
        
        }
        
    }
    
}

acf_new_instance('acfe_module_form_upgrades');

endif;