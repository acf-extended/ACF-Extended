<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_term')):

class acfe_form_term{
    
    function __construct(){
        
        /*
         * Form
         */
        add_filter('acfe/form/load/term',                                           array($this, 'load'), 1, 3);
        add_action('acfe/form/prepare/term',                                        array($this, 'prepare'), 1, 3);
        add_action('acfe/form/submit/term',                                         array($this, 'submit'), 10, 5);
        
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
        
        add_filter('acf/render_field/name=acfe_form_term_advanced_load',            array($this, 'advanced_load'));
        add_filter('acf/render_field/name=acfe_form_term_advanced_save_args',       array($this, 'advanced_save_args'));
        add_filter('acf/render_field/name=acfe_form_term_advanced_save',            array($this, 'advanced_save'));
        
    }
    
    function load($form, $post_id, $alias){
        
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
        
        $_term_id = apply_filters('acfe/form/load/term_id',                      $_term_id, $form);
        $_term_id = apply_filters('acfe/form/load/term_id/form=' . $form_name,   $_term_id, $form);
        
        if(!empty($alias))
            $_term_id = apply_filters('acfe/form/load/term_id/action=' . $alias, $_term_id, $form);
        
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
    
    function prepare($form, $post_id, $alias){
        
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
        
        $args = apply_filters('acfe/form/submit/term_args',                     $args, $term_action, $form);
        $args = apply_filters('acfe/form/submit/term_args/form=' . $form_name,  $args, $term_action, $form);
        
        if(!empty($alias))
            $args = apply_filters('acfe/form/submit/term_args/action=' . $alias, $args, $term_action, $form);
        
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
        
        // Save meta
        do_action('acfe/form/submit/term',                     $_term_id, $term_action, $args, $form);
        do_action('acfe/form/submit/term/name=' . $form_name,  $_term_id, $term_action, $args, $form);
        
        if(!empty($alias))
            do_action('acfe/form/submit/term/action=' . $alias, $_term_id, $term_action, $args, $form);
        
    }
    
    function submit($_term_id, $term_action, $args, $form, $action){
        
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
    
    function advanced_load($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_filter('acfe/form/load/term_id', 'my_form_term_values_source', 10, 3);
add_filter('acfe/form/load/term_id/form=<?php echo $form_name; ?>', 'my_form_term_values_source', 10, 3);
add_filter('acfe/form/load/term_id/action=my-term-action', 'my_form_term_values_source', 10, 3);
</pre>
<br />
<pre>
add_filter('acfe/form/load/term_id/form=<?php echo $form_name; ?>', 'my_form_term_values_source', 10, 3);
function my_form_term_values_source($term_id, $form, $action){
    
    /**
     * @int     $term_id    Term ID used as source
     * @array   $form       The form settings
     * @string  $action     The action alias name
     */
    
    
    /**
     * Force to load values from the term ID 45
     */
    $term_id = 45;
    
    
    /**
     * Return
     */
    return $term_id;
    
}
</pre><?php
        
    }
    
    function advanced_save_args($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_filter('acfe/form/submit/term_args', 'my_form_term_args', 10, 4);
add_filter('acfe/form/submit/term_args/form=<?php echo $form_name; ?>', 'my_form_term_args', 10, 4);
add_filter('acfe/form/submit/term_args/action=my-term-action', 'my_form_term_args', 10, 4);
</pre>
<br />
<pre>
add_filter('acfe/form/submit/term_args/form=<?php echo $form_name; ?>', 'my_form_term_args', 10, 4);
function my_form_term_args($args, $type, $form, $action){
    
    /**
     * @array   $args   The generated term arguments
     * @string  $type   Action type: 'insert_term' or 'update_term'
     * @array   $form   The form settings
     * @string  $action The action alias name
     */
    
    
    /**
     * Force specific description if the action type is 'insert_term'
     */
    if($type === 'insert_term'){
        
        $args['description'] = 'My term description';
        
    }
    
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the post ID 145
     */
    $my_post_field = get_field('my_field', 145);
    
    
    /**
     * Return arguments
     * Note: Return false will stop post & meta insert/update
     */
    return $args;
    
}
</pre><?php
        
    }
    
    function advanced_save($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre>
add_action('acfe/form/submit/term', 'my_form_term_save', 10, 5);
add_action('acfe/form/submit/term/form=<?php echo $form_name; ?>', 'my_form_term_save', 10, 5);
add_action('acfe/form/submit/term/action=my-term-action', 'my_form_term_save', 10, 5);
</pre>
<br />
<pre>
/**
 * At this point the term is already saved into the database
 * Use a priority less than 10 to hook before ACF save meta fields
 * Use a priority greater than 10 to hook after ACF save meta fields
 */
add_action('acfe/form/submit/term/form=<?php echo $form_name; ?>', 'my_form_term_save', 10, 5);
function my_form_term_save($term_id, $type, $args, $form, $action){
    
    /**
     * @int     $term_id    The targeted term ID
     * @string  $type       Action type: 'insert_term' or 'update_term'
     * @array   $args       The generated term arguments
     * @array   $form       The form settings
     * @string  $action     The action alias name
     */
    
    
    /**
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    
    /**
     * Get the field value 'my_field' from the currently saved term
     */
    $my_term_field = get_field('my_field', 'term_' . $term_id);
    
}
</pre><?php
        
    }
    
}

new acfe_form_term();

endif;