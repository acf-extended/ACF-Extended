<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_advanced_link')):

class acfe_field_advanced_link extends acf_field{
    
    /**
     * initialize
     */
    function initialize(){

        $this->name = 'acfe_advanced_link';
        $this->label = __('Advanced Link', 'acfe');
        $this->category = 'relational';
        $this->defaults = array(
            'post_type' => array(),
            'taxonomy'  => array(),
        );
        
        $this->add_action('wp_ajax_acfe/fields/advanced_link/post_query',        array($this, 'ajax_query'));
        $this->add_action('wp_ajax_nopriv_acfe/fields/advanced_link/post_query', array($this, 'ajax_query'));

    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
        
        // filter post types
        acf_render_field_setting($field, array(
            'label'         => __('Filter by Post Type', 'acf'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'post_type',
            'choices'       => acf_get_pretty_post_types(),
            'multiple'      => 1,
            'ui'            => 1,
            'allow_null'    => 1,
            'placeholder'   => __('All post types', 'acf'),
        ));
        
        // filter taxonomies
        acf_render_field_setting($field, array(
            'label'         => __('Filter by Taxonomy', 'acf'),
            'instructions'  => '',
            'type'          => 'select',
            'name'          => 'taxonomy',
            'choices'       => acf_get_taxonomy_terms(),
            'multiple'      => 1,
            'ui'            => 1,
            'allow_null'    => 1,
            'placeholder'   => __('All taxonomies', 'acf'),
        ));
        
    }
    
    
    /**
     * render_field
     *
     * @param $field
     */
    function render_field($field){
        
        // vars
        $div = array(
            'id'    => $field['id'],
            'class' => $field['class'] . ' acf-link',
        );
    
        // subfields
        $sub_fields = $this->get_sub_fields($field);
        
        // render value
        $render = $this->render_value($field['value']);
        
        // classes
        if($render['url'] || $render['title']){
            $div['class'] .= ' -value';
        }
        
        if($render['target']){
            $div['class'] .= ' -external';
        }
        
        ?>
        
        <div <?php echo acf_esc_atts($div); ?>>
    
            <?php acf_hidden_input(array('name' => $field['name'])); ?>
        
            <div class="acfe-modal" data-title="<?php echo $field['label']; ?>" data-size="medium" data-footer="<?php _e('Close', 'acfe'); ?>">
                <div class="acfe-modal-wrapper">
                    <div class="acfe-modal-content">
                    
                    <div class="acf-fields -left">
                        
                        <?php foreach($sub_fields as $sub_field): ?>
                        
                            <?php acf_render_field_wrap($sub_field); ?>
        
                        <?php endforeach; ?>
                        
                    </div>
                        
                    </div>
                </div>
            </div>
            
            <a href="#" class="button" data-name="add" target=""><?php _e('Select Link', 'acf'); ?></a>
            
            <div class="link-wrap">
                <span class="link-title"><?php echo esc_html($render['title']); ?></span>
                <a class="link-url" href="<?php echo esc_url($render['url']); ?>" target="_blank"><?php echo esc_html($render['name']); ?></a>
                <i class="acf-icon -link-ext acf-js-tooltip" title="<?php _e('Opens in a new window/tab', 'acf'); ?>"></i><?php
                ?><a class="acf-icon -pencil -clear acf-js-tooltip" data-name="edit" href="#" title="<?php _e('Edit', 'acf'); ?>"></a><?php
                ?><a class="acf-icon -cancel -clear acf-js-tooltip" data-name="remove" href="#" title="<?php _e('Remove', 'acf'); ?>"></a>
            </div>
            
        </div>
        <?php
        
    }
    
    
    /**
     * get_sub_fields
     *
     * @param $field
     *
     * @return mixed|null
     */
    function get_sub_fields($field){
        
        // get value
        $value = $field['value'];
        
        // storage
        $sub_fields = array();
        
        // type
        $sub_fields[] = array(
            'name'      => 'type',
            'key'       => 'type',
            'label'     => __('Type', 'acf'),
            'type'      => 'radio',
            'required'  => false,
            'class'     => 'input-type',
            'choices'   => array(
                'url'       => __('URL', 'acf'),
                'post'      => __('Post', 'acf'),
                'term'      => __('Term', 'acf'),
            ),
        );
        
        // url
        $sub_fields[] = array(
            'name'              => 'url',
            'key'               => 'url',
            'label'             => __('URL', 'acf'),
            'type'              => 'text',
            'required'          => false,
            'class'             => 'input-url',
            'value'             => isset($value['type']) && $value['type'] === 'url' ? $value['value'] : '', // inject value based on type
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'type',
                        'operator'  => '==',
                        'value'     => 'url',
                    )
                )
            )
        );
        
        // post
        $sub_fields[] = array(
            'name'              => 'post',
            'key'               => 'post',
            'label'             => __('Post', 'acf'),
            'type'              => 'select',
            'required'          => false,
            'class'             => 'input-post',
            'allow_null'        => 0,
            'ui'                => 1,
            'ajax'              => 1,
            'ajax_action'       => 'acfe/fields/advanced_link/post_query',
            'choices'           => $this->get_post_choices($field),
            'value'             => isset($value['type']) && $value['type'] === 'post' ? $value['value'] : '', // inject value based on type
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'type',
                        'operator'  => '==',
                        'value'     => 'post',
                    )
                )
            )
        );
        
        // term
        $sub_fields[] = array(
            'name'              => 'term',
            'key'               => 'term',
            'label'             => __('Term', 'acf'),
            'type'              => 'acfe_taxonomy_terms',
            'required'          => false,
            'class'             => 'input-term',
            'field_type'        => 'select',
            'return_format'     => 'id',
            'ui'                => 1,
            'allow_null'        => 0,
            'value'             => isset($value['type']) && $value['type'] === 'term' ? $value['value'] : '', // inject value based on type
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'type',
                        'operator'  => '==',
                        'value'     => 'term',
                    )
                )
            )
        );
        
        // title
        $sub_fields[] = array(
            'name'      => 'title',
            'key'       => 'title',
            'label'     => __('Link text', 'acf'),
            'type'      => 'text',
            'required'  => false,
            'class'     => 'input-title',
        );
        
        // target
        $sub_fields[] = array(
            'name'      => 'target',
            'key'       => 'target',
            'label'     => __('Target', 'acf'),
            'type'      => 'true_false',
            'message'   => __('Open in a new window', 'acf'),
            'required'  => false,
            'class'     => 'input-target',
        );
        
        // deprecated
        $sub_fields = apply_filters_deprecated('acfe/fields/advanced_link/fields',                         array($sub_fields, $field, $value), '0.8.1', 'acfe/fields/advanced_link/sub_fields');
        $sub_fields = apply_filters_deprecated('acfe/fields/advanced_link/fields/name=' . $field['_name'], array($sub_fields, $field, $value), '0.8.1', 'acfe/fields/advanced_link/sub_fields/name=' . $field['_name']);
        $sub_fields = apply_filters_deprecated('acfe/fields/advanced_link/fields/key=' . $field['key'],    array($sub_fields, $field, $value), '0.8.1', 'acfe/fields/advanced_link/sub_fields/key=' . $field['key']);
        
        // filters
        $sub_fields = apply_filters('acfe/fields/advanced_link/sub_fields',                         $sub_fields, $field, $value);
        $sub_fields = apply_filters('acfe/fields/advanced_link/sub_fields/name=' . $field['_name'], $sub_fields, $field, $value);
        $sub_fields = apply_filters('acfe/fields/advanced_link/sub_fields/key=' . $field['key'],    $sub_fields, $field, $value);
        
        // map subfields
        $sub_fields = acfe_map_fields($sub_fields, function($sub_field){
            
            // handle missing key
            if(!isset($sub_field['key'])){
                $sub_field['key'] = $sub_field['name'];
            }
            
            return $sub_field;
            
        });
        
        foreach($sub_fields as &$sub_field){
            
            // add value
            if(isset($value[ $sub_field['key'] ])){
                
                // this is a normal value
                $sub_field['value'] = $value[ $sub_field['key'] ];
                
            }elseif(isset($sub_field['default_value'])){
                
                // no value, but this subfield has a default value
                $sub_field['value'] = $sub_field['default_value'];
                
            }
            
            // update prefix to allow for nested values
            $sub_field['prefix'] = $field['name'];
            
        }
        
        return $sub_fields;
        
    }
    
    
    /**
     * load_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array
     */
    function load_value($value, $post_id, $field){
        
        // bail early
        if(empty($value)){
            return $value;
        }
        
        // if value is string then set as value
        if(is_string($value)){
            $value = array('value' => $value);
        }
        
        if(is_array($value)){
    
            // defaults array
            $value = wp_parse_args($value, array(
                'type'      => 'url',
                'value'     => '',
                'title'     => '',
                'target'    => false,
            ));
    
            // handle old args
            foreach(array('post', 'term') as $arg){
        
                if(isset($value[ $arg ])){
            
                    $value['value'] = $value[ $arg ];
                    unset($value[ $arg ]);
            
                }
        
            }
            
        }
        
        // return value
        return $value;
        
    }
    
    
    /**
     * format_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array
     */
    function format_value($value, $post_id, $field){
        
        // bail early
        if(empty($value)){
            return $value;
        }
        
        // if value is string then set as value
        if(is_string($value)){
            $value = array('value' => $value);
        }
        
        // return
        return $this->render_value($value);
        
    }
    
    
    /**
     * validate_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     *
     * @return false
     */
    function validate_value($valid, $value, $field, $input){
        
        // bail early if not required
        if(!$field['required']){
            return $valid;
        }
        
        // loop over fields
        foreach(array('url', 'post', 'term') as $type){
            
            if($value['type'] === $type && empty($value[ $type ])){
                return false;
            }
            
        }
        
        // return
        return $valid;
        
    }
    
    
    /**
     * update_value
     *
     * @param $value
     * @param $post_id
     * @param $field
     *
     * @return array
     */
    function update_value($value, $post_id, $field){
        
        // bail early
        if(empty($value)){
            return $value;
        }
        
        // compatibility with string
        if(is_string($value)){
            $value = array('value' => $value);
        }
        
        if($value['type'] === 'url' && !empty($value['value'])){
            $value['url'] = $value['value'];
        }
    
        // defaults
        $value = wp_parse_args($value, array(
            'type'   => 'url',
            'value'  => '',
            'url'    => '',
            'title'  => '',
            'target' => '',
        ));
    
        // loop over fields
        foreach(array('url', 'post', 'term') as $type){
        
            if($value['type'] === $type && isset($value[ $type ])){
                $value['value'] = $value[ $type ];
                break;
            }
        
        }
        
        // remove unecessary arguments
        unset($value['url'], $value['post'], $value['term']);
        
        // sanitize target
        $value['target'] = (bool) $value['target'];
        
        // empty value
        // allow to save empty value to not pollute db
        if(empty($value['value']) && empty($value['title'])){
            
            // must be empty string so options page can save empty value
            // fix a bug where option would not save value if value = false
            $value = '';
        }
        
        return $value;
        
    }
    
    
    /**
     * render_value
     *
     * @param $value
     *
     * @return array
     */
    function render_value($value){
        
        // defaults
        $value = wp_parse_args($value, array(
            'type'   => 'url',
            'value'  => '',
            'url'    => '',
            'name'   => '',
            'title'  => '',
            'target' => '',
        ));
        
        if(!empty($value['value'])){
            
            switch($value['type']){
                
                case 'url': {
                    
                    $value['url'] = $value['value'];
                    $value['name'] = $value['value'];
                    break;
                }
                
                case 'post': {
                    
                    $value['url'] = is_numeric($value['value']) ? get_permalink($value['value']) : get_post_type_archive_link($value['value']);
                    $value['name'] = is_numeric($value['value']) ? get_the_title($value['value']) : acf_get_post_type_label($value['value']) . ' Archive';
                    break;
                }
                
                case 'term': {
                    
                    $term = get_term($value['value']);
                    if(!empty($term) && !is_wp_error($term)){
                        
                        $value['url'] = get_term_link($term);
                        $value['name'] = $term->name;
                        
                    }
                    
                    break;
                }
                
            }
            
        }
        
        // format target
        $value['target'] = $value['target'] ? '_blank' : '';
        
        return $value;
        
    }
    
    
    /**
     * get_post_choices
     *
     * @param $field
     *
     * @return array
     */
    function get_post_choices($field){
        
        // vars
        $value = $field['value'];
        $choices = array();
        
        if(empty($value)){
            return $choices;
        }
        
        $post_object = acf_get_field_type('post_object');
        
        // load posts
        $posts = $post_object->get_posts($value['value'], $field);
        
        if($posts){
            
            foreach(array_keys($posts) as $i){
                
                // append choice
                $post = acf_extract_var($posts, $i);
                $choices[ $post->ID ] = $post_object->get_post_title($post, $field);
                
            }
            
        }
        
        // string value
        // post type archive
        if(!empty($value['value']) && is_string($value['value'])){
            
            // get post type
            $post_type = $value['value'];
            
            // check post type exists
            if(post_type_exists($post_type)){
                
                $label = acf_get_post_type_label($post_type);
                $choices[ $post_type ] = "{$label} Archive";
                
            }
            
        }
        
        return $choices;
        
    }
    
    
    /**
     * ajax_query
     */
    function ajax_query(){
        
        // validate
        if(!acf_verify_ajax()){
            die();
        }
        
        // get choices
        $response = $this->get_ajax_query($_POST);
        
        // return
        acf_send_ajax_results($response);
        
    }
    
    
    /**
     * get_ajax_query
     *
     * Based on the post_object get_ajax_query() function
     *
     * @param $options
     *
     * @return array|false
     */
    function get_ajax_query($options = array()){
        
        // defaults
        $options = acf_parse_args($options, array(
            'post_id'   => 0,
            's'         => '',
            'field_key' => '',
            'paged'     => 1,
        ));
        
        // post object
        $post_object = acf_get_field_type('post_object');
        
        // load field
        $field = acf_get_field($options['field_key']);
        if(!$field){
            return false;
        }
        
        // vars
        $results   = array();
        $args      = array();
        $is_search = false;
        
        // paged
        $args['posts_per_page'] = 20;
        $args['paged']          = $options['paged'];
        
        // search
        if($options['s'] !== ''){
            
            // strip slashes (search may be integer)
            $s = wp_unslash(strval($options['s']));
            
            // update vars
            $args['s'] = $s;
            $is_search = true;
            
        }
        
        // post_type
        $args['post_type'] = acf_get_post_types();
        
        if(!empty($field['post_type'])){
            $args['post_type'] = acf_get_array($field['post_type']);
        }
        
        // taxonomy
        if(!empty($field['taxonomy'])){
            
            // vars
            $terms = acf_decode_taxonomy_terms($field['taxonomy']);
            
            // append to $args
            $args['tax_query'] = array();
            
            // now create the tax queries
            foreach($terms as $k => $v){
                
                $args['tax_query'][] = array(
                    'taxonomy' => $k,
                    'field'    => 'slug',
                    'terms'    => $v,
                );
                
            }
        }
        
        // filters
        $args = apply_filters('acf/fields/post_object/query',                        $args, $field, $options['post_id']);
        $args = apply_filters('acf/fields/post_object/query/name=' . $field['name'], $args, $field, $options['post_id']);
        $args = apply_filters('acf/fields/post_object/query/key=' . $field['key'],   $args, $field, $options['post_id']);
        
        // get posts grouped by post type
        $groups = acf_get_grouped_posts($args);
    
        $archives = array();
        $post_types_archives = acfe_get_post_types(array(
            'include'     => $field['post_type'],
            'has_archive' => true,
        ));
    
        foreach($post_types_archives as $post_type){
        
            $label = acf_get_post_type_label($post_type);
            $label = "{$label} Archive";
        
            if($is_search && stripos($label, $s) === false){
                continue;
            }
        
            $archives[] = array(
                'id'   => $post_type,
                'text' => $label,
            );
        
        }
    
    
        if(!empty($archives)){
        
            // data
            $results[] = array(
                'text'     => __('Archives', 'acfe'),
                'children' => $archives,
            );
        
        }
        
        // loop
        foreach(array_keys($groups) as $group_title){
            
            // vars
            $posts = acf_extract_var($groups, $group_title);
            
            // data
            $data = array(
                'text'     => $group_title,
                'children' => array(),
            );
            
            // convert post objects to post titles
            foreach(array_keys($posts) as $post_id){
                $posts[ $post_id ] = $post_object->get_post_title($posts[ $post_id ], $field, $options['post_id'], $is_search);
            }
            
            // order posts by search
            if($is_search && empty($args['orderby']) && isset($args['s'])){
                $posts = acf_order_by_search($posts, $args['s']);
            }
            
            // append to $data
            foreach(array_keys($posts) as $post_id){
                $data['children'][] = $post_object->get_post_result($post_id, $posts[ $post_id ]);
            }
            
            // append to $results
            $results[] = $data;
            
        }
        
        // optgroup or single
        $post_type = acf_get_array($args['post_type']);
        if(count($post_type) === 1 && empty($post_types_archives)){
            $results = $results[0]['children'];
        }
        
        // vars
        $response = array(
            'results' => $results,
            'limit'   => $args['posts_per_page'],
        );
        
        // return
        return $response;
        
    }
    
}

// initialize
acf_register_field_type('acfe_field_advanced_link');

endif;