<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_module_form_action_email')):

class acfe_module_form_action_email extends acfe_module_form_action{
    
    /**
     * initialize
     */
    function initialize(){
        
        $this->name = 'email';
        $this->title = __('Email action', 'acfe');
        $this->title_alt = __('Email', 'acfe');
        
        $this->item = array(
            'action'      => 'email',
            'name'        => '',
            'email'       => array(
                'from'     => '',
                'to'       => '',
                'reply_to' => '',
                'cc'       => '',
                'bcc'      => '',
                'subject'  => '',
                'content'  => '',
                'html'     => false,
            ),
            'attachments' => array(
            ),
        );
        
    }
    
    
    /**
     * prepare_action
     *
     * acfe/form/prepare_post:9
     *
     * @param $action
     * @param $form
     *
     * @return array
     */
    function prepare_action($action, $form){
        
        return $action;
        
    }
    
    
    /**
     * make_action
     *
     * acfe/form/make_email:9
     *
     * @param $form
     * @param $action
     */
    function make_action($form, $action){
        
        // send email
        $args = $this->process($form, $action);
        
        if(!$args){
            return;
        }
        
        // output
        $this->generate_output($args, $form, $action);
        
        // hooks
        do_action("acfe/form/submit_email",                          $args, $form, $action);
        do_action("acfe/form/submit_email/form={$form['name']}",     $args, $form, $action);
        do_action("acfe/form/submit_email/action={$action['name']}", $args, $form, $action);
    
    }
    
    
    /**
     * process
     *
     * @param $form
     * @param $action
     *
     * @return false
     */
    function process($form, $action){
        
        // tags context
        acfe_add_context(array('context' => 'display'));
        
        // apply tags
        acfe_apply_tags($action['email']['from']);
        acfe_apply_tags($action['email']['to']);
        acfe_apply_tags($action['email']['reply_to']);
        acfe_apply_tags($action['email']['cc']);
        acfe_apply_tags($action['email']['bcc']);
        acfe_apply_tags($action['email']['subject']);
        acfe_apply_tags($action['email']['content'], array('unformat' => 'wysiwyg'));
        
        acfe_delete_context(array('context'));
        
        // html: apply shortcodes
        if($action['email']['html']){
            $action['email']['content'] = do_shortcode($action['email']['content']);
            
        // wysiwyg: apply the_content filters (autop, shortcode, etc.)
        }else{
            $action['email']['content'] = apply_filters('acf_the_content', $action['email']['content']);
        }
        
        // args
        $args = $action['email'];

        // headers
        $args['headers'] = $this->get_headers($action);
        
        // attachments
        $attachments = $this->get_attachments($action);
        $args['attachments'] = $attachments['attachments'];
        $args['delete_files'] = $attachments['delete_files'];
    
        // filters
        $args = apply_filters("acfe/form/submit_email_args",                          $args, $form, $action);
        $args = apply_filters("acfe/form/submit_email_args/form={$form['name']}",     $args, $form, $action);
        $args = apply_filters("acfe/form/submit_email_args/action={$action['name']}", $args, $form, $action);
    
        // bail early
        if($args === false){
            return false;
        }
        
        // attachements/delete_files might have been deleted by filters
        // reset to empty array if not set
        if(!isset($args['attachments']) || empty($args['attachments'])){
            $args['attachments'] = array();
        }
        
        if(!isset($args['delete_files']) || empty($args['delete_files'])){
            $args['delete_files'] = array();
        }
    
        // check arguments change after filters
        // re-construct headers with new arguments if needed
        $args = $this->validate_headers($args, $action);
    
        // send email
        wp_mail($args['to'], $args['subject'], $args['content'], $args['headers'], $args['attachments']);
    
        // delete files
        foreach($args['delete_files'] as $file_id){
            wp_delete_attachment($file_id, true);
        }
        
        return $args;
        
    }
    
    
    /**
     * get_headers
     *
     * @param $action
     *
     * @return array
     */
    function get_headers($action){
        
        // fields
        $fields = array(
            array('name' => 'From',         'value' => $action['email']['from']),
            array('name' => 'Reply-To',     'value' => $action['email']['reply_to']),
            array('name' => 'Cc',           'value' => $action['email']['cc']),
            array('name' => 'Bcc',          'value' => $action['email']['bcc']),
            array('name' => 'Content-Type', 'value' => 'text/html'),
        );
        
        // headers
        $headers = array();
    
        // construct headers
        foreach($fields as $field){
        
            // From: email@domain.com
            if(!empty($field['value'])){
                $headers[] = "{$field['name']}: {$field['value']}";
            }
        
        }
        
        // return
        return $headers;
        
    }
    
    
    /**
     * get_attachments
     *
     * @param $action
     *
     * @return array[]
     */
    function get_attachments($action){
    
        // attachments
        $attachments = array();
        $delete_files = array();
        
        /**
         * $action[attachments] => Array(
         *     [0] => Array(
         *         [file] => {field:field_626202af1fbcd}
         *         [delete] => true
         *     )
         *     [1] => 36,
         *     [2] => 52,
         * )
         */
        
        // construct attachments
        foreach($action['attachments'] as $row){
        
            // files
            if(is_array($row)){
                
                // vars
                $file_id = $row['file'];
                $delete = $row['delete'];
                
                // files
                $file_id = acfe_parse_tags($file_id, array('context' => 'save', 'format' => false, 'return' => 'raw')); // parse tags (unformatted + raw)
                $files = acf_get_array($file_id);
            
                // deprecated
                // just in case someone pass a file array in filters
                if(isset($files['ID'])){
                    $files = array($files);
                }
            
                foreach($files as $file){
                
                    $attachment_id = false;
                    $attachment_path = false;
                
                    // numeric
                    if(is_numeric($file)){
                        $attachment_id = $file;
                        $attachment_path = get_attached_file($file);
                    
                    // array
                    }elseif(is_array($file) && isset($file['ID'])){
                        $attachment_id = $file['ID'];
                        $attachment_path = get_attached_file($file['ID']);
                    
                    // url
                    }else{
    
                        // retrieve url from path/url
                        $path = acfe_locate_file_url($file);
                        $attachment_id = attachment_url_to_postid($path);
                    
                        if($attachment_id){
                            $attachment_path = get_attached_file($attachment_id);
                        }
                    
                    }
                
                    // add to attachments array
                    if($attachment_id && $attachment_path){
                        $attachments[] = $attachment_path;
                    
                        if($delete){
                            $delete_files[] = $attachment_id;
                        }
                    }
                
                }
            
            // static files
            }else{
            
                $attachment_path = false;
            
                // numeric
                if(is_numeric($row)){
                    $attachment_path = get_attached_file($row);
                
                // url
                }else{
                    
                    // retrieve url from path/url
                    $path = acfe_locate_file_url($row);
                    $attachment_id = attachment_url_to_postid($path);
                    
                    if($attachment_id){
                        $attachment_path = get_attached_file($attachment_id);
                    }
                
                }
            
                // add to attachments array
                if($attachment_path){
                    $attachments[] = $attachment_path;
                }
            
            }
        
        }
        
        return array(
            'attachments' => $attachments,
            'delete_files' => $delete_files,
        );
        
    }
    
    
    /**
     * validate_headers
     *
     * @param $args
     * @param $action
     */
    function validate_headers($args, $action){
        
        // fields
        $fields = array(
            'from'     => 'From:',
            'reply_to' => 'Reply-To:',
            'cc'       => 'Cc:',
            'bcc'      => 'Bcc:',
        );
    
        // loop fields
        foreach($fields as $slug => $label){
            
            // vars
            $action_value = $action['email'][ $slug ]; // get $action['email']['from']
            $args_value = acf_maybe_get($args, $slug); // get $args['from']
        
            // check args value changed compared to action value
            if($args_value && $args_value !== $action_value){
                
                // loop $args['headers']
                foreach(array_keys($args['headers']) as $i){
                    
                    // headers row starts with "From:..."
                    if(acfe_starts_with($args['headers'][ $i ], $label)){
                        $args['headers'][ $i ] = "{$label} {$args_value}";
                        break;
                    }
                
                }
            
            }
        
        }
        
        return $args;
        
    }
    
    
    /**
     * generate_output
     *
     * @param $args
     * @param $form
     * @param $action
     */
    function generate_output($args, $form, $action){
    
        // filters
        $args = apply_filters("acfe/form/submit_email_output",                          $args, $form, $action);
        $args = apply_filters("acfe/form/submit_email_output/form={$form['name']}",     $args, $form, $action);
        $args = apply_filters("acfe/form/submit_email_output/action={$action['name']}", $args, $form, $action);
    
        // action output
        $this->set_action_output($args, $action);
        
    }
    
    
    /**
     * prepare_load_action
     *
     * acfe/module/prepare_load_action
     *
     * @param $action
     *
     * @return array
     */
    function prepare_load_action($action){
        
        // email loop
        foreach(array_keys($action['email']) as $k){
            $action["email_{$k}"] = $action['email'][ $k ];
        }
        
        // save: target
        $value = $action['email']['content'];
        
        if($action['email']['html']){
            $action['email_content_group']['email_content_type'] = 'html';
            $action['email_content_group']['email_content_html'] = $value;
        }else{
            $action['email_content_group']['email_content_type'] = 'editor';
            $action['email_content_group']['email_content_editor'] = $value;
        }
        
        // clone var
        $attachments = $action['attachments'];
        
        // reset
        $action['files'] = array();
        $action['files_static'] = array();
        
        foreach($attachments as $row){
            
            // string (files_static)
            if(!is_array($row)){
                $action['files_static'][] = array('file_static' => $row);
                
            //array (files)
            }else{
                
                $action['files'][] = array(
                    'files_file' => $row['file'],
                    'files_delete' => $row['delete'],
                );
                
            }
            
        }
        
        return $action;
        
    }
    
    
    /**
     * prepare_save_action
     *
     * acfe/module/prepare_save_action
     *
     * @param $action
     * @param $item
     *
     * @return mixed
     */
    function prepare_save_action($action){
        
        $save = $this->item;
        
        // general
        $save['name'] = $action['name'];
        
        // email loop
        foreach(array_keys($save['email']) as $k){
            
            // from => email_from
            if(acf_maybe_get($action, "email_{$k}")){
                $save['email'][ $k ] = $action["email_{$k}"];
            }
            
        }
        
        // content group
        $group = $action['email_content_group'];
        
        // content type: editor
        if($group['content_type'] === 'editor'){
            $save['email']['content'] = $group['content_editor'];
            
        // content type: html
        }elseif($group['content_type'] === 'html'){
            $save['email']['content'] = $group['content_html'];
            $save['email']['html'] = true;
        }
        
        // files
        $action['files'] = acf_get_array($action['files']);
        $action['files_static'] = acf_get_array($action['files_static']);
        
        foreach($action['files'] as $row){
            $save['attachments'][] = $row;
        }
        
        foreach($action['files_static'] as $row){
            $save['attachments'][] = $row['file'];
        }
        
        return $save;
        
    }
    
    
    /**
     * register_layout
     *
     * @param $layout
     *
     * @return array
     */
    function register_layout($layout){
    
        return array(
    
            /**
             * documentation
             */
            array(
                'key' => 'field_doc',
                'label' => '',
                'name' => '',
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
    
            /**
             * action
             */
            array(
                'key' => 'field_tab_action',
                'label' => __('Action', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_name',
                'label' => __('Action name', 'acfe'),
                'name' => 'name',
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
                'default_value' => '',
                'placeholder' => __('Email', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
    
            /**
             * email
             */
            array(
                'key' => 'field_tab_email',
                'label' => __('Email', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_email_from',
                'label' => __('From', 'acfe'),
                'name' => 'email_from',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => __('Name <email@domain.com>', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_email_to',
                'label' => __('To', 'acfe'),
                'name' => 'email_to',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => __('email@domain.com', 'acfe'),
                'prepend' => '',
                'append' => '',
            ),
            array(
                'key' => 'field_email_reply_to',
                'label' => __('Reply to', 'acfe'),
                'name' => 'email_reply_to',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => __('Name <email@domain.com>', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_email_cc',
                'label' => __('Cc', 'acfe'),
                'name' => 'email_cc',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => __('email@domain.com', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_email_bcc',
                'label' => __('Bcc', 'acfe'),
                'name' => 'email_bcc',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => __('email@domain.com', 'acfe'),
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            array(
                'key' => 'field_email_subject',
                'label' => __('Subject', 'acfe'),
                'name' => 'email_subject',
                'type' => 'text',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => '',
                'placeholder' => '',
                'prepend' => '',
                'append' => '',
                'maxlength' => '',
            ),
            
            array(
                'key' => 'field_email_content_group',
                'label' => __('Content', 'acfe'),
                'name' => 'email_content_group',
                'type' => 'group',
                'instructions' => __('Render customized email content.', 'acfe') . '<br /><br />' .
                                  __('Render all fields values:' ,'acfe') . '<br /><code>{fields}</code><br/><br/>' .
                                  __('Render field value:', 'acfe') . '<br /><code>{field:field_abc123}</code><br/><code>{field:my_field}</code>',
                'required' => 0,
                'conditional_logic' => array(),
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'acfe_seamless_style' => true,
                'acfe_group_modal' => 0,
                'sub_fields' => array(
                    array(
                        'key' => 'field_email_content_type',
                        'label' => '',
                        'name' => 'content_type',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            'editor' => __('Content Editor', 'acfe'),
                            'html'   => __('Raw HTML', 'acfe'),
                        ),
                        'default_value' => array('custom'),
                        'allow_null' => 0,
                        'multiple' => 0,
                        'ui' => 0,
                        'return_format' => 'value',
                        'placeholder' => __('Default', 'acfe'),
                        'ajax' => 0,
                        'allow_custom' => 0,
                    ),
                    array(
                        'key' => 'field_email_content_editor',
                        'label' => '',
                        'name' => 'content_editor',
                        'type' => 'wysiwyg',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_email_content_type',
                                    'operator' => '==',
                                    'value' => 'editor',
                                ),
                            ),
                        ),
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'tabs' => 'all',
                        'toolbar' => 'full',
                        'media_upload' => 1,
                        'delay' => 0,
                    ),
                    array(
                        'key' => 'field_email_content_html',
                        'label' => '',
                        'name' => 'content_html',
                        'type' => 'acfe_code_editor',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => array(
                            array(
                                array(
                                    'field' => 'field_email_content_type',
                                    'operator' => '==',
                                    'value' => 'html',
                                ),
                            ),
                        ),
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'default_value' => '',
                        'rows' => 18,
                    ),
                ),
            ),
    
            /**
             * attachments
             */
            array(
                'key' => 'field_tab_attachments',
                'label' => __('Attachments', 'acfe'),
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
                'placement' => 'top',
                'endpoint' => 0,
            ),
            array(
                'key' => 'field_files',
                'label' => __('Dynamic files', 'acfe'),
                'name' => 'files',
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => __('Add file', 'acfe'),
                'sub_fields' => array(
                    array(
                        'key' => 'field_files_file',
                        'label' => __('File', 'acfe'),
                        'name' => 'file',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(),
                        'default_value' => array(),
                        'allow_null' => 0,
                        'multiple' => 0,
                        'ui' => 1,
                        'return_format' => 'value',
                        'ajax' => 1,
                        'placeholder' => '',
                        'search_placeholder' => __('Select a field or enter a custom value/template tag.', 'acfe'),
                        'allow_custom' => 1,
                        'ajax_action' => 'acfe/form/map_field_ajax',
                    ),
                    array(
                        'key' => 'field_files_delete',
                        'label' => __('Delete file', 'acfe'),
                        'name' => 'delete',
                        'type' => 'true_false',
                        'instructions' => '',
                        'required' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'message' => __('Delete file once sent', 'acfe'),
                        'default_value' => 0,
                        'ui' => 1,
                        'ui_on_text' => '',
                        'ui_off_text' => '',
                    ),
                ),
            ),
            array(
                'key' => 'field_files_static',
                'label' => __('Static files', 'acfe'),
                'name' => 'files_static',
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'collapsed' => '',
                'min' => 0,
                'max' => 0,
                'layout' => 'table',
                'button_label' => __('Add file', 'acfe'),
                'sub_fields' => array(
                    array(
                        'key' => 'field_file_static',
                        'label' => __('File', 'acfe'),
                        'name' => 'file',
                        'type' => 'file',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'return_format' => 'id',
                    ),
                ),
            ),

        );
        
    }
    
}

acfe_register_form_action_type('acfe_module_form_action_email');

endif;