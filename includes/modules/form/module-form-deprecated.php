<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_deprecated')):

class acfe_module_form_deprecated{
    
    function __construct(){
        
        // deprecated actions hooks
        foreach(array('post', 'term', 'user', 'option', 'custom', 'email', 'redirect') as $name){
            
            add_filter("acfe/form/load_{$name}",     array($this, 'load_action'), 10, 2);
            add_action("acfe/form/validate_{$name}", array($this, 'validate_action'), 10, 2);
            add_filter("acfe/form/prepare_{$name}",  array($this, 'prepare_action'), 10, 2);
            add_action("acfe/form/make_{$name}",     array($this, 'make_action'), 10, 2);
            
        }
        
        // deprecated global hooks
        add_filter('acfe/form/load_form',            array($this, 'load_form'));
        add_action('acfe/form/validate_form',        array($this, 'validate_form'));
        add_action('acfe/form/submit_form',          array($this, 'submit_form'));
        
        // deprecated global render
        add_action('acfe/form/render_success',       array($this, 'render_success'));
        add_action("acfe/form/render_before_form",   array($this, 'render_before_form'));
        add_action("acfe/form/render_before_fields", array($this, 'render_before_fields'));
        add_action("acfe/form/render_fields",        array($this, 'render_fields'));
        add_action("acfe/form/render_after_fields",  array($this, 'render_after_fields'));
        add_action("acfe/form/render_after_form",    array($this, 'render_after_form'));
        
        // deprecated specific actions
        add_filter("acfe/form/prepare_custom",       array($this, 'prepare_custom'), 10, 2);
        add_action("acfe/form/validate_custom",      array($this, 'validate_custom'), 10, 2);
        add_action("acfe/form/make_custom",          array($this, 'make_custom'), 10, 2);
        
        add_action("acfe/form/submit_email",         array($this, 'submit_email'), 10, 3);
        add_filter("acfe/form/submit_email_args",    array($this, 'submit_email_args'), 10, 3);
        add_filter("acfe/form/submit_email_output",  array($this, 'submit_email_output'), 10, 3);
        
        add_filter("acfe/form/load_post_id",         array($this, 'load_post_id'), 10, 3);
        add_action("acfe/form/submit_post",          array($this, 'submit_post'), 10, 4);
        add_filter("acfe/form/submit_post_args",     array($this, 'submit_post_args'), 10, 3);
        add_filter("acfe/form/submit_post_output",   array($this, 'submit_post_output'), 10, 4);
        
        add_filter("acfe/form/submit_redirect_url",  array($this, 'submit_redirect_url'), 10, 3);
        
        add_filter("acfe/form/load_term_id",         array($this, 'load_term_id'), 10, 3);
        add_action("acfe/form/submit_term",          array($this, 'submit_term'), 10, 4);
        add_filter("acfe/form/submit_term_args",     array($this, 'submit_term_args'), 10, 3);
        add_filter("acfe/form/submit_term_output",   array($this, 'submit_term_output'), 10, 4);
        
        add_filter("acfe/form/load_user_id",         array($this, 'load_user_id'), 10, 3);
        add_filter("acfe/form/validate_user_errors", array($this, 'validate_user_errors'), 10, 3);
        add_action("acfe/form/submit_user",          array($this, 'submit_user'), 10, 4);
        add_filter("acfe/form/submit_user_args",     array($this, 'submit_user_args'), 10, 3);
        add_filter("acfe/form/submit_user_output",   array($this, 'submit_user_output'), 10, 4);
        
        // prepare form
        add_filter('acfe/form/prepare_form',         array($this, 'prepare_form'), 5);
        
    }
    
    
    /**
     * load_user_id
     *
     * @param $user_id
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function load_user_id($user_id, $form, $action){
        
        // deprecated
        $user_id = apply_filters_deprecated("acfe/form/load/user_id",                          array($user_id, $form, $action['name']), '0.9', "acfe/form/load_user_id");
        $user_id = apply_filters_deprecated("acfe/form/load/user_id/form={$form['name']}",     array($user_id, $form, $action['name']), '0.9', "acfe/form/load_user_id/form={$form['name']}");
        $user_id = apply_filters_deprecated("acfe/form/load/user_id/action={$action['name']}", array($user_id, $form, $action['name']), '0.9', "acfe/form/load_user_id/action={$action['name']}");
        
        return $user_id;
    
    }
    
    
    /**
     * validate_user_errors
     *
     * @param $errors
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function validate_user_errors($errors, $form, $action){
        
        // deprecated
        $errors = apply_filters_deprecated('acfe/form/validation/user/login_errors', array($errors), '0.8.8.8', 'acfe/form/validate_user_errors');
        $errors = apply_filters_deprecated('acfe/form/validation/user_errors',       array($errors), '0.9',     'acfe/form/validate_user_errors');
        
        return $errors;
    
    }
    
    
    /**
     * submit_user
     *
     * @param $user_id
     * @param $args
     * @param $form
     * @param $action
     */
    function submit_user($user_id, $args, $form, $action){
        
        // deprecated
        do_action_deprecated("acfe/form/submit/user",                          array($user_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_user");
        do_action_deprecated("acfe/form/submit/user/form={$form['name']}",     array($user_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_user/form={$form['name']}");
        do_action_deprecated("acfe/form/submit/user/action={$action['name']}", array($user_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_user/action={$action['name']}");
        
    }
    
    
    /**
     * submit_user_args
     *
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_user_args($args, $form, $action){
        
        // deprecated
        $args = apply_filters_deprecated("acfe/form/submit/user_args",                          array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_user_args");
        $args = apply_filters_deprecated("acfe/form/submit/user_args/form={$form['name']}",     array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_user_args/form={$form['name']}");
        $args = apply_filters_deprecated("acfe/form/submit/user_args/action={$action['name']}", array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_user_args/action={$action['name']}");
        
        return $args;
    
    }
    
    
    /**
     * submit_user_output
     *
     * @param $user
     * @param $user_id
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_user_output($user, $args, $form, $action){
        
        // deprecated
        $user = apply_filters_deprecated("acfe/form/query_var/user",                          array($user, $user['ID'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_user_output");
        $user = apply_filters_deprecated("acfe/form/query_var/user/form={$form['name']}",     array($user, $user['ID'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_user_output/form={$form['name']}");
        $user = apply_filters_deprecated("acfe/form/query_var/user/action={$action['name']}", array($user, $user['ID'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_user_output/action={$action['name']}");
        
        // deprecated
        $user = apply_filters_deprecated("acfe/form/output/user",                          array($user, $user['ID'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_user_output");
        $user = apply_filters_deprecated("acfe/form/output/user/form={$form['name']}",     array($user, $user['ID'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_user_output/form={$form['name']}");
        $user = apply_filters_deprecated("acfe/form/output/user/action={$action['name']}", array($user, $user['ID'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_user_output/action={$action['name']}");
        
        return $user;
    
    }
    
    
    /**
     * load_term_id
     *
     * @param $term_id
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function load_term_id($term_id, $form, $action){
        
        // deprecated
        $term_id = apply_filters_deprecated("acfe/form/load/term_id",                          array($term_id, $form, $action['name']), '0.9', "acfe/form/load_term_id");
        $term_id = apply_filters_deprecated("acfe/form/load/term_id/form={$form['name']}",     array($term_id, $form, $action['name']), '0.9', "acfe/form/load_term_id/form={$form['name']}");
        $term_id = apply_filters_deprecated("acfe/form/load/term_id/action={$action['name']}", array($term_id, $form, $action['name']), '0.9', "acfe/form/load_term_id/action={$action['name']}");
        
        return $term_id;
    
    }
    
    
    /**
     * submit_term
     *
     * @param $term_id
     * @param $args
     * @param $form
     * @param $action
     */
    function submit_term($term_id, $args, $form, $action){
        
        // deprecated
        do_action_deprecated("acfe/form/submit/term",                          array($term_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_term");
        do_action_deprecated("acfe/form/submit/term/name={$form['name']}",     array($term_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_term/form={$form['name']}"); // name= was a typo error
        do_action_deprecated("acfe/form/submit/term/form={$form['name']}",     array($term_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_term/form={$form['name']}");
        do_action_deprecated("acfe/form/submit/term/action={$action['name']}", array($term_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_term/action={$action['name']}");
    
    }
    
    
    /**
     * submit_term_args
     *
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_term_args($args, $form, $action){
        
        // deprecated
        $args = apply_filters_deprecated("acfe/form/submit/term_args",                          array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_term_args");
        $args = apply_filters_deprecated("acfe/form/submit/term_args/form={$form['name']}",     array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_term_args/form={$form['name']}");
        $args = apply_filters_deprecated("acfe/form/submit/term_args/action={$action['name']}", array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_term_args/action={$action['name']}");
        
        return $args;
        
    }
    
    
    /**
     * submit_term_output
     *
     * @param $term
     * @param $term_id
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_term_output($term, $args, $form, $action){
        
        // deprecated
        $term = apply_filters_deprecated("acfe/form/query_var/term",                          array($term, $term['term_id'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_term_output");
        $term = apply_filters_deprecated("acfe/form/query_var/term/form={$form['name']}",     array($term, $term['term_id'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_term_output/form={$form['name']}");
        $term = apply_filters_deprecated("acfe/form/query_var/term/action={$action['name']}", array($term, $term['term_id'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_term_output/action={$action['name']}");
        
        // deprecated
        $term = apply_filters_deprecated("acfe/form/output/term",                          array($term, $term['term_id'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_term_output");
        $term = apply_filters_deprecated("acfe/form/output/term/form={$form['name']}",     array($term, $term['term_id'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_term_output/form={$form['name']}");
        $term = apply_filters_deprecated("acfe/form/output/term/action={$action['name']}", array($term, $term['term_id'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_term_output/action={$action['name']}");
        
        return $term;
        
    }
    
    
    /**
     * submit_redirect_url
     *
     * @param $url
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_redirect_url($url, $form, $action){
        
        // deprecated
        $url = apply_filters_deprecated("acfe/form/submit/redirect_url",                          array($url, $form, $action['name']), '0.9', "acfe/form/submit_redirect_url");
        $url = apply_filters_deprecated("acfe/form/submit/redirect_url/form={$form['name']}",     array($url, $form, $action['name']), '0.9', "acfe/form/submit_redirect_url/form={$form['name']}");
        $url = apply_filters_deprecated("acfe/form/submit/redirect_url/action={$action['name']}", array($url, $form, $action['name']), '0.9', "acfe/form/submit_redirect_url/action={$action['name']}");
        
        return $url;
        
    }
    
    
    /**
     * load_post_id
     *
     * @param $post_id
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function load_post_id($post_id, $form, $action){
        
        // deprecated
        $post_id = apply_filters_deprecated("acfe/form/load/post_id",                          array($post_id, $form, $action['name']), '0.9', "acfe/form/load_post_id");
        $post_id = apply_filters_deprecated("acfe/form/load/post_id/form={$form['name']}",     array($post_id, $form, $action['name']), '0.9', "acfe/form/load_post_id/form={$form['name']}");
        $post_id = apply_filters_deprecated("acfe/form/load/post_id/action={$action['name']}", array($post_id, $form, $action['name']), '0.9', "acfe/form/load_post_id/action={$action['name']}");
        
        return $post_id;
        
    }
    
    
    /**
     * submit_post
     *
     * @param $post_id
     * @param $args
     * @param $form
     * @param $action
     */
    function submit_post($post_id, $args, $form, $action){
        
        // deprecated
        do_action_deprecated("acfe/form/submit/post",                          array($post_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_post");
        do_action_deprecated("acfe/form/submit/post/form={$form['name']}",     array($post_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_post/form={$form['name']}");
        do_action_deprecated("acfe/form/submit/post/action={$action['name']}", array($post_id, $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_post/action={$action['name']}");
        
    }
    
    
    /**
     * submit_post_args
     *
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_post_args($args, $form, $action){
        
        // deprecated
        $args = apply_filters_deprecated("acfe/form/submit/post_args",                          array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_post_args");
        $args = apply_filters_deprecated("acfe/form/submit/post_args/form={$form['name']}",     array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_post_args/form={$form['name']}");
        $args = apply_filters_deprecated("acfe/form/submit/post_args/action={$action['name']}", array($args, $action['type'], $form, $action['name']), '0.9', "acfe/form/submit_post_args/action={$action['name']}");
        
        return $args;
        
    }
    
    
    /**
     * submit_post_output
     *
     * @param $post
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_post_output($post, $args, $form, $action){
        
        // deprecated
        $post = apply_filters_deprecated("acfe/form/query_var/post",                          array($post, $post['ID'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_post_output");
        $post = apply_filters_deprecated("acfe/form/query_var/post/form={$form['name']}",     array($post, $post['ID'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_post_output/form={$form['name']}");
        $post = apply_filters_deprecated("acfe/form/query_var/post/action={$action['name']}", array($post, $post['ID'], $action['type'], $args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_post_output/action={$action['name']}");
        
        // deprecated
        $post = apply_filters_deprecated("acfe/form/output/post",                          array($post, $post['ID'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_post_output");
        $post = apply_filters_deprecated("acfe/form/output/post/form={$form['name']}",     array($post, $post['ID'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_post_output/form={$form['name']}");
        $post = apply_filters_deprecated("acfe/form/output/post/action={$action['name']}", array($post, $post['ID'], $action['type'], $args, $form, $action['name']), '0.9', "acfe/form/submit_post_output/action={$action['name']}");
        
        return $post;
    
    }
    
    
    /**
     * submit_email
     *
     * @param $args
     * @param $form
     * @param $action
     */
    function submit_email($args, $form, $action){
        
        // deprecated
        do_action_deprecated("acfe/form/submit/email",                          array($args, $form, $action['name']), '0.9', "acfe/form/submit_email");
        do_action_deprecated("acfe/form/submit/email/form={$form['name']}",     array($args, $form, $action['name']), '0.9', "acfe/form/submit_email/form={$form['name']}");
        do_action_deprecated("acfe/form/submit/email/action={$action['name']}", array($args, $form, $action['name']), '0.9', "acfe/form/submit_email/action={$action['name']}");
        
    }
    
    
    /**
     * submit_email_args
     *
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_email_args($args, $form, $action){
        
        // deprecated
        $args = apply_filters_deprecated("acfe/form/submit/email/args",                          array($args, $form, $action['name']), '0.8.1', "acfe/form/submit_email_args");
        $args = apply_filters_deprecated("acfe/form/submit/email/args/form={$form['name']}",     array($args, $form, $action['name']), '0.8.1', "acfe/form/submit_email_args/form={$form['name']}");
        $args = apply_filters_deprecated("acfe/form/submit/email/args/action={$action['name']}", array($args, $form, $action['name']), '0.8.1', "acfe/form/submit_email_args/action={$action['name']}");
        
        // deprecated
        $args = apply_filters_deprecated("acfe/form/submit/email_args",                          array($args, $form, $action['name']), '0.9', "acfe/form/submit_email_args");
        $args = apply_filters_deprecated("acfe/form/submit/email_args/form={$form['name']}",     array($args, $form, $action['name']), '0.9', "acfe/form/submit_email_args/form={$form['name']}");
        $args = apply_filters_deprecated("acfe/form/submit/email_args/action={$action['name']}", array($args, $form, $action['name']), '0.9', "acfe/form/submit_email_args/action={$action['name']}");
        
        return $args;
        
    }
    
    
    /**
     * submit_email_output
     *
     * @param $args
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function submit_email_output($args, $form, $action){
        
        // deprecated
        $args = apply_filters_deprecated("acfe/form/query_var/email",                          array($args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_email_output");
        $args = apply_filters_deprecated("acfe/form/query_var/email/form={$form['name']}",     array($args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_email_output/form={$form['name']}");
        $args = apply_filters_deprecated("acfe/form/query_var/email/action={$action['name']}", array($args, $form, $action['name']), '0.8.7.5', "acfe/form/submit_email_output/action={$action['name']}");
        
        // deprecated
        $args = apply_filters_deprecated("acfe/form/output/email",                          array($args, $form, $action['name']), '0.9', "acfe/form/submit_email_output");
        $args = apply_filters_deprecated("acfe/form/output/email/form={$form['name']}",     array($args, $form, $action['name']), '0.9', "acfe/form/submit_email_output/form={$form['name']}");
        $args = apply_filters_deprecated("acfe/form/output/email/action={$action['name']}", array($args, $form, $action['name']), '0.9', "acfe/form/submit_email_output/action={$action['name']}");
        
        return $args;
        
    }
    
    
    /**
     * prepare_custom
     *
     * @param $action
     * @param $form
     *
     * @return false|mixed
     */
    function prepare_custom($action, $form){
        
        // deprecated
        $prepare = true;
        $prepare = apply_filters_deprecated("acfe/form/prepare/{$action['name']}",                      array($prepare, $form, $form['post_id'], $action['name']), '0.9', "acfe/form/prepare_{$action['name']}");
        $prepare = apply_filters_deprecated("acfe/form/prepare/{$action['name']}/form={$form['name']}", array($prepare, $form, $form['post_id'], $action['name']), '0.9', "acfe/form/prepare_{$action['name']}/form={$form['name']}");
        
        if($prepare === false){
            return false;
        }
        
        // return
        return $action;
        
    }
    
    
    /**
     * validate_custom
     *
     * @param $form
     * @param $action
     */
    function validate_custom($form, $action){
        
        // deprecated
        do_action_deprecated("acfe/form/validation/{$action['name']}",                      array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/validate_{$action['name']}");
        do_action_deprecated("acfe/form/validation/{$action['name']}/form={$form['name']}", array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/validate_{$action['name']}/form={$form['name']}");
        
    }
    
    
    /**
     * make_custom
     *
     * @param $form
     * @param $action
     */
    function make_custom($form, $action){
        
        // deprecated
        do_action_deprecated("acfe/form/submit/{$action['name']}",                      array($form, $action['name']), '0.9', "acfe/form/submit_{$action['name']}");
        do_action_deprecated("acfe/form/submit/{$action['name']}/form={$form['name']}", array($form, $action['name']), '0.9', "acfe/form/submit_{$action['name']}/form={$form['name']}");
        
    }
    
    
    /**
     * load_action
     *
     * @param $form
     * @param $action
     *
     * @return mixed
     */
    function load_action($form, $action){
        
        // deprecated
        $form = apply_filters_deprecated("acfe/form/load/{$action['action']}",                          array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/load_{$action['action']}");
        $form = apply_filters_deprecated("acfe/form/load/{$action['action']}/form={$form['name']}",     array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/load_{$action['action']}/form={$form['name']}");
        $form = apply_filters_deprecated("acfe/form/load/{$action['action']}/action={$action['name']}", array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/load_{$action['action']}/action={$action['name']}");
        
        return $form;
        
    }
    
    
    /**
     * validate_action
     *
     * @param $form
     * @param $action
     */
    function validate_action($form, $action){
        
        do_action_deprecated("acfe/form/validation/{$action['action']}",                          array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/validate_{$action['action']}");
        do_action_deprecated("acfe/form/validation/{$action['action']}/form={$form['name']}",     array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/validate_{$action['action']}/form={$form['name']}");
        do_action_deprecated("acfe/form/validation/{$action['action']}/action={$action['name']}", array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/validate_{$action['action']}/action={$action['name']}");
        
    }
    
    
    /**
     * prepare_action
     *
     * @param $action
     * @param $form
     *
     * @return false|mixed
     */
    function prepare_action($action, $form){
        
        // deprecated
        $prepare = true;
        $prepare = apply_filters_deprecated("acfe/form/prepare/{$action['action']}",                          array($prepare, $form, $form['post_id'], $action['name']), '0.9', "acfe/form/prepare_{$action['action']}");
        $prepare = apply_filters_deprecated("acfe/form/prepare/{$action['action']}/form={$form['name']}",     array($prepare, $form, $form['post_id'], $action['name']), '0.9', "acfe/form/prepare_{$action['action']}/form={$form['name']}");
        $prepare = apply_filters_deprecated("acfe/form/prepare/{$action['action']}/action={$action['name']}", array($prepare, $form, $form['post_id'], $action['name']), '0.9', "acfe/form/prepare_{$action['action']}/action={$action['name']}");
        
        if($prepare === false){
            return false;
        }
        
        return $action;
        
    }
    
    /**
     * make_action
     *
     * @param $form
     * @param $action
     */
    function make_action($form, $action){
        
        do_action_deprecated("acfe/form/make/{$action['action']}", array($form, $form['post_id'], $action['name']), '0.9', "acfe/form/make_{$action['action']}");
        
    }
    
    
    /**
     * load_form
     *
     * @param $form
     */
    function load_form($form){
                
        // deprecated
        if($form){$form = apply_filters_deprecated("acfe/form/load",                      array($form, $form['post_id']), '0.9', "acfe/form/load_form");}
        if($form){$form = apply_filters_deprecated("acfe/form/load/form={$form['name']}", array($form, $form['post_id']), '0.9', "acfe/form/load_form/form={$form['name']}");}
        
        return $form;
        
    }
    
    
    /**
     * validate_form
     *
     * @param $form
     */
    function validate_form($form){
        
        // deprecated
        do_action_deprecated("acfe/form/validation",                      array($form, $form['post_id']), '0.9', "acfe/form/validate_form");
        do_action_deprecated("acfe/form/validation/form={$form['name']}", array($form, $form['post_id']), '0.9', "acfe/form/validate_form/form={$form['name']}");
        
    }
    
    
    /**
     * submit_form
     *
     * @param $form
     */
    function submit_form($form){
        
        // deprecated
        do_action_deprecated("acfe/form/submit",                      array($form, $form['post_id']), '0.9', "acfe/form/submit_form");
        do_action_deprecated("acfe/form/submit/form={$form['name']}", array($form, $form['post_id']), '0.9', "acfe/form/submit_form/form={$form['name']}");
        
    }
    
    
    /**
     * render_success
     *
     * @param $form
     */
    function render_success($form){
        
        // deprecated
        do_action_deprecated("acfe/form/success",                           array($form), '0.9.0.3', "acfe/form/render_success");
        do_action_deprecated("acfe/form/success/id={$form['ID']}",          array($form), '0.9.0.3', "acfe/form/render_success/form={$form['name']}");
        do_action_deprecated("acfe/form/success/name={$form['name']}",      array($form), '0.9.0.3', "acfe/form/render_success/form={$form['name']}");
        
        // deprecated
        do_action_deprecated("acfe/form/success_form",                      array($form), '0.9.0.3', "acfe/form/render_success");
        do_action_deprecated("acfe/form/success_form/form={$form['name']}", array($form), '0.9.0.3', "acfe/form/render_success/form={$form['name']}");
        do_action_deprecated("acfe/form/success_form/name={$form['name']}", array($form), '0.9.0.3', "acfe/form/render_success/form={$form['name']}");
        
    }
    
    
    /**
     * render_before_form
     *
     * @param $form
     */
    function render_before_form($form){
        
        do_action_deprecated("acfe/form/render/before_form",                      array($form), '0.9', "acfe/form/render_before_form");
        do_action_deprecated("acfe/form/render/before_form/id={$form['ID']}",     array($form), '0.9', "acfe/form/render_before_form/form={$form['name']}");
        do_action_deprecated("acfe/form/render/before_form/name={$form['name']}", array($form), '0.9', "acfe/form/render_before_form/form={$form['name']}");
        
    }
    
    
    /**
     * render_before_fields
     *
     * @param $form
     */
    function render_before_fields($form){
        
        do_action_deprecated("acfe/form/render/before_fields",                      array($form), '0.9', "acfe/form/render_before_fields");
        do_action_deprecated("acfe/form/render/before_fields/id={$form['ID']}",     array($form), '0.9', "acfe/form/render_before_fields/form={$form['name']}");
        do_action_deprecated("acfe/form/render/before_fields/name={$form['name']}", array($form), '0.9', "acfe/form/render_before_fields/form={$form['name']}");
        
    }
    
    
    /**
     * render_fields
     *
     * @param $form
     */
    function render_fields($form){
        
        do_action_deprecated("acfe/form/render/fields",                      array($form), '0.9', "acfe/form/render_fields");
        do_action_deprecated("acfe/form/render/fields/id={$form['ID']}",     array($form), '0.9', "acfe/form/render_fields/form={$form['name']}");
        do_action_deprecated("acfe/form/render/fields/name={$form['name']}", array($form), '0.9', "acfe/form/render_fields/form={$form['name']}");
        
    }
    
    
    /**
     * render_after_fields
     *
     * @param $form
     */
    function render_after_fields($form){
        
        do_action_deprecated("acfe/form/render/after_fields",                      array($form), '0.9', "acfe/form/render_after_fields");
        do_action_deprecated("acfe/form/render/after_fields/id={$form['ID']}",     array($form), '0.9', "acfe/form/render_after_fields/form={$form['name']}");
        do_action_deprecated("acfe/form/render/after_fields/name={$form['name']}", array($form), '0.9', "acfe/form/render_after_fields/form={$form['name']}");
        
    }
    
    
    /**
     * render_after_form
     *
     * @param $form
     */
    function render_after_form($form){
        
        do_action_deprecated("acfe/form/render/after_form",                      array($form), '0.9', "acfe/form/render_after_form");
        do_action_deprecated("acfe/form/render/after_form/id={$form['ID']}",     array($form), '0.9', "acfe/form/render_after_form/form={$form['name']}");
        do_action_deprecated("acfe/form/render/after_form/name={$form['name']}", array($form), '0.9', "acfe/form/render_after_form/form={$form['name']}");
        
    }
    
    
    /**
     * prepare_form
     *
     * acfe/form/prepare_form:5
     *
     * handle deprecated form arguments for 0.9
     *
     * @param $item
     *
     * @return mixed
     */
    function prepare_form($item){
    
        /**
         * render
         */
        $render = '';
        
        if(!empty($item['custom_html_enabled']) && !empty($item['custom_html'])){
            $render = $item['custom_html'];
        }
    
        // generate render
        if(!empty($item['html_before_fields']) || !empty($item['html_after_fields'])){
        
            // empty render
            // use {render:fields}
            if(empty($render)){
                $render = '{render:fields}';
            }
        
            // prepend before render
            if(!empty($item['html_before_fields'])){
                $render = $item['html_before_fields'] . "\n\n" . $render;
            }
        
            // append before render
            if(!empty($item['html_after_fields'])){
                $render = $render . "\n\n" . $item['html_after_fields'];
            }
        
        }
        
        // deprecated {field_group:group_61642cb824d8a}
        if(strpos($render, '{field:') !== false){
            _deprecated_function('ACF Extended: {field:field_625e53aa1a791} template tag in form render', '0.9', 'the template tag {render:field_625e53aa1a791}');
            $render = str_replace('{field:', '{render:', $render);
        }
    
        // deprecated {field_group:group_61642cb824d8a} to render field
        if(strpos($render, '{field_group:') !== false){
            _deprecated_function('ACF Extended: {field_group:group_61642cb824d8a} template tag in form render', '0.9', 'the template tag {render:group_61642cb824d8a}');
            $render = str_replace('{field_group:', '{render:', $render);
        }
    
        if(isset($item['custom_html_enabled'])){
            _deprecated_function('ACF Extended: "custom_html_enabled" form argument', '0.9', 'the "render" argument');
        }
        
        if(isset($item['custom_html'])){
            _deprecated_function('ACF Extended: "custom_html" form argument', '0.9', 'the "render" argument');
        }
        
        if(isset($item['html_before_fields'])){
            _deprecated_function('ACF Extended: "html_before_fields" form argument', '0.9', 'the "render" argument');
        }
        
        if(isset($item['html_after_fields'])){
            _deprecated_function('ACF Extended: "html_after_fields" form argument', '0.9', 'the "render" argument');
        }
        
        // cleanup keys
        unset($item['custom_html_enabled'], $item['custom_html'], $item['html_before_fields'], $item['html_after_fields']);
    
        // assign form render
        if(!empty($render)){
            $item['render'] = $render;
        }
    
        /**
         * attributes
         */
        if(isset($item['form_submit'])){
            _deprecated_function('ACF Extended: "form_submit" form argument', '0.9');
            unset($item['form_submit']);
        }
    
        if(isset($item['submit_value'])){
            _deprecated_function('ACF Extended: "submit_value" form argument', '0.9', 'the "attributes > submit > value" argument');
            $item['attributes']['submit']['value'] = $item['submit_value'];
            unset($item['submit_value']);
        }
    
        if(isset($item['html_submit_button'])){
            _deprecated_function('ACF Extended: "html_submit_button" form argument', '0.9', 'the "attributes > submit > button" argument');
            $item['attributes']['submit']['button'] = $item['html_submit_button'];
            unset($item['html_submit_button']);
        }
    
        if(isset($item['html_submit_spinner'])){
            _deprecated_function('ACF Extended: "html_submit_spinner" form argument', '0.9', 'the "attributes > submit > spinner" argument');
            $item['attributes']['submit']['spinner'] = $item['html_submit_spinner'];
            unset($item['html_submit_spinner']);
        }
    
        if(isset($item['field_el'])){
            _deprecated_function('ACF Extended: "field_el" form argument', '0.9', 'the "attributes > fields > element" argument');
            $item['attributes']['fields']['element'] = $item['field_el'];
            unset($item['field_el']);
        }
    
        if(isset($item['fields_attributes']['wrapper_class'])){
            _deprecated_function('ACF Extended: "fields_attributes > wrapper_class" form argument', '0.9', 'the "attributes > fields > wrapper_class" argument');
            $item['attributes']['fields']['wrapper_class'] = $item['fields_attributes']['wrapper_class'];
            unset($item['fields_attributes']['wrapper_class']);
        }
    
        if(isset($item['fields_attributes']['class'])){
            _deprecated_function('ACF Extended: "fields_attributes > class" form argument', '0.9', 'the "attributes > fields > class" argument');
            $item['attributes']['fields']['class'] = $item['fields_attributes']['class'];
            unset($item['fields_attributes']['class']);
        }
    
        if(isset($item['fields_attributes'])){
            _deprecated_function('ACF Extended: "fields_attributes" form argument', '0.9', 'the "attributes > fields" argument');
            unset($item['fields_attributes']);
        }
    
        if(isset($item['form'])){
            _deprecated_function('ACF Extended: "form" form argument', '0.9', 'the "attributes > form > element" argument');
            $item['attributes']['form']['element'] = $item['form'] ? 'form' : 'div';
            unset($item['form']);
        }
    
        if(isset($item['form_attributes']['id'])){
            _deprecated_function('ACF Extended: "form_attributes > id" form argument', '0.9', 'the "attributes > form > id" argument');
            $item['attributes']['form']['id'] = $item['form_attributes']['id'];
            unset($item['form_attributes']['id']);
        }
    
        if(isset($item['form_attributes']['class'])){
            _deprecated_function('ACF Extended: "form_attributes > class" form argument', '0.9', 'the "attributes > form > class" argument');
            $item['attributes']['form']['class'] = $item['form_attributes']['class'];
            unset($item['form_attributes']['class']);
        }
    
        if(isset($item['form_attributes'])){
            _deprecated_function('ACF Extended: "form_attributes" form argument', '0.9', 'the "attributes > form" argument');
            unset($item['form_attributes']);
        }
    
        /**
         * validation
         */
        if(isset($item['hide_error'])){
            _deprecated_function('ACF Extended: "hide_error" form argument', '0.9', 'the "validation > hide_error" argument');
            $item['validation']['hide_error'] = $item['hide_error'];
            unset($item['hide_error']);
        }
    
        if(isset($item['hide_unload'])){
            _deprecated_function('ACF Extended: "hide_unload" form argument', '0.9', 'the "validation > hide_unload" argument');
            $item['validation']['hide_unload'] = $item['hide_unload'];
            unset($item['hide_unload']);
        }
    
        if(isset($item['hide_revalidation'])){
            _deprecated_function('ACF Extended: "hide_revalidation" form argument', '0.9', 'the "validation > hide_revalidation" argument');
            $item['validation']['hide_revalidation'] = $item['hide_revalidation'];
            unset($item['hide_revalidation']);
        }
    
        if(isset($item['errors_position'])){
            _deprecated_function('ACF Extended: "errors_position" form argument', '0.9', 'the "validation > errors_position" argument');
            $item['validation']['errors_position'] = $item['errors_position'];
            unset($item['errors_position']);
        }
    
        if(isset($item['errors_class'])){
            _deprecated_function('ACF Extended: "errors_class" form argument', '0.9', 'the "validation > errors_class" argument');
            $item['validation']['errors_class'] = $item['errors_class'];
            unset($item['errors_class']);
        }
    
        /**
         * success
         */
        if(isset($item['updated_message'])){
            _deprecated_function('ACF Extended: "updated_message" form argument', '0.9', 'the "success > message" argument');
            $item['success']['message'] = $item['updated_message'];
            unset($item['updated_message']);
        }
    
        if(isset($item['html_updated_message'])){
            _deprecated_function('ACF Extended: "html_updated_message" form argument', '0.9', 'the "success > wrapper" argument');
            $item['success']['wrapper'] = $item['html_updated_message'];
            unset($item['html_updated_message']);
        }
    
        if(isset($item['updated_hide_form'])){
            _deprecated_function('ACF Extended: "updated_hide_form" form argument', '0.9', 'the "success > hide_form" argument');
            $item['success']['hide_form'] = $item['updated_hide_form'];
            unset($item['updated_hide_form']);
        }
        
        if(isset($item['return'])){
            _deprecated_function('ACF Extended: "Redirection" form setting', '0.8.7.5', "the Redirect Action (See documentation: https://www.acf-extended.com/features/modules/dynamic-forms)");
        }
    
        /**
         * settings
         */
        if(isset($item['field_groups_rules'])){
            _deprecated_function('ACF Extended: "field_groups_rules" form argument', '0.9', 'the "settings > location" argument');
            $item['settings']['location'] = $item['field_groups_rules'];
            unset($item['field_groups_rules']);
        }
        
        if(isset($item['honeypot'])){
            _deprecated_function('ACF Extended: "honeypot" form argument', '0.9', 'the "settings > honeypot" argument');
            $item['settings']['honeypot'] = $item['honeypot'];
            unset($item['honeypot']);
        }
    
        if(isset($item['kses'])){
            _deprecated_function('ACF Extended: "kses" form argument', '0.9', 'the "settings > kses" argument');
            $item['settings']['kses'] = $item['kses'];
            unset($item['kses']);
        }
    
        if(isset($item['uploader'])){
            _deprecated_function('ACF Extended: "uploader" form argument', '0.9', 'the "settings > uploader" argument');
            $item['settings']['uploader'] = $item['uploader'];
            unset($item['uploader']);
        }
    
        if(isset($item['label_placement'])){
            _deprecated_function('ACF Extended: "label_placement" form argument', '0.9', 'the "attributes > field > label" argument');
            $item['attributes']['fields']['label'] = $item['label_placement'];
            unset($item['label_placement']);
        }
        
        if(isset($item['instruction_placement'])){
            _deprecated_function('ACF Extended: "instruction_placement" form argument', '0.9', 'the "attributes > fields > instruction" argument');
            $item['attributes']['fields']['instruction'] = $item['instruction_placement'];
            unset($item['instruction_placement']);
        }
        
        return $item;
        
    }
    
}

acf_new_instance('acfe_module_form_deprecated');

endif;


/**
 * acfe_import_forms
 *
 * @param $forms
 *
 * @return array|mixed|WP_Error
 */
function acfe_import_forms($forms){
    _deprecated_function('ACF Extended: acfe_import_forms()', '0.8.8.2', 'acfe_import_form()');
    return acfe_import_form($forms);
}


/**
 * acfe_import_dynamic_form
 *
 * @param $forms
 *
 * @deprecated
 *
 * @return array|mixed|WP_Error
 */
function acfe_import_dynamic_form($forms = false){
    _deprecated_function('ACF Extended: acfe_import_dynamic_form()', '0.8.8.2', 'acfe_import_form()');
    return acfe_import_form($forms);
}


/**
 * acfe_form_map_fields_values
 *
 * @param $data
 * @param $array
 *
 * @deprecated
 *
 * @return array|mixed
 */
function acfe_form_map_fields_values($data = array(), $array = array()){
    _deprecated_function('ACF Extended: acfe_form_map_fields_values()', '0.9');
    return $data;
}


/**
 * acfe_form_map_field_value
 *
 * @param $field
 * @param $post_id
 * @param $form
 *
 * @deprecated
 *
 * @return mixed
 */
function acfe_form_map_field_value($field, $post_id = 0, $form = array()){
    _deprecated_function('ACF Extended: acfe_form_map_field_value()', '0.9');
    return $field;
}


/**
 * acfe_form_map_field_value_load
 *
 * @param $field
 * @param $post_id
 * @param $form
 *
 * @deprecated
 *
 * @return mixed
 */
function acfe_form_map_field_value_load($field, $post_id = 0, $form = array()){
    _deprecated_function('ACF Extended: acfe_form_map_field_value_load()', '0.9');
    return $field;
}


/**
 * acfe_form_filter_meta
 *
 * @param $meta
 * @param $acf
 *
 * @deprecated
 *
 * @return mixed
 */
function acfe_form_filter_meta($meta, $acf){
    _deprecated_function('ACF Extended: acfe_form_filter_meta()', '0.9');
    return $meta;
}


/**
 * acfe_form_map_vs_fields
 *
 * @param $map
 * @param $fields
 * @param $post_id
 * @param $form
 *
 * @deprecated
 *
 * @return mixed
 */
function acfe_form_map_vs_fields($map, $fields, $post_id = 0, $form = array()){
    _deprecated_function('ACF Extended: acfe_form_map_vs_fields()', '0.9');
    return $map;
}