<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_email')):

class acfe_form_email{
    
    function __construct(){
        
        add_action('acfe/form/prepare/email',                               array($this, 'prepare'), 1, 3);
        add_action('acfe/form/submit/email',                                array($this, 'submit'), 1, 3);
        
        add_filter('acf/prepare_field/name=acfe_form_email_file',           array(acfe()->acfe_form, 'map_fields_deep'));
        
        add_action('acf/render_field/name=acfe_form_email_advanced_args',   array($this, 'advanced_args'));
        add_action('acf/render_field/name=form_email_advanced_send',        array($this, 'advanced_send'));
        
    }
    
    function prepare($form, $current_post_id, $action){
        
        // Form
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        
        // Fields
        $from = get_sub_field('acfe_form_email_from');
        $from = acfe_form_map_field_value($from, $current_post_id, $form);
        
        $to = get_sub_field('acfe_form_email_to');
        $to = acfe_form_map_field_value($to, $current_post_id, $form);
        
        $subject = get_sub_field('acfe_form_email_subject');
        $subject = acfe_form_map_field_value($subject, $current_post_id, $form);
        
        $content = get_sub_field('acfe_form_email_content');
        $content = acfe_form_map_field_value($content, $current_post_id, $form);
        
        $headers = array();
        $attachments = array();
        
        // Attachments: Dynamic
        if(have_rows('acfe_form_email_files')):
            while(have_rows('acfe_form_email_files')): the_row();
            
                $file_field_key = get_sub_field('acfe_form_email_file');
                $file_id = acfe_form_map_field_value($file_field_key, $current_post_id, $form);
                
                $field = acf_get_field($file_field_key);
                $file = acf_format_value($file_id, 0, $field);
                
                if(!acf_maybe_get($file, 'ID'))
                    continue;
                
                $attachments[] = get_attached_file($file['ID']);
        
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
        $headers[] = 'Content-Type: text/html';
        $headers[] = 'charset=UTF-8';
        
        $args = array(
            'from'          => $from,
            'to'            => $to,
            'subject'       => $subject,
            'content'       => $content,
            'headers'       => $headers,
            'attachments'   => $attachments,
        );
        
        // Deprecated filters
        $args = apply_filters('acfe/form/submit/email/args',                      $args, $form, $action);
        $args = apply_filters('acfe/form/submit/email/args/form=' . $form_name,   $args, $form, $action);
        
        // Filters
        $args = apply_filters('acfe/form/submit/email_args',                      $args, $form, $action);
        $args = apply_filters('acfe/form/submit/email_args/form=' . $form_name,   $args, $form, $action);
        
        if(!empty($action)){
            
            $args = apply_filters('acfe/form/submit/email/args/action=' . $action, $args, $form, $action);
            
            $args = apply_filters('acfe/form/submit/email_args/action=' . $action, $args, $form, $action);
            
        }
        
        // Check if 'from' has changed
        $new_from = acf_maybe_get($args, 'from');
        
        // Re-assign header
        if(!empty($new_from) && $new_from !== $from){
            
            foreach($args['headers'] as &$header){
                
                if(stripos($header, 'from:') !== 0)
                    continue;
                
                $header = 'From: ' . $args['from'];
                break;
                
            }
            
        }
        
        if(!$args)
            return;
         
        wp_mail($args['to'], $args['subject'], $args['content'], $args['headers'], $args['attachments']);
        
        do_action('acfe/form/submit/email',                     $args, $form, $action);
        do_action('acfe/form/submit/email/form=' . $form_name,  $args, $form, $action);
        
        if(!empty($action))
            do_action('acfe/form/submit/email/action=' . $action, $args, $form, $action);
        
    }
    
    function submit($args, $form, $action){
        
        if(!empty($action)){
        
            // Custom Query Var
            $custom_query_var = get_sub_field('acfe_form_custom_query_var');
            
            if(!empty($custom_query_var)){
                
                // Form name
                $form_name = acf_maybe_get($form, 'form_name');
                
                $args = apply_filters('acfe/form/query_var/email',                    $args, $form, $action);
                $args = apply_filters('acfe/form/query_var/email/form=' . $form_name, $args, $form, $action);
                $args = apply_filters('acfe/form/query_var/email/action=' . $action,  $args, $form, $action);
                
                set_query_var($action, $args);
            
            }
        
        }
        
    }
    
    function advanced_args($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_filter('acfe/form/submit/email_args', 'my_form_email_args', 10, 3);
add_filter('acfe/form/submit/email_args/form=<?php echo $form_name; ?>', 'my_form_email_args', 10, 3);
add_filter('acfe/form/submit/email_args/action=my-email-action', 'my_form_email_args', 10, 3);</pre>
<br />
<pre data-codemirror="php-plain">
/**
 * @array   $args   The generated email arguments
 * @array   $form   The form settings
 * @string  $action The action alias name
 */
add_filter('acfe/form/submit/email_args/form=<?php echo $form_name; ?>', 'my_form_email_args', 10, 4);
function my_form_email_args($args, $form, $action){
    
    /**
     * $args = array(
     *     'from'          => 'email@domain.com',
     *     'to'            => 'email@domain.com',
     *     'subject'       => 'Subject',
     *     'content'       => 'Content',
     *     'headers'       => array(
     *         'From: email@domain.com',
     *         'Content-Type: text/html',
     *         'charset=UTF-8'
     *     ),
     *     'attachments'   => array(
     *         '/path/to/file.jpg'
     *     )
     * );
     */
    
    
    /**
     * Return arguments
     * Note: Return false will stop e-mail from being sent
     */
    return $args;
    
}</pre><?php
        
    }
    
    function advanced_send($field){
        
        $form_name = 'my_form';
        
        if(acf_maybe_get($field, 'value'))
            $form_name = get_field('acfe_form_name', $field['value']);
        
        ?>You may use the following hooks:<br /><br />
<pre data-codemirror="php-plain">
add_action('acfe/form/submit/email', 'my_form_email_send', 10, 3);
add_action('acfe/form/submit/email/form=<?php echo $form_name; ?>', 'my_form_email_send', 10, 3);
add_action('acfe/form/submit/email/action=my-email-action', 'my_form_email_send', 10, 3);</pre>
<br />
<pre data-codemirror="php-plain">
/**
 * @array   $args       The generated email arguments
 * @array   $form       The form settings
 * @string  $action     The action alias name
 */
add_action('acfe/form/submit/email/form=<?php echo $form_name; ?>', 'my_form_email_send', 10, 3);
function my_form_email_send($args, $form, $action){
    
    /**
     * Get the value from the form input named 'my_field'
     * This is the value entered by the user during the form submission
     */
    $my_field = get_field('my_field');
    
}</pre><?php
        
    }
    
}

new acfe_form_email();

endif;