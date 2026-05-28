<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_Field_Group')):

class ACFE_Field_Group{
    
    /**
     * construct
     */
    function __construct(){

        add_action('acf/field_group/admin_head', array($this, 'admin_head'));
        add_filter('acf/validate_field_group',   array($this, 'validate_field_group'), 50);
        add_filter('acf/validate_field_group',   array($this, 'validate_default_field_group'));

    }


    /**
     * should_render_sidebar_metabox
     *
     * @param $field_group
     *
     * @return bool
     */
    function should_render_sidebar_metabox($field_group){

        return acfe_is_sync_available($field_group) || acf_get_setting('acfe/json') || acf_get_setting('acfe/php');

    }
    
    
    /**
     * admin_head
     */
    function admin_head(){
        
        global $field_group;
        
        // submit mextabox
        add_action('post_submitbox_misc_actions', array($this, 'render_submit_metabox'), 11);

        // extra metabox
        if(!acfe_get_setting('modules/field_group_ui')){
            add_meta_box('acf-field-group-acfe-extra', __('Field group', 'acf'), array($this, 'render_extra_metabox'), 'acf-field-group', 'normal');
        }
        
        // sidebar metabox
        if(!acfe_get_setting('modules/field_group_ui') || $this->should_render_sidebar_metabox($field_group)){
            add_meta_box('acf-field-group-acfe-side', __('Advanced Settings', 'acfe'), array($this, 'render_sidebar_metabox'), 'acf-field-group', 'side');
        }
        
    }
    
    
    /**
     * render_submit_metabox
     *
     * @param $post
     */
    function render_submit_metabox($post){
        
        global $field_group;
        
        $export_php = admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=php&keys=' . $field_group['key']);
        $export_json = admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=json&keys=' . $field_group['key']);
        
        ?>
        <div class="misc-pub-section misc-pub-acfe-field-group-key">
            <span class="dashicons dashicons-tag"></span> <code><?php echo $field_group['key']; ?></code>
        </div>
        <div class="misc-pub-section misc-pub-acfe-field-group-export">
            <span class="dashicons dashicons-editor-code"></span> Export: <a href="<?php echo $export_php; ?>">PHP</a> <a href="<?php echo $export_json; ?>">Json</a>
        </div>
        <script type="text/javascript">
            (function($) {
                $('.misc-pub-acfe-field-group-key').insertAfter('.misc-pub-post-status');
                $('.misc-pub-acfe-field-group-export').insertAfter('.misc-pub-post-status');
            })(jQuery);
        </script>
        <?php
        
    }


    /**
     * render_extra_metabox
     */
    function render_extra_metabox(){

        // global
        global $field_group;

        // action
        do_action('acfe/field_group/render_extra_metabox', $field_group);

    }


    /**
     * render_sidebar_metabox
     */
    function render_sidebar_metabox(){

        // global
        global $field_group;

        // action
        do_action('acfe/field_group/render_sidebar_metabox', $field_group);

    }


    /**
     * validate_field_group
     *
     * Cleanup empty settings
     *
     * @param $field_group
     *
     * @return mixed
     */
    function validate_field_group($field_group){

        // sanitize acfe.meta repeater
        $meta = acfe_get($field_group, 'acfe.meta');
        $meta = acfe_as_array($meta);
        $meta = array_values($meta);

        acfe_set($field_group, 'acfe.meta', $meta);

        // get acfe
        $acfe = acfe_get($field_group, 'acfe');
        $acfe = acfe_as_array($acfe);

        // loop through settings and unset empty ones
        foreach($acfe as $setting => $value){
            if(empty($value)){
                acfe_unset($field_group, "acfe.{$setting}");
            }
        }

        // unset acfe if empty
        if(empty(acfe_get($field_group, 'acfe'))){
            acfe_unset($field_group, 'acfe');
        }

        // return
        return $field_group;

    }
    
    
    /**
     * validate_default_field_group
     *
     * @param $field_group
     *
     * @return mixed
     */
    function validate_default_field_group($field_group){
        
        // validate screen
        if(!acf_is_screen('acf-field-group')){
            return $field_group;
        }

        // bail early on existing field group
        // (note: location is empty on "add new field group" screen)
        if(!empty(acfe_get($field_group, 'location'))){
            return $field_group;
        }

        // default label placement
        $field_group['label_placement'] = 'left';

        // filter
        $field_group = apply_filters('acfe/default_field_group', $field_group);

        // return
        return $field_group;
        
    }
    
}

acf_new_instance('ACFE_Field_Group');

endif;