<?php

if(!defined('ABSPATH')){
    exit;
}

// check setting
if(!acf_get_setting('acfe/modules/ui')){
    return;
}

// check setting
if(!acf_get_setting('acfe/modules/attachment_ui')){
    return;
}

if(!class_exists('acfe_enhanced_ui_attachment')):
    
class acfe_enhanced_ui_attachment extends acfe_enhanced_ui{
    
    /**
     * initialize
     */
    function initialize(){
        
        // load
        add_action('acfe/load_attachment',                array($this, 'load_attachment'));
    
        // meta boxes
        add_action('acfe/add_attachment_meta_boxes',      array($this, 'add_attachment_meta_boxes'));
        
    }
    
    
    /**
     * load_attachment
     *
     * @param $post_id
     */
    function load_attachment($post_id){
    
        // remove acf edit fields
        // advanced-custom-fields-pro/includes/forms/form-attachment.php
        acfe_remove_action('attachment_fields_to_edit', array('acf_form_attachment', 'edit_attachment'));
    
        // acf form data + acf_after_title metabox
        add_action('edit_form_after_title', array($this, 'edit_form_after_title'));
    
        // enqueue enhanced style & script
        $this->enqueue_scripts();
    
        // footer
        add_action('acf/admin_footer',  array($this, 'admin_footer'));
        
    }
    
    
    /**
     * add_attachment_meta_boxes
     *
     * @param $post
     */
    function add_attachment_meta_boxes($post){
    
        // post id
        $post_id = $post->ID;
    
        // screen
        $screen = get_current_screen();
    
        // field groups
        $field_groups = acf_get_field_groups(array(
            'attachment_id' => $post_id,
            'attachment'    => $post_id, // leave for backwards compatibility
        ));
    
        if($field_groups){
            $this->add_metaboxes($field_groups, $post_id, $screen);
        }
    
    }
    
    
    /**
     * edit_form_after_title
     */
    function edit_form_after_title(){
        
        // globals
        global $post;
        
        // render post data
        acf_form_data(array(
            'screen'  => 'attachment',
            'post_id' => $post->ID,
        ));
        
        // render 'acf_after_title' metaboxes
        do_meta_boxes(get_current_screen(), 'acf_after_title', $post);
        
    }
    
    
    /**
     * admin_footer
     */
    function admin_footer(){
        
        ?>
        <script type="text/javascript">
        (function($){
            acfe.enhancedAttachmentUI({
                title: '<?php echo __( 'Edit Media' ); ?>'
            });
        })(jQuery);
        </script>
        <?php
        
    }

}

new acfe_enhanced_ui_attachment();

endif;