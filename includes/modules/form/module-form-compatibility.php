<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_compatibility')):

class acfe_module_form_compatibility{
    
    function __construct(){
        
        // import
        add_filter('acfe/module/prepare_item_for_import/module=form', array($this, 'import_0_9'),   20);
        add_filter('acfe/module/prepare_item_for_import/module=form', array($this, 'import_0_8_5'), 10);
        
    }
    
    
    /**
     * import_0_9
     *
     * ACF Extended: 0.9
     *
     * acfe/module/prepare_item_for_import/module=form
     *
     * @param $item
     *
     * @return mixed
     */
    function import_0_9($item){
    
        // validate old args
        if(!isset($item['acfe_form_actions'])){
            return $item;
        }
        
        // generate old acf data
        $args = array();
        
        // extract old keys
        foreach($item as $key => $value){
            if(acfe_starts_with($key, 'acfe_form')){
                $args[ $key ] = acf_extract_var($item, $key);
            }
        }
        
        // default item
        $item = array(
            'ID'    => $item['ID'],
            'name'  => $item['name'],
            'label' => $item['label'],
            'title' => $item['title'],
        );
        
        // upgrade item
        $item = acf_get_instance('acfe_module_form_upgrades')->upgrade_v2_item_to_v3($item, $args);
        
        return $item;
        
    }
    
    
    /**
     * acfe_form_import
     *
     * acfe/module/prepare_item_for_import/module=form
     *
     * Module Dynamic Forms: Upgrade previous versions
     *
     * @since 0.8.5 (15/03/2020)
     */
    function import_0_8_5($args){
        
        // validate old args
        if(!isset($args['acfe_form_actions'])){
            return $args;
        }
        
        // ACF Extended: 0.8.5 Compatibility - Step 1
        // Groups upgrade
        $has_upgraded = false;
        
        $rules = array(
            
            // Post: title
            array(
                'group'             => 'field_acfe_form_post_save_post_title_group',
                'sub_field'         => 'field_acfe_form_post_save_post_title',
                'sub_field_custom'  => 'field_acfe_form_post_save_post_title_custom',
            ),
            
            // Post: name
            array(
                'group'             => 'field_acfe_form_post_save_post_name_group',
                'sub_field'         => 'field_acfe_form_post_save_post_name',
                'sub_field_custom'  => 'field_acfe_form_post_save_post_name_custom',
            ),
            
            // Term: name
            array(
                'group'             => 'field_acfe_form_term_save_name_group',
                'sub_field'         => 'field_acfe_form_term_save_name',
                'sub_field_custom'  => 'field_acfe_form_term_save_name_custom',
            ),
            
            // Term: slug
            array(
                'group'             => 'field_acfe_form_term_save_slug_group',
                'sub_field'         => 'field_acfe_form_term_save_slug',
                'sub_field_custom'  => 'field_acfe_form_term_save_slug_custom',
            ),
            
            // User: e-mail
            array(
                'group'             => 'field_acfe_form_user_save_email_group',
                'sub_field'         => 'field_acfe_form_user_save_email',
                'sub_field_custom'  => 'field_acfe_form_user_save_email_custom',
            ),
            
            // User: username
            array(
                'group'             => 'field_acfe_form_user_save_username_group',
                'sub_field'         => 'field_acfe_form_user_save_username',
                'sub_field_custom'  => 'field_acfe_form_user_save_username_custom',
            ),
            
            // User: password
            array(
                'group'             => 'field_acfe_form_user_save_password_group',
                'sub_field'         => 'field_acfe_form_user_save_password',
                'sub_field_custom'  => 'field_acfe_form_user_save_password_custom',
            ),
            
            // User: first name
            array(
                'group'             => 'field_acfe_form_user_save_first_name_group',
                'sub_field'         => 'field_acfe_form_user_save_first_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_first_name_custom',
            ),
            
            // User: last name
            array(
                'group'             => 'field_acfe_form_user_save_last_name_group',
                'sub_field'         => 'field_acfe_form_user_save_last_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_last_name_custom',
            ),
            
            // User: nickname
            array(
                'group'             => 'field_acfe_form_user_save_nickname_group',
                'sub_field'         => 'field_acfe_form_user_save_nickname',
                'sub_field_custom'  => 'field_acfe_form_user_save_nickname_custom',
            ),
            
            // User: display name
            array(
                'group'             => 'field_acfe_form_user_save_display_name_group',
                'sub_field'         => 'field_acfe_form_user_save_display_name',
                'sub_field_custom'  => 'field_acfe_form_user_save_display_name_custom',
            ),
            
            // User: website
            array(
                'group'             => 'field_acfe_form_user_save_website_group',
                'sub_field'         => 'field_acfe_form_user_save_website',
                'sub_field_custom'  => 'field_acfe_form_user_save_website_custom',
            ),
        
        );
        
        foreach(acf_get_array($args['acfe_form_actions']) as &$row){
            
            foreach($rules as $rule){
                
                if(!acf_maybe_get($row, $rule['group'])){
                    continue;
                }
                
                $value = null;
                $group = $row[$rule['group']];
                
                if(acf_maybe_get($group, $rule['sub_field']) === 'custom'){
                    $value = acf_maybe_get($group, $rule['sub_field_custom']);
                    
                }else{
                    $value = acf_maybe_get($group, $rule['sub_field']);
                }
                
                unset($row[$rule['group']]);
                
                $row[ $rule['sub_field'] ] = $value;
                
                $has_upgraded = true;
                
            }
            
        }
        
        // ACF Extended: 0.8.5 Compatibility - Step 2
        // Field mapping upgrade
        if($has_upgraded){
            
            // Rules
            $rules = array(
                
                array(
                    'load_values' => 'field_acfe_form_post_load_values',
                    'fields' => array(
                        'field_acfe_form_post_map_post_type'       => 'field_acfe_form_post_save_post_type',
                        'field_acfe_form_post_map_post_status'     => 'field_acfe_form_post_save_post_status',
                        'field_acfe_form_post_map_post_title'      => 'field_acfe_form_post_save_post_title',
                        'field_acfe_form_post_map_post_name'       => 'field_acfe_form_post_save_post_name',
                        'field_acfe_form_post_map_post_content'    => 'field_acfe_form_post_save_post_content',
                        'field_acfe_form_post_map_post_author'     => 'field_acfe_form_post_save_post_author',
                        'field_acfe_form_post_map_post_parent'     => 'field_acfe_form_post_save_post_parent',
                        'field_acfe_form_post_map_post_terms'      => 'field_acfe_form_post_save_post_terms',
                    )
                ),
                
                array(
                    'load_values' => 'field_acfe_form_term_load_values',
                    'fields' => array(
                        'field_acfe_form_term_map_name'            => 'field_acfe_form_term_save_name',
                        'field_acfe_form_term_map_slug'            => 'field_acfe_form_term_save_slug',
                        'field_acfe_form_term_map_taxonomy'        => 'field_acfe_form_term_save_taxonomy',
                        'field_acfe_form_term_map_parent'          => 'field_acfe_form_term_save_parent',
                        'field_acfe_form_term_map_description'     => 'field_acfe_form_term_save_description',
                    )
                ),
                
                array(
                    'load_values' => 'field_acfe_form_user_load_values',
                    'fields' => array(
                        'field_acfe_form_user_map_email'        => 'field_acfe_form_user_save_email',
                        'field_acfe_form_user_map_username'     => 'field_acfe_form_user_save_username',
                        'field_acfe_form_user_map_password'     => 'field_acfe_form_user_save_password',
                        'field_acfe_form_user_map_first_name'   => 'field_acfe_form_user_save_first_name',
                        'field_acfe_form_user_map_last_name'    => 'field_acfe_form_user_save_last_name',
                        'field_acfe_form_user_map_nickname'     => 'field_acfe_form_user_save_nickname',
                        'field_acfe_form_user_map_display_name' => 'field_acfe_form_user_save_display_name',
                        'field_acfe_form_user_map_website'      => 'field_acfe_form_user_save_website',
                        'field_acfe_form_user_map_description'  => 'field_acfe_form_user_save_description',
                        'field_acfe_form_user_map_role'         => 'field_acfe_form_user_save_role',
                    )
                ),
            
            );
            
            foreach(acf_get_array($args['acfe_form_actions']) as &$row){
                
                foreach($rules as $rule){
                    
                    $load_values = acf_maybe_get($row, $rule['load_values']);
                    $fields = $rule['fields'];
                    
                    if(!empty($load_values)){
                        continue;
                    }
                    
                    foreach($fields as $map => $save){
                        
                        $map_value = acf_maybe_get($row, $map);
                        
                        if(empty($map_value)){
                            continue;
                        }
                        
                        switch($save){
                            
                            case 'field_acfe_form_post_save_post_content': {
    
                                $row['field_acfe_form_post_save_post_content_group'][ $save ] = $map_value;
                                break;
                                
                            }
                            
                            case 'field_acfe_form_term_save_description': {
    
                                $row['field_acfe_form_term_save_description_group'][ $save ] = $map_value;
                                break;
                                
                            }
                            
                            case 'field_acfe_form_user_save_description': {
    
                                $row['field_acfe_form_user_save_description_group'][ $save ] = $map_value;
                                break;
                                
                            }
                            
                            default: {
    
                                $row[ $save ] = $map_value;
                                break;
                                
                            }
                            
                        }
                        
                    }
                    
                }
                
            }
            
        }
        
        return $args;
        
    }
    
}

acf_new_instance('acfe_module_form_compatibility');

endif;