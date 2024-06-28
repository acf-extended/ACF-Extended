<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_action_post')):

class acfe_module_form_action_post extends acfe_module_form_action{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'post';
        $this->title = __('Post action', 'acfe');
        
        $this->item = array(
            'action' => 'post',
            'type'   => 'insert_post', // insert_post | update_post
            'name'   => '',
            'save'   => array(
                'target'         => '',
                'post_type'      => '',
                'post_status'    => '',
                'post_title'     => '',
                'post_name'      => '',
                'post_content'   => '',
                'post_excerpt'   => '',
                'post_author'    => '',
                'post_parent'    => '',
                'post_date'      => '',
                'post_thumbnail' => '',
                'post_terms'     => '',
                'append_terms'   => '',
                'acf_fields'     => array(),
            ),
            'load'   => array(
                'source'         => '',
                'post_type'      => '',
                'post_status'    => '',
                'post_title'     => '',
                'post_name'      => '',
                'post_content'   => '',
                'post_excerpt'   => '',
                'post_author'    => '',
                'post_parent'    => '',
                'post_date'      => '',
                'post_thumbnail' => '',
                'post_terms'     => '',
                'acf_fields'     => array(),
            ),
        );
        
        $this->fields = array('post_type', 'post_status', 'post_title', 'post_name', 'post_content', 'post_excerpt', 'post_author', 'post_parent', 'post_date', 'post_date_gmt', 'edit_date');
        
    }
    
    
    /**
     * load_action
     *
     * acfe/form/load_post:9
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
        $post_id = acf_extract_var($load, 'source');
        $post_thumbnail = acf_extract_var($load, 'post_thumbnail');
        $post_terms = acf_extract_var($load, 'post_terms');
        $acf_fields = acf_extract_var($load, 'acf_fields');
        $acf_fields = acf_get_array($acf_fields);
        $acf_fields_exclude = array();
        
        // filters
        $post_id = apply_filters("acfe/form/load_post_id",                          $post_id, $form, $action);
        $post_id = apply_filters("acfe/form/load_post_id/form={$form['name']}",     $post_id, $form, $action);
        $post_id = apply_filters("acfe/form/load_post_id/action={$action['name']}", $post_id, $form, $action);
        
        // bail early if no source
        if(!$post_id){
            return $form;
        }
        
        // get source post
        $post = get_post($post_id);
    
        // no post found
        if(!$post){
            return $form;
        }
        
        /**
         * load post fields
         *
         * $load = array(
         *     post_type    => 'field_655af3dd3bd56',
         *     post_status  => 'field_655af3dd3bd56',
         *     post_title   => 'field_655af3dd3bd56',
         *     post_name    => '',
         *     post_content => '',
         *     post_excerpt => '',
         *     post_author  => '',
         *     post_parent  => '',
         * )
         */
        foreach($load as $post_field => $field_key){
            
            // check field is not hidden and has no value set in 'acfe/form/load_form'
            if(acf_maybe_get($form['map'], $field_key) !== false && !isset($form['map'][ $field_key ]['value'])){
                
                // check key exists in WP_Post and is field key
                if(in_array($post_field, $this->fields) && !empty($field_key) && is_string($field_key) && acf_is_field_key($field_key)){
                    
                    // add field to excluded list
                    $acf_fields_exclude[] = $field_key;
                    
                    // assign post field as value
                    $form['map'][ $field_key ]['value'] = get_post_field($post_field, $post_id);
                    
                }
                
            }
            
        }
        
        // load post thumbnail
        if(!empty($post_thumbnail) && is_string($post_thumbnail) && acf_is_field_key($post_thumbnail)){
            
            // vars
            $field_key = $post_thumbnail;
            
            // check field is not hidden and has no value set in 'acfe/form/load_form'
            if(acf_maybe_get($form['map'], $field_key) !== false && !isset($form['map'][ $field_key ]['value'])){
        
                // add field to excluded list
                $acf_fields_exclude[] = $field_key;
                
                // get thumbnail
                $thumbnail_id = get_post_thumbnail_id($post_id);
        
                // map thumbnail value
                if($thumbnail_id){
                    $form['map'][ $field_key ]['value'] = $thumbnail_id;
                }
            
            }
        
        }
        
        // load post terms
        if(!empty($post_terms) && is_string($post_terms) && acf_is_field_key($post_terms)){
            
            // field key
            $field_key = $post_terms;
            
            // check field is not hidden and has no value set in 'acfe/form/load_form'
            if(acf_maybe_get($form['map'], $field_key) !== false && !isset($form['map'][ $field_key ]['value'])){
                
                // vars
                $terms = array();
        
                // add field to excluded list
                $acf_fields_exclude[] = $field_key;
                
                // get taxonomies
                $taxonomies = acf_get_taxonomies(array(
                    'post_type' => get_post_type($post_id)
                ));
        
                // loop
                foreach($taxonomies as $taxonomy){
            
                    // get taxonomy terms
                    $_terms = get_the_terms($post_id, $taxonomy);
                    
                    // validate
                    if($_terms && !is_wp_error($_terms)){
                        $terms = array_merge($terms, $_terms);
                    }
            
                }
        
                // map terms value
                if($terms){
                    $form['map'][ $field_key ]['value'] = wp_list_pluck($terms, 'term_id');
                }
            
            }
        
        }
    
        // load acf values
        $form = $this->load_acf_values($form, $post_id, $acf_fields, $acf_fields_exclude);
        
        // media field keys
        if($action['type'] === 'update_post'){
            
            // vars
            $field_keys = array();
            $all_fields = array_merge(array_values($load), array_values($acf_fields));
            
            // loop post fields
            foreach($all_fields as $field_key){
                
                // get field
                $field = acf_get_field($field_key);
                
                // check field type
                if($field && in_array($field['type'], array('file', 'image', 'gallery'))){
                    $field_keys[] = $field_key;
                }
                
            }
            
            // localize data
            if(!empty($field_keys)){
                
                add_filter('acfe/form/set_form_data', function($data, $displayed_form) use($post_id, $field_keys, $form){
                    
                    if($displayed_form['cid'] === $form['cid']){
                        $data['media'] = array(
                            'post_id' => $post_id,
                            'fields'  => $field_keys
                        );
                    }
                    
                    return $data;
                    
                }, 10, 2);
                
            }
            
        }
        
        // return
        return $form;
    
    }
    
    
    /**
     * prepare_action
     *
     * acfe/form/prepare_post:9
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
     * acfe/form/make_post:9
     *
     * @param $form
     * @param $action
     */
    function make_action($form, $action){
    
        // insert/update post
        $process = $this->process($form, $action);
    
        // validate
        if(!$process){
            return;
        }
    
        // process vars
        $post_id = $process['post_id'];
        $args = $process['args'];
        
        // output
        $this->generate_output($post_id, $args, $form, $action);
        
        // update gallery attachment
        add_filter('acf/update_value/type=gallery', array($this, 'update_gallery_value'), 20, 3);
    
        // acf values
        $this->save_acf_fields($post_id, $action);
        
        remove_filter('acf/update_value/type=gallery', array($this, 'update_gallery_value'), 20);
        
        // hooks
        do_action("acfe/form/submit_post",                          $post_id, $args, $form, $action);
        do_action("acfe/form/submit_post/form={$form['name']}",     $post_id, $args, $form, $action);
        do_action("acfe/form/submit_post/action={$action['name']}", $post_id, $args, $form, $action);
        
        // update queried object
        // this fix an issue where current post is not displaying updated title/content/author
        // when the form update the queried_object() post
        if($action['type'] === 'update_post'){
            if($post_id === get_queried_object_id()){
                
                // refresh current post
                global $wp;
                $wp->query_posts();
                
            }
        }
    
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
        $has_post_parent = !acf_is_empty($action['save']['post_parent']);
        $has_post_thumbnail = !acf_is_empty($action['save']['post_thumbnail']);
        
        // tags context
        $opt     = array('context' => 'save');
        $opt_fmt = array('context' => 'save', 'format' => false);
        $opt_raw = array('context' => 'save', 'format' => false, 'return' => 'raw');
        
        // apply tags
        acfe_apply_tags($action['save']['target'],         $opt_fmt);
        acfe_apply_tags($action['save']['post_type'],      $opt_fmt);
        acfe_apply_tags($action['save']['post_status'],    $opt_fmt);
        acfe_apply_tags($action['save']['post_title'],     $opt);
        acfe_apply_tags($action['save']['post_name'],      $opt);
        acfe_apply_tags($action['save']['post_content'],   $opt);
        acfe_apply_tags($action['save']['post_excerpt'],   $opt);
        acfe_apply_tags($action['save']['post_author'],    $opt_fmt);
        acfe_apply_tags($action['save']['post_parent'],    $opt_fmt);
        acfe_apply_tags($action['save']['post_date'],      $opt_fmt);
        acfe_apply_tags($action['save']['post_thumbnail'], $opt_fmt);
        acfe_apply_tags($action['save']['post_terms'],     $opt_raw);
        
        // if post parent is supposed to have a value but is empty, set it to 0
        // post_parent was most likely removed from the field
        if($has_post_parent && acf_is_empty($action['save']['post_parent'])){
            $action['save']['post_parent'] = 0;
        }
        
        // if post thumbnail is supposed to have a value but is empty, set it to 0
        // post_thumbnail was most likely removed from the field
        if($has_post_thumbnail && acf_is_empty($action['save']['post_thumbnail'])){
            $action['save']['post_thumbnail'] = 0;
        }
        
        // post date
        if(!empty($action['save']['post_date'])){
            
            $post_date = $action['save']['post_date'];
            
            // timestamp
            $timestamp = $post_date;
            
            // date format
            if(!is_numeric($post_date)){
                $post_date = str_replace('/', '-', $post_date);
                $timestamp = strtotime($post_date);
            }
            
            if($timestamp){
                
                $action['save']['post_date'] = wp_date('Y-m-d H:i:s', $timestamp);
                $action['save']['post_date_gmt'] = get_gmt_from_date($action['save']['post_date']);
                $action['save']['edit_date'] = true;
                
            }
            
        }
        
        // post terms
        $post_terms = acf_get_array($action['save']['post_terms']);
        $action['save']['post_terms'] = array();
        
        foreach($post_terms as $term_id){
            
            // if $term_id is an array (ie: multiselect field) then merge it with $action['save']['post_terms']
            if(is_array($term_id)){
                $action['save']['post_terms'] = array_merge($action['save']['post_terms'], $term_id);
            }else{
                $action['save']['post_terms'][] = $term_id;
            }
            
        }
        
        // sanitize post terms
        $action['save']['post_terms'] = array_unique($action['save']['post_terms']);
        $action['save']['post_terms'] = array_filter($action['save']['post_terms']);
        
        // sanitize append terms
        $action['save']['append_terms'] = (bool) $action['save']['append_terms'];
        
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
        $post_id = (int) acf_extract_var($save, 'target');
        $post_thumbnail = acf_extract_var($save, 'post_thumbnail');
        $post_terms = acf_extract_var($save, 'post_terms');
        $append_terms = acf_extract_var($save, 'append_terms');
        
        // pre-insert post
        if($action['type'] === 'insert_post'){
            
            $post_id = wp_insert_post(array(
                'post_title' => 'Post'
            ));
            
        }
        
        // invalid target
        if(!$post_id || is_wp_error($post_id)){
            return false;
        }
        
        // context
        // generated_id
        acfe_add_context(array('context' => 'save', 'generated_id' => $post_id));
        
        acfe_apply_tags($action['save']['post_title']);
        acfe_apply_tags($action['save']['post_name']);
        
        $save['post_title'] = $action['save']['post_title'];
        $save['post_name'] = $action['save']['post_name'];
        
        acfe_delete_context(array('context', 'generated_id'));
    
        // default post arguments
        $args = array(
            'ID' => $post_id
        );
    
        // construct post arguments
        foreach($save as $post_field => $value){
        
            // post_type, post_title, post_status, post_content etc...
            if(in_array($post_field, $this->fields) && !acf_is_empty($value)){
                $args[ $post_field ] = $value;
            }
        
        }
    
        // filters
        $args = apply_filters("acfe/form/submit_post_args",                          $args, $form, $action);
        $args = apply_filters("acfe/form/submit_post_args/form={$form['name']}",     $args, $form, $action);
        $args = apply_filters("acfe/form/submit_post_args/action={$action['name']}", $args, $form, $action);
    
        // bail early
        if($args === false){
        
            // delete pre-insert post
            if($action['type'] === 'insert_post'){
                wp_delete_post($post_id, true);
            }
        
            return false;
        
        }
    
        // update post
        $post_id = wp_update_post($args);
    
        // bail early
        if(!$post_id || is_wp_error($post_id)){
            return false;
        }
    
        // post thumbnail
        if(!acf_is_empty($post_thumbnail)){
            
            if($post_thumbnail){
                set_post_thumbnail($post_id, $post_thumbnail);
            }else{
                delete_post_thumbnail($post_id);
            }
            
        }
    
        // post terms
        $process_terms = array();
        
        // loop post terms
        foreach($post_terms as $value){
        
            // vars
            $taxonomy = false;
            $id_or_slug = false;
        
            // numeric
            if(is_numeric($value)){
                
                // get term by id
                $term = get_term($value);
                
                if(!empty($term) && !is_wp_error($term)){
                    $taxonomy = $term->taxonomy;
                    $id_or_slug = $term->term_id;
                }
            
            // slug
            }elseif(is_string($value)){
                
                // slug can be in the following format:
                // My Term|taxonomy
                $keys       = explode('|', $value);
                $id_or_slug = array_shift($keys);
                $taxonomy   = array_shift($keys);
            
                // no taxonomy provided
                if(!$taxonomy){
                
                    // try to retrieve taxonomy from the created post
                    $post_type = acf_maybe_get($args, 'post_type', 'post');
                    $taxonomies = get_object_taxonomies($post_type);
                    $taxonomy = array_shift($taxonomies);
                
                }
            
            }
        
            // assign term
            if($taxonomy && $id_or_slug){
                
                // group by taxonomy
                $process_terms[ $taxonomy ][] = $id_or_slug;
                
            }
        
        }
        
        // loop terms to set
        foreach($process_terms as $taxonomy => $terms){
            
            // deprecated filter
            $append_terms = apply_filters_deprecated("acfe/form/submit/post_append_terms",                          array($append_terms, $post_id, $terms, $taxonomy, $form, $action['name']), '0.9', "acfe/form/prepare_post");
            $append_terms = apply_filters_deprecated("acfe/form/submit/post_append_terms/form={$form['name']}",     array($append_terms, $post_id, $terms, $taxonomy, $form, $action['name']), '0.9', "acfe/form/prepare_post/form={$form['name']}");
            $append_terms = apply_filters_deprecated("acfe/form/submit/post_append_terms/action={$action['name']}", array($append_terms, $post_id, $terms, $taxonomy, $form, $action['name']), '0.9', "acfe/form/prepare_post/action={$action['name']}");
            
            wp_set_object_terms($post_id, $terms, $taxonomy, $append_terms);
        
        }
    
        // return
        return array(
            'post_id' => $post_id,
            'args'    => $args
        );
        
    }
    
    
    /**
     * generate_output
     *
     * @param $post_id
     * @param $args
     * @param $form
     * @param $action
     */
    function generate_output($post_id, $args, $form, $action){
    
        // post array
        $post = get_post($post_id, ARRAY_A);
        $post['permalink'] = get_permalink($post_id);
        $post['admin_url'] = admin_url("post.php?post={$post_id}&action=edit");
        
        // get user array
        $user = acfe_get_form_action_type('user')->get_user_array($post['post_author']);
        
        if($user){
            $post['post_author_data'] = $user;
        }
    
        // filters
        $post = apply_filters("acfe/form/submit_post_output",                          $post, $args, $form, $action);
        $post = apply_filters("acfe/form/submit_post_output/form={$form['name']}",     $post, $args, $form, $action);
        $post = apply_filters("acfe/form/submit_post_output/action={$action['name']}", $post, $args, $form, $action);
    
        // action output
        $this->set_action_output($post, $action);
        
    }
    
    
    /**
     * update_gallery_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return mixed
     */
    function update_gallery_value($value, $post_id, $field){
        
        // bail early
        if(empty($value)){
            return $value;
        }
        
        // loop attachments
        foreach($value as $attachment_id){
            acf_connect_attachment_to_post($attachment_id, $post_id);
        }
        
        return $value;
        
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
                'target'          => function($value){return !empty($value) && is_numeric($value);},
                'post_content'    => function($value){return acfe_is_html(nl2br($value));},
                'post_excerpt'    => function($value){return acfe_is_html(nl2br($value));},
                'post_author'     => function($value){return !empty($value) && is_numeric($value);},
                'post_date'       => function($value){return !empty($value) && DateTime::createFromFormat('Y-m-d H:i:s', $value) !== false;},
                'post_thumbnail'  => function($value){return !empty($value) && is_numeric($value);},
                'post_parent'     => function($value){return !empty($value) && is_numeric($value);},
            ),
            'load' => array(
                'source'          => function($value){return !empty($value) && is_numeric($value);},
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
            
            // post_type => save_post_type
            if(acf_maybe_get($action, "save_{$k}")){
                $save['save'][ $k ] = $action["save_{$k}"];
            }
            
        }
        
        // groups
        $keys = array(
            'save' => array('target', 'post_content', 'post_excerpt', 'post_author', 'post_date', 'post_thumbnail', 'post_parent'),
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
        
                // post_type => load_post_type
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
        if($action['type'] === 'update_post' && empty($save['save']['target'])){
            $save['save']['target'] = '{post}';
        }
        
        // default load: source
        if($action['load_active'] && empty($save['load']['source'])){
            $save['load']['source'] = '{post}';
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
        
        // cleanup save: target
        if($action['type'] === 'insert_post'){
            unset($action['save']['target']);
        }
        
        // cleanup load
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
                    echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/post-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
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
                    'insert_post' => __('Create post', 'acfe'),
                    'update_post' => __('Update post', 'acfe'),
                ),
                'default_value' => 'insert_post',
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
                'default_value' => '',
                'placeholder' => __('Post', 'acfe'),
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
                            'value' => 'update_post',
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
                            '{post}'             => __('Current Post', 'acfe'),
                            '{post:post_parent}' => __('Current Post Parent', 'acfe'),
                            'custom'             => __('Post Selector', 'acfe'),
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
                        'type' => 'post_object',
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
                        'return_format' => 'id',
                        'default_value' => '',
                    ),
                ),
            ),
            
            
            array(
                'key' => 'field_save_post_type',
                'label' => __('Post type', 'acfe'),
                'name' => 'save_post_type',
                'type' => 'acfe_post_types',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'post_type' => '',
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
                'ajax_action' => 'acfe/form/map_field_ajax'
            ),
            array(
                'key' => 'field_save_post_status',
                'label' => __('Post status', 'acfe'),
                'name' => 'save_post_status',
                'type' => 'acfe_post_statuses',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'post_status' => '',
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
                'ajax_action' => 'acfe/form/map_field_ajax'
            ),
    
            array(
                'key' => 'field_save_post_title',
                'label' => __('Post title', 'acfe'),
                'name' => 'save_post_title',
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
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax'
            ),
            array(
                'key' => 'field_save_post_name',
                'label' => __('Post name', 'acfe'),
                'name' => 'save_post_name',
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
                'default_value' => array(),
                'allow_null' => 1,
                'multiple' => 0,
                'ui' => 1,
                'return_format' => 'value',
                'placeholder' => __('Default', 'acfe'),
                'ajax' => 1,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'conditional_logic' => array(),
                'ajax_action' => 'acfe/form/map_field_ajax'
            ),
            array(
                'key' => 'field_save_post_content_group',
                'label' => __('Post content', 'acfe'),
                'name' => 'save_post_content_group',
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
                        'key' => 'field_save_post_content',
                        'label' => '',
                        'name' => 'post_content',
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
                        'ajax_action' => 'acfe/form/map_field_ajax'
                    ),
                    array(
                        'key' => 'field_save_post_content_custom',
                        'label' => '',
                        'name' => 'post_content_custom',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_post_content',
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
                'key' => 'field_save_post_excerpt_group',
                'label' => __('Post excerpt', 'acfe'),
                'name' => 'save_post_excerpt_group',
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
                        'key' => 'field_save_post_excerpt',
                        'label' => '',
                        'name' => 'post_excerpt',
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
                        'ajax_action' => 'acfe/form/map_field_ajax'
                    ),
                    array(
                        'key' => 'field_save_post_excerpt_custom',
                        'label' => '',
                        'name' => 'post_excerpt_custom',
                        'type' => 'textarea',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_post_excerpt',
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
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_post_author_group',
                'label' => __('Post author', 'acfe'),
                'name' => 'save_post_author_group',
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
                        'key' => 'field_save_post_author',
                        'label' => '',
                        'name' => 'post_author',
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
                            '{user}'             => __('Current User', 'acfe'),
                            '{post:post_author}' => __('Current Post Author', 'acfe'),
                            'custom'             => __('User Selector', 'acfe'),
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
                        'key' => 'field_save_post_author_custom',
                        'label' => '',
                        'name' => 'post_author_custom',
                        'type' => 'user',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_post_author',
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
                        'return_format' => 'id',
                        'default_value' => '',
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_post_date_group',
                'label' => __('Post date', 'acfe'),
                'name' => 'save_post_date_group',
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
                        'key' => 'field_save_post_date',
                        'label' => '',
                        'name' => 'post_date',
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
                            'custom' => __('Date picker', 'acfe'),
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
                        'key' => 'field_save_post_date_custom',
                        'label' => '',
                        'name' => 'post_date_custom',
                        'type' => 'date_time_picker',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_post_date',
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
                        'display_format' => 'd/m/Y H:i:s',
                        'return_format' => 'Y-m-d H:i:s',
                        'default_value' => '',
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_post_thumbnail_group',
                'label' => __('Post thumbnail', 'acfe'),
                'name' => 'save_post_thumbnail_group',
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
                        'key' => 'field_save_post_thumbnail',
                        'label' => '',
                        'name' => 'post_thumbnail',
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
                            'custom' => __('Image Selector', 'acfe'),
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
                        'key' => 'field_save_post_thumbnail_custom',
                        'label' => '',
                        'name' => 'post_thumbnail_custom',
                        'type' => 'image',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_post_thumbnail',
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
                        'preview_size' => 'thumbnail',
                        'return_format' => 'id',
                        'default_value' => '',
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_post_parent_group',
                'label' => __('Post parent', 'acfe'),
                'name' => 'save_post_parent_group',
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
                        'key' => 'field_save_post_parent',
                        'label' => '',
                        'name' => 'post_parent',
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
                            '{post}'             => __('Current Post', 'acfe'),
                            '{post:post_parent}' => __('Current Post Parent', 'acfe'),
                            'custom'             => __('Post Selector', 'acfe'),
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
                        'key' => 'field_save_post_parent_custom',
                        'label' => '',
                        'name' => 'post_parent_custom',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_save_post_parent',
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
                        'return_format' => 'id',
                        'default_value' => '',
                    ),
                ),
            ),
            
            array(
                'key' => 'field_save_post_terms',
                'label' => __('Post terms', 'acfe'),
                'name' => 'save_post_terms',
                'type' => 'acfe_taxonomy_terms',
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
                'return_format' => 'id',
                'allow_null' => 1,
                'placeholder' => __('Default', 'acfe'),
                'multiple' => 1,
                'ui' => 1,
                'ajax' => 1,
                'choices' => array(),
                'layout' => '',
                'toggle' => 0,
                'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                'allow_custom' => 1,
                'ajax_action' => 'acfe/form/map_field_ajax'
            ),
            array(
                'key' => 'field_save_append_terms',
                'label' => __('Append terms', 'acfe'),
                'name' => 'save_append_terms',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'ui' => 0,
                'message' => __('Append', 'acfe'),
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
                            '{post}'             => __('Current Post', 'acfe'),
                            '{post:post_parent}' => __('Current Post Parent', 'acfe'),
                            'custom'             => __('Post Selector', 'acfe'),
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
                        'type' => 'post_object',
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
                        'return_format' => 'id',
                        'default_value' => '',
                    ),
                ),
            ),
            array(
                'key' => 'field_load_post_type',
                'label' => __('Post type', 'acfe'),
                'name' => 'load_post_type',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_type'
                ),
                'choices' => array(),
                'default_value' => '',
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
                'key' => 'field_load_post_status',
                'label' => __('Post status', 'acfe'),
                'name' => 'load_post_status',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_status'
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
                'key' => 'field_load_post_title',
                'label' => __('Post title', 'acfe'),
                'name' => 'load_post_title',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_title'
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
                'key' => 'field_load_post_name',
                'label' => __('Post name', 'acfe'),
                'name' => 'load_post_name',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_name'
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
                'key' => 'field_load_post_content',
                'label' => __('Post content', 'acfe'),
                'name' => 'load_post_content',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_content'
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
                'key' => 'field_load_post_excerpt',
                'label' => __('Post excerpt', 'acfe'),
                'name' => 'load_post_excerpt',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_excerpt'
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
                'key' => 'field_load_post_author',
                'label' => __('Post author', 'acfe'),
                'name' => 'load_post_author',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_author'
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
                'key' => 'field_load_post_date',
                'label' => __('Post date', 'acfe'),
                'name' => 'load_post_date',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_date'
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
                'key' => 'field_load_post_thumbnail',
                'label' => __('Post thumbnail', 'acfe'),
                'name' => 'load_post_thumbnail',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_thumbnail'
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
                'key' => 'field_load_post_parent',
                'label' => __('Post parent', 'acfe'),
                'name' => 'load_post_parent',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_parent'
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
                'key' => 'field_load_post_terms',
                'label' => __('Post terms', 'acfe'),
                'name' => 'load_post_terms',
                'type' => 'select',
                'instructions' => '',
                'required' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                    'data-related-field' => 'field_save_post_terms'
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

acfe_register_form_action_type('acfe_module_form_action_post');

endif;