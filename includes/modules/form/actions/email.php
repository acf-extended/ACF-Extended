<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_form_email')):

class acfe_form_email{
    
    function __construct(){
        
        add_action('acfe/form/prepare/email',                       array($this, 'submit'), 1, 3);
        
        add_filter('acf/prepare_field/name=acfe_form_email_file',   array(acfe()->acfe_form, 'map_fields_deep'));
        
    }
    
    function submit($form, $post_id, $action){
        
        $form_name = acf_maybe_get($form, 'form_name');
        $form_id = acf_maybe_get($form, 'form_id');
        $post_info = acf_get_post_id_info($post_id);
        
        $from = get_sub_field('acfe_form_email_from');
        $from = acfe_form_map_field_value($from, $_POST['acf']);
        
        $to = get_sub_field('acfe_form_email_to');
        $to = acfe_form_map_field_value($to, $_POST['acf']);
        
        $subject = get_sub_field('acfe_form_email_subject');
        $subject = acfe_form_map_field_value($subject, $_POST['acf']);
        
        $content = get_sub_field('acfe_form_email_content');
        $content = acfe_form_map_field_value($content, $_POST['acf']);
        
        $headers = array();
        $attachments = array();
        
        if(have_rows('acfe_form_email_files')):
            while(have_rows('acfe_form_email_files')): the_row();
            
                $file_field_key = get_sub_field('acfe_form_email_file');
                $file_id = acfe_form_map_field_value($file_field_key, $_POST['acf']);
                
                $field = acf_get_field($file_field_key);
                $file = acf_format_value($file_id, 0, $field);
                
                if(!acf_maybe_get($file, 'ID'))
                    continue;
                
                $attachments[] = get_attached_file($file['ID']);
        
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
        
        $args = apply_filters('acfe/form/submit/email/args',                      $args, $form, $action);
        $args = apply_filters('acfe/form/submit/email/args/name=' . $form_name,   $args, $form, $action);
        $args = apply_filters('acfe/form/submit/email/args/id=' . $form_id,       $args, $form, $action);
        
        if(!empty($action))
            $args = apply_filters('acfe/form/submit/email/args/action=' . $action, $args, $form, $action);
        
        if(!$args)
            return;
         
        wp_mail($args['to'], $args['subject'], $args['content'], $args['headers'], $args['attachments']);
        
        do_action('acfe/form/submit/email',                     $args, $form, $action);
        do_action('acfe/form/submit/email/name=' . $form_name,  $args, $form, $action);
        do_action('acfe/form/submit/email/id=' . $form_id,      $args, $form, $action);
        
        if(!empty($action))
            do_action('acfe/form/submit/email/action=' . $action, $args, $form, $action);
        
    }
    
}

new acfe_form_email();

endif;