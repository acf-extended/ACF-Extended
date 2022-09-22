<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_form_term')):

class acfe_form_term{
    
    function __construct(){
    
        /*
         * Helpers
         */
        $helpers = acf_get_instance('acfe_dynamic_forms_helpers');
        
        /*
         * Action
         */
        add_filter('acfe/form/actions',                                             array($this, 'add_action'));
        add_filter('acfe/form/load/term',                                           array($this, 'load'), 10, 3);
        add_action('acfe/form/make/term',                                           array($this, 'make'), 10, 3);
        add_action('acfe/form/submit/term',                                         array($this, 'submit'), 10, 5);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_term_save_meta',               array($helpers, 'map_fields'));
        add_filter('acf/prepare_field/name=acfe_form_term_load_meta',               array($helpers, 'map_fields'));
        
        add_filter('acf/prepare_field/name=acfe_form_term_save_target',             array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_load_source',             array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_term_save_name',               array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_save_slug',               array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_save_taxonomy',           array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_save_parent',             array($helpers, 'map_fields_deep'));
        add_filter('acf/prepare_field/name=acfe_form_term_save_description',        array($helpers, 'map_fields_deep'));
        
        add_filter('acf/prepare_field/name=acfe_form_term_map_name',                array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_slug',                array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_taxonomy',            array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_parent',              array($helpers, 'map_fields_deep_no_custom'));
        add_filter('acf/prepare_field/name=acfe_form_term_map_description',         array($helpers, 'map_fields_deep_no_custom'));
        
        add_filter('acf/prepare_field/name=acfe_form_term_save_target',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_term_load_source',             array($this, 'prepare_choices'), 5);
        add_filter('acf/prepare_field/name=acfe_form_term_save_parent',             array($this, 'prepare_choices'), 5);
        
    }
    
    function load($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
        
        // Action
        $term_action = get_sub_field('acfe_form_term_action');
        
        // Load values
        $load_values = get_sub_field('acfe_form_term_load_values');
        $load_meta = get_sub_field('acfe_form_term_load_meta');
        
        // Load values
        if(!$load_values)
            return $form;
    
        $_term_id = get_sub_field('acfe_form_term_load_source');
        $_name = get_sub_field('acfe_form_term_map_name');
        $_slug = get_sub_field('acfe_form_term_map_slug');
        $_taxonomy = get_sub_field('acfe_form_term_map_taxonomy');
        $_parent = get_sub_field('acfe_form_term_map_parent');
        $_description = get_sub_field('acfe_form_term_map_description');
        
        // Map {field:name} {get_field:name} {query_var:name}
        $_term_id = acfe_form_map_field_value_load($_term_id, $current_post_id, $form);
        $_name = acfe_form_map_field_value_load($_name, $current_post_id, $form);
        $_slug = acfe_form_map_field_value_load($_slug, $current_post_id, $form);
        $_taxonomy = acfe_form_map_field_value_load($_taxonomy, $current_post_id, $form);
        $_parent = acfe_form_map_field_value_load($_parent, $current_post_id, $form);
        $_description = acfe_form_map_field_value_load($_description, $current_post_id, $form);
        
        $_term_id = apply_filters('acfe/form/load/term_id',                      $_term_id, $form, $action);
        $_term_id = apply_filters('acfe/form/load/term_id/form=' . $form_name,   $_term_id, $form, $action);
        
        if(!empty($action))
            $_term_id = apply_filters('acfe/form/load/term_id/action=' . $action, $_term_id, $form, $action);
        
        // Invalid Term ID
        if(!$_term_id)
            return $form;
    
        $rules = array(
        
            array(
                'key'   => $_name,
                'value' => get_term_field('name', $_term_id),
            ),
    
            array(
                'key'   => $_slug,
                'value' => get_term_field('slug', $_term_id),
            ),
    
            array(
                'key'   => $_taxonomy,
                'value' => get_term_field('taxonomy', $_term_id),
            ),
    
            array(
                'key'   => $_parent,
                'value' => get_term_field('parent', $_term_id),
            ),
    
            array(
                'key'   => $_description,
                'value' => get_term_field('description', $_term_id),
            ),
    
        );
    
        foreach($rules as $rule){
        
            if(acf_is_field_key($rule['key'])){
            
                // disable loading from meta if checked
                if(($key = array_search($rule['key'], $load_meta)) !== false){
                    unset($load_meta[ $key ]);
                }
            
                if(!isset($form['map'][ $rule['key'] ]) || $form['map'][ $rule['key'] ] !== false){
                    if(!isset($form['map'][ $rule['key'] ]['value'])){
                        $form['map'][ $rule['key'] ]['value'] = $rule['value'];
                    }
                }
            
            }
        
        }
        
        // Load others values
        if(!empty($load_meta)){
            
            foreach($load_meta as $field_key){
    
                $field = acf_get_field($field_key);
    
                if(!$field)
                    continue;
    
                if($field['type'] === 'clone' && $field['display'] === 'seamless'){
        
                    $sub_fields = acf_get_value('term_' . $_term_id, $field);
        
                    foreach($sub_fields as $sub_field_key => $value){
            
                        $form['map'][$sub_field_key]['value'] = $value;
            
                    }
        
                }else{
        
                    $form['map'][$field_key]['value'] = acf_get_value('term_' . $_term_id, $field);
        
                }
                
            }
            
        }
        
        return $form;
        
    }
    
    function make($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
    
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/term',                          $prepare, $form, $current_post_id, $action);
        $prepare = apply_filters('acfe/form/prepare/term/form=' . $form_name,       $prepare, $form, $current_post_id, $action);
    
        if(!empty($action))
            $prepare = apply_filters('acfe/form/prepare/term/action=' . $action,    $prepare, $form, $current_post_id, $action);
    
        if($prepare === false)
            return;
        
        // Action
        $term_action = get_sub_field('acfe_form_term_action');
    
        // Load values
        $load_values = get_sub_field('acfe_form_term_load_values');
        
        // Pre-process
        $_description_group = get_sub_field('acfe_form_term_save_description_group');
        $_description = $_description_group['acfe_form_term_save_description'];
        $_description_custom = $_description_group['acfe_form_term_save_description_custom'];
        
        if($_description === 'custom')
            $_description = $_description_custom;
    
        $map = array();
    
        if($load_values){
        
            // Mapping
            $map = array(
                'name'        => get_sub_field( 'acfe_form_term_map_name' ),
                'slug'        => get_sub_field( 'acfe_form_term_map_slug' ),
                'taxonomy'    => get_sub_field( 'acfe_form_term_map_taxonomy' ),
                'parent'      => get_sub_field( 'acfe_form_term_map_parent' ),
                'description' => get_sub_field( 'acfe_form_term_map_description' ),
            );
        
        }
        
        // Fields
        $fields = array(
            'target'        => get_sub_field('acfe_form_term_save_target'),
            'name'          => get_sub_field('acfe_form_term_save_name'),
            'slug'          => get_sub_field('acfe_form_term_save_slug'),
            'taxonomy'      => get_sub_field('acfe_form_term_save_taxonomy'),
            'parent'        => get_sub_field('acfe_form_term_save_parent'),
            'description'   => $_description,
        );
        
        $data = acfe_form_map_vs_fields($map, $fields, $current_post_id, $form);
        
        // args
        $args = array();
        
        // Insert term
        $_term_id = 0;
        
        // Update term
        if($term_action === 'update_term'){
            
            $_term_id = $data['target'];
            
            // Invalid Term ID
            if(!$_term_id)
                return;
            
            $args['ID'] = $_term_id;
            
        }
        
        // Name
        if(!empty($data['name'])){
    
            if(is_array($data['name']))
                $data['name'] = acfe_array_to_string($data['name']);
            
            $args['name'] = $data['name'];
            
        }
        
        // Slug
        if(!empty($data['slug'])){
    
            if(is_array($data['name']))
                $data['name'] = acfe_array_to_string($data['name']);
            
            $args['slug'] = $data['slug'];
            
        }
        
        // Taxonomy
        if(!empty($data['taxonomy'])){
    
            if(is_array($data['name']))
                $data['name'] = acfe_array_to_string($data['name']);
            
            $args['taxonomy'] = $data['taxonomy'];
            
        }
        
        // Parent
        if(!empty($data['parent'])){
    
            if(is_array($data['name']))
                $data['name'] = acfe_array_to_string($data['name']);
            
            $args['parent'] = $data['parent'];
            
        }
        
        // Description
        if(!empty($data['description'])){
    
            if(is_array($data['name']))
                $data['name'] = acfe_array_to_string($data['name']);
            
            $args['description'] = $data['description'];
            
        }
        
        $args = apply_filters('acfe/form/submit/term_args',                     $args, $term_action, $form, $action);
        $args = apply_filters('acfe/form/submit/term_args/form=' . $form_name,  $args, $term_action, $form, $action);
        
        if(!empty($action))
            $args = apply_filters('acfe/form/submit/term_args/action=' . $action, $args, $term_action, $form, $action);
        
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
        
        $args['ID'] = $_term_id;
        
        // Save meta
        do_action('acfe/form/submit/term',                     $_term_id, $term_action, $args, $form, $action);
        do_action('acfe/form/submit/term/name=' . $form_name,  $_term_id, $term_action, $args, $form, $action);
        
        if(!empty($action))
            do_action('acfe/form/submit/term/action=' . $action, $_term_id, $term_action, $args, $form, $action);
        
    }
    
    function submit($_term_id, $term_action, $args, $form, $action){
    
        // Form name
        $form_name = acf_maybe_get($form, 'name');
    
        // Get term array
        $term_object = get_term($_term_id, $args['taxonomy'], 'ARRAY_A');
    
        $term_object['permalink'] = get_term_link($_term_id, $term_object['taxonomy']);
        $term_object['admin_url'] = admin_url('term.php?tag_ID=' . $_term_id . '&taxonomy=' . $term_object['taxonomy']);
    
        // Deprecated
        $term_object = apply_filters_deprecated("acfe/form/query_var/term",                    array($term_object, $_term_id, $term_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/term");
        $term_object = apply_filters_deprecated("acfe/form/query_var/term/form={$form_name}",  array($term_object, $_term_id, $term_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/term/form={$form_name}");
        $term_object = apply_filters_deprecated("acfe/form/query_var/term/action={$action}",   array($term_object, $_term_id, $term_action, $args, $form, $action), '0.8.7.5', "acfe/form/output/term/action={$action}");
    
        // Output
        $term_object = apply_filters("acfe/form/output/term",                                       $term_object, $_term_id, $term_action, $args, $form, $action);
        $term_object = apply_filters("acfe/form/output/term/form={$form_name}",                     $term_object, $_term_id, $term_action, $args, $form, $action);
        $term_object = apply_filters("acfe/form/output/term/action={$action}",                      $term_object, $_term_id, $term_action, $args, $form, $action);
    
        // Old Query var
        $query_var = acfe_form_unique_action_id($form, 'term');
        
        if(!empty($action))
            $query_var = $action;
        
        set_query_var($query_var, $term_object);
        // ------------------------------------------------------------
        
        // Action Output
        $actions = get_query_var('acfe_form_actions', array());
        
        $actions['term'] = $term_object;
        
        if(!empty($action))
            $actions[$action] = $term_object;
        
        set_query_var('acfe_form_actions', $actions);
        // ------------------------------------------------------------
        
        // Meta save
        $save_meta = get_sub_field('acfe_form_term_save_meta');
        
        if(!empty($save_meta)){
            
            $meta = acfe_form_filter_meta($save_meta, $_POST['acf']);
            
            if(!empty($meta)){
                
                // Backup original acf post data
                $acf = $_POST['acf'];
                
                // Save meta fields
                acf_save_post('term_' . $_term_id, $meta);
                
                // Restore original acf post data
                $_POST['acf'] = $acf;
            
            }
            
        }
        
    }
    
    /**
     *  Term: Select2 Choices
     */
    function prepare_choices($field){
        
        $field['choices']['current_term'] = 'Current: Term';
        $field['choices']['current_term_parent'] = 'Current: Term Parent';
        
        if(acf_maybe_get($field, 'value')){
            
            $value = $field['value'];
            
            if(is_array($value))
                $value = $value[0];
            
            $term = get_term($value);
            
            if($term){
                
                $field['choices'][$term->term_id] = $term->name;
                
            }
        
        }
        
        return $field;
        
    }
    
    function add_action($layouts){
        
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
        
        return $layouts;
        
    }
    
}

new acfe_form_term();

endif;