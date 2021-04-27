<?php

if(!class_exists('acfe_upgrades')):

class acfe_upgrades{
    
    public $upgrades = array(
        'do_0_8_5' => '0.8.5',
        'do_0_8_6' => '0.8.6',
        'do_0_8_8' => '0.8.8',
        'do_reset' => '0.0',
    );
    
    public $model = array(
        'version' => ACFE_VERSION,
        'modules' => array(
            'block_types'   => array(),
            'options_pages' => array(),
            'post_types'    => array(),
            'taxonomies'    => array(),
        )
    );

    function __construct(){
        
        $db_version = acfe_get_settings('version');
        
        // Bail early
        if(acf_version_compare($db_version, '>=', ACFE_VERSION))
            return;
        
        // Loop upgrades
        foreach($this->upgrades as $upgrade_function => $upgrade_version){
            
            if(acf_version_compare($upgrade_version, '<=', $db_version))
                continue;
            
            add_action('acf/init', array($this, $upgrade_function), 999);
            
        }
        
        $settings = acfe_get_settings();
        
        $model = $this->parse_args_r($settings, $this->model);
        $model['version'] = ACFE_VERSION;
        
        acfe_update_settings($model);
        
    }
    
    /*
     * Reset Modules
     */
    function do_reset(){
        
        // Modules
        acf_get_instance('acfe_dynamic_block_types')->reset();
        acf_get_instance('acfe_dynamic_options_pages')->reset();
        acf_get_instance('acfe_dynamic_post_types')->reset();
        acf_get_instance('acfe_dynamic_taxonomies')->reset();
        
    }
    
    /*
     * ACF Extended: 0.8.8
     */
    function do_0_8_8(){
        
        $tasks = array(
            'block_types',
            'options_pages',
            'post_types',
            'taxonomies',
            'clean',
        );
        
        foreach($tasks as $task){
    
            /*
             * Block Types
             */
            if($task === 'block_types'){
        
                $old = acfe_get_settings('modules.dynamic_block_type.data', array());
                $new = acfe_get_settings('modules.block_types', array());
        
                // Check
                if(empty($old))
                    continue;
    
                // Log
                acf_log('[ACF Extended] 0.8.8 Upgrade: Block Types');
        
                // Update
                acfe_update_settings('modules.block_types', array_merge($old, $new));
        
            }
            
            /*
             * Options Pages
             */
            elseif($task === 'options_pages'){
                
                $old = acfe_get_settings('modules.dynamic_option.data', array());
                $new = acfe_get_settings('modules.options_pages', array());
                
                // Check
                if(empty($old))
                    continue;
                
                // Log
                acf_log('[ACF Extended] 0.8.8 Upgrade: Options Pages');
        
                // Update
                acfe_update_settings('modules.options_pages', array_merge($old, $new));
        
            }
            
            /*
             * Post Types
             */
            elseif($task === 'post_types'){
                
                $old = acfe_get_settings('modules.dynamic_post_type.data', array());
                $new = acfe_get_settings('modules.post_types', array());
                
                // Check
                if(empty($old))
                    continue;
                
                // Log
                acf_log('[ACF Extended] 0.8.8 Upgrade: Post Types');
        
                // Update
                acfe_update_settings('modules.post_types', array_merge($old, $new));
        
            }
            
            /*
             * Taxonomies
             */
            elseif($task === 'taxonomies'){
                
                $old = acfe_get_settings('modules.dynamic_taxonomy.data', array());
                $new = acfe_get_settings('modules.taxonomies', array());
                
                // Check
                if(empty($old))
                    continue;
                
                // Log
                acf_log('[ACF Extended] 0.8.8 Upgrade: Taxonomies');
        
                // Update
                acfe_update_settings('modules.taxonomies', array_merge($old, $new));
        
            }
            
            /*
             * Clean
             */
            elseif($task === 'clean'){
    
                acfe_delete_settings('modules.author');
                acfe_delete_settings('modules.dev');
                acfe_delete_settings('modules.meta');
                acfe_delete_settings('modules.option');
                acfe_delete_settings('modules.ui');
                acfe_delete_settings('modules.dynamic_block_type');
                acfe_delete_settings('modules.dynamic_form');
                acfe_delete_settings('modules.dynamic_option');
                acfe_delete_settings('modules.dynamic_post_type');
                acfe_delete_settings('modules.dynamic_taxonomy');
                acfe_delete_settings('upgrades');
        
            }
            
        }
        
    }
    
    /*
     * ACF Extended: 0.8.6
     */
    function do_0_8_6(){
        
        $get_options = get_posts(array(
            'post_type'         => 'acfe-dop',
            'posts_per_page'    => -1,
            'fields'            => 'ids'
        ));
        
        if(!empty($get_options)){
            
            $updated = false;
            
            foreach($get_options as $post_id){
                
                $menu_slug = get_field('menu_slug', $post_id);
                $acfe_dop_name = get_field('acfe_dop_name', $post_id);
                $post_name = get_post_field('post_name', $post_id);
                
                // Update empty 'menu_slug' fields in options pages
                if(empty($menu_slug)){
                    
                    // Page Title
                    $page_title = get_post_field('post_title', $post_id);
                    
                    // Menu Title
                    $menu_title = get_field('menu_title', $post_id);
                    
                    if(empty($menu_title)){
                        
                        $menu_title = $page_title;
                        
                    }
                    
                    // Menu Slug
                    $menu_slug = sanitize_title($menu_title);
                    
                    // Update field
                    update_field('menu_slug', $menu_slug, $post_id);
                    
                    $updated = true;
                    
                }
                
                // Upgrade old name to menu_slug
                if($acfe_dop_name === $post_name){
                    
                    // Get ACFE option
                    $option = acfe_get_settings('modules.options_pages', array());
                    
                    // Check ACFE option
                    if(isset($option[$acfe_dop_name])){
                        
                        $register_args = $option[$acfe_dop_name];
                        
                        // Delete old option page slug
                        unset($option[$acfe_dop_name]);
                        
                        // Re-assign to menu_slug
                        $option[$menu_slug] = $register_args;
                        
                        // Sort keys ASC
                        ksort($option);
                        
                        // Update ACFE option
                        acfe_update_settings('modules.options_pages', $option);
                        
                        // Update post: force menu slug as name
                        wp_update_post(array(
                            'ID'            => $post_id,
                            'post_name'     => $menu_slug,
                        ));
                        
                        $updated = true;
                        
                    }
                    
                }
                
            }
            
            if($updated)
                acf_log('[ACF Extended] 0.8.6 Upgrade: Options Pages');
            
        }
        
    }
    
    /*
     * ACF Extended: 0.8.5
     */
    function do_0_8_5(){
        
        $tasks = array(
            'forms',
            'post_types',
            'taxonomies',
            'block_types',
            'options_pages',
        );
        
        foreach($tasks as $task){
            
            /*
             * Forms
             */
            if($task === 'forms'){
                
                // Retrieve all forms posts
                $get_forms = get_posts(array(
                    'post_type'         => 'acfe-form',
                    'posts_per_page'    => -1,
                    'fields'            => 'ids',
                    'post_status'       => 'any'
                ));
                
                // Bail early if no form found
                if(empty($get_forms))
                    continue;
                
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
                            if(strpos($acf['key'], $prefix) !== 0)
                                continue;
                            
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
                                        if(empty($field_value))
                                            continue;
                                        
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
                                        if(empty($field_value))
                                            continue;
                                        
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
                                        if(empty($field_value))
                                            continue;
                                        
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
            
            /*
             * Post Types
             */
            elseif($task === 'post_types'){
                
                $old = get_option('acfe_dynamic_post_types', array());
                $new = acfe_get_settings('modules.post_types', array());
                
                delete_option('acfe_dynamic_post_types');
                
                if(empty($old))
                    continue;
                
                acf_log('[ACF Extended] 0.8.5 Upgrade: Post Types');
                
                // Update
                acfe_update_settings('modules.post_types', array_merge($old, $new));
                
            }
            
            /*
             * Taxonomies
             */
            elseif($task === 'taxonomies'){
                
                $old = get_option('acfe_dynamic_taxonomies', array());
                $new = acfe_get_settings('modules.taxonomies', array());
                
                delete_option('acfe_dynamic_taxonomies');
                
                if(empty($old))
                    continue;
                
                acf_log('[ACF Extended] 0.8.5 Upgrade: Taxonomies');
                
                // Update
                acfe_update_settings('modules.taxonomies', array_merge($old, $new));
                
            }
            
            /*
             * Block Types
             */
            elseif($task === 'block_types'){
                
                $old = get_option('acfe_dynamic_block_types', array());
                $new = acfe_get_settings('modules.block_types', array());
                
                delete_option('acfe_dynamic_block_types');
                
                if(empty($old))
                    continue;
                
                acf_log('[ACF Extended] 0.8.5 Upgrade: Block Types');
                
                // Update
                acfe_update_settings('modules.block_types', array_merge($old, $new));
                
            }
            
            /*
             * Option Pages
             */
            elseif($task === 'options_pages'){
                
                $old = get_option('acfe_dynamic_options_pages', array());
                $new = acfe_get_settings('modules.options_pages', array());
                
                delete_option('acfe_dynamic_options_pages');
                
                if(empty($old))
                    continue;
                
                acf_log('[ACF Extended] 0.8.5 Upgrade: Options Pages');
                
                // Update
                acfe_update_settings('modules.options_pages', array_merge($old, $new));
                
            }
            
        }
        
    }
    
    function parse_args_r(&$a, $b){
        
        $a = (array) $a;
        $b = (array) $b;
        $r = $b;
        
        foreach($a as $k => &$v){
            
            if(is_array($v) && isset($r[ $k ])){
                $r[$k] = $this->parse_args_r($v, $r[ $k ]);
            }else{
                $r[$k] = $v;
            }
            
        }
        
        return $r;
        
    }
    
}

acf_new_instance('acfe_upgrades');

endif;