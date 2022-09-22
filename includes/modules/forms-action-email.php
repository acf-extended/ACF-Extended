<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_form_email')):

class acfe_form_email{
    
    function __construct(){
    
        /*
         * Helpers
         */
        $helpers = acf_get_instance('acfe_dynamic_forms_helpers');
    
        /*
         * Action
         */
        add_filter('acfe/form/actions',                                     array($this, 'add_action'));
        add_action('acfe/form/make/email',                                  array($this, 'make'), 10, 3);
        add_action('acfe/form/submit/email',                                array($this, 'submit'), 10, 3);
        
        /*
         * Admin
         */
        add_filter('acf/prepare_field/name=acfe_form_email_file',           array($helpers, 'map_fields_deep'));
        
    }
    
    function make($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'name');
        $form_id = acf_maybe_get($form, 'ID');
    
        // Prepare
        $prepare = true;
        $prepare = apply_filters('acfe/form/prepare/email',                          $prepare, $form, $current_post_id, $action);
        $prepare = apply_filters('acfe/form/prepare/email/form=' . $form_name,       $prepare, $form, $current_post_id, $action);
    
        if(!empty($action))
            $prepare = apply_filters('acfe/form/prepare/email/action=' . $action,    $prepare, $form, $current_post_id, $action);
    
        if($prepare === false)
            return;
        
        // Fields
        $from = get_sub_field('acfe_form_email_from');
        $from = acfe_form_map_field_value($from, $current_post_id, $form);
    
        $reply_to = get_sub_field('acfe_form_email_reply_to');
        $reply_to = acfe_form_map_field_value($reply_to, $current_post_id, $form);
        
        $to = get_sub_field('acfe_form_email_to');
        $to = acfe_form_map_field_value($to, $current_post_id, $form);
        
        $cc = get_sub_field('acfe_form_email_cc');
        $cc = acfe_form_map_field_value($cc, $current_post_id, $form);
        
        $bcc = get_sub_field('acfe_form_email_bcc');
        $bcc = acfe_form_map_field_value($bcc, $current_post_id, $form);
        
        $subject = get_sub_field('acfe_form_email_subject');
        $subject = acfe_form_map_field_value($subject, $current_post_id, $form);
        
        $content = get_sub_field('acfe_form_email_content');
        $content = acfe_form_map_field_value($content, $current_post_id, $form);
        
        $headers = array();
        $attachments = array();
        
        // Delete files
        $delete_files = array();
        
        // Attachments: Dynamic
        if(have_rows('acfe_form_email_files')):
            while(have_rows('acfe_form_email_files')): the_row();
            
                $file_field_key = get_sub_field('acfe_form_email_file');
                $file_delete = get_sub_field('acfe_form_email_file_delete');
                $file_id = acfe_form_map_field_value($file_field_key, $current_post_id, $form);
                
                // Force Array
                $field = acf_get_field($file_field_key);
                $field['return_format'] = 'array';
                
                $files = acf_format_value($file_id, 0, $field);
                $files = acf_get_array($files);
                
                // Single
                if(acf_maybe_get($files, 'ID')){
                    $files = array($files);
                }
                
                foreach($files as $file){
    
                    if(!acf_maybe_get($file, 'ID'))
                        continue;
    
                    $attachments[] = get_attached_file($file['ID']);
    
                    if($file_delete){
        
                        $delete_files[] = $file['ID'];
        
                    }
                    
                }
        
            endwhile;
        endif;
        
        // Attachments: Static
        if(have_rows('acfe_form_email_files_static')):
            while(have_rows('acfe_form_email_files_static')): the_row();
            
                $file = get_sub_field('acfe_form_email_file_static');
                
                $attachments[] = get_attached_file($file);
        
            endwhile;
        endif;
        
        $headers[] = 'From: ' . $from;
    
        if(!empty($reply_to)){
        
            $headers[] = 'Reply-To: ' . $reply_to;
        
        }
        
        if(!empty($cc)){
    
            $headers[] = 'Cc: ' . $cc;
         
        }
        
        if(!empty($bcc)){
    
            $headers[] = 'Bcc: ' . $bcc;
         
        }
    
        $headers[] = 'Content-Type: text/html';
        $headers[] = 'charset=UTF-8';
        
        $args = array(
            'from'          => $from,
            'to'            => $to,
            'reply_to'      => $reply_to,
            'cc'            => $cc,
            'bcc'           => $bcc,
            'subject'       => $subject,
            'content'       => $content,
            'headers'       => $headers,
            'attachments'   => $attachments,
        );
        
        // Deprecated filters
        $args = apply_filters_deprecated('acfe/form/submit/email/args',                      array($args, $form, $action), '0.8.1', 'acfe/form/submit/email_args');
        $args = apply_filters_deprecated('acfe/form/submit/email/args/form=' . $form_name,   array($args, $form, $action), '0.8.1', 'acfe/form/submit/email_args/form=' . $form_name);
        
        // Filters
        $args = apply_filters('acfe/form/submit/email_args',                      $args, $form, $action);
        $args = apply_filters('acfe/form/submit/email_args/form=' . $form_name,   $args, $form, $action);
        
        if(!empty($action)){
    
            // Deprecated filter
            $args = apply_filters_deprecated('acfe/form/submit/email/args/action=' . $action, array($args, $form, $action), '0.8.1', 'acfe/form/submit/email_args/action=' . $action);
            
            // Filter
            $args = apply_filters('acfe/form/submit/email_args/action=' . $action, $args, $form, $action);
            
        }
        
        // Bail early if no args
        if($args === false)
            return;
    
        // Check if Headers changed
        $rules = array(
            array(
                'args_key'     => 'from',
                'value_old'    => $from,
                'header_key'   => 'From:',
            ),
            array(
                'args_key'     => 'reply_to',
                'value_old'    => $reply_to,
                'header_key'   => 'Reply-To:',
            ),
            array(
                'args_key'     => 'cc',
                'value_old'    => $cc,
                'header_key'   => 'Cc:',
            ),
            array(
                'args_key'     => 'bcc',
                'value_old'    => $bcc,
                'header_key'   => 'Bcc:',
            ),
        );
    
        foreach($rules as $rule){
        
            $new_check = acf_maybe_get($args, $rule['args_key']);
        
            if(!empty($new_check) && $new_check !== $rule['value_old']){
            
                foreach($args['headers'] as &$header){
                
                    if(stripos($header, $rule['header_key']) !== 0)
                        continue;
                
                    $header = $rule['header_key'] . ' ' . $new_check;
                    break;
                
                }
            
            }
        
        }
        
        wp_mail($args['to'], $args['subject'], $args['content'], $args['headers'], $args['attachments']);
        
        do_action('acfe/form/submit/email',                     $args, $form, $action);
        do_action('acfe/form/submit/email/form=' . $form_name,  $args, $form, $action);
        
        if(!empty($action))
            do_action('acfe/form/submit/email/action=' . $action, $args, $form, $action);
        
        // Delete files
        if(!empty($delete_files)){
            
            foreach($delete_files as $file_id){
    
                wp_delete_attachment($file_id, true);
            
            }
            
        }
        
    }
    
    function submit($args, $form, $action){
    
        // Form name
        $form_name = acf_maybe_get($form, 'name');
    
        // Deprecated
        $args = apply_filters_deprecated("acfe/form/query_var/email",                    array($args, $form, $action), '0.8.7.5', "acfe/form/output/email");
        $args = apply_filters_deprecated("acfe/form/query_var/email/form={$form_name}",  array($args, $form, $action), '0.8.7.5', "acfe/form/output/email/form={$form_name}");
        $args = apply_filters_deprecated("acfe/form/query_var/email/action={$action}",   array($args, $form, $action), '0.8.7.5', "acfe/form/output/email/action={$action}");
    
        // Output
        $args = apply_filters("acfe/form/output/email",                                       $args, $form, $action);
        $args = apply_filters("acfe/form/output/email/form={$form_name}",                     $args, $form, $action);
        $args = apply_filters("acfe/form/output/email/action={$action}",                      $args, $form, $action);
    
        // Old Query var
        $query_var = acfe_form_unique_action_id($form, 'email');
    
        if(!empty($action))
            $query_var = $action;
        
        set_query_var($query_var, $args);
        // ------------------------------------------------------------
    
        // Action Output
        $actions = get_query_var('acfe_form_actions', array());
        
        $actions['email'] = $args;
        
        if(!empty($action))
            $actions[$action] = $args;
        
        set_query_var('acfe_form_actions', $actions);
        // ------------------------------------------------------------
        
    }
    
    function add_action($layouts){
        
        $layouts['layout_email'] = array(
            'key' => 'layout_email',
            'name' => 'email',
            'label' => 'Email action',
            'display' => 'row',
            'sub_fields' => array(
    
                /*
                 * Documentation
                 */
                array(
                    'key' => 'field_acfe_form_email_action_docs',
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
                        echo '<a href="https://www.acf-extended.com/features/modules/dynamic-forms/e-mail-action" target="_blank">' . __('Documentation', 'acfe') . '</a>';
                    }
                ),
        
                /*
                 * Layout: Email Action
                 */
                array(
                    'key' => 'field_acfe_form_email_tab_action',
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
                    'key' => 'field_acfe_form_email_custom_alias',
                    'label' => 'Action name',
                    'name' => 'acfe_form_custom_alias',
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
                    'placeholder' => 'Email',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
        
                /*
                 * Layout: Email Send
                 */
                array(
                    'key' => 'field_acfe_form_email_tab_email',
                    'label' => 'Email',
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
                    'key' => 'field_acfe_form_email_from',
                    'label' => 'From',
                    'name' => 'acfe_form_email_from',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Name <email@domain.com>',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_to',
                    'label' => 'To',
                    'name' => 'acfe_form_email_to',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'email@domain.com',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_reply_to',
                    'label' => 'Reply to',
                    'name' => 'acfe_form_email_reply_to',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'Name <email@domain.com>',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_cc',
                    'label' => 'Cc',
                    'name' => 'acfe_form_email_cc',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'email@domain.com',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_bcc',
                    'label' => 'Bcc',
                    'name' => 'acfe_form_email_bcc',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => 'email@domain.com',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_subject',
                    'label' => 'Subject',
                    'name' => 'acfe_form_email_subject',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_email_content',
                    'label' => 'Content',
                    'name' => 'acfe_form_email_content',
                    'type' => 'wysiwyg',
                    'instructions' => 'Fields values may be included using <code>{field:field_key}</code> <code>{field:title}</code>. All fields may be included using <code>{fields}</code>.<br />See "Cheatsheet" tab for advanced usage.',
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
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
        
                /*
                 * Layout: Email Attachments
                 */
                array(
                    'key' => 'field_acfe_form_email_tab_attachments',
                    'label' => 'Attachments',
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
                    'key' => 'field_acfe_form_email_files',
                    'label' => 'Dynamic files',
                    'name' => 'acfe_form_email_files',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'acfe_repeater_stylised_button' => 0,
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'table',
                    'button_label' => 'Add file',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_email_file',
                            'label' => 'File',
                            'name' => 'acfe_form_email_file',
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
                            ),
                            'default_value' => array(
                            ),
                            'allow_null' => 0,
                            'multiple' => 0,
                            'ui' => 1,
                            'return_format' => 'value',
                            'ajax' => 0,
                            'placeholder' => '',
                            'search_placeholder' => 'Enter a custom value or template tag. (See "Cheatsheet" tab)',
                            'allow_custom' => 1,
                        ),
                        array(
                            'key' => 'field_acfe_form_email_file_delete',
                            'label' => 'Delete file',
                            'name' => 'acfe_form_email_file_delete',
                            'type' => 'true_false',
                            'instructions' => '',
                            'required' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'message' => 'Delete once submitted',
                            'default_value' => 0,
                            'ui' => 1,
                            'ui_on_text' => '',
                            'ui_off_text' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_email_files_static',
                    'label' => 'Static files',
                    'name' => 'acfe_form_email_files_static',
                    'type' => 'repeater',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'acfe_repeater_stylised_button' => 0,
                    'collapsed' => '',
                    'min' => 0,
                    'max' => 0,
                    'layout' => 'table',
                    'button_label' => 'Add file',
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_email_file_static',
                            'label' => 'File',
                            'name' => 'acfe_form_email_file_static',
                            'type' => 'file',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => 0,
                            'wrapper' => array(
                                'width' => '',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'return_format' => 'id',
                        ),
                    ),
                ),

            ),
            'min' => '',
            'max' => '',
        );
        
        return $layouts;
        
    }
    
}

new acfe_form_email();

endif;