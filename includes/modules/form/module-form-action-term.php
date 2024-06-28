<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_action_term')):

class acfe_module_form_action_term extends acfe_module_form_action{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'term';
        $this->title = __('Term action', 'acfe');
        
        $this->item = array(
            'action' => 'term',
            'type'   => 'insert_term', // insert_term | update_term
            'name'   => '',
            'save'   => array(
                'target'      => '',
                'name'        => '',
                'slug'        => '',
                'taxonomy'    => '',
                'parent'      => '',
                'description' => '',
                'acf_fields'  => array(),
            ),
            'load'   => array(
                'source'      => '',
                'name'        => '',
                'slug'        => '',
                'taxonomy'    => '',
                'parent'      => '',
                'description' => '',
                'acf_fields'  => array(),
            ),
        );
    
        $this->fields = array('name', 'slug', 'taxonomy', 'parent', 'description');
        
    }
    
    
    /**
     * load_action
     *
     * acfe/form/load_term:9
     *
     * @param $form
     * @param $action
     *
     * @return array
     */
    function load_action($form, $action){
        
        // check source
        if(!$action['load']['source']){
            return $form;
        }
        
        // apply template tags
        acfe_apply_tags($action['load']['source'], array('context' => 'load', 'format' => false));
        
        // vars
        $load = $action['load'];
        $term_id = acf_extract_var($load, 'source');
        $acf_fields = acf_extract_var($load, 'acf_fields');
        $acf_fields = acf_get_array($acf_fields);
        $acf_fields_exclude = array();
        
        // filters
        $term_id = apply_filters("acfe/form/load_term_id",                          $term_id, $form, $action);
        $term_id = apply_filters("acfe/form/load_term_id/form={$form['name']}",     $term_id, $form, $action);
        $term_id = apply_filters("acfe/form/load_term_id/action={$action['name']}", $term_id, $form, $action);
        
        // bail early if no source
        if(!$term_id){
            return $form;
        }
        
        // get source term
        $term = get_term($term_id);
    
        // no term found
        if(!$term || is_wp_error($term)){
            return $form;
        }
        
        /**
         * load term fields
         *
         * $load = array(
         *     name        => 'field_655af3dd3bd56'
         *     slug        => 'field_655af3dd3bd56'
         *     taxonomy    => ''
         *     parent      => ''
         *     description => ''
         * )
         */
        foreach($load as $term_field => $field_key){
            
            // check field is not hidden and has no value set in 'acfe/form/load_form'
            if(acf_maybe_get($form['map'], $field_key) !== false && !isset($form['map'][ $field_key ]['value'])){
                
                // check key exists in WP_Term and is field key
                if(in_array($term_field, $this->fields) && !empty($field_key) && is_string($field_key) && acf_is_field_key($field_key)){
                    
                    // add field to excluded list
                    $acf_fields_exclude[] = $field_key;
                    
                    // assign term field as value
                    $form['map'][ $field_key ]['value'] = get_term_field($term_field, $term_id, '', 'raw');
            
                }
                
            }
            
        }
    
        // load acf values
        $form = $this->load_acf_values($form, "term_{$term_id}", $acf_fields, $acf_fields_exclude);
        
        // return
        return $form;
    
    }
    
    
    /**
     * validate_action
     *
     * acfe/form/validate_term:9
     *
     * @param $form
     * @param $action
     */
    function validate_action($form, $action){
        
//        if(empty($action['save']['name'])){
//            acfe_add_validation_error('', __('Term name is empty', 'acfe'));
//        }
//
//        if(empty($action['save']['taxonomy'])){
//            acfe_add_validation_error('', __('Term taxonomy is empty', 'acfe'));
//        }
        
    }
    
    
    /**
     * prepare_action
     *
     * acfe/form/prepare_term:9
     *
     * @param $action
     * @param $form
     *
     * @return array
     */
    function prepare_action($action, $form){
        
        return $action;
        
    }
    
    
    /**
     * make_action
     *
     * acfe/form/make_term:9
     *
     * @param $form
     * @param $action
     */
    function make_action($form, $action){
    
        // insert/update term
        $process = $this->process($form, $action);
    
        // validate
        if(!$process){
            return;
        }
    
        // process vars
        $term_id = $process['term_id'];
        $args = $process['args'];
        
        // output
        $this->generate_output($term_id, $args, $form, $action);
        
        // acf values
        $this->save_acf_fields("term_{$term_id}", $action);
        
        // hooks
        do_action("acfe/form/submit_term",                          $term_id, $args, $form, $action);
        do_action("acfe/form/submit_term/form={$form['name']}",     $term_id, $args, $form, $action);
        do_action("acfe/form/submit_term/action={$action['name']}", $term_id, $args, $form, $action);
    
    }
    
    
    /**
     * setup_action
     *
     * @param $action
     * @param $form
     *
     * @return array
     */
    function setup_action($action, $form){
        
        // check if post_parent has a field key or value
        $has_term_parent = !acf_is_empty($action['save']['parent']);
        
        // tags context
        $opt     = array('context' => 'save');
        $opt_fmt = array('context' => 'save', 'format' => false);
        
        // apply tags
        acfe_apply_tags($action['save']['target'],      $opt_fmt);
        acfe_apply_tags($action['save']['name'],        $opt);
        acfe_apply_tags($action['save']['slug'],        $opt);
        acfe_apply_tags($action['save']['taxonomy'],    $opt_fmt);
        acfe_apply_tags($action['save']['parent'],      $opt_fmt);
        acfe_apply_tags($action['save']['description'], $opt);
        
        // if post parent is supposed to have a value but is empty, set it to 0
        // parent was most likely removed from the field
        if($has_term_parent && acf_is_empty($action['save']['parent'])){
            $action['save']['parent'] = 0;
        }
        
        // return
        return $action;
        
    }
    
    
    /**
     * process
     *
     * @param $form
     * @param $action
     *
     * @return array|false
     */
    function process($form, $action){
        
        // apply tags
        $action = $this->setup_action($action, $form);
        
        // vars
        $save = $action['save'];
        $term_id = (int) acf_extract_var($save, 'target');
        
        // pre-insert term
        if($action['type'] === 'insert_term'){
            
            if(empty($action['save']['taxonomy'])){
                $action['save']['taxonomy'] = 'category';
            }
            
            // insert
            $insert = wp_insert_term($save['name'], $save['taxonomy']);
            
            // invalid insert
            if($insert && !is_wp_error($insert)){
                $term_id = $insert['term_id'];
            }
            
        }
        
        // invalid target
        if(!$term_id){
            return false;
        }
        
        // generated id
        acfe_add_context(array('context' => 'save', 'generated_id' => $term_id));
        
        acfe_apply_tags($action['save']['name']);
        acfe_apply_tags($action['save']['slug']);
        
        $save['name'] = $action['save']['name'];
        $save['slug'] = $action['save']['slug'];
        
        acfe_delete_context('context', 'generated_id');
        
        // get term
        $term = get_term($term_id);
        
        // validate
        if(!$term || is_wp_error($term)){
            return false;
        }
    
        // default term arguments
        $args = array(
            'ID'       => $term_id,
            'taxonomy' => $term->taxonomy
        );
    
        // construct term arguments
        foreach($save as $term_field => $value){
        
            // name, slug, taxonomy, parent etc...
            if(in_array($term_field, $this->fields) && !acf_is_empty($value)){
                $args[ $term_field ] = $value;
            }
        
        }
    
        // filters
        $args = apply_filters("acfe/form/submit_term_args",                          $args, $form, $action);
        $args = apply_filters("acfe/form/submit_term_args/form={$form['name']}",     $args, $form, $action);
        $args = apply_filters("acfe/form/submit_term_args/action={$action['name']}", $args, $form, $action);
    
        // bail early
        if($args === false){
        
            // delete pre-insert term
            if($action['type'] === 'insert_term'){
                wp_delete_term($term_id, $save['taxonomy']);
            }
        
            return false;
        
        }
    
        // update term
        $update = wp_update_term($args['ID'], $args['taxonomy'], $args);
    
        // bail early
        if(!$update || is_wp_error($update)){
            return false;
        }
        
        // return
        return array(
            'term_id' => $update['term_id'],
            'args'    => $args
        );
        
    }
    
    
    /**
     * generate_output
     *
     * @param $term_id
     * @param $args
     * @param $form
     * @param $action
     */
    function generate_output($term_id, $args, $form, $action){
    
        // term array
        $term = get_term($term_id, $args['taxonomy'], ARRAY_A);
        $term['permalink'] = get_term_link($term_id, $args['taxonomy']);
        $term['admin_url'] = admin_url("edit-tags.php?taxonomy={$args['taxonomy']}&tag_ID=1{$term_id}");
    
        // filters
        $term = apply_filters("acfe/form/submit_term_output",                          $term, $args, $form, $action);
        $term = apply_filters("acfe/form/submit_term_output/form={$form['name']}",     $term, $args, $form, $action);
        $term = apply_filters("acfe/form/submit_term_output/action={$action['name']}", $term, $args, $form, $action);
    
        // action output
        $this->set_action_output($term, $action);
        
    }
    
    
    /**
     * prepare_load_action
     *
     * acfe/module/prepare_load_action
     *
     * @param $action
     *
     * @return array
     */
    function prepare_load_action($action){
    
        // save loop
        foreach(array_keys($action['save']) as $k){
            $action["save_{$k}"] = $action['save'][ $k ];
        }
        
        // groups
        $keys = array(
            'save' => array(
                'target'      => function($value){return !empty($value) && is_numeric($value);},
                'description' => function($value){return acfe_is_html(nl2br($value));},
                'parent'      => function($value){return !empty($value) && is_numeric($value);},
            ),
            'load' => array(
                'source'      => function($value){return !empty($value) && is_numeric($value);},
            )
        );
        
        foreach($keys as $parent => $row){
            foreach($row as $key => $callback){
                
                // save: target
                $value = $action[ $parent ][ $key ];
                $action["{$parent}_{$key}_group"]["{$parent}_{$key}"] = $value;
                $action["{$parent}_{$key}_group"]["{$parent}_{$key}_custom"] = '';
                
                if(call_user_func_array($callback, array($value))){
                    $action["{$parent}_{$key}_group"]["{$parent}_{$key}"] = 'custom';
                    $action["{$parent}_{$key}_group"]["{$parent}_{$key}_custom"] = $value;
                }
                
            }
        }
        
        // load loop
        $load_active = false;
        
        foreach(array_keys($action['load']) as $k){
            
            $action["load_{$k}"] = $action['load'][ $k ];
            
            if(!empty($action['load'][ $k ])){
                $load_active = true;
            }
            
        }
        
        $action['load_active'] = $load_active;
        
        // cleanup
        unset($action['action']);
        unset($action['save']);
        unset($action['load']);
        
        return $action;
        
    }
    
    
    /**
     * prepare_save_action
     *
     * acfe/module/prepare_save_action
     *
     * @param $action
     * @param $item
     *
     * @return mixed
     */
    function prepare_save_action($action){
        
        $save = $this->item;
        
        // general
        $save['type'] = $action['type'];
        $save['name'] = $action['name'];
        
        // save loop
        foreach(array_keys($save['save']) as $k){
            
            // taxonomy => save_taxonomy
            if(acf_maybe_get($action, "save_{$k}")){
                $save['save'][ $k ] = $action["save_{$k}"];
            }
            
        }
        
        // groups
        $keys = array(
            'save' => array('target', 'description', 'parent'),
            'load' => array('source'),
        );
        
        foreach($keys as $parent => $row){
            foreach($row as $key){
                
                $group = $action["{$parent}_{$key}_group"];
                $save[ $parent ][ $key ] = $group[ $key ];
                
                if($group[ $key ] === 'custom'){
                    $save[ $parent ][ $key ] = $group["{$key}_custom"];
                }
                
            }
        }
        
        // check load switch activated
        if($action['load_active']){
    
            // load loop
            foreach(array_keys($save['load']) as $k){
        
                // taxonomy => load_taxonomy
                if(acf_maybe_get($action, "load_{$k}")){
                    
                    $value = $action["load_{$k}"];
                    $save['load'][ $k ] = $value;
                    
                    // assign to save array when field_key
                    if(isset($save['save'][ $k ]) && !empty($value) && is_string($value) && acf_is_field_key($value)){
                        $save['save'][ $k ] = "{field:$value}";
                    }
                    
                }
        
            }
            
        }
        
        // default save: target
        if($action['type'] === 'update_term' && empty($save['save']['target'])){
            $save['save']['target'] = '{term}';
        }
        
        // default load: source
        if($action['load_active'] && empty($save['load']['source'])){
            $save['load']['source'] = '{term}';
        }
        
        return $save;
        
    }
    
    
    /**
     * prepare_action_for_export
     *
     * @param $action
     *
     * @return mixed
     */
    function prepare_action_for_export($action){
        
        if($action['type'] === 'insert_term'){
            unset($action['save']['target']);
        }
        
        if(empty($action['load']['source'])){
            unset($action['load']);
        }
        
        return $action;
        
    }
    
    
    /**
     * register_layout
     *
     * @param $layout
     *
     * @return array
     */
    function register_layout($layout){
    
        return array(
    
            /**
             * documentation
             */
            array(
                'key' => 'field_doc',
                'label' => '',
                'name' => '',
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
    
            /**
             * action
             */
            array(
                'key' => 'field_tab_action',
                'label' => __('Action', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_type',
                'label' => __('Action', 'acfe'),
                'name' => 'type',
                'type' => 'radio',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    'insert_term' => __('Create term', 'acfe'),
                    'update_term' => __('Update term', 'acfe'),
                ),
                'default_value' => 'insert_term',
            ),
            array(
                'key' => 'field_name',
                'label' => __('Action name', 'acfe'),
                'name' => 'name',
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
                'placeholder' => __('Term', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
    
            /**
             * save
             */
            array(
                'key' => 'field_tab_save',
                'label' => __('Save', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            
            
            array(
                'key' => 'field_save_target_group',
                'label' => __('Target', 'acfe'),
                'name' => 'save_target_group',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'type',
                            'operator' => '==',
                            'value' => 'update_term',
                        ),
                    ),
                ),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_save_target',
                        'label' => '',
                        'name' => 'target',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            '{term}'        => __('Current Term', 'acfe'),
                            '{term:parent}' => __('Current Term Parent', 'acfe'),
                            'custom'        => __('Term Selector', 'acfe'),
                        ),
                        'default_value' => array(),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 1,
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax'
                    ),
                    array(
                        'key' => 'field_save_target_custom',
                        'label' => '',
                        'name' => 'target_custom',
                        'type' => 'acfe_taxonomy_terms',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_target',
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
                        'field_type' => 'select',
                        'return_format' => 'id',
                        'ui' => true,
                        'ajax' => true,
                        'default_value' => '',
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_name',
                'label' => __('Name', 'acfe'),
                'name' => 'save_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    '{generated_id}'  => __('Generated ID', 'acfe'),
                    '#{generated_id}' => __('#Generated ID', 'acfe'),
                ),
                'default_value' => array(
                ),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_slug',
                'label' => __('Slug', 'acfe'),
                'name' => 'save_slug',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(
                    '{generated_id}' => __('Generated ID', 'acfe'),
                ),
                'default_value' => array(
                ),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            array(
                'key' => 'field_save_taxonomy',
                'label' => __('Taxonomy', 'acfe'),
                'name' => 'save_taxonomy',
                'type' => 'acfe_taxonomies',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'taxonomy' => '',
                'field_type' => 'select',
                'default_value' => '',
                'return_format' => 'name',
                'allow_null' => 1,
                'placeholder' => __('Default', 'acfe'),
                'multiple' => 0,
                'ui' => 1,
                'choices' => array(),
                'ajax' => 1,
                'layout' => '',
                'toggle' => 0,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
            ),
            
            
            array(
                'key' => 'field_save_parent_group',
                'label' => __('Parent', 'acfe'),
                'name' => 'save_parent_group',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_save_parent',
                        'label' => '',
                        'name' => 'parent',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            '{term}'        => __('Current Term', 'acfe'),
                            '{term:parent}' => __('Current Term Parent', 'acfe'),
                            'custom'        => __('Term Selector', 'acfe'),
                        ),
                        'default_value' => array(),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 1,
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax'
                    ),
                    array(
                        'key' => 'field_save_parent_custom',
                        'label' => '',
                        'name' => 'parent_custom',
                        'type' => 'acfe_taxonomy_terms',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_parent',
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
                        'field_type' => 'select',
                        'return_format' => 'id',
                        'ui' => true,
                        'ajax' => true,
                        'default_value' => '',
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_description_group',
                'label' => __('Description', 'acfe'),
                'name' => 'save_description_group',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_save_description',
                        'label' => '',
                        'name' => 'description',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            'custom' => __('Content Editor', 'acfe'),
                        ),
                        'default_value' => array(),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 1,
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax',
                    ),
                    array(
                        'key' => 'field_save_description_custom',
                        'label' => '',
                        'name' => 'description_custom',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_description',
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
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 0,
                    ),
                ),
            ),
    
            array(
                'key' => 'field_save_acf_fields',
                'label' => __('Save ACF fields', 'acfe'),
                'name' => 'save_acf_fields',
                'type' => 'checkbox',
                'instructions' => __('Which ACF fields should be saved as metadata', 'acfe'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'choices' => array(),
                'allow_custom' => 0,
                'default_value' => array(),
                'layout' => 'vertical',
                'toggle' => 0,
                'return_format' => 'value',
                'save_custom' => 0,
            ),
    
            /**
             * load
             */
            array(
                'key' => 'field_tab_load',
                'label' => __('Load', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_load_active',
                'label' => __('Load Values', 'acfe'),
                'name' => 'load_active',
                'type' => 'true_false',
                'instructions' => __('Fill inputs with values', 'acfe'),
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 1,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            
            
            array(
                'key' => 'field_load_source_group',
                'label' => __('Source', 'acfe'),
                'name' => 'load_source_group',
                'type' => 'group',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
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
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_load_source',
                        'label' => '',
                        'name' => 'source',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            '{term}'        => __('Current Term', 'acfe'),
                            '{term:parent}' => __('Current Term Parent', 'acfe'),
                            'custom'        => __('Term Selector', 'acfe'),
                        ),
                        'default_value' => array(),
                        'allow_null' => 1,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 1,
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax'
                    ),
                    array(
                        'key' => 'field_load_source_custom',
                        'label' => '',
                        'name' => 'source_custom',
                        'type' => 'acfe_taxonomy_terms',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_load_source',
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
                        'field_type' => 'select',
                        'return_format' => 'id',
                        'ui' => true,
                        'ajax' => true,
                        'default_value' => '',
                    )
                ),
            ),
            
            array(
                'key' => 'field_load_name',
                'label' => __('Name', 'acfe'),
                'name' => 'load_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_name'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_slug',
                'label' => __('Slug', 'acfe'),
                'name' => 'load_slug',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_slug'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_taxonomy',
                'label' => __('Taxonomy', 'acfe'),
                'name' => 'load_taxonomy',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_taxonomy'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_parent',
                'label' => __('Parent', 'acfe'),
                'name' => 'load_parent',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_parent'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_description',
                'label' => __('Description', 'acfe'),
                'name' => 'load_description',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_description'
                ),
                'choices' => array(),
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('None', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a field key', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
                            'operator' => '==',
                            'value' => '1',
                        ),
                    ),
                ),
            ),
            array(
                'key' => 'field_load_acf_fields',
                'label' => __('Load ACF fields', 'acfe'),
                'name' => 'load_acf_fields',
                'type' => 'checkbox',
                'instructions' => __('Select which ACF fields should have their values loaded', 'acfe'),
                'required' => 0,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_load_active',
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
                'choices' => array(),
                'allow_custom' => 0,
                'default_value' => array(),
                'layout' => 'vertical',
                'toggle' => 0,
                'return_format' => 'value',
                'save_custom' => 0,
            ),

        );
        
    }
    
}

acfe_register_form_action_type('acfe_module_form_action_term');

endif;