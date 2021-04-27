<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_redirect')):

class acfe_form_redirect{
    
    function __construct(){
    
        /*
         * Action
         */
        add_filter('acfe/form/actions',         array($this, 'add_action'));
        add_action('acfe/form/make/redirect',   array($this, 'make'), 10, 3);
        
    }
    
    function make($form, $current_post_id, $action){
    
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
    
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/redirect',                          $prepare, $form, $current_post_id, $action);
        $prepare = apply_filters('acfe/form/prepare/redirect/form=' . $form_name,       $prepare, $form, $current_post_id, $action);
    
        if(!empty($action))
            $prepare = apply_filters('acfe/form/prepare/redirect/action=' . $action,    $prepare, $form, $current_post_id, $action);
        
        if($prepare === false)
            return;
    
        // Fields
        $url = get_sub_field('acfe_form_redirect_url');
        $url = acfe_form_map_field_value($url, $current_post_id, $form);
    
        // Args
        $url = apply_filters('acfe/form/submit/redirect_url',                     $url, $form, $action);
        $url = apply_filters('acfe/form/submit/redirect_url/form=' . $form_name,  $url, $form, $action);
    
        if(!empty($action))
            $url = apply_filters('acfe/form/submit/redirect_url/action=' . $action, $url, $form, $action);
        
        // Sanitize
        $url = trim($url);
        
        // Bail early if empty
        if(empty($url))
            return;
        
        // Redirect
        wp_redirect($url);
        exit;
        
    }
    
    function add_action($layouts){
        
        $layouts['layout_redirect'] = array(
            'key' => 'layout_redirect',
            'name' => 'redirect',
            'label' => 'Redirect action',
            'display' => 'row',
            'sub_fields' => array(
                
                /*
                 * Layout: Redirect Action
                 */
                array(
                    'key' => 'field_acfe_form_redirect_action_tab_action',
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
                    'key' => 'field_acfe_form_redirect_custom_alias',
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
                    'placeholder' => 'Redirect',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_redirect_url',
                    'label' => 'Action URL',
                    'name' => 'acfe_form_redirect_url',
                    'type' => 'text',
                    'instructions' => 'The URL to redirect to. See "Cheatsheet" tab for all available template tags.',
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
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),

                /*
                 * Layout: Redirect Advanced
                 */
                array(
                    'key' => 'field_acfe_form_redirect_action_tab_advanced',
                    'label' => 'Code',
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
                    'key' => 'field_acfe_form_redirect_action_tab_advanced_prepare',
                    'label' => 'Prepare the action',
                    'name' => 'acfe_form_redirect_action_tab_advanced_prepare',
                    'type' => 'acfe_dynamic_message',
                    'instructions' => 'Stop the action execution if necessary',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function($field){
                        
                        $form_name = get_field('acfe_form_name', acfe_get_post_id());
                        if(empty($form_name))
                            $form_name = 'my_form';
        
        ?>You may use the following hooks:<br /><br />
        
        <?php acfe_highlight(); ?>
add_filter('acfe/form/prepare/redirect', 'my_form_redirect_prepare', 10, 4);
add_filter('acfe/form/prepare/redirect/form=<?php echo $form_name; ?>', 'my_form_redirect_prepare', 10, 4);
add_filter('acfe/form/prepare/redirect/action=my-redirect-action', 'my_form_redirect_prepare', 10, 4);<?php acfe_highlight(); ?>
        <br />
        <?php acfe_highlight(); ?>
/*
 * @bool    $prepare  Execute the action
 * @array   $form     The form settings
 * @int     $post_id  Current post ID
 * @string  $action   Action alias name
 */
add_filter('acfe/form/prepare/redirect/form=<?php echo $form_name; ?>', 'my_form_redirect_prepare', 10, 4);
function my_form_redirect_prepare($prepare, $form, $post_id, $action){

    /*
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');

    if($my_field === 'Company'){

        // Do not execute Redirect
        $prepare = false;

    }

    /*
     * Get previous Post Action output
     */
    $prev_post_action = acfe_form_get_action('post');

    if(!empty($prev_post_action)){

        if($prev_post_action['post_title'] === 'Company'){

            // Do not execute Redirect
            $prepare = false;

        }

    }
    
    return $prepare;
    
}<?php acfe_highlight();
                    
                    },
                ),
                array(
                    'key' => 'field_acfe_form_redirect_action_tab_advanced_url',
                    'label' => 'Change Redirect URL',
                    'name' => 'acfe_form_redirect_action_tab_advanced_url',
                    'type' => 'acfe_dynamic_message',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'render' => function($field){
                        
                        $form_name = get_field('acfe_form_name', acfe_get_post_id());
                        if(empty($form_name))
                            $form_name = 'my_form';
        
        ?>You may use the following hooks:<br /><br />
        
        <?php acfe_highlight(); ?>
add_filter('acfe/form/submit/redirect_url', 'my_form_redirect_url', 10, 3);
add_filter('acfe/form/submit/redirect_url/form=<?php echo $form_name; ?>', 'my_form_redirect_url', 10, 3);
add_filter('acfe/form/submit/redirect_url/action=my-redirect-action', 'my_form_redirect_url', 10, 3);<?php acfe_highlight(); ?>
        <br />
        <?php acfe_highlight(); ?>
/*
 * @bool    $url     Redirect URL
 * @array   $form    The form settings
 * @string  $action  Action alias name
 */
add_filter('acfe/form/submit/redirect_url/form=<?php echo $form_name; ?>', 'my_form_redirect_url', 10, 3);
function my_form_redirect_url($url, $form, $action){
    
    /*
     * Get the form input value named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
    if($my_field === 'Company'){
        
        // Change Redirect URL
        $url = home_url('thank-you');
        
    }

    /*
     * Get previous Post Action output
     */
    $prev_post_action = acfe_form_get_action('post');
    
    if(!empty($prev_post_action)){

        if($prev_post_action['post_title'] === 'Company'){

            // Change Redirect URL
            $url = home_url('thank-you');

        }
        
    }
    
    // Do not redirect
    // return false;
    
    return $url;
    
}<?php acfe_highlight();
                    
                    },
                ),
                
            ),
            'min' => '',
            'max' => '',
        );
        
        return $layouts;
        
    }
    
}

new acfe_form_redirect();

endif;