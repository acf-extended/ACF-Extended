<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/ui'))
    return;

if(!class_exists('acfe_enhanced_ui_settings')):
    
class acfe_enhanced_ui_settings extends acfe_enhanced_ui{
    
    function initialize(){
        
        // hooks
        add_action('acfe/load_settings',            array($this, 'load_settings'));
        add_action('acfe/add_settings_meta_boxes',  array($this, 'add_settings_meta_boxes'));
        
    }
    
    /*
     * Load
     */
    function load_settings(){
    
        // enqueue
        $this->enqueue_scripts();
    
        // Settings
        add_action('acf/admin_footer',  array($this, 'settings_footer'));
        
    }
    
    /*
     * Add Meta Boxes
     */
    function add_settings_meta_boxes(){
        
        $screen = get_current_screen()->id;
        
        // post id
        $post_id = acf_get_valid_post_id($screen);
        
        // field groups
        $field_groups = acf_get_field_groups(array(
            'wp_settings' => $screen
        ));
        
        if($field_groups){
            
            // form data
            acf_form_data(array(
                'screen'    => 'wp_settings',
                'post_id'   => $post_id,
            ));
            
            $this->add_metaboxes($field_groups, $post_id, $screen);
            
        }
        
        // Sidebar submit
        add_meta_box('submitdiv', __('Edit'), array($this, 'render_metabox_submit'), $screen, 'side', 'high', 'settings');
        
    }
    
    /*
     * Settings: Footer
     */
    function settings_footer(){
        
        global $pagenow;
        
        ?>
        <script type="text/javascript">
        (function($){

            var pageTitle = false;
            
            <?php if(!in_array($pagenow, array('options-permalink.php', 'options-media.php'))){ ?>
            pageTitle = true;
            <?php } ?>

            acfe.enhancedEditUI({
                screen:     'settings',
                pageTitle:  pageTitle
            });

        })(jQuery);
        </script>
        <?php
    }
    

}

new acfe_enhanced_ui_settings();

endif;