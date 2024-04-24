<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_upgrades')):

class acfe_module_form_upgrades{
    
    function __construct(){
    
        // upgrade
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_9_0_1'), 40);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_9'),     30);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_8'),   20);
        add_action('acfe/do_upgrade', array($this, 'upgrade_0_8_5'),   10);
        
    }
    
    /**
     * upgrade_0_9_0_1
     *
     * acfe/do_upgrade:40
     *
     * @param $db_version
     */
    function upgrade_0_9_0_1($db_version){
        
        // check already done
        if(acf_version_compare($db_version, '>=', '0.9.0.1')){
            return;
        }
        
        // re-run 0.9 upgrade
        $this->upgrade_0_9('0.8.9.5');
        
    }
    
    /**
     * upgrade_0_9
     *
     * acfe/do_upgrade:30
     *
     * @param $db_version
     */
    function upgrade_0_9($db_version){
        
        // check already done
        if(acf_version_compare($db_version, '>=', '0.9')){
            return;
        }
        
        // get forms posts
        $forms = get_posts(array(
            'post_type'      => 'acfe-form',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'post_status'    => 'any',
        ));
    
        $todo = array();
    
        foreach($forms as $post_id){
        
            // validate old item
            if(acfe_is_module_v2_item($post_id)){
                $todo[] = $post_id;
            }
        
        }
        
        // bail early
        if(!$todo){
            return;
        }
        
        // add legacy form field group
        $this->add_v2_field_group();
    
        // get module
        $module = acfe_get_module('form');
    
        // loop
        foreach($todo as $post_id){
            
            // get meta values
            $meta = get_fields($post_id, false);
            
            // default item
            $item = array(
                'ID'    => $post_id,
                'name'  => get_field('acfe_form_name',  $post_id),
                'label' => get_post_field('post_title', $post_id),
                'title' => get_post_field('post_title', $post_id),
            );

            // upgrade item
            $item = $this->upgrade_v2_item_to_v3($item, $meta);
            
            // allow button html in post_content
            remove_filter('content_save_pre', 'wp_filter_post_kses');
            
            // import item (update db)
            $module->import_item($item);
        
        }
    
        // remove legacy form field group
        $this->remove_v2_field_group();
    
        // log
        acf_log('[ACF Extended] 0.9 Upgrade: Forms');
    
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
    
    
    /**
     * upgrade_v2_item_to_v3
     *
     * ACF Extended: 0.9
     *
     * @param $item
     * @param $args
     *
     * @return array
     */
    function upgrade_v2_item_to_v3($item, $args = array()){
    
        // form attributes
        $form_attributes = acf_maybe_get($args, 'acfe_form_attributes');
        $fields_attributes = acf_maybe_get($args, 'acfe_form_fields_attributes');
    
        // new item
        $item = array(
            'ID'            => $item['ID'],
            'name'          => $item['name'],
            'label'         => $item['label'],
            'title'         => $item['title'],
            'active'        => acf_maybe_get($args, 'acfe_form_active', true),
            'field_groups'  => acf_maybe_get($args, 'acfe_form_field_groups'),
            'settings'      => array(
                'location'              => acf_maybe_get($args, 'acfe_form_field_groups_rules'),
                'honeypot'              => acf_maybe_get($args, 'acfe_form_honeypot'),
                'kses'                  => acf_maybe_get($args, 'acfe_form_kses'),
                'uploader'              => acf_maybe_get($args, 'acfe_form_uploader'),
            ),
            'attributes'    => array(
                'form' => array(
                    'element' => acf_maybe_get($args, 'acfe_form_form_element') ? 'form' : 'div',
                    'class'   => acf_maybe_get($form_attributes, 'field_acfe_form_attributes_class'),
                    'id'      => acf_maybe_get($form_attributes, 'field_acfe_form_attributes_id'),
                ),
                'fields' => array(
                    'element'       => acf_maybe_get($args, 'acfe_form_form_field_el'),
                    'wrapper_class' => acf_maybe_get($fields_attributes, 'field_acfe_form_fields_wrapper_class'),
                    'class'         => acf_maybe_get($fields_attributes, 'field_acfe_form_fields_class'),
                    'label'         => acf_maybe_get($args, 'acfe_form_label_placement'),
                    'instruction'   => acf_maybe_get($args, 'acfe_form_instruction_placement'),
                ),
                'submit' => array(
                    'value'   => acf_maybe_get($args, 'acfe_form_submit_value'),
                    'button'  => acf_maybe_get($args, 'acfe_form_html_submit_button'),
                    'spinner' => acf_maybe_get($args, 'acfe_form_html_submit_spinner'),
                )
            ),
            'validation'    => array(
                'hide_error'        => acf_maybe_get($args, 'acfe_form_hide_error'),
                'hide_revalidation' => acf_maybe_get($args, 'acfe_form_hide_revalidation'),
                'hide_unload'       => acf_maybe_get($args, 'acfe_form_hide_unload'),
                'errors_position'   => acf_maybe_get($args, 'acfe_form_errors_position'),
                'errors_class'      => acf_maybe_get($args, 'acfe_form_errors_class'),
            ),
            'success'       => array(
                'hide_form' => acf_maybe_get($args, 'acfe_form_updated_hide_form'),
                'message'   => acf_maybe_get($args, 'acfe_form_updated_message'),
                'wrapper'   => acf_maybe_get($args, 'acfe_form_html_updated_message'),
            ),
            'actions'       => array(),
            'render'        => '',
        );
        
        // submit disabled
        if(!acf_maybe_get($args, 'acfe_form_form_submit')){
            $item['attributes']['submit'] = false;
        }
    
        // render
        $render               = '';
        $old_render           = acf_maybe_get($args, 'acfe_form_custom_html');
        $old_render_enabled   = acf_maybe_get($args, 'acfe_form_custom_html_enable');
        $before_render        = acf_maybe_get($args, 'acfe_form_html_before_fields');
        $after_render         = acf_maybe_get($args, 'acfe_form_html_after_fields');
        
        // old render
        if($old_render_enabled && $old_render){
            $render = $old_render;
        }
    
        // generate render
        if(!empty($before_render) || !empty($after_render)){
        
            // empty render
            // use {render:fields}
            if(empty($render)){
                $render = '{render:fields}';
            }
        
            // prepend before render
            if(!empty($before_render)){
                $render = $before_render . "\n\n" . $render;
            }
        
            // append before render
            if(!empty($after_render)){
                $render = $render . "\n\n" . $after_render;
            }
        
        }
    
        // deprecated {field:field_625e53aa1a791}
        // deprecated {field_group:group_61642cb824d8a}
        $render = str_replace('{field:', '{render:', $render);
        $render = str_replace('{field_group:', '{render:', $render);
    
        // assign form render
        $item['render'] = $render;
    
        // loop actions
        foreach(acf_get_array($args['acfe_form_actions']) as $row){
        
            switch($row['acf_fc_layout']){
            
                /**
                 * custom
                 */
                case 'custom':{
                
                    $action = array(
                        'action' => 'custom',
                        'name'   => acf_maybe_get($row, 'field_acfe_form_custom_action'),
                    );
                
                    // append action
                    $item['actions'][] = $action;
                
                    break;
                }
            
                /**
                 * email
                 */
                case 'email':{
                
                    $action = array(
                        'action'      => 'email',
                        'name'        => acf_maybe_get($row, 'field_acfe_form_email_custom_alias'),
                        'email'       => array(
                            'from'        => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_email_from')),
                            'to'          => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_email_to')),
                            'reply_to'    => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_email_reply_to')),
                            'cc'          => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_email_cc')),
                            'bcc'         => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_email_bcc')),
                            'subject'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_email_subject')),
                            'content'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_email_content')),
                        ),
                        'attachments' => array(),
                    );
                
                    // files dynamic
                    foreach(acf_get_array($row['field_acfe_form_email_files']) as $file){
                        $action['attachments'][] = array(
                            'file'   => $this->handle_field_tags(acf_maybe_get($file, 'field_acfe_form_email_file')),
                            'delete' => acf_maybe_get($file, 'field_acfe_form_email_file_delete'),
                        );
                    }
                
                    // files static
                    foreach(acf_get_array($row['field_acfe_form_email_files_static']) as $file){
                        $action['attachments'][] = acf_maybe_get($file, 'field_acfe_form_email_file_static');
                    }
                
                    // append action
                    $item['actions'][] = $action;
                
                    break;
                }
            
                /**
                 * option
                 */
                case 'option':{
                
                    $action = array(
                        'action' => 'option',
                        'name'   => acf_maybe_get($row, 'field_acfe_form_option_custom_alias'),
                        'save'   => array(
                            'target'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_option_save_target')),
                            'acf_fields' => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_option_save_meta')),
                        ),
                        'load'   => array(
                            'source'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_option_load_source')),
                            'acf_fields' => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_option_load_meta')),
                        ),
                    );
                
                    // load
                    $load_values = acf_maybe_get($row, 'field_acfe_form_option_load_values');
                
                    // reset load if disabled
                    if(!$load_values){
                        unset($action['load']);
                    }
                
                    // append action
                    $item['actions'][] = $action;
                
                    break;
                }
            
                /**
                 * post
                 */
                case 'post':{
                
                    $action = array(
                        'action' => 'post',
                        'type'   => acf_maybe_get($row, 'field_acfe_form_post_action'), // insert_post | update_post
                        'name'   => acf_maybe_get($row, 'field_acfe_form_post_custom_alias'),
                        'save'   => array(
                            'target'         => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_target')),
                            'post_type'      => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_post_type')),
                            'post_status'    => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_post_status')),
                            'post_title'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_post_title')),
                            'post_name'      => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_post_name')),
                            'post_content'   => '',
                            'post_excerpt'   => '',
                            'post_author'    => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_post_author')),
                            'post_parent'    => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_post_parent')),
                            'post_date'      => '',
                            'post_thumbnail' => '',
                            'post_terms'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_save_post_terms')),
                            'append_terms'   => '',
                            'acf_fields'     => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_post_save_meta')),
                        ),
                        'load'   => array(
                            'source'         => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_post_load_source')),
                            'post_type'      => acf_maybe_get($row, 'field_acfe_form_post_map_post_type'),
                            'post_status'    => acf_maybe_get($row, 'field_acfe_form_post_map_post_status'),
                            'post_title'     => acf_maybe_get($row, 'field_acfe_form_post_map_post_title'),
                            'post_name'      => acf_maybe_get($row, 'field_acfe_form_post_map_post_name'),
                            'post_content'   => acf_maybe_get($row, 'field_acfe_form_post_map_post_content'),
                            'post_excerpt'   => acf_maybe_get($row, 'field_acfe_form_post_map_post_excerpt'),
                            'post_author'    => acf_maybe_get($row, 'field_acfe_form_post_map_post_author'),
                            'post_parent'    => acf_maybe_get($row, 'field_acfe_form_post_map_post_parent'),
                            'post_date'      => '',
                            'post_thumbnail' => '',
                            'post_terms'     => acf_maybe_get($row, 'field_acfe_form_post_map_post_terms'),
                            'acf_fields'     => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_post_load_meta')),
                        ),
                    );
                
                    // post content
                    $group = acf_maybe_get($row, 'field_acfe_form_post_save_post_content_group');
                    $post_content = $group['field_acfe_form_post_save_post_content'];
                    $post_content_custom = $group['field_acfe_form_post_save_post_content_custom'];
                
                    if($post_content === 'custom'){
                        $post_content = $post_content_custom;
                    }
                
                    // post excerpt
                    $group = acf_maybe_get($row, 'field_acfe_form_post_save_post_excerpt_group');
                    $post_excerpt = $group['field_acfe_form_post_save_post_excerpt'];
                    $post_excerpt_custom = $group['field_acfe_form_post_save_post_excerpt_custom'];
                
                    if($post_excerpt === 'custom'){
                        $post_excerpt = $post_excerpt_custom;
                    }
                
                    // assign
                    $action['save']['post_content'] = $this->handle_field_tags($post_content);
                    $action['save']['post_excerpt'] = $this->handle_field_tags($post_excerpt);
                    
                    // load
                    $load_values = acf_maybe_get($row, 'field_acfe_form_post_load_values');
                    
                    // reset load if disabled
                    if(!$load_values){
                        unset($action['load']);
                        
                    }else{
                        
                        // load loop
                        foreach(array_keys($action['load']) as $key){
                            
                            $value = $action['load'][ $key ];
                            
                            // assign save key with {field:field_abcdef123456}
                            if(isset($action['save'][ $key ]) && !empty($value) && is_string($value) && acf_is_field_key($value)){
                                $action['save'][ $key ] = "{field:$value}";
                            }
                            
                        }
                        
                    }
                
                    // append action
                    $item['actions'][] = $action;
                
                    break;
                }
            
                /**
                 * redirect
                 */
                case 'redirect':{
                
                    $action = array(
                        'action' => 'redirect',
                        'name'   => acf_maybe_get($row, 'field_acfe_form_redirect_custom_alias'),
                        'url'    => acf_maybe_get($row, 'field_acfe_form_redirect_url'),
                    );
                
                    // append action
                    $item['actions'][] = $action;
                
                    break;
                }
            
                /**
                 * term
                 */
                case 'term':{
                
                    $action = array(
                        'action' => 'term',
                        'type'   => acf_maybe_get($row, 'field_acfe_form_term_action'), // insert_term | update_term
                        'name'   => acf_maybe_get($row, 'field_acfe_form_term_custom_alias'),
                        'save'   => array(
                            'target'      => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_term_save_target')),
                            'name'        => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_term_save_name')),
                            'slug'        => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_term_save_slug')),
                            'taxonomy'    => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_term_save_taxonomy')),
                            'parent'      => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_term_save_parent')),
                            'description' => '',
                            'acf_fields'  => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_term_save_meta')),
                        ),
                        'load'   => array(
                            'source'      => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_term_load_source')),
                            'name'        => acf_maybe_get($row, 'field_acfe_form_term_map_name'),
                            'slug'        => acf_maybe_get($row, 'field_acfe_form_term_map_slug'),
                            'taxonomy'    => acf_maybe_get($row, 'field_acfe_form_term_map_taxonomy'),
                            'parent'      => acf_maybe_get($row, 'field_acfe_form_term_map_parent'),
                            'description' => acf_maybe_get($row, 'field_acfe_form_term_map_description'),
                            'acf_fields'  => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_term_load_meta')),
                        ),
                    );
                
                    // description
                    $group = acf_maybe_get($row, 'field_acfe_form_term_save_description_group');
                    $description = $group['field_acfe_form_term_save_description'];
                    $description_custom = $group['field_acfe_form_term_save_description_custom'];
                
                    if($description === 'custom'){
                        $description = $description_custom;
                    }
                
                    // assign
                    $action['save']['description'] = $this->handle_field_tags($description);
                    
                    // load
                    $load_values = acf_maybe_get($row, 'field_acfe_form_term_load_values');
                    
                    // reset load if disabled
                    if(!$load_values){
                        unset($action['load']);
                        
                    }else{
                        
                        // load loop
                        foreach(array_keys($action['load']) as $key){
                            
                            $value = $action['load'][ $key ];
                            
                            // assign save key with {field:field_abcdef123456}
                            if(isset($action['save'][ $key ]) && !empty($value) && is_string($value) && acf_is_field_key($value)){
                                $action['save'][ $key ] = "{field:$value}";
                            }
                            
                        }
                        
                    }
                
                    // append action
                    $item['actions'][] = $action;
                
                    break;
                }
            
                /**
                 * user
                 */
                case 'user':{
                
                    $action = array(
                        'action' => 'user',
                        'type'   => acf_maybe_get($row, 'field_acfe_form_user_action'), // insert_user | update_user | log_user
                        'name'   => acf_maybe_get($row, 'field_acfe_form_user_custom_alias'),
                        'login'   => array(
                            'type'         => acf_maybe_get($row, 'field_acfe_form_user_log_type'),
                            'user'         => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_login_user')),
                            'pass'         => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_login_pass')),
                            'remember'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_login_remember')),
                        ),
                        'save'   => array(
                            'target'       => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_target')),
                            'user_email'   => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_email')),
                            'user_login'   => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_username')),
                            'user_pass'    => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_password')),
                            'first_name'   => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_first_name')),
                            'last_name'    => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_last_name')),
                            'nickname'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_nickname')),
                            'display_name' => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_display_name')),
                            'user_url'     => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_website')),
                            'description'  => '',
                            'role'         => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_save_role')),
                            'log_user'     => false,
                            'acf_fields'   => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_user_save_meta')),
                        ),
                        'load'   => array(
                            'source'       => $this->handle_field_tags(acf_maybe_get($row, 'field_acfe_form_user_load_source')),
                            'user_email'   => acf_maybe_get($row, 'field_acfe_form_user_map_email'),
                            'user_login'   => acf_maybe_get($row, 'field_acfe_form_user_map_username'),
                            'user_pass'    => acf_maybe_get($row, 'field_acfe_form_user_map_password'),
                            'first_name'   => acf_maybe_get($row, 'field_acfe_form_user_map_first_name'),
                            'last_name'    => acf_maybe_get($row, 'field_acfe_form_user_map_last_name'),
                            'nickname'     => acf_maybe_get($row, 'field_acfe_form_user_map_nickname'),
                            'display_name' => acf_maybe_get($row, 'field_acfe_form_user_map_display_name'),
                            'user_url'     => acf_maybe_get($row, 'field_acfe_form_user_map_website'),
                            'description'  => acf_maybe_get($row, 'field_acfe_form_user_map_description'),
                            'role'         => acf_maybe_get($row, 'field_acfe_form_user_map_role'),
                            'acf_fields'   => $this->handle_acf_fields(acf_maybe_get($row, 'field_acfe_form_user_load_meta')),
                        ),
                    );
                
                    // description
                    $group = acf_maybe_get($row, 'field_acfe_form_user_save_description_group');
                    $description = $group['field_acfe_form_user_save_description'];
                    $description_custom = $group['field_acfe_form_user_save_description_custom'];
                
                    if($description === 'custom'){
                        $description = $description_custom;
                    }
                
                    // assign
                    $action['save']['description'] = $this->handle_field_tags($description);
                    
                    // load
                    $load_values = acf_maybe_get($row, 'field_acfe_form_user_load_values');
                    
                    // reset load if disabled
                    if(!$load_values){
                        unset($action['load']);
                        
                    }else{
                        
                        // load loop
                        foreach(array_keys($action['load']) as $key){
                            
                            $value = $action['load'][ $key ];
                            
                            // assign save key with {field:field_abcdef123456}
                            if(isset($action['save'][ $key ]) && !empty($value) && is_string($value) && acf_is_field_key($value)){
                                $action['save'][ $key ] = "{field:$value}";
                            }
                            
                        }
                        
                    }
                
                    // append action
                    $item['actions'][] = $action;
                
                    break;
                }
            
            }
        
        }
    
        return $item;
        
    }
    
    
    /**
     * handle_field_tags
     *
     * @param $field
     *
     * @return mixed|string
     */
    function handle_field_tags($field){
        
        // array
        if(is_array($field)){
            foreach(array_keys($field) as $k){
                $field[ $k ] = $this->handle_field_tags($field[ $k ]);
            }
        }
        
        // not string or empty
        if(!is_string($field) || empty($field)){
            return $field;
        }
        
        // direct tags
        $search_replace = array(
            'generated_id'        => '{generated_id}',
            '#generated_id'       => '#{generated_id}',
            'generate_password'   => '{generate_password}',
            'current_post'        => '{post}',
            'current_post_parent' => '{post:post_parent}',
            'current_post_author' => '{post:post_author}',
            'current_term'        => '{term}',
            'current_term_parent' => '{term:parent}',
            'current_user'        => '{user}',
        );
        
        // loop
        foreach($search_replace as $search => $replace){
            if($field === $search){
                $field = $replace;
            }
        }
        
        // field_abcdef123456
        if(is_string($field) && preg_match('/^field_[a-zA-Z0-9]+/', $field) && acf_is_field_key($field)){
            $field = "{field:{$field}}";
        }
        
        // return
        return $field;
        
    }
    
    
    /**
     * handle_acf_fields
     *
     * @param $fields
     *
     * @return array|mixed
     */
    function handle_acf_fields($fields){
        
        if(!is_array($fields) || empty($fields)){
            return $fields;
        }
        
        $is_clone_enabled = acf_is_filter_enabled('clone');
        
        if($is_clone_enabled){
            acf_disable_filter('clone');
        }
        
        // loop over fields and check if value is a acf_field_key, then use acf_get_field() to check if the field type is a group, if it is a group then append the sub_fields to the main $fields
        foreach(array_keys($fields) as $k){
            
            $field_key = $fields[ $k ];
            
            // check if field value is a acf field key
            if(!is_string($field_key) || !preg_match('/^field_[a-zA-Z0-9]+/', $field_key) || !acf_is_field_key($field_key)){
                continue;
            }
            
            // get field
            $field = acf_get_field($field_key);
            
            // validate
            if(!$field){
                continue;
            }
            
            // check if field is a group
            if($field['type'] !== 'group'){
                continue;
            }
            
            // remove main group field from values
            unset($fields[ $k ]);
            
            if(empty($field['sub_fields'])){
                continue;
            }
            
            $sub_fields = acfe_get_fields_details_recursive($field['sub_fields'], array($this, 'get_fields_details'));
            
            if(!empty($sub_fields)){
                
                foreach($sub_fields as $sub_field){
                    $fields[] = $sub_field['key'];
                }
                
            }
            
        }
        
        if($is_clone_enabled){
            acf_enable_filter('clone');
        }
        
        return $fields;
        
    }
    
    
    /**
     * get_fields_details
     *
     * @param $field
     *
     * @return false
     */
    function get_fields_details($field){
        
        // disallow tab, message, accordion
        if(in_array($field['type'], array('tab', 'message', 'accordion'))){
            return false;
        }
        
        // disallow subfields for repeater/flexible content
        if(in_array($field['type'], array('repeater', 'flexible_content'))){
            $field['sub_fields'] = array();
        }
        
        return $field;
        
    }
    
    
    /**
     * remove_v2_field_group
     *
     * Remove old local field group for 0.9
     */
    function remove_v2_field_group(){
        acf_remove_local_field_group('group_acfe_dynamic_form');
    }
    
    
    /**
     * add_v2_field_group
     *
     * Add old local field group for 0.9
     */
    function add_v2_field_group(){
        
        $layouts = array();
        
        $layouts['layout_custom'] = array(
            'key' => 'layout_custom',
            'name' => 'custom',
            'label' => 'Custom action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_custom_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/custom-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
             * Layout: Custom Action
             */
                array(
                    'key' => 'field_acfe_form_custom_action_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_custom_action',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_action',
                    'type' => 'acfe_slug',
                    'instructions' => __('Set a unique action slug.', 'acfe'),
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'my-custom-action',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            ),
            'min' => '',
            'max' => '',
        );
        
        $layouts['layout_email'] = array(
            'key' => 'layout_email',
            'name' => 'email',
            'label' => 'Email action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_email_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/e-mail-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
                 * Layout: Email Action
                 */
                array(
                    'key' => 'field_acfe_form_email_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_email_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => __('(Optional) Target this action using hooks.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Email',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                
                /*
                 * Layout: Email Send
                 */
                array(
                    'key' => 'field_acfe_form_email_tab_email',
                    'label' => 'Email',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_email_from',
                    'label' => 'From',
                    'name' => 'acfe_form_email_from',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Name <email@domain.com>',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_to',
                    'label' => 'To',
                    'name' => 'acfe_form_email_to',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'email@domain.com',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_reply_to',
                    'label' => 'Reply to',
                    'name' => 'acfe_form_email_reply_to',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Name <email@domain.com>',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_cc',
                    'label' => 'Cc',
                    'name' => 'acfe_form_email_cc',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'email@domain.com',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_bcc',
                    'label' => 'Bcc',
                    'name' => 'acfe_form_email_bcc',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'email@domain.com',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_subject',
                    'label' => 'Subject',
                    'name' => 'acfe_form_email_subject',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_content',
                    'label' => 'Content',
                    'name' => 'acfe_form_email_content',
                    'type' => 'wysiwyg',
                    'instructions' => 'Fields values may be included using <code>{field:field_key}</code> <code>{field:title}</code>. All fields may be included using <code>{fields}</code>.<br />See "Cheatsheet" tab for advanced usage.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                
                /*
                 * Layout: Email Attachments
                 */
                array(
                    'key' => 'field_acfe_form_email_tab_attachments',
                    'label' => 'Attachments',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_email_files',
                    'label' => 'Dynamic files',
                    'name' => 'acfe_form_email_files',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'acfe_repeater_stylised_button' => 0,
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'table',
                    'button_label' => 'Add file',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_email_file',
                            'label' => 'File',
                            'name' => 'acfe_form_email_file',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 0,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'ajax' => 0,
                            'placeholder' => '',
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_email_file_delete',
                            'label' => 'Delete file',
                            'name' => 'acfe_form_email_file_delete',
                            'type' => 'true_false',
                            'instructions' => '',
                            'required' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'message' => 'Delete once submitted',
                            'default_value' => 0,
                            'ui' => 1,
                            'ui_on_text' => '',
                            'ui_off_text' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_email_files_static',
                    'label' => 'Static files',
                    'name' => 'acfe_form_email_files_static',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'acfe_repeater_stylised_button' => 0,
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'table',
                    'button_label' => 'Add file',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_email_file_static',
                            'label' => 'File',
                            'name' => 'acfe_form_email_file_static',
                            'type' => 'file',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'return_format' => 'id',
                        ),
                    ),
                ),
            
            ),
            'min' => '',
            'max' => '',
        );
        
        $layouts['layout_post'] = array(
            'key' => 'layout_post',
            'name' => 'post',
            'label' => 'Post action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_post_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/post-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
                 * Layout: Post Action
                 */
                array(
                    'key' => 'field_acfe_form_post_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_post_action',
                    'label' => 'Action',
                    'name' => 'acfe_form_post_action',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'insert_post' => 'Create post',
                        'update_post' => 'Update post',
                    ),
                    'default_value' => 'insert_post',
                ),
                array(
                    'key' => 'field_acfe_form_post_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => '(Optional) Target this action using hooks.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Post',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                
                /*
                 * Layout: Post Save
                 */
                array(
                    'key' => 'field_acfe_form_post_tab_save',
                    'label' => 'Save',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_post_save_target',
                    'label' => 'Target',
                    'name' => 'acfe_form_post_save_target',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_action',
                                'operator' => '==',
                                'value' => 'update_post',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_post',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_type',
                    'label' => 'Post type',
                    'name' => 'acfe_form_post_save_post_type',
                    'type' => 'acfe_post_types',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_type',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'post_type' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'return_format' => 'name',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 0,
                    'ui' => 1,
                    'choices' => array(
                    ),
                    'ajax' => 0,
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_type_message',
                    'label' => 'Post type',
                    'name' => 'acfe_form_post_map_post_type_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_type',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_status',
                    'label' => 'Post status',
                    'name' => 'acfe_form_post_save_post_status',
                    'type' => 'acfe_post_statuses',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_status',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'post_status' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'return_format' => 'name',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 0,
                    'ui' => 1,
                    'choices' => array(
                    ),
                    'ajax' => 0,
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_status_message',
                    'label' => 'Post status',
                    'name' => 'acfe_form_post_map_post_status_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_status',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                
                array(
                    'key' => 'field_acfe_form_post_save_post_title',
                    'label' => 'Post title',
                    'name' => 'acfe_form_post_save_post_title',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'generated_id'  => 'Generated ID',
                        '#generated_id' => '#Generated ID',
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_title',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                ),
                
                array(
                    'key' => 'field_acfe_form_post_map_post_title_message',
                    'label' => 'Post title',
                    'name' => 'acfe_form_post_map_post_title_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_title',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_name',
                    'label' => 'Post slug',
                    'name' => 'acfe_form_post_save_post_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'generated_id' => 'Generated ID',
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_name',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                ),
                
                array(
                    'key' => 'field_acfe_form_post_map_post_name_message',
                    'label' => 'Post slug',
                    'name' => 'acfe_form_post_map_post_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_name',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_content_group',
                    'label' => 'Post content',
                    'name' => 'acfe_form_post_save_post_content_group',
                    'type' => 'group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_content',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_post_save_post_content',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_content',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'custom' => 'WYSIWYG editor',
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_post_save_post_content_custom',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_content_custom',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_form_post_save_post_content',
                                        'operator' => '==',
                                        'value' => 'custom',
                                    ),
                                ),
                            ),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'tabs' => 'all',
                            'toolbar' => 'full',
                            'media_upload' => 1,
                            'delay' => 0,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_content_message',
                    'label' => 'Post content',
                    'name' => 'acfe_form_post_map_post_content_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_content',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_excerpt_group',
                    'label' => 'Post excerpt',
                    'name' => 'acfe_form_post_save_post_excerpt_group',
                    'type' => 'group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_excerpt',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_post_save_post_excerpt',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_excerpt',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'custom' => 'Textarea',
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_post_save_post_excerpt_custom',
                            'label' => '',
                            'name' => 'acfe_form_post_save_post_excerpt_custom',
                            'type' => 'textarea',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_form_post_save_post_excerpt',
                                        'operator' => '==',
                                        'value' => 'custom',
                                    ),
                                ),
                            ),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_excerpt_message',
                    'label' => 'Post excerpt',
                    'name' => 'acfe_form_post_map_post_excerpt_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_excerpt',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_author',
                    'label' => 'Post author',
                    'name' => 'acfe_form_post_save_post_author',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_author',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_author_message',
                    'label' => 'Post author',
                    'name' => 'acfe_form_post_map_post_author_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_author',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_parent',
                    'label' => 'Post parent',
                    'name' => 'acfe_form_post_save_post_parent',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_parent',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_parent_message',
                    'label' => 'Post parent',
                    'name' => 'acfe_form_post_map_post_parent_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_parent',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_post_terms',
                    'label' => 'Post terms',
                    'name' => 'acfe_form_post_save_post_terms',
                    'type' => 'acfe_taxonomy_terms',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_terms',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'taxonomy' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'return_format' => 'id',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 1,
                    'ui' => 1,
                    'ajax' => 0,
                    'choices' => array(
                    ),
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_terms_message',
                    'label' => 'Post terms',
                    'name' => 'acfe_form_post_map_post_terms_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_map_post_terms',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_save_meta',
                    'label' => 'Save ACF fields',
                    'name' => 'acfe_form_post_save_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should be saved as metadata',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
                
                /*
                 * Layout: Post Load
                 */
                array(
                    'key' => 'acfe_form_post_tab_load',
                    'label' => 'Load',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_post_load_values',
                    'label' => 'Load Values',
                    'name' => 'acfe_form_post_load_values',
                    'type' => 'true_false',
                    'instructions' => 'Fill inputs with values',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_post_load_source',
                    'label' => 'Source',
                    'name' => 'acfe_form_post_load_source',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_post',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                
                array(
                    'key' => 'field_acfe_form_post_map_post_type',
                    'label' => 'Post type',
                    'name' => 'acfe_form_post_map_post_type',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_status',
                    'label' => 'Post status',
                    'name' => 'acfe_form_post_map_post_status',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_title',
                    'label' => 'Post title',
                    'name' => 'acfe_form_post_map_post_title',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_name',
                    'label' => 'Post slug',
                    'name' => 'acfe_form_post_map_post_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_content',
                    'label' => 'Post content',
                    'name' => 'acfe_form_post_map_post_content',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_excerpt',
                    'label' => 'Post excerpt',
                    'name' => 'acfe_form_post_map_post_excerpt',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_author',
                    'label' => 'Post author',
                    'name' => 'acfe_form_post_map_post_author',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_parent',
                    'label' => 'Post parent',
                    'name' => 'acfe_form_post_map_post_parent',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_map_post_terms',
                    'label' => 'Post terms',
                    'name' => 'acfe_form_post_map_post_terms',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_post_load_meta',
                    'label' => 'Load ACF fields',
                    'name' => 'acfe_form_post_load_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should have their values loaded',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_post_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
            
            ),
            'min' => '',
            'max' => '',
        );
        
        $layouts['layout_redirect'] = array(
            'key' => 'layout_redirect',
            'name' => 'redirect',
            'label' => 'Redirect action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_redirect_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/redirect-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
                 * Layout: Redirect Action
                 */
                array(
                    'key' => 'field_acfe_form_redirect_action_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_redirect_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => __('(Optional) Target this action using hooks.', 'acfe'),
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Redirect',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_redirect_url',
                    'label' => 'Action URL',
                    'name' => 'acfe_form_redirect_url',
                    'type' => 'text',
                    'instructions' => 'The URL to redirect to. See "Cheatsheet" tab for all available template tags.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            
            ),
            'min' => '',
            'max' => '',
        );
        
        $layouts['layout_term'] = array(
            'key' => 'layout_term',
            'name' => 'term',
            'label' => 'Term action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_term_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/term-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
                 * Layout: Term Action
                 */
                array(
                    'key' => 'field_acfe_form_term_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'acfe_form_term_action',
                    'label' => 'Action',
                    'name' => 'acfe_form_term_action',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'insert_term' => 'Create term',
                        'update_term' => 'Update term',
                    ),
                    'default_value' => 'insert_term',
                ),
                array(
                    'key' => 'field_acfe_form_term_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => '(Optional) Target this action using hooks.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Term',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                
                /*
                 * Layout: Term Save
                 */
                array(
                    'key' => 'field_acfe_form_term_tab_save',
                    'label' => 'Save',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_term_save_target',
                    'label' => 'Target',
                    'name' => 'acfe_form_term_save_target',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'acfe_form_term_action',
                                'operator' => '==',
                                'value' => 'update_term',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_term',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_term_save_name',
                    'label' => 'Name',
                    'name' => 'acfe_form_term_save_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_name',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_map_name_message',
                    'label' => 'Name',
                    'name' => 'acfe_form_term_map_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_name',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_term_save_slug',
                    'label' => 'Slug',
                    'name' => 'acfe_form_term_save_slug',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_slug',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_map_slug_message',
                    'label' => 'Slug',
                    'name' => 'acfe_form_term_map_slug_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_slug',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_term_save_taxonomy',
                    'label' => 'Taxonomy',
                    'name' => 'acfe_form_term_save_taxonomy',
                    'type' => 'acfe_taxonomies',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_taxonomy',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'taxonomy' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'return_format' => 'name',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 0,
                    'ui' => 1,
                    'choices' => array(
                    ),
                    'ajax' => 0,
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_term_map_taxonomy_message',
                    'label' => 'Taxonomy',
                    'name' => 'acfe_form_term_map_taxonomy_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_taxonomy',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_term_save_parent',
                    'label' => 'Parent',
                    'name' => 'acfe_form_term_save_parent',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_parent',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_term_map_parent_message',
                    'label' => 'Parent',
                    'name' => 'acfe_form_term_map_parent_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_parent',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_term_save_description_group',
                    'label' => 'Description',
                    'name' => 'acfe_form_term_save_description_group',
                    'type' => 'group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_description',
                                'operator' => '==empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_term_save_description',
                            'label' => '',
                            'name' => 'acfe_form_term_save_description',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'custom' => 'WYSIWYG Editor',
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_term_save_description_custom',
                            'label' => '',
                            'name' => 'acfe_form_term_save_description_custom',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 1,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_form_term_save_description',
                                        'operator' => '==',
                                        'value' => 'custom',
                                    ),
                                ),
                            ),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'tabs' => 'all',
                            'toolbar' => 'full',
                            'media_upload' => 1,
                            'delay' => 0,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_map_description_message',
                    'label' => 'Description',
                    'name' => 'acfe_form_term_map_description_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_map_description',
                                'operator' => '!=empty',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_term_save_meta',
                    'label' => 'Save ACF fields',
                    'name' => 'acfe_form_term_save_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should be saved as metadata',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
                
                /*
                 * Layout: Term Load
                 */
                array(
                    'key' => 'field_acfe_form_term_tab_load',
                    'label' => 'Load',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_term_load_values',
                    'label' => 'Load Values',
                    'name' => 'acfe_form_term_load_values',
                    'type' => 'true_false',
                    'instructions' => 'Fill inputs with values',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_term_load_source',
                    'label' => 'Source',
                    'name' => 'acfe_form_term_load_source',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_term',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_term_map_name',
                    'label' => 'Name',
                    'name' => 'acfe_form_term_map_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_map_slug',
                    'label' => 'Slug',
                    'name' => 'acfe_form_term_map_slug',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_map_taxonomy',
                    'label' => 'Taxonomy',
                    'name' => 'acfe_form_term_map_taxonomy',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_map_parent',
                    'label' => 'Parent',
                    'name' => 'acfe_form_term_map_parent',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_map_description',
                    'label' => 'Description',
                    'name' => 'acfe_form_term_map_description',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_term_load_meta',
                    'label' => 'Load ACF fields',
                    'name' => 'acfe_form_term_load_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should have their values loaded',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_term_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
            
            ),
            'min' => '',
            'max' => '',
        );
        
        $layouts['layout_user'] = array(
            'key' => 'layout_user',
            'name' => 'user',
            'label' => 'User action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_user_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/user-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
                 * Layout: User Action
                 */
                array(
                    'key' => 'field_acfe_form_user_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_user_action',
                    'label' => 'Action',
                    'name' => 'acfe_form_user_action',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'insert_user'   => 'Create user',
                        'update_user'   => 'Update user',
                        'log_user'      => 'Log user',
                    ),
                    'default_value' => 'insert_post',
                ),
                array(
                    'key' => 'field_acfe_form_user_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => '(Optional) Target this action using hooks.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'User',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                
                /*
                 * Layout: User Login
                 */
                array(
                    'key' => 'field_acfe_form_user_tab_login',
                    'label' => 'Login',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_log_type',
                    'label' => 'Login type',
                    'name' => 'acfe_form_user_log_type',
                    'type' => 'radio',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'email'             => 'E-mail',
                        'username'          => 'Username',
                        'email_username'    => 'E-mail or username',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'email',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_login_user',
                    'label' => 'Login',
                    'name' => 'acfe_form_user_save_login_user',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_login_pass',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_save_login_pass',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_login_remember',
                    'label' => 'Remember me',
                    'name' => 'acfe_form_user_save_login_remember',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                
                /*
                 * Layout: User Save
                 */
                array(
                    'key' => 'field_acfe_form_user_tab_save',
                    'label' => 'Save',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_save_target',
                    'label' => 'Target',
                    'name' => 'acfe_form_user_save_target',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '==',
                                'value' => 'update_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_user',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_user_save_email',
                    'label' => 'Email',
                    'name' => 'acfe_form_user_save_email',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_email',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_email_message',
                    'label' => 'Email',
                    'name' => 'acfe_form_user_map_email_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_email',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_username',
                    'label' => 'Username',
                    'name' => 'acfe_form_user_save_username',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_username',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_username_message',
                    'label' => 'Username',
                    'name' => 'acfe_form_user_map_username_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_username',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_password',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_save_password',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'generate_password' => 'Generate password',
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_password',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_password_message',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_map_password_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_password',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_first_name',
                    'label' => 'First name',
                    'name' => 'acfe_form_user_save_first_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_first_name',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_first_name_message',
                    'label' => 'First name',
                    'name' => 'acfe_form_user_map_first_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_first_name',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_last_name',
                    'label' => 'Last name',
                    'name' => 'acfe_form_user_save_last_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_last_name',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_last_name_message',
                    'label' => 'Last name',
                    'name' => 'acfe_form_user_map_last_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_last_name',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_nickname',
                    'label' => 'Nickname',
                    'name' => 'acfe_form_user_save_nickname',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_nickname',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_nickname_message',
                    'label' => 'Nickname',
                    'name' => 'acfe_form_user_map_nickname_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_nickname',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_display_name',
                    'label' => 'Display name',
                    'name' => 'acfe_form_user_save_display_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_display_name',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_display_name_message',
                    'label' => 'Display name',
                    'name' => 'acfe_form_user_map_display_name_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_display_name',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_website',
                    'label' => 'Website',
                    'name' => 'acfe_form_user_save_website',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_website',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_website_message',
                    'label' => 'Website',
                    'name' => 'acfe_form_user_map_website_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_website',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_description_group',
                    'label' => 'Description',
                    'name' => 'acfe_form_user_save_description_group',
                    'type' => 'group',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_description',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_user_save_description',
                            'label' => '',
                            'name' => 'acfe_form_user_save_description',
                            'type' => 'select',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'choices' => array(
                                'custom' => 'WYSIWYG Editor',
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 1,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'placeholder' => 'Default',
                            'ajax' => 0,
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_user_save_description_custom',
                            'label' => '',
                            'name' => 'acfe_form_user_save_description_custom',
                            'type' => 'wysiwyg',
                            'instructions' => '',
                            'required' => 1,
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field' => 'field_acfe_form_user_save_description',
                                        'operator' => '==',
                                        'value' => 'custom',
                                    ),
                                ),
                            ),
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'tabs' => 'all',
                            'toolbar' => 'full',
                            'media_upload' => 1,
                            'delay' => 0,
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_description_message',
                    'label' => 'Description',
                    'name' => 'acfe_form_user_map_description_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_description',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_role',
                    'label' => 'Role',
                    'name' => 'acfe_form_user_save_role',
                    'type' => 'acfe_user_roles',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_role',
                                'operator' => '==empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'user_role' => '',
                    'field_type' => 'select',
                    'default_value' => '',
                    'allow_null' => 1,
                    'placeholder' => 'Default',
                    'multiple' => 0,
                    'ui' => 1,
                    'choices' => array(
                    ),
                    'ajax' => 0,
                    'layout' => '',
                    'toggle' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_user_map_role_message',
                    'label' => 'Role',
                    'name' => 'acfe_form_user_map_role_message',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_map_role',
                                'operator' => '!=empty',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_save_meta',
                    'label' => 'Save ACF fields',
                    'name' => 'acfe_form_user_save_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should be saved as metadata',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                
                /*
                 * Layout: User Load
                 */
                array(
                    'key' => 'acfe_form_user_tab_load',
                    'label' => 'Load',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_load_values',
                    'label' => 'Load Values',
                    'name' => 'acfe_form_user_load_values',
                    'type' => 'true_false',
                    'instructions' => 'Fill inputs with values',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_user_load_source',
                    'label' => 'Source',
                    'name' => 'acfe_form_user_load_source',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => 'current_user',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                
                array(
                    'key' => 'field_acfe_form_user_map_email',
                    'label' => 'Email',
                    'name' => 'acfe_form_user_map_email',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_username',
                    'label' => 'Username',
                    'name' => 'acfe_form_user_map_username',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_password',
                    'label' => 'Password',
                    'name' => 'acfe_form_user_map_password',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_first_name',
                    'label' => 'First name',
                    'name' => 'acfe_form_user_map_first_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_last_name',
                    'label' => 'Last name',
                    'name' => 'acfe_form_user_map_last_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_nickname',
                    'label' => 'Nickname',
                    'name' => 'acfe_form_user_map_nickname',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_display_name',
                    'label' => 'Display name',
                    'name' => 'acfe_form_user_map_display_name',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_website',
                    'label' => 'Website',
                    'name' => 'acfe_form_user_map_website',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_description',
                    'label' => 'Description',
                    'name' => 'acfe_form_user_map_description',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_map_role',
                    'label' => 'Role',
                    'name' => 'acfe_form_user_map_role',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 1,
                    'multiple' => 0,
                    'ui' => 1,
                    'return_format' => 'value',
                    'placeholder' => 'Default',
                    'ajax' => 0,
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                            array(
                                'field' => 'field_acfe_form_user_action',
                                'operator' => '!=',
                                'value' => 'log_user',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_user_load_meta',
                    'label' => 'Load ACF fields',
                    'name' => 'acfe_form_user_load_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should have their values loaded',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_user_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
            
            ),
            'min' => '',
            'max' => '',
        );
        
        $layouts['layout_option'] = array(
            'key' => 'layout_option',
            'name' => 'option',
            'label' => 'Option action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_options_action_docs',
                    'label' => '',
                    'name' => 'acfe_form_action_docs',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function(){
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/option-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
                
                /*
                 * Layout: Option Action
                 */
                array(
                    'key' => 'field_acfe_form_option_tab_action',
                    'label' => 'Action',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_option_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
                    'type' => 'acfe_slug',
                    'instructions' => '(Optional) Target this action using hooks.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Option',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                
                /*
                 * Layout: Option Save
                 */
                array(
                    'key' => 'field_acfe_form_option_tab_save',
                    'label' => 'Save',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_option_save_target',
                    'label' => 'Target',
                    'name' => 'acfe_form_option_save_target',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_option_save_meta',
                    'label' => 'Save ACF fields',
                    'name' => 'acfe_form_option_save_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should be saved as metadata',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
                
                /*
                 * Layout: Option Load
                 */
                array(
                    'key' => 'acfe_form_option_tab_load',
                    'label' => 'Load',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_option_load_values',
                    'label' => 'Load Values',
                    'name' => 'acfe_form_option_load_values',
                    'type' => 'true_false',
                    'instructions' => 'Fill inputs with values',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_option_load_source',
                    'label' => 'Source',
                    'name' => 'acfe_form_option_load_source',
                    'type' => 'select',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_option_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => '',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                    'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                    'allow_custom' => 1,
                ),
                array(
                    'key' => 'field_acfe_form_option_load_meta',
                    'label' => 'Load ACF fields',
                    'name' => 'acfe_form_option_load_meta',
                    'type' => 'checkbox',
                    'instructions' => 'Choose which ACF fields should have their values loaded',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_option_load_values',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'allow_custom' => 0,
                    'default_value' => array(
                    ),
                    'layout' => 'vertical',
                    'toggle' => 1,
                    'return_format' => 'value',
                    'save_custom' => 0,
                ),
            
            ),
            'min' => '',
            'max' => '',
        );
        
        acf_add_local_field_group(array(
            'key' => 'group_acfe_dynamic_form',
            'title' => 'Dynamic Form',
            'acfe_display_title' => '',
            'fields' => array(
                
                array(
                    'key' => 'field_acfe_form_active',
                    'label' => '',
                    'name' => 'acfe_form_active',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                
                /*
                 * Actions
                 */
                array(
                    'key' => 'field_acfe_form_tab_general',
                    'label' => 'General',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-no-preference' => true,
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_name',
                    'label' => 'Form name',
                    'name' => 'acfe_form_name',
                    'type' => 'acfe_slug',
                    'instructions' => 'The unique form slug',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_field_groups',
                    'label' => 'Field groups',
                    'name' => 'acfe_form_field_groups',
                    'type' => 'select',
                    'instructions' => 'Render & map fields of the following field groups',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 1,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_form_actions',
                    'label' => 'Actions',
                    'name' => 'acfe_form_actions',
                    'type' => 'flexible_content',
                    'instructions' => 'Add actions on form submission',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_flexible_stylised_button' => 1,
                    'layouts' => $layouts,
                    'button_label' => 'Add action',
                    'min' => '',
                    'max' => '',
                ),
                
                /*
                 * Settings
                 */
                array(
                    'key' => 'field_acfe_form_tab_settings',
                    'label' => 'Settings',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_field_groups_rules',
                    'label' => 'Field groups locations rules',
                    'name' => 'acfe_form_field_groups_rules',
                    'type' => 'true_false',
                    'instructions' => 'Apply field groups locations rules for front-end display',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_form_element',
                    'label' => 'Form element',
                    'name' => 'acfe_form_form_element',
                    'type' => 'true_false',
                    'instructions' => 'Whether or not to create a <code>&lt;form&gt;</code> element',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_attributes',
                    'label' => 'Form attributes',
                    'name' => 'acfe_form_attributes',
                    'type' => 'group',
                    'instructions' => 'Form class and id',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_element',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_attributes_class',
                            'label' => '',
                            'name' => 'acfe_form_attributes_class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => 'acf-form',
                            'placeholder' => '',
                            'prepend' => 'class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_form_attributes_id',
                            'label' => '',
                            'name' => 'acfe_form_attributes_id',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'id',
                            'append' => '',
                            'maxlength' => '',
                        ),
                    
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_fields_attributes',
                    'label' => 'Fields class',
                    'name' => 'acfe_form_fields_attributes',
                    'type' => 'group',
                    'instructions' => 'Add class to all fields',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'conditional_logic' => array(),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_fields_wrapper_class',
                            'label' => '',
                            'name' => 'acfe_form_fields_wrapper_class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'wrapper class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_form_fields_class',
                            'label' => '',
                            'name' => 'acfe_form_fields_class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'input class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_form_submit',
                    'label' => 'Submit button',
                    'name' => 'acfe_form_form_submit',
                    'type' => 'true_false',
                    'instructions' => 'Whether or not to create a form submit button. Defaults to true',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_submit_value',
                    'label' => 'Submit value',
                    'name' => 'acfe_form_submit_value',
                    'type' => 'text',
                    'instructions' => 'The text displayed on the submit button',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_submit',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'Submit',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_html_submit_button',
                    'label' => 'Submit button',
                    'name' => 'acfe_form_html_submit_button',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'HTML used to render the submit button.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_submit',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                array(
                    'key' => 'field_acfe_form_html_submit_spinner',
                    'label' => 'Submit spinner',
                    'name' => 'acfe_form_html_submit_spinner',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'HTML used to render the submit button loading spinner.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_submit',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '<span class="acf-spinner"></span>',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                array(
                    'key' => 'field_acfe_form_honeypot',
                    'label' => 'Honeypot',
                    'name' => 'acfe_form_honeypot',
                    'type' => 'true_false',
                    'instructions' => 'Whether to include a hidden input field to capture non human form submission. Defaults to true.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_kses',
                    'label' => 'Kses',
                    'name' => 'acfe_form_kses',
                    'type' => 'true_false',
                    'instructions' => 'Whether or not to sanitize all $_POST data with the wp_kses_post() function. Defaults to true.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_uploader',
                    'label' => 'Uploader',
                    'name' => 'acfe_form_uploader',
                    'type' => 'radio',
                    'instructions' => 'Whether to use the WP uploader or a basic input for image and file fields. Defaults to \'wp\'
    Choices of \'wp\' or \'basic\'.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'default' => 'Default',
                        'wp' => 'WordPress',
                        'basic' => 'Browser',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'default',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_form_field_el',
                    'label' => 'Field element',
                    'name' => 'acfe_form_form_field_el',
                    'type' => 'radio',
                    'instructions' => 'Determines element used to wrap a field. Defaults to \'div\'',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'div' => '&lt;div&gt;',
                        'tr' => '&lt;tr&gt;',
                        'td' => '&lt;td&gt;',
                        'ul' => '&lt;ul&gt;',
                        'ol' => '&lt;ol&gt;',
                        'dl' => '&lt;dl&gt;',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'div',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_label_placement',
                    'label' => 'Label placement',
                    'name' => 'acfe_form_label_placement',
                    'type' => 'radio',
                    'instructions' => 'Determines where field labels are places in relation to fields. Defaults to \'top\'. <br />
    Choices of \'top\' (Above fields) or \'left\' (Beside fields)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'top' => 'Top',
                        'left' => 'Left',
                        'hidden' => 'Hidden',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'top',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_instruction_placement',
                    'label' => 'Instruction placement',
                    'name' => 'acfe_form_instruction_placement',
                    'type' => 'radio',
                    'instructions' => 'Determines where field instructions are places in relation to fields. Defaults to \'label\'. <br />
    Choices of \'label\' (Below labels) or \'field\' (Below fields)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'label' => 'Label',
                        'field' => 'Field',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'label',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                
                /*
                 * HTML
                 */
                array(
                    'key' => 'field_acfe_form_tab_html',
                    'label' => 'HTML',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_custom_html_enable',
                    'label' => 'Override Form render',
                    'name' => 'acfe_form_custom_html_enable',
                    'type' => 'true_false',
                    'instructions' => 'Override the native field groups HTML render',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => false,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_html_before_fields',
                    'label' => 'HTML Before render',
                    'name' => 'acfe_form_html_before_fields',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'Extra HTML to add before the fields',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                array(
                    'key' => 'field_acfe_form_custom_html',
                    'label' => 'HTML Form render',
                    'name' => 'acfe_form_custom_html',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'Render your own customized HTML.<br /><br />
    Field groups may be included using <code>{field_group:group_key}</code><br/><code>{field_group:Group title}</code><br/><br/>
    Fields may be included using <code>{field:field_key}</code><br/><code>{field:field_name}</code>',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 12,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_custom_html_enable',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_html_after_fields',
                    'label' => 'HTML After render',
                    'name' => 'acfe_form_html_after_fields',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'Extra HTML to add after the fields',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                
                /*
                 * Validation
                 */
                array(
                    'key' => 'field_acfe_form_tab_validation',
                    'label' => 'Validation',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_hide_error',
                    'label' => 'Hide general error',
                    'name' => 'acfe_form_hide_error',
                    'type' => 'true_false',
                    'instructions' => 'Hide the general error message: "Validation failed. 1 field requires attention"',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_hide_revalidation',
                    'label' => 'Hide successful re-validation',
                    'name' => 'acfe_form_hide_revalidation',
                    'type' => 'true_false',
                    'instructions' => 'Hide the successful notice when an error has been thrown',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_hide_unload',
                    'label' => 'Hide confirmation on exit',
                    'name' => 'acfe_form_hide_unload',
                    'type' => 'true_false',
                    'instructions' => 'Do not prompt user on page refresh',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_errors_position',
                    'label' => 'Fields errors position',
                    'name' => 'acfe_form_errors_position',
                    'type' => 'radio',
                    'instructions' => 'Choose where to display field errors',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'above' => 'Above fields',
                        'below' => 'Below fields',
                        'group' => 'Group errors',
                        'hide' => 'Hide errors',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'above',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_errors_class',
                    'label' => 'Fields errors class',
                    'name' => 'acfe_form_errors_class',
                    'type' => 'text',
                    'instructions' => 'Add class to error message',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_errors_position',
                                'operator' => '!=',
                                'value' => 'group',
                            ),
                            array(
                                'field' => 'field_acfe_form_errors_position',
                                'operator' => '!=',
                                'value' => 'hide',
                            ),
                        )
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                
                /*
                 * Submission
                 */
                array(
                    'key' => 'field_acfe_form_tab_submission',
                    'label' => 'Success Page',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_return',
                    'label' => 'Redirection',
                    'name' => 'acfe_form_return',
                    'type' => 'text',
                    'instructions' => 'The URL to be redirected to after the form is submitted. See "Cheatsheet" tab for all available template tags.<br/><br/><u>This setting is deprecated, use the new "Redirect Action" instead.</u>',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-enable-switch' => true
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_updated_hide_form',
                    'label' => 'Hide form',
                    'name' => 'acfe_form_updated_hide_form',
                    'type' => 'true_false',
                    'instructions' => 'Hide form on successful submission',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_updated_message',
                    'label' => 'Success message',
                    'name' => 'acfe_form_updated_message',
                    'type' => 'wysiwyg',
                    'instructions' => 'A message displayed above the form after being redirected. See "Cheatsheet" tab for all available template tags.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => __('Post updated', 'acf'),
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_html_updated_message',
                    'label' => 'Success wrapper HTML',
                    'name' => 'acfe_form_html_updated_message',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'HTML used to render the updated message.<br />
If used, you have to include the following code <code>%s</code> to print the actual "Success message" above.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '<div id="message" class="updated">%s</div>',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                
                /*
                 * Cheatsheet
                 */
                array(
                    'key' => 'field_acfe_form_tab_cheatsheet',
                    'label' => 'Cheatsheet',
                    'name' => '',
                    'type' => 'tab',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                
                array(
                    'key' => 'field_acfe_form_cheatsheet_field',
                    'label' => 'Field',
                    'name' => 'acfe_form_cheatsheet_field',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve user input from the current form',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_fields',
                    'label' => 'Fields',
                    'name' => 'acfe_form_cheatsheet_fields',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve all user inputs from the current form',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_get_field',
                    'label' => 'Get Field',
                    'name' => 'acfe_form_cheatsheet_get_field',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve ACF field value from database',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_get_option',
                    'label' => 'Get Option',
                    'name' => 'acfe_form_cheatsheet_get_option',
                    'type' => 'acfe_dynamic_render',
                    'value' => '',
                    'instructions' => 'Retrieve option value from database',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_request',
                    'label' => 'Request',
                    'name' => 'acfe_form_cheatsheet_request',
                    'type' => 'acfe_dynamic_render',
                    'value' => '',
                    'instructions' => 'Retrieve <code>$_REQUEST</code> value',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_query_var',
                    'label' => 'Query Var',
                    'name' => 'acfe_form_cheatsheet_query_var',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve query var values. Can be used to get data from previous action',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_form',
                    'label' => 'Form Settings',
                    'name' => 'acfe_form_cheatsheet_current_form',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current Dynamic Form data',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_actions_post',
                    'label' => 'Action Output: Post',
                    'name' => 'acfe_form_cheatsheet_actions_post',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'acfe_form_cheatsheet_actions_term',
                    'label' => 'Action Output: Term',
                    'name' => 'acfe_form_cheatsheet_actions_term',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'acfe_form_cheatsheet_actions_user',
                    'label' => 'Action Output: User',
                    'name' => 'acfe_form_cheatsheet_actions_user',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'acfe_form_cheatsheet_actions_email',
                    'label' => 'Action Output: Email',
                    'name' => 'acfe_form_cheatsheet_actions_email',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_post',
                    'label' => 'Current Post',
                    'name' => 'acfe_form_cheatsheet_current_post',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current post data (where the form is being printed)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_term',
                    'label' => 'Current Term',
                    'name' => 'acfe_form_cheatsheet_current_term',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current term data (where the form is being printed)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_user',
                    'label' => 'Current User',
                    'name' => 'acfe_form_cheatsheet_current_user',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve currently logged user data',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_author',
                    'label' => 'Current Author',
                    'name' => 'acfe_form_cheatsheet_current_author',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current post author data (where the form is being printed)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'acfe-form',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'acf_after_title',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'acfe_permissions' => '',
            'acfe_form' => 0,
            'acfe_meta' => '',
            'acfe_note' => '',
        ));
        
    }
    
}

acf_new_instance('acfe_module_form_upgrades');

endif;