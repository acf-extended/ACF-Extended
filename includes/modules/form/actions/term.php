<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_term')):

class acfe_form_term{
    
    function __construct(){
        
        /*
         * Form
         */
        add_filter('acfe/form/load/action/term',                                    array($this, 'load'), 1, 2);
        add_action('acfe/form/submit/action/term',                                  array($this, 'submit'), 1, 2);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_term_save_meta',               array(acfe()->acfe_form, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_term_load_meta',               array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_term_map_name',                array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_slug',                array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_taxonomy',            array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_parent',              array(acfe()->acfe_form, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_description',         array(acfe()->acfe_form, 'map_fields_deep'));
        
    }
    
    function load($form, $post_id){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $term_action = get_sub_field('acfe_form_term_action');
        
        // Load values
        $load_values = get_sub_field('acfe_form_term_load_values');
        $load_source = get_sub_field('acfe_form_term_load_source');
        $load_meta = get_sub_field('acfe_form_term_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
        
        $_name = get_sub_field('acfe_form_term_map_name');
        $_slug = get_sub_field('acfe_form_term_map_slug');
        $_taxonomy = get_sub_field('acfe_form_term_map_taxonomy');
        $_parent = get_sub_field('acfe_form_term_map_parent');
        $_description = get_sub_field('acfe_form_term_map_description');
        
        $_term_id = 0;
        
        // Custom Term ID
        if($load_source !== 'current_term'){
            
            $_term_id = $load_source;
        
        }
        
        // Current Term
        elseif($load_source === 'current_term'){
            
            if($post_info['type'] === 'term')
                $_term_id = $post_info['id'];
            
        }
        
        $_term_id = apply_filters('acfe/form/load/action/term/' . $term_action . '_id',                      $_term_id, $form);
        $_term_id = apply_filters('acfe/form/load/action/term/' . $term_action . '_id/name=' . $form_name,   $_term_id, $form);
        $_term_id = apply_filters('acfe/form/load/action/term/' . $term_action . '_id/id=' . $form_id,       $_term_id, $form);
        
        // Invalid Term ID
        if(!$_term_id)
            return $form;
        
        // Name
        if(acf_is_field_key($_name)){
            
            $key = array_search($_name, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_name]['value'] = get_term_field('name', $_term_id);
                
            }
            
        }
        
        // Slug
        if(acf_is_field_key($_slug)){
            
            $key = array_search($_slug, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_slug]['value'] = get_term_field('slug', $_term_id);
                
            }
            
        }
        
        // Taxonomy
        if(acf_is_field_key($_taxonomy)){
            
            $key = array_search($_taxonomy, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_taxonomy]['value'] = get_term_field('taxonomy', $_term_id);
                
            }
            
        }
        
        // Parent
        if(acf_is_field_key($_parent)){
            
            $key = array_search($_parent, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                
                $get_term_field = get_term_field('parent', $_term_id);
                
                if(!empty($get_term_field))
                    $form['map'][$_parent]['value'] = get_term_field('parent', $_term_id);
                
            }
            
        }
        
        // Description
        if(acf_is_field_key($_description)){
            
            $key = array_search($_description, $load_meta);
            
            if($key !== false){
                
                unset($load_meta[$key]);
                $form['map'][$_description]['value'] = get_term_field('description', $_term_id);
                
            }
            
        }
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
                
                $field = acf_get_field($field_key);
                
                $form['map'][$field_key]['value'] = acf_get_value('term_' . $_term_id, $field);
                
            }
            
        }
        
        return $form;
        
    }
    
    function submit($form, $post_id){
        
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        // Action
        $term_action = get_sub_field('acfe_form_term_action');
        
        // Mapping
        $map = array(
            'name'          => get_sub_field('acfe_form_term_map_name'),
            'slug'          => get_sub_field('acfe_form_term_map_slug'),
            'taxonomy'      => get_sub_field('acfe_form_term_map_taxonomy'),
            'parent'        => get_sub_field('acfe_form_term_map_parent'),
            'description'   => get_sub_field('acfe_form_term_map_description'),
        );
        
        // Fields
        $_target = get_sub_field('acfe_form_term_save_target');
        
        $_name_group = get_sub_field('acfe_form_term_save_name_group');
        $_name = $_name_group['acfe_form_term_save_name'];
        $_name_custom = $_name_group['acfe_form_term_save_name_custom'];
        
        $_slug_group = get_sub_field('acfe_form_term_save_slug_group');
        $_slug = $_slug_group['acfe_form_term_save_slug'];
        $_slug_custom = $_slug_group['acfe_form_term_save_slug_custom'];
        
        $_taxonomy = get_sub_field('acfe_form_term_save_taxonomy');
        $_parent = get_sub_field('acfe_form_term_save_parent');
        
        $_description_group = get_sub_field('acfe_form_term_save_description_group');
        $_description = $_description_group['acfe_form_term_save_description'];
        $_description_custom = $_description_group['acfe_form_term_save_description_custom'];
        
        // args
        $args = array();
        
        // Insert term
        $_term_id = 0;
        
        // Update user
        if($term_action === 'update_term'){
            
            // Custom Term ID
            $_term_id = $_target;
            
            // Current Term
            if($_target === 'current_term'){
                
                if($post_info['type'] === 'term')
                    $_term_id = $post_info['id'];
                
                // Invalid Term ID
                if(!$_term_id)
                    return;
            
            }
            
            $args['ID'] = $_term_id;
            
        }
        
        // Name
        if(!empty($map['name'])){
            
            $args['name'] = acfe_form_map_field_value($map['name'], $_POST['acf'], $_term_id);
            
        }elseif($_name === 'custom'){
            
            $args['name'] = acfe_form_map_field_value($_name_custom, $_POST['acf'], $_term_id);
            
        }
        
        // Slug
        if(!empty($map['slug'])){
            
            $args['slug'] = acfe_form_map_field_value($map['slug'], $_POST['acf'], $_term_id);
            
        }elseif($_slug === 'custom'){
            
            $args['slug'] = acfe_form_map_field_value($_slug_custom, $_POST['acf'], $_term_id);
            
        }
        
        // Taxonomy
        if(!empty($map['taxonomy'])){
            
            $args['taxonomy'] = acfe_form_map_field_value($map['taxonomy'], $_POST['acf'], $_term_id);
            
        }elseif(!empty($_taxonomy)){
            
            $args['taxonomy'] = $_taxonomy;
            
        }
        
        // Parent
        if(!empty($map['parent'])){
            
            $args['parent'] = acfe_form_map_field_value($map['parent'], $_POST['acf'], $_term_id);
            
        }elseif(!empty($_parent)){
            
            // Custom Term ID
            $args['parent'] = $_parent;
            
            // Current Term
            if($_parent === 'current_term'){
                
                if($post_info['type'] === 'term')
                    $args['parent'] = $post_info['id'];
                
            }
            
        }
        
        // Description
        if(!empty($map['description'])){
            
            $args['description'] = acfe_form_map_field_value($map['description'], $_POST['acf'], $_term_id);
            
        }elseif($_description === 'custom'){
            
            $args['description'] = acfe_form_map_field_value($_description_custom, $_POST['acf'], $_term_id);
            
        }
        
        $args = apply_filters('acfe/form/submit/action/term/' . $term_action . '_args',                     $args, $form);
        $args = apply_filters('acfe/form/submit/action/term/' . $term_action . '_args/name=' . $form_name,  $args, $form);
        $args = apply_filters('acfe/form/submit/action/term/' . $term_action . '_args/id=' . $form_id,      $args, $form);
        
        // Insert Term
        if($term_action === 'insert_term'){
            
            if(!isset($args['name']) || !isset($args['taxonomy'])){
                
                $args = false;
                
            }
            
        }
        
        if($args === false)
            return;
        
        // Insert Term
        if($term_action === 'insert_term'){
            
            $_insert_term = wp_insert_term($args['name'], $args['taxonomy'], $args);
            
        }
        
        // Update Term
        elseif($term_action === 'update_term'){
            
            $_insert_term = wp_update_term($args['ID'], $args['taxonomy'], $args);
            
        }
        
        // Term Error
        if(is_wp_error($_insert_term))
            return;
        
        $_term_id = $_insert_term['term_id'];
        
        do_action('acfe/form/submit/action/term/' . $term_action,                           $form, $_term_id, $args);
        do_action('acfe/form/submit/action/term/' . $term_action . '/name=' . $form_name,   $form, $_term_id, $args);
        do_action('acfe/form/submit/action/term/' . $term_action . '/id=' . $form_id,       $form, $_term_id, $args);
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_term_save_meta');
        
        if(!empty($save_meta)){
            
            $data = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($data)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post('term_' . $_term_id, $data);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
    }
    
}

new acfe_form_term();

endif;