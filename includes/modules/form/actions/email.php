<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/dynamic_forms', true))
    return;

if(!class_exists('acfe_form_email')):

class acfe_form_email{
    
    function __construct(){
        
        add_action('acfe/form/submit/action/email', array($this, 'submit'), 1, 3);
        
    }
    
    function submit($form, $post_id, $acf){
        
        $form_name = acf_maybe_get($form, 'acfe_form_name');
        $form_id = acf_maybe_get($form, 'acfe_form_id');
        
        $from = get_sub_field('acfe_form_email_from');
        $from = acfe_form_map_field_value($from, $acf);
        
        $to = get_sub_field('acfe_form_email_to');
        $to = acfe_form_map_field_value($to, $acf);
        
        $subject = get_sub_field('acfe_form_email_subject');
        $subject = acfe_form_map_field_value($subject, $acf);
        
        $content = get_sub_field('acfe_form_email_content');
        $content = acfe_form_map_field_value($content, $acf);
        
        $headers = array();
        $attachments = array();
        
        if(have_rows('acfe_form_email_files')):
            while(have_rows('acfe_form_email_files')): the_row();
            
                $file = get_sub_field('acfe_form_email_file');
                $file = acfe_form_map_field_value($file, $acf);
                
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
        
        $args = apply_filters('acfe/form/submit/mail_args',                      $args, $form);
        $args = apply_filters('acfe/form/submit/mail_args/name=' . $form_name,   $args, $form);
        $args = apply_filters('acfe/form/submit/mail_args/id=' . $form_id,       $args, $form);
        
        if($args === false)
            return;
         
        wp_mail($args['to'], $args['subject'], $args['content'], $args['headers'], $args['attachments']);
        
        do_action('acfe/form/submit/mail',                       $form, $args);
        do_action('acfe/form/submit/mail/name=' . $form_name,    $form, $args);
        do_action('acfe/form/submit/mail/id=' . $form_id,        $form, $args);
        
    }
    
}

new acfe_form_email();

endif;