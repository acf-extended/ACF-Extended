<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form_term')):

class acfe_form_term{
    
    function __construct(){
        
        add_filter('acfe/form/load/action/term', array($this, 'load'), 1);
        add_action('acfe/form/submit/action/term', array($this, 'submit'), 1, 3);
        
    }
    
    function load($args){
        
        $form_name = acf_maybe_get($args, 'acfe_form_name');
        $form_id = acf_maybe_get($args, 'acfe_form_id');
        
        // Behavior
        $term_behavior = get_sub_field('acfe_form_term_behavior');
        
        // Update Post
        if($term_behavior !== 'update_term')
            return $args;
        
        if(!get_sub_field('acfe_form_term_update_load'))
            return $args;
        
        $_term_id_group = get_sub_field('acfe_form_term_update_term_id_group');
        $_term_id_data = $_term_id_group['acfe_form_term_update_term_id'];
        $_term_id_custom = $_term_id_group['acfe_form_term_update_term_id_custom'];
        
        $_term_name_group = get_sub_field('acfe_form_term_update_name_group');
        $_term_name = $_term_name_group['acfe_form_term_update_name'];
        
        $_term_slug_group = get_sub_field('acfe_form_term_update_slug_group');
        $_term_slug = $_term_slug_group['acfe_form_term_update_slug'];
        
        $_term_taxonomy = get_sub_field('acfe_form_term_update_taxonomy');
        
        $_term_parent_group = get_sub_field('acfe_form_term_update_parent_group');
        $_term_parent = $_term_parent_group['acfe_form_term_update_parent'];
        
        $_term_description_group = get_sub_field('acfe_form_term_update_description_group');
        $_term_description = $_term_description_group['acfe_form_term_update_description'];
        
        // var
        $_term_id = $args['post_id'];
        
        // Current post
        if($_term_id_data === 'current_term'){
            
            $_term_id = get_current_object_id();
        
        // Custom Post ID
        }elseif($_term_id_data === 'custom_term_id'){
            
            $_term_id = acfe_form_map_field_get_value($_term_id_custom);
        
        // Field
        }elseif(acf_is_field_key($_term_id_data)){
            
            $_term_id = get_field($_term_id_data);
        
        }
        
        $_term_id = apply_filters('acfe/form/load/term_id',                      $_term_id, $args);
        $_term_id = apply_filters('acfe/form/load/term_id/name=' . $form_name,   $_term_id, $args);
        $_term_id = apply_filters('acfe/form/load/term_id/id=' . $form_id,       $_term_id, $args);
        
        // ID
        $args['post_id'] = 'term_' . $_term_id;
        
        // Name
        if(acf_is_field_key($_term_name)){
            
            $args['map'][$_term_name]['value'] = get_term_field('name', $_term_id);
            
        }
        
        // Slug
        if(acf_is_field_key($_term_slug)){
            
            $args['map'][$_term_slug]['value'] = get_term_field('slug', $_term_id);
            
        }
        
        // Taxonomy
        if(acf_is_field_key($_term_taxonomy)){
            
            $args['map'][$_term_taxonomy]['value'] = get_term_field('taxonomy', $_term_id);
            
        }
        
        // Parent
        if(acf_is_field_key($_term_parent)){
            
            $args['map'][$_term_parent]['value'] = get_term_field('parent', $_term_id);
            
        }
        
        // Description
        if(acf_is_field_key($_term_description)){
            
            $args['map'][$_term_description]['value'] = get_term_field('description', $_term_id);
            
        }
        
        return $args;
        
    }
    
    function submit($form, $post_id, $acf){
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        
        // Behavior
        $term_behavior = get_sub_field('acfe_form_term_behavior');
        
        // Create Term
        if($term_behavior === 'create_term'){
            
            $_term_name_group = get_sub_field('acfe_form_term_create_name_group');
            $_term_name = $_term_name_group['acfe_form_term_create_name'];
            $_term_name_custom = $_term_name_group['acfe_form_term_create_name_custom'];
            
            $_term_slug_group = get_sub_field('acfe_form_term_create_slug_group');
            $_term_slug = $_term_slug_group['acfe_form_term_create_slug'];
            $_term_slug_custom = $_term_slug_group['acfe_form_term_create_slug_custom'];
            
            $_term_taxonomy = get_sub_field('acfe_form_term_create_taxonomy');
            
            $_term_parent_group = get_sub_field('acfe_form_term_create_parent_group');
            $_term_parent = $_term_parent_group['acfe_form_term_create_parent'];
            $_term_parent_custom = $_term_parent_group['acfe_form_term_create_parent_custom'];
            
            $_term_description_group = get_sub_field('acfe_form_term_create_description_group');
            $_term_description = $_term_description_group['acfe_form_term_create_description'];
            $_term_description_custom = $_term_description_group['acfe_form_term_create_description_custom'];
            
            $args = array();
            
            // Name
            $args['name'] = '';
            
            if(acf_is_field_key($_term_name)){
                
                $args['name'] = acfe_form_map_field_value($_term_name, $acf);
                
            }elseif($_term_name === 'custom'){
                
                $args['name'] = acfe_form_map_field_value($_term_name_custom, $acf);
                
            }
            
            // Taxonomy
            $args['taxonomy'] = acfe_form_map_field_value($_term_taxonomy, $acf);
            
            // Args: Slug
            if(acf_is_field_key($_term_slug)){
                
                $args['slug'] = acfe_form_map_field_value($_term_slug, $acf);
                
            }elseif($_term_slug === 'custom'){
                
                $args['slug'] = acfe_form_map_field_value($_term_slug_custom, $acf);
                
            }
            
            // Args: Parent
            if(acf_is_field_key($_term_parent)){
                
                $args['parent'] = acfe_form_map_field_value($_term_parent, $acf);
                
            }elseif($_term_parent === 'custom'){
                
                $args['parent'] = acfe_form_map_field_value($_term_parent_custom, $acf);
                
            }
            
            // Args: Description
            if(acf_is_field_key($_term_description)){
                
                $args['description'] = acfe_form_map_field_value($_term_description, $acf);
                
            }elseif($_term_description === 'custom'){
                
                $args['description'] = acfe_form_map_field_value($_term_description_custom, $acf);
                
            }
            
            $args = apply_filters('acfe/form/submit/insert_term_args',                      $args, $form);
            $args = apply_filters('acfe/form/submit/insert_term_args/name=' . $form_name,   $args, $form);
            $args = apply_filters('acfe/form/submit/insert_term_args/id=' . $form_id,       $args, $form);
            
            if($args === false)
                return;
            
            // Insert Term
            $_term_return = wp_insert_term($args['name'], $args['taxonomy'], $args);
            
            if(is_wp_error($_term_return))
                return;
            
            $_term_id = $_term_return['term_id'];
            
            do_action('acfe/form/submit/insert_term',                       $form, $_term_id, $args);
            do_action('acfe/form/submit/insert_term/name=' . $form_name,    $form, $_term_id, $args);
            do_action('acfe/form/submit/insert_term/id=' . $form_id,        $form, $_term_id, $args);
            
            // Meta save
            $_meta = get_sub_field('acfe_form_term_meta');
            
            $data = acfe_form_filter_meta($_meta, $acf);
            
            if(!empty($data)){
                
                // Save meta fields
                acf_save_post('term_' . $_term_id, $data);
            
            }
            
        }
        
        // Update Term
        elseif($term_behavior === 'update_term'){
            
            $_term_id_data_group = get_sub_field('acfe_form_term_update_term_id_group');
            $_term_id_data = $_term_id_data_group['acfe_form_term_update_term_id'];
            $_term_id_data_custom = $_term_id_data_group['acfe_form_term_update_term_id_custom'];
            
            $_term_name_group = get_sub_field('acfe_form_term_update_name_group');
            $_term_name = $_term_name_group['acfe_form_term_update_name'];
            $_term_name_custom = $_term_name_group['acfe_form_term_update_name_custom'];
            
            $_term_slug_group = get_sub_field('acfe_form_term_update_slug_group');
            $_term_slug = $_term_slug_group['acfe_form_term_update_slug'];
            $_term_slug_custom = $_term_slug_group['acfe_form_term_update_slug_custom'];
            
            $_term_taxonomy = get_sub_field('acfe_form_term_update_taxonomy');
            
            $_term_parent_group = get_sub_field('acfe_form_term_update_parent_group');
            $_term_parent = $_term_parent_group['acfe_form_term_update_parent'];
            $_term_parent_custom = $_term_parent_group['acfe_form_term_update_parent_custom'];
            
            $_term_description_group = get_sub_field('acfe_form_term_update_description_group');
            $_term_description = $_term_description_group['acfe_form_term_update_description'];
            $_term_description_custom = $_term_description_group['acfe_form_term_update_description_custom'];
            
            $_term_id = false;
            
            // Current Term
            if($_term_id_data === 'current_term'){
                
                $_term_id = get_current_object_id();
            
            // Custom Term ID
            }elseif($_term_id_data === 'custom_term_id'){
                
                $_term_id = acfe_form_map_field_value($_term_id_data_custom, $acf);
            
            // Field
            }elseif(acf_is_field_key($_term_id_data)){
                
                $_term_id = acfe_form_map_field_value($_term_id_data, $acf);
                
            }
            
            $args = array();
            
            $args['ID'] = $_term_id;
            
            // Taxonomy
            if(!empty($_term_taxonomy)){
                
                $args['taxonomy'] = acfe_form_map_field_value($_term_taxonomy, $acf);
                
            }else{
                
                $get_term = get_term($_term_id);
                
                $args['taxonomy'] = $get_term->taxonomy;
                
            }
            
            // Args: Name
            if(!empty($_term_name)){
                
                if(acf_is_field_key($_term_name)){
                    
                    $args['name'] = acfe_form_map_field_value($_term_name, $acf);
                    
                }elseif($_term_name === 'custom'){
                    
                    $args['name'] = acfe_form_map_field_value($_term_name_custom, $acf);
                    
                }
                
            }
            
            // Args: Slug
            if(!empty($_term_slug)){
                
                if(acf_is_field_key($_term_slug)){
                    
                    $args['slug'] = acfe_form_map_field_value($_term_slug, $acf);
                    
                }elseif($_term_slug === 'custom'){
                    
                    $args['slug'] = acfe_form_map_field_value($_term_slug_custom, $acf);
                    
                }
                
            }
            
            // Args: Parent
            if(!empty($_term_parent)){
                
                if(acf_is_field_key($_term_parent)){
                    
                    $args['parent'] = acfe_form_map_field_value($_term_parent, $acf);
                    
                }elseif($_term_parent === 'custom'){
                    
                    $args['parent'] = acfe_form_map_field_value($_term_parent_custom, $acf);
                    
                }
            
            }
            
            // Args: Description
            if(!empty($_term_description)){
                
                if(acf_is_field_key($_term_description)){
                    
                    $args['description'] = acfe_form_map_field_value($_term_description, $acf);
                    
                }elseif($_term_description === 'custom'){
                    
                    $args['description'] = acfe_form_map_field_value($_term_description_custom, $acf);
                    
                }
                
            }
            
            
            $args = apply_filters('acfe/form/submit/update_term_args',                      $args, $form, $_term_id);
            $args = apply_filters('acfe/form/submit/update_term_args/name=' . $form_name,   $args, $form, $_term_id);
            $args = apply_filters('acfe/form/submit/update_term_args/id=' . $form_id,       $args, $form, $_term_id);
            
            if($args === false)
                return;
            
            // Update Post
            $_term_return = wp_update_term($args['ID'], $args['taxonomy'], $args);
            
            if(is_wp_error($_term_return))
                return;
            
            $_term_id = $_term_return['term_id'];
            
            do_action('acfe/form/submit/update_term',                       $form, $_term_id, $args);
            do_action('acfe/form/submit/update_term/name=' . $form_name,    $form, $_term_id, $args);
            do_action('acfe/form/submit/update_term/id=' . $form_id,        $form, $_term_id, $args);
            
            // Meta save
            $_meta = get_sub_field('acfe_form_term_meta');
            
            $data = acfe_form_filter_meta($_meta, $acf);
            
            if(!empty($data)){
                
                // Save meta fields
                acf_save_post('term_' . $_term_id, $data);
            
            }
            
        }
        
    }
    
}

new acfe_form_term();

endif;