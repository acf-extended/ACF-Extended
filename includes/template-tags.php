<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_template_tags')):

class acfe_template_tags{
    
    // vars
    public $tags = array();
    public $context = array();
    
    /**
     * construct
     */
    function __construct(){
        
        // {field:my_field}
        // {field:my_field:false}
        // {field:field_5d5f3b2b3e3e4}
        $this->add_tag(array(
            'name'      => 'field',
            'priority'  => 5,
            'condition' => function($args){
                return !acf_is_empty($args);
            },
            'resolver'  => function($args){
                
                // extract args
                $keys   = explode(':', $args);
                $name   = array_shift($keys);
                $format = array_shift($keys);
                
                // validate
                $format = $format && $format === 'false' ? false : true;
                
                // vars
                $setup_meta = !acfe_is_local_meta() && !empty($_POST['acf']);
                
                if($setup_meta){
                    acfe_setup_meta(wp_unslash($_POST['acf']), 'acfe/tag/field', true);
                }
                
                // vars
                $post_id = acf_get_valid_post_id();
                $field = acf_maybe_get_field($name, $post_id, false);
                
                if(!$field){
                    
                    $field = acf_validate_field(array(
                        'name' => $name,
                        'key'  => '',
                        'type' => '',
                    ));
                    
                    // prevent formatting
                    $format = false;
                    
                }
                
                // field_key
                // get_field('my_group_my_sub_field') can retrieve values from group subfield
                // but get_field('field_abcdef123456') cannot, thus this implementation
                // apply acf/load_value filter so payment/date range fields generate dynamic subfields
                if(acf_is_field_key($name)){
                    $acf = acfe_get_fields(); // pass via acf_get_value() > acf/load_value
                    $value = acfe_get_value_from_acf_values_by_key($acf, $name);
                    
                // field name
                }else{
                    $value = acf_get_value($post_id, $field);
                }
                
                if($setup_meta){
                    acfe_reset_meta();
                }
                
                // format via context
                if($this->has_context('format')){
                    $format = $this->get_context('format');
                }
                
                // context = save
                // never format wysiwyg in save context
                // to avoid shortcodes to be saved as interpreted
                if($this->get_context('context') === 'save'){
                    if($field['type'] === 'wysiwyg'){
                        $format = false;
                    }
                }
                
                // unformat = field_type
                // never format specific fields in unformat context
                if($this->get_context('unformat') && in_array($field['type'], acf_get_array($this->get_context('unformat')))){
                    $format = false;
                }
                
                // format value
                if($format){
                    $value = acfe_form_format_value($value, $field);
                }
                
                // context display
                // value is already unslashed when using name, via acfe_setup_meta()
                if($this->get_context('context') === 'display' && acf_is_field_key($name)){
                    $value = wp_unslash($value);
                }
                
                // return
                return $value;
                
            }
        ));
        
        // {get_field:my_field}
        // {get_field:my_field:145}
        // {get_field:my_field:145:false}
        // {get_field:field_5d5f3b2b3e3e4}
        $this->add_tag(array(
            'name'      => 'get_field',
            'condition' => function($args){
                return !acf_is_empty($args);
            },
            'resolver'  => function($args){
                
                // extract args
                $keys    = explode(':', $args);
                $name    = array_shift($keys);
                $post_id = array_shift($keys);
                $format  = array_shift($keys);
                
                // validate
                $post_id = $post_id && ($post_id === 'false' || $post_id === 'current') ? false : $post_id;
                $format  = $format && $format === 'false' ? false : true;
                
                // return
                return get_field($name, $post_id, $format);
                
            }
        ));
        
        // {fields}
        $this->add_tag(array(
            'name'     => 'fields',
            'resolver' => function($args){
                
                $values = acfe_get_fields();
                $tag = $this->get_tag('field');
                
                if(!$values){
                    return false;
                }
                
                // html
                $html = '';
                
                // loop raw values
                foreach($values as $field_key => $unformatted){
                    
                    // get field array
                    $field = acf_get_field($field_key);
                    
                    if($field){
                        
                        // bypass
                        if($field['key'] === '_validate_email' || $field['type'] === 'acfe_recaptcha'){
                            continue;
                        }
                        
                        // label
                        $label = !empty($field['label']) ? $field['label'] : $field['name'];
                        
                        $formatted = call_user_func_array($tag['resolver'], array($field_key));
                        
                        // html
                        $html .= "$label: $formatted<br />\n";
                        
                    }
                    
                }
                
                // get form context
                $form = $this->get_context('form', array('name' => '')); // default for deprecated filter
                
                // depreacted filters
                $html = apply_filters_deprecated("acfe/form/template_tag/fields",                      array($html, array(), $form), '0.9');
                $html = apply_filters_deprecated("acfe/form/template_tag/fields/form={$form['name']}", array($html, array(), $form), '0.9');
                
                // return
                return $html;
                
            }
        ));
        
        // {render:group_5d5f3b2b3e3e4}
        // {render:field_5d5f3b2b3e3e4}
        // {render:my_field}
        // {render:fields}
        // {render:submit}
        $this->add_tag(array(
            'name'      => 'render',
            'condition' => function($args){
                return !acf_is_empty($args);
            },
            'resolver'  => function($args){
                
                // get context
                $form = $this->get_context('form');
                if(!$form){
                    return false;
                }
                
                // field key
                if(acf_is_field_key($args)){
                    
                    $field = acf_get_field($args);
                    if(!$field){
                        return false;
                    }
                    
                    $fields = array($field);
                    
                // field group key
                }elseif(acf_is_field_group_key($args)){
                    
                    $field_group = acf_get_field_group($args);
                    if(!$field_group){
                        return false;
                    }
                    
                    $fields = acf_get_fields($field_group);
                    if(!$fields){
                        return false;
                    }
                    
                // fields
                }elseif($args === 'fields'){
                    
                    $fields = acf_get_instance('acfe_module_form_front_render')->get_allowed_fields($form);
                    
                // submit
                }elseif($args === 'submit'){
                    
                    // form submit
                    if($form['attributes']['submit']){
                        
                        ob_start();
                        do_action("acfe/form/render_submit", $form);
                        return ob_get_clean();
                        
                    }
                    
                    return false;
                    
                // field name
                }else{
                    
                    // try to get field key using field name from mapped field groups
                    // otheriwse, fallback to field name
                    $args = $this->get_field_key_from_field_groups($args);
                    
                    // get field array
                    $field = acf_get_field($args);
                    
                    // field name
                    if($field){
                        $fields = array($field);
                        
                    // field group ID
                    }else{
                        
                        $field_group = acf_get_field_group($args);
                        if(!$field_group){
                            return false;
                        }
                        
                        $fields = acf_get_fields($field_group);
                        if(!$fields){
                            return false;
                        }
                        
                    }
                    
                }
                
                ob_start();
                acf_render_fields($fields, $form['uniqid'], $form['attributes']['fields']['element'], $form['attributes']['fields']['instruction']);
                return ob_get_clean();
                
            }
        ));
        
        // {action:my-post}
        // {action:my-post:ID}
        $this->add_tag(array(
            'name'      => 'action',
            'condition' => function($args){
                
                $keys   = explode(':', $args);
                $name   = array_shift($keys);
                $action = acfe_get_form_action($name);
                
                return !acf_is_empty($name) && $action;
                
            },
            'resolver'  => function($args){
                
                // extract args
                $keys = explode(':', $args);
                $name = array_shift($keys);
                
                // get action
                $action = acfe_get_form_action($name);
                if(!$action){
                    return false;
                }
                
                return $this->array_get($action, $keys);
                
            }
        ));
        
        // {get_option:my_option}
        // {get_option:my_option:child}
        $this->add_tag(array(
            'name'      => 'get_option',
            'condition' => function($args){
                return !acf_is_empty($args);
            },
            'resolver'  => function($args){
                
                // extract args
                $keys = explode(':', $args);
                $name = array_shift($keys);
                
                // get option
                $option = get_option($name);
                
                // other arguments
                if($keys){
                    return $this->array_get($option, $keys);
                }
                
                // return
                return $option;
                
            }
        ));
        
        // {request:url_param}
        // {request:url_param:child}
        $this->add_tag(array(
            'name'      => 'request',
            'condition' => function($args){
                return !acf_is_empty($args);
            },
            'resolver'  => function($args){
                return $this->array_get($_REQUEST, $args);
            }
        ));
        
        // {query_var:my_var}
        // {query_var:my_var:child}
        $this->add_tag(array(
            'name'      => 'query_var',
            'condition' => function($args){
                return !acf_is_empty($args);
            },
            'resolver'  => function($args){
                
                // extract args
                $keys = explode(':', $args);
                $name = array_shift($keys);
                
                // get query var
                $query_var = get_query_var($name);
                
                // other arguments
                if($keys){
                    return $this->array_get($query_var, $keys);
                }
                
                return $query_var;
                
            }
        ));
        
        // {post}
        // {post:ID}
        $this->add_tag(array(
            'name'     => 'post',
            'resolver' => function($args){
                
                // extract
                $keys = explode(':', $args);
                $keys = array_merge(array('0'), $keys);
                
                // merge back
                $args = implode(':', $keys);
                
                // fallback to {get_post}
                $tag = $this->get_tag('get_post');
                
                return call_user_func_array($tag['resolver'], array($args));
                
            }
        ));
        
        // {get_post}
        // {get_post:145}
        // {get_post:145:ID}
        $this->add_tag(array(
            'name'     => 'get_post',
            'resolver' => function($args){
                
                // extract args
                $keys = explode(':', $args);
                $post_id = (int) array_shift($keys);
                
                // current post
                if(empty($post_id)){
                    
                    // default
                    $post_id = null;
                    
                    // check form context exists
                    $form = $this->get_context('form');
                    if($form){

                        // decode post_id
                        $decoded = acf_decode_post_id($form['post_id']);

                        // check if post
                        if($decoded['type'] === 'post'){
                            $post_id = $decoded['id'];
                        }

                    }
                    
                }
                
                // merge back
                $args = implode(':', $keys);
                
                // default to ID
                if(empty($args)){
                    $args = 'ID';
                }
                
                // allow id/post_id
                if(in_array(strtolower($args), array('id', 'post_id'), true)){
                    $args = 'ID';
                }
                
                // get post
                $post = get_post($post_id, ARRAY_A);
                
                // validate post found
                if(!$post){
                    
                    // allow to return ID '0' if no post found
                    if($args === 'ID'){
                        return $post_id;
                    }
                    
                    return false;
                }
                
                // additional fields
                $post['permalink'] = get_permalink($post['ID']);
                $post['admin_url'] = admin_url("post.php?post={$post['ID']}&action=edit");
                $post['post_author_data'] = $this->get_user_array($post['post_author']);
                
                // return
                return $this->array_get($post, $args);
                
            }
        ));
        
        // {term}
        // {term:term_id}
        $this->add_tag(array(
            'name'     => 'term',
            'resolver' => function($args){
                
                // extract
                $keys = explode(':', $args);
                $keys = array_merge(array('0'), $keys);
                
                // merge back
                $args = implode(':', $keys);
                
                // fallback to {get_term}
                $tag = $this->get_tag('get_term');
                
                return call_user_func_array($tag['resolver'], array($args));
                
                
            }
        ));
        
        // {get_term}
        // {get_term:45}
        // {get_term:45:term_id}
        $this->add_tag(array(
            'name'     => 'get_term',
            'resolver' => function($args){
                
                // extract args
                $keys = explode(':', $args);
                $term_id = (int) array_shift($keys);
                
                if(empty($term_id)){
                    
                    $term_id = null; // current queried object
                    
                    // check form context exists
                    $form = $this->get_context('form');
                    if($form){
                        
                        // decode post_id
                        $decoded = acf_decode_post_id($form['post_id']);
                        
                        // check if post
                        if($decoded['type'] === 'term'){
                            $term_id = $decoded['id'];
                        }
                        
                    }
                    
                }
                
                // merge back
                $args = implode(':', $keys);
                
                // default to ID
                if(empty($args)){
                    $args = 'term_id';
                }
                
                // allow id/term_id
                if(in_array(strtolower($args), array('id', 'term_id'), true)){
                    $args = 'term_id';
                }
                
                // get object
                if($term_id === null){
                    $object = get_queried_object();
                }else{
                    $object = get_term($term_id);
                }
                
                // validate term found
                if(!$object || is_wp_error($object) || !is_a($object, 'WP_Term')){
                    
                    // allow to return ID '0' if no term found
                    if($args === 'term_id'){
                        return $term_id;
                    }
                    
                    return false;
                    
                }
                
                // convert to array
                $term = (array) $object;
                
                // additional fields
                $term['permalink'] = get_term_link($term['term_id']);
                $term['admin_url'] = admin_url("term.php?taxonomy={$term['taxonomy']}&tag_ID={$term['term_id']}");
                
                // return
                return $this->array_get($term, $args);
                
                
            }
        ));
        
        // {user}
        // {user:ID}
        $this->add_tag(array(
            'name'     => 'user',
            'resolver' => function($args){
                
                // extract
                $keys = explode(':', $args);
                $keys = array_merge(array('0'), $keys);
                
                // merge back
                $args = implode(':', $keys);
                
                // fallback to {get_user}
                $tag = $this->get_tag('get_user');
                
                return call_user_func_array($tag['resolver'], array($args));
                
                
            }
        ));
        
        
        // {get_user}
        // {get_user:25}
        // {get_user:25:ID}
        $this->add_tag(array(
            'name'     => 'get_user',
            'resolver' => function($args){
                
                // extract args
                $keys = explode(':', $args);
                $user_id = (int) array_shift($keys);
                
                if(empty($user_id)){
                    $user_id = get_current_user_id(); // current user
                }
                
                // merge back
                $args = implode(':', $keys);
                
                // default to ID
                if(empty($args)){
                    $args = 'ID';
                }
                
                // allow id/user_id
                if(in_array(strtolower($args), array('id', 'user_id'), true)){
                    $args = 'ID';
                }
                
                // allow to return ID '0' if not logged in
                if($args === 'ID'){
                    return $user_id;
                }
                
                // get user array
                $user = $this->get_user_array($user_id);
                
                // validate
                if(!$user){
                    return false;
                }
                
                // return
                return $this->array_get($user, $args);
                
                
            }
        ));
        
        // {author}
        // {author:ID}
        $this->add_tag(array(
            'name'     => 'author',
            'resolver' => function($args){
                
                // default id
                $user_id = 0;
                
                // default to ID
                if(empty($args)){
                    $args = 'ID';
                }
                
                // allow id/user_id
                if(in_array(strtolower($args), array('id', 'user_id'), true)){
                    $args = 'ID';
                }
                
                // get post
                $tag = $this->get_tag('post');
                $post_id = call_user_func_array($tag['resolver'], array(''));
                
                $post = get_post($post_id, ARRAY_A);
                
                // validate post found
                if(!$post){
                    
                    // allow to return ID '0' if no post found
                    if($args === 'ID'){
                        return $user_id;
                    }
                    
                    return false;
                    
                }
                
                // get author id
                $user_id = (int) $post['post_author'];
                $user = $this->get_user_array($user_id);
                
                // validate
                if(!$user){
                    return false;
                }
                
                // return
                return $this->array_get($user, $args);
                
            }
        ));
        
        // {form}
        // {form:post_id}
        $this->add_tag(array(
            'name'      => 'form',
            'resolver'  => function($args){
                
                // get form
                $form = $this->get_context('form');
                if(!$form){
                    return false;
                }
                
                // default to ID
                if(empty($args)){
                    $args = 'ID';
                }
                
                // allow id/form_id
                if(in_array(strtolower($args), array('id', 'form_id'), true)){
                    $args = 'ID';
                }
                
                // allow name/form_name
                if(in_array(strtolower($args), array('name', 'form_name'), true)){
                    $args = 'name';
                }
                
                // allow title/form_title
                if(in_array(strtolower($args), array('title', 'form_title'), true)){
                    $args = 'title';
                }
                
                // return
                return $this->array_get($form, $args);
                
            }
        ));
        
        // {current:form:post_id}
        // deprecated
        $this->add_tag(array(
            'name'      => 'current',
            'condition' => function($args){
                return !acf_is_empty($args);
            },
            'resolver' => function($args){
                
                // tag: {form:post_id}
                $keys      = explode(':', $args);
                $name      = array_shift($keys);  // form
                $args_left = implode(':', $keys); // post_id
                
                // deprecated warning
                $deprecated_version = $name === 'form' ? '0.8.7.5' : '0.8.8';
                _deprecated_function('ACF Extended: "{current:' . $args . '}" template tag', $deprecated_version, "the new {" . $args . "} Template Tag");
                
                // get fallback tag (form, post, term etc...)
                $tag = $this->get_tag($name);
                
                // return tag resolver
                if($tag && is_callable($tag['resolver'])){
                    return call_user_func_array($tag['resolver'], array($args_left));
                }
                
                return false;
                
            }
        ));
        
        // {generate_password}
        $this->add_tag(array(
            'name'      => 'generate_password',
            'resolver'  => function($args){
                return wp_generate_password(8, false);
            }
        ));
        
        // {generated_id}
        $this->add_tag(array(
            'name'      => 'generated_id',
            'condition' => function($args){
                return (bool) $this->get_context('generated_id');
            },
            'resolver'  => function($args){
                return $this->get_context('generated_id');
            }
        ));
        
        // {date}
        // {date:+2 days +1 year}
        // {date:+2 days +1 year:d/m/Y}
        $this->add_tag(array(
            'name'      => 'date',
            'resolver'  => function($args){
                
                // escape
                $args   = str_replace('\:', '[sep]', $args);
                
                // explode
                $keys   = explode(':', $args);
                $date   = array_shift($keys);
                $format = array_shift($keys);
                
                // unespace
                $date = str_replace('[sep]', ':', $date);
                $format = str_replace('[sep]', ':', $format);
                
                $date   = $date ? $date : 'now';
                $format = $format ? $format : 'Y-m-d';
                
                return date($format, strtotime($date));
            }
        ));
        
    }
    
    
    /**
     * parse
     *
     * @param $string
     * @param $tmp_context
     *
     * @return mixed|string
     */
    function parse($string, $tmp_context){
        
        // array
        if(is_array($string)){
            foreach(array_keys($string) as $key){
                $string[ $key ] = $this->parse($string[ $key ], $tmp_context);
            }
        }
        
        // validate
        if(!is_string($string) || empty($string)){
            return $string;
        }
        
        // add temp context
        if(is_array($tmp_context)){
            $this->add_context($tmp_context);
        }
        
        // match
        while($this->match($string)){}
        
        // remove temp context
        if(is_array($tmp_context)){
            $this->delete_context($tmp_context);
        }
        
        // restore excluded tags
        $string = str_replace('ACFE_ESCAPE_START', '{', $string);
        $string = str_replace('ACFE_ESCAPE_END', '}', $string);
        
        // return
        return $string;
    
    }
    
    
    /**
     * match
     *
     * @param $string
     *
     * @return bool
     */
    function match(&$string){
        
        // direct tags
        // match field_65671b1d11f07 current_post etc...
        $direct_tags = $this->get_tags(array('direct' => true));
        
        // loop
        foreach($direct_tags as $tag){
            
            // default condition
            $condition = $string === $tag['name'];
            
            // tag condition
            if(is_callable($tag['condition'])){
                $condition = call_user_func_array($tag['condition'], array($string));
            }
            
            // check condition
            if($condition && is_callable($tag['resolver'])){
                
                // replace string
                $string = call_user_func_array($tag['resolver'], array($string));
                
                if($this->get_context('return') !== 'raw'){
                    $string = acfe_array_to_string($string);
                }
                
                // stop loop
                return false;
                
            }
            
        }
        
        // tags
        // match {name} {name:value} {name:value:value2} etc...
        preg_match_all('/[{\w: +-\\\]*(?<full>{(?<name>[\w]+)(?!:})(?::(?<value>.*?))?})}*/', $string, $matches);
        
        // keys
        $keys = current($matches);
        
        // stop loop
        if(empty($keys)){
            return false;
        }
        
        // loop matches
        foreach(array_keys($keys) as $i){
        
            // vars
            $replace = '';
            $search  = $matches['full'][ $i ];        // {field:my_field}
            $name    = trim($matches['name'][ $i ]);  // field
            $value   = trim($matches['value'][ $i ]); // my_field
            
            // get tags
            $tags = $this->get_tags(array('direct' => false, 'name' => $name));
            
            if($tags){
                
                // var
                $resolver_executed = false;
                
                // loop tags
                foreach($tags as $tag){
                    
                    // default condition
                    $condition = true;
        
                    // check condition
                    if(is_callable($tag['condition'])){
                        $condition = call_user_func_array($tag['condition'], array($value));
                    }
                    
                    // condition ok: call resolver
                    if($condition && is_callable($tag['resolver'])){
                        
                        // execute resolver
                        $replace = call_user_func_array($tag['resolver'], array($value));
                        
                        if($this->get_context('return') !== 'raw'){
                            
                            // convert array value into string
                            $replace = acfe_array_to_string($replace);
                            
                            // escape brackets
                            if(!empty($replace) && is_string($replace)){
                                $replace = str_replace('{', 'ACFE_ESCAPE_START', $replace);
                                $replace = str_replace('}', 'ACFE_ESCAPE_END', $replace);
                            }
                            
                        }
                        
                        // marker
                        $resolver_executed = true;
                        
                    }
                    
                    // resolver executed: stop tags loop
                    if($resolver_executed){
                        break;
                    }
                    
                }
                
                // condition/resolver not executed: add as excluded tag
                // this will prevent current tag from being parsed again by next loop call
                if(!$resolver_executed){
                    
                    $replace = $search;
                    $replace = str_replace('{', 'ACFE_ESCAPE_START', $replace);
                    $replace = str_replace('}', 'ACFE_ESCAPE_END', $replace);
                    
                }
                
            }
            
            // raw context + array
            if($this->get_context('return') === 'raw' && is_array($replace)){
                
                // return array
                $string = $replace;
                
                // stop loop
                return false;
                
            }
            
            if($replace === null || $replace === false){
                $replace = '';
            }
            
            // replace
            $string = str_replace($search, $replace, $string);
        
        }
        
        // keep looping
        return true;
        
    }
    
    
    /**
     * array_get
     *
     * @param $array
     * @param $key
     * @param $default
     *
     * @return mixed|null
     */
    function array_get($array, $key, $default = null){
        
        // bail early if empty key
        if(acf_is_empty($key)){
            return $array;
        }
        
        // explode keys
        if(!is_array($key)){
            $key = explode(':', $key);
        }
        
        $count = count($key);
        $i=-1; foreach($key as $segment){ $i++;
            
            if(isset($array[ $segment ])){
                
                if($i+1 === $count){
                    return $array[ $segment ];
                }
                
                unset($key[ $i ]);
                
                return $this->array_get($array[ $segment ], $key, $default);
                
            }
            
        }
        
        return $default;
        
    }
    
    
    /**
     * get_user_array
     *
     * @param $user_id
     *
     * @return array|false
     */
    function get_user_array($user_id){
        
        // bail early if user id is 0
        if(!$user_id){
            return false;
        }
        
        // user object
        $user = get_user_by('ID', $user_id);
        
        // validate
        if(!$user){
            return false;
        }
        
        // cast as array
        $user = (array) $user->data;
        
        // user meta
        $user_meta = get_user_meta($user_id);
        foreach($user_meta as $k => $v){
            if(isset($v[0])){
                $user[ $k ] = $v[0];
            }
        }
        
        // additional fields
        $user['permalink'] = get_author_posts_url($user_id);
        $user['admin_url'] = admin_url("user-edit.php?user_id=$user_id");
        
        // return
        return $user;
        
    }
    
    
    /**
     * get_field_key_from_field_groups
     *
     * @param $name
     *
     * @return mixed
     */
    function get_field_key_from_field_groups($name){
        
        $mapped = $this->get_context('mapped_fields');
        
        if(!empty($mapped) && isset($mapped[ $name ])){
            return $mapped[ $name ];
        }
        
        return $name;
        
    }
    
    
    /**
     * add_tag
     *
     * @param $args
     */
    function add_tag($args){
        $this->tags[] = $args;
    }
    
    
    /**
     * get_tag
     *
     * @param $name
     *
     * @return array|false|mixed
     */
    function get_tag($name){
        return current($this->get_tags(array('name' => $name)));
    }
    
    
    /**
     * get_tags
     *
     * @return array
     */
    function get_tags($args = array(), $operator = 'AND'){
        
        // vars
        $tags = array();
        
        // loop raw tags
        foreach($this->tags as $tag){
            
            // validate tag
            $tag = $this->validate_tag($tag);
            
            // append
            if($tag){
                $tags[] = $tag;
            }
            
        }
        
        // found tags
        if($tags){
            
            // sort by priority
            $priority = array_column($tags, 'priority');
            array_multisort($priority, SORT_ASC, $tags);
            
            // filter by args
            if($args){
                
                $tags = wp_list_filter($tags, $args, $operator);
                $tags = array_values($tags);
                
            }
            
            return $tags;
        
        }
        
        // return
        return $tags;
        
    }
    
    
    /**
     * validate_tag
     *
     * @param $args
     *
     * @return array
     */
    function validate_tag($args){
        
        // default args
        $args = wp_parse_args($args, array(
            'name'      => '',
            'priority'  => 10,
            'direct'    => false,
            'condition' => false,
            'resolver'  => false,
        ));
        
        // validate args
        $args['direct']   = (bool) $args['direct'];
        $args['priority'] = (int) $args['priority'];
        
        // return
        return $args;
        
    }
    
    
    /**
     * get_context
     *
     * @param $name
     *
     * @return array|mixed|null
     */
    function has_context($name){
        
        return acfe_array_get($this->context, $name) !== null;
        
    }
    
    
    /**
     * get_context
     *
     * @param $name
     *
     * @return array|mixed|null
     */
    function get_context($name = false, $default = null){
        
        if($name){
            return acfe_array_get($this->context, $name, $default);
        }
        
        return $this->context;
        
    }
    
    
    /**
     * add_context
     *
     * @param $name
     * @param $value
     */
    function add_context($name, $value = null){
        
        // array
        if(is_array($name) && $value === null){
            
            foreach($name as $key => $val){
                $this->context[ $key ] = $val;
            }
            
        // normal
        }else{
            $this->context[ $name ] = $value;
        }
        
    }
    
    
    /**
     * delete_context
     *
     * @param $name
     */
    function delete_context($name){
        
        // array
        if(is_array($name)){
            
            if(acf_is_associative_array($name)){
                foreach(array_keys($name) as $key){
                    unset($this->context[ $key ]);
                }
            }else{
                foreach($name as $key){
                    unset($this->context[ $key ]);
                }
            }
            
        // normal
        }else{
            unset($this->context[ $name ]);
        }
        
    }
    
    
    /**
     * clear_context
     */
    function clear_context(){
        $this->context = array();
    }
    
}

acf_new_instance('acfe_template_tags');

endif;


/**
 * acfe_add_tag
 *
 * @param $args
 */
function acfe_add_tag($args){
    acf_get_instance('acfe_template_tags')->add_tag($args);
}


/**
 * acfe_get_tag
 *
 * @param $name
 *
 * @return mixed
 */
function acfe_get_tag($name){
    return acf_get_instance('acfe_template_tags')->get_tag($name);
}


/**
 * acfe_get_tags
 *
 * @return mixed
 */
function acfe_get_tags($args = array(), $operator = 'AND'){
    return acf_get_instance('acfe_template_tags')->get_tags($args, $operator);
}


/**
 * acfe_get_context
 *
 * @param $name
 *
 * @return mixed
 */
function acfe_get_context($name = false, $default = null){
    return acf_get_instance('acfe_template_tags')->get_context($name, $default);
}


/**
 * acfe_add_context
 *
 * @param $name
 * @param $value
 */
function acfe_add_context($name, $value = null){
    acf_get_instance('acfe_template_tags')->add_context($name, $value);
}


/**
 * acfe_delete_context
 *
 * @param $name
 */
function acfe_delete_context($name){
    acf_get_instance('acfe_template_tags')->delete_context($name);
}


/**
 * acfe_clear_context
 */
function acfe_clear_context(){
    acf_get_instance('acfe_template_tags')->clear_context();
}


/**
 * acfe_parse_tags
 *
 * @param $text
 * @param $tmp_context
 *
 * @return mixed
 */
function acfe_parse_tags($text, $tmp_context = null){
    return acf_get_instance('acfe_template_tags')->parse($text, $tmp_context);
}


/**
 * acfe_apply_tags
 *
 * @param $text
 * @param $tmp_context
 *
 * @return mixed
 */
function acfe_apply_tags(&$text, $tmp_context = null){
    $text = acfe_parse_tags($text, $tmp_context);
    return $text;
}