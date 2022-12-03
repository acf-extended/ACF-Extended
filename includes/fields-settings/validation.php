<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_field_validation')):

class acfe_field_validation{
    
    /**
     * construct
     */
    function __construct(){
        
        // actions
        add_action('acf/field_group/admin_head',                        array($this, 'load'));
        add_action('wp_ajax_acf/field_group/render_field_settings',     array($this, 'load_ajax'), 5);
        
        // filters
        add_filter('acf/validate_value',                                array($this, 'validate_value'), 99, 4);
        
    }
    
    
    /**
     * load
     */
    function load(){
        
        if(!acf_is_filter_enabled('acfe/field_group/advanced')){
            return;
        }
        
        $this->prepare_settings();
        $this->add_settings();
        
    }
    
    
    /**
     * load_ajax
     */
    function load_ajax(){
        
        $post_id = acf_maybe_get_POST('post_id');
        $field_group = acf_get_field_group($post_id);
        
        if(!$field_group){
            return;
        }
        
        if(!acf_maybe_get($field_group, 'acfe_form')){
            return;
        }
        
        $this->add_settings();
        
    }
    
    
    /**
     * add_settings
     */
    function add_settings(){
        
        // exclude
        $exclude = array('accordion', 'acfe_button', 'acfe_column', 'acfe_dynamic_render', 'clone', 'flexible_content', 'group', 'message', 'repeater', 'tab');
        
        // get fields types
        foreach(acf_get_field_types_info() as $field){
            
            // field type
            $field_type = $field['name'];
            
            // check
            if(in_array($field_type, $exclude)){
                continue;
            }
            
            add_action("acf/render_field_settings/type={$field_type}", array($this, 'render_field_settings'), 99);
            
        }
        
    }
    
    
    /**
     * render_field_settings
     *
     * @param $field
     */
    function render_field_settings($field){
    
        $functions = array(
        
            'General' => array(
                'value'                 => 'If value',
                'strlen'                => 'If value length - strlen(value)',
                'count'                 => 'If count value - count(value)',
            ),
        
            'Exists' => array(
                'email_exists'          => 'If email exists - email_exists(value)',
                'post_type_exists'      => 'If post type exists - post_type_exists(value)',
                'taxonomy_exists'       => 'If taxonomy exists - taxonomy_exists(value)',
                'term_exists'           => 'If term exists - term_exists(value)',
                'username_exists'       => 'If username exists - username_exists(value)',
            ),
        
            'Is' => array(
                'is_email'              => 'If is email - is_email(value)',
                'is_user_logged_in'     => 'If is user logged in - is_user_logged_in()',
                'is_array'              => 'If is array - is_array(value)',
                'is_string'             => 'If is string - is_string(value)',
                'is_numeric'            => 'If is numeric - is_numeric(value)',
            ),
        
            'Post' => array(
                'get_post_type'         => 'If get post type - get_post_type(value)',
                'get_post_by_id'        => 'If post id exists - get_post_by_id(value)',
                'get_post_by_slug'      => 'If post slug exists - get_post_by_slug(value)',
                'get_post_by_title'     => 'If post title exists - get_post_by_title(value)',
            ),
        
            'Sanitize' => array(
                'sanitize_email'        => 'If sanitize email - sanitize_email(value)',
                'sanitize_file_name'    => 'If sanitize file name - sanitize_file_name(value)',
                'sanitize_html_class'   => 'If sanitize html class - sanitize_html_class(value)',
                'sanitize_key'          => 'If sanitize key - sanitize_key(value)',
                'sanitize_meta'         => 'If sanitize meta - sanitize_meta(value)',
                'sanitize_mime_type'    => 'If sanitize mime type - sanitize_mime_type(value)',
                'sanitize_option'       => 'If sanitize option - sanitize_option(value)',
                'sanitize_text_field'   => 'If sanitize text field - sanitize_text_field(value)',
                'sanitize_title'        => 'If sanitize title - sanitize_title(value)',
                'sanitize_user'         => 'If sanitize user - sanitize_user(value)',
            ),
        
            'User' => array(
                'get_user_by_id'        => 'If get user by id - get_user_by(\'id\', value)',
                'get_user_by_slug'      => 'If get user by slug - get_user_by(\'slug\', value)',
                'get_user_by_email'     => 'If get user by email - get_user_by(\'email\', value)',
                'get_user_by_login'     => 'If get user by login - get_user_by(\'login\', value)',
            )
    
        );
        
        $choices = apply_filters('acfe/validate/functions', $functions, $field);
        
        if(empty($choices)){
            return;
        }
        
        // settings
        acf_render_field_setting($field, array(
            'label'         => __('Advanced Validation'),
            'name'          => 'acfe_validate',
            'key'           => 'acfe_validate',
            'instructions'  => __('Validate value against rules'),
            'type'          => 'repeater',
            'button_label'  => __('Add validation'),
            'required'      => false,
            'layout'        => 'row',
            'wrapper'       => array(
                'data-enable-switch' => true
            ),
            'sub_fields'    => array(
                array(
                    'label'             => 'Location',
                    'name'              => 'acfe_validate_location',
                    'key'               => 'acfe_validate_location',
                    'type'              => 'select',
                    'instructions'      => '',
                    'required'          => 0,
                    'conditional_logic' => 0,
                    'wrapper'           => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                    'choices'           => array(
                        'admin' => 'Administration',
                        'front' => 'Front-end',
                    ),
                    'allow_null'        => true,
                    'multiple'          => 0,
                    'ui'                => 0,
                    'return_format'     => 'value',
                    'ajax'              => 0,
                    'placeholder'       => 'Everywhere',
                ),
                array(
                    'label'         => __('Rules'),
                    'name'          => 'acfe_validate_rules_and',
                    'key'           => 'acfe_validate_rules_and',
                    'instructions'  => '',
                    'type'          => 'repeater',
                    'button_label'  => __('+ AND'),
                    'required'      => false,
                    'layout'        => 'table',
                    'sub_fields'    => array(
                        array(
                            'ID'            => false,
                            'label'         => 'Function',
                            'name'          => 'acfe_validate_function',
                            'key'           => 'acfe_validate_function',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'select',
                            'choices'       => $choices,
                            'instructions'  => false,
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                        ),
                        array(
                            'ID'            => false,
                            'label'         => 'Operator / Value',
                            'name'          => 'acfe_validate_operator',
                            'key'           => 'acfe_validate_operator',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'select',
                            'choices'       => array(
                                'Operators' => array(
                                    '=='        => '==',
                                    '!='        => '!=',
                                    '>'         => '>',
                                    '>='        => '>=',
                                    '<'         => '<',
                                    '<='        => '<=',
                                    'contains'  => 'Contains',
                                    '!contains' => 'Doesn\'t contain',
                                    'starts'    => 'Starts with',
                                    '!starts'   => 'Doesn\'t start with',
                                    'ends'      => 'Ends with',
                                    '!ends'     => 'Doesn\'t end with',
                                    'regex'     => 'Matches regex',
                                    '!regex'    => 'Doesn\'t matches regex',
                                ),
                                'Values'     => array(
                                    'true'  => '== true',
                                    '!true' => '!= true',
                                    'false' => '== false',
                                    '!false'=> '!= false',
                                    'null'  => '== null',
                                    '!null' => '!= null',
                                    'empty' => '== (empty)',
                                    '!empty'=> '!= (empty)',
                                )
                            ),
                            'instructions'  => false,
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                        ),
                        array(
                            'ID'            => false,
                            'label'         => 'Value',
                            'name'          => 'acfe_validate_match',
                            'key'           => 'acfe_validate_match',
                            'prefix'        => '',
                            '_name'         => '',
                            '_prepare'      => '',
                            'type'          => 'text',
                            'instructions'  => false,
                            'placeholder'   => '',
                            'required'      => false,
                            'wrapper'       => array(
                                'width' => '',
                                'class' => '',
                                'id'    => '',
                            ),
                            'conditional_logic' => array(
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '==',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!=',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '>',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '>=',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '<',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '<=',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => 'contains',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!contains',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => 'starts',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!starts',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => 'ends',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!ends',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => 'regex',
                                    )
                                ),
                                array(
                                    array(
                                        'field'     => 'acfe_validate_operator',
                                        'operator'  => '==',
                                        'value'     => '!regex',
                                    )
                                ),
                            )
                        ),
                    )
                ),
                array(
                    'label'         => 'Error',
                    'name'          => 'acfe_validate_error',
                    'key'           => 'acfe_validate_error',
                    'prefix'        => '',
                    '_name'         => '',
                    '_prepare'      => '',
                    'type'          => 'text',
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                ),
            )
        ));
        
    }
    
    
    /**
     * validate_value
     *
     * @param $valid
     * @param $value
     * @param $field
     * @param $input
     *
     * @return mixed
     */
    function validate_value($valid, $value, $field, $input){
        
        if(!$valid){
            return $valid;
        }
        
        if(!acf_maybe_get($field, 'acfe_validate')){
            return $valid;
        }
        
        foreach($field['acfe_validate'] as $k => $rule){
            
            // fix possible acf clone index
            if($k === 'acfcloneindex'){
                continue;
            }
            
            // screen
            $screen = isset($rule['acfe_validate_location']) ? $rule['acfe_validate_location'] : '';
            $screen_allow = false;
            
            // screen: all
            if(empty($screen)){
                $screen_allow = true;
            }
            
            // screen: admin
            elseif($screen === 'admin' && acfe_is_admin()){
                $screen_allow = true;
            }
            
            // screen: front
            elseif($screen === 'front' && acfe_is_front()){
                $screen_allow = true;
            }
            
            if(!$screen_allow){
                continue;
            }
            
            if(!acf_maybe_get($rule, 'acfe_validate_rules_and')){
                continue;
            }
            
            $rule_match = true;
            
            foreach($rule['acfe_validate_rules_and'] as $k => $function){
                
                if(!$rule_match){
                    break;
                }
                
                $rule_match = false;
                
                // check filters
                $filters = array(
                    'acfe/validate/function/' . $function['acfe_validate_function'] . '/key=' . $field['key'],
                    'acfe/validate/function/' . $function['acfe_validate_function'] . '/name=' . $field['name'],
                    'acfe/validate/function/' . $function['acfe_validate_function'] . '/type=' . $field['type'],
                    'acfe/validate/function/' . $function['acfe_validate_function'],
                );
                
                $filter_call = false;
                foreach($filters as $filter){
                    
                    if(has_filter($filter)){
                        $filter_call = $filter;
                    }
                    
                }
                
                // filter
                if($filter_call){
                    $result = apply_filters($filter_call, false, $value, $field);
                }
                
                // class
                elseif(is_callable(array($this, $function['acfe_validate_function']))){
                    $result = call_user_func(array($this, $function['acfe_validate_function']), $value);
                }
                
                // function
                elseif(is_callable($function['acfe_validate_function'])){
                    $result = call_user_func($function['acfe_validate_function'], $value);
                }
                
                // nothing
                else{
                    continue;
                }
                
                // vars
                $operator = $function['acfe_validate_operator'];
                $match = acf_maybe_get($function, 'acfe_validate_match');
                
                if($operator === '==' && $result == $match){
                    $rule_match = true;
                }
                
                elseif($operator === '!=' && $result != $match){
                    $rule_match = true;
                }
                
                elseif($operator === '>' && $result > $match){
                    $rule_match = true;
                }
                
                elseif($operator === '>=' && $result >= $match){
                    $rule_match = true;
                }
                
                elseif($operator === '<' && $result < $match){
                    $rule_match = true;
                }
                
                elseif($operator === '<=' && $result <= $match){
                    $rule_match = true;
                }
                
                elseif($operator === 'contains' && stripos($result, $match) !== false){
                    $rule_match = true;
                }
                
                elseif($operator === '!contains' && stripos($result, $match) === false){
                    $rule_match = true;
                }
                
                elseif($operator === 'starts' && stripos($result, $match) === 0){
                    $rule_match = true;
                }
                
                elseif($operator === '!starts' && stripos($result, $match) !== 0){
                    $rule_match = true;
                }
                
                elseif($operator === 'ends' && acfe_ends_with($result, $match)){
                    $rule_match = true;
                }
                
                elseif($operator === '!ends' && !acfe_ends_with($result, $match)){
                    $rule_match = true;
                }
                
                elseif($operator === 'regex' && preg_match('/' . $match . '/u', $result)){
                    $rule_match = true;
                }

                elseif($operator === '!regex' && !preg_match('/' . $match . '/u', $result)){
                    $rule_match = true;
                }
                
                elseif($operator === 'true' && $result === true){
                    $rule_match = true;
                }
                
                elseif($operator === '!true' && $result !== true){
                    $rule_match = true;
                }
                
                elseif($operator === 'false' && $result === false){
                    $rule_match = true;
                }

                elseif($operator === '!false' && $result !== false){
                    $rule_match = true;
                }
                
                elseif($operator === 'null' && $result === null){
                    $rule_match = true;
                }

                elseif($operator === '!null' && $result !== null){
                    $rule_match = true;
                }
                
                elseif($operator === 'empty' && empty($result)){
                    $rule_match = true;
                }
                
                elseif($operator === '!empty' && !empty($result)){
                    $rule_match = true;
                }

            }
            
            // rrror
            $error = $rule['acfe_validate_error'];
            
            if($rule_match && !empty($error)){
                $valid = $error;
            }
            
            if(!$valid || is_string($valid)){
                break;
            }
            
        }
        
        return $valid;
        
    }
    
    
    /**
     * get_user_by_id
     *
     * @param $value
     *
     * @return false|WP_User
     */
    function get_user_by_id($value){
        return get_user_by('id', $value);
    }
    
    
    /**
     * get_user_by_slug
     *
     * @param $value
     *
     * @return false|WP_User
     */
    function get_user_by_slug($value){
        return get_user_by('slug', $value);
    }
    
    
    /**
     * get_user_by_email
     *
     * @param $value
     *
     * @return false|WP_User
     */
    function get_user_by_email($value){
        return get_user_by('email', $value);
    }
    
    
    /**
     * get_user_by_login
     *
     * @param $value
     *
     * @return false|WP_User
     */
    function get_user_by_login($value){
        return get_user_by('login', $value);
    }
    
    
    /**
     * value
     *
     * @param $value
     *
     * @return mixed
     */
    function value($value){
        return $value;
    }
    
    
    /**
     * get_post_by_id
     *
     * @param $value
     *
     * @return bool
     */
    function get_post_by_id($value){
        
        $get_post = get_post($value);
        
        if(!$get_post || is_wp_error($get_post)){
            return false;
        }
        
        return true;
        
    }
    
    
    /**
     * get_post_by_slug
     *
     * @param $value
     *
     * @return bool
     */
    function get_post_by_slug($value){
        
        $get_posts = get_posts(array(
            'name'              => $value,
            'post_type'         => 'any',
            'post_status'       => 'any',
            'posts_per_page'    => 1
        ));
        
        if(empty($get_posts)){
            return false;
        }
        
        return true;
        
    }
    
    
    /**
     * get_post_by_title
     *
     * @param $value
     *
     * @return bool
     */
    function get_post_by_title($value){
        
        $get_posts = get_posts(array(
            's'                 => $value,
            'post_type'         => 'any',
            'post_status'       => 'any',
            'posts_per_page'    => 1
        ));
        
        if(empty($get_posts)){
            return false;
        }
        
        return true;
        
    }
    
    
    /**
     * prepare_settings
     */
    function prepare_settings(){
    
        $fields = array('acfe_validate', 'acfe_validate_location', 'acfe_validate_rules_and', 'acfe_validate_function', 'acfe_validate_operator', 'acfe_validate_match', 'acfe_validate_error');
    
        foreach($fields as $name){
        
            add_filter("acf/prepare_field/name={$name}", function($field){
    
                $field['prefix'] = str_replace('row-', '', $field['prefix']);
                $field['name'] = str_replace('row-', '', $field['name']);
    
                return $field;
    
            });
        
        }
        
    }
    
    
}

// initialize
new acfe_field_validation();

endif;