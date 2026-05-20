<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_form_post')):

class acfe_form_post{

    /**
     * construct
     */
    function __construct(){

        // re-add sidebar submitdiv metabox
        acfe_replace_action('load-post.php',     array('ACF_Form_Post', 'initialize'), array($this, 'initialize'));
        acfe_replace_action('load-post-new.php', array('ACF_Form_Post', 'initialize'), array($this, 'initialize'));
        
    }


    /**
     * initialize
     *
     * Rewrites ACF_Form_Post->initialize() which remove the submitdiv metabox in ACF Field Group since ACF 6.0+
     *
     * advanced-custom-fields-pro/includes/forms/form-post.php:48
     */
    function initialize(){

        // globals
        global $typenow;

        // get restricted post types
        $internal_post_types = $this->get_internal_posts_types();
        $restricted = array_merge($internal_post_types, array('acf-taxonomy', 'attachment'));

        // restrict specific post types
        if(in_array($typenow, $restricted)){
            return;
        }

        // enqueue scripts
        acf_enqueue_scripts(array(
            'uploader' => true,
        ));

        // actions
        add_action('add_meta_boxes', array(acf_get_instance('ACF_Form_Post'), 'add_meta_boxes'), 10, 2);

    }


    /**
     * get_internal_posts_types
     *
     * @return array|string[]
     */
    function get_internal_posts_types(){

        // function was introduced in acf 6.1+
        if(function_exists('acf_get_internal_post_types')){
            return (array) acf_get_internal_post_types();
        }

        // fallback
        return array('acf-field-group', 'acf-post-type', 'acf-taxonomy', 'acf-ui-options-page');

    }
    
}

acf_new_instance('acfe_form_post');

endif;