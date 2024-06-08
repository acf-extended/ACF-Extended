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
        add_filter('acf/validate_field_group',   array($this, 'validate_field_group'));
    }
    
    
    /**
     * has_enhanced_ui
     *
     * @return bool
     */
    function has_enhanced_ui(){
        return (bool) acfe_get_setting('modules/field_group_ui');
    }
    
    
    /**
     * admin_head
     */
    function admin_head(){
        
        global $field_group;
        
        // submitbox
        add_action('post_submitbox_misc_actions', array($this, 'submitbox'), 11);
        
        // condition vars
        $has_enhanced_ui = $this->has_enhanced_ui();
        $is_sync_available = acfe_is_sync_available($field_group);
        $has_json = acf_get_setting('acfe/json');
        $has_php = acf_get_setting('acfe/php');
        
        // sidebar metabox
        if(!$has_enhanced_ui || $is_sync_available || $has_json || $has_php){
            add_meta_box('acf-field-group-acfe-side', __('Advanced Settings', 'acfe'), array($this, 'render_sidebar_metabox'), 'acf-field-group', 'side');
        }
        
    }
    
    
    /**
     * submitbox
     *
     * @param $post
     */
    function submitbox($post){
        
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
     * render_sidebar_metabox
     */
    function render_sidebar_metabox(){
        
        // global
        global $field_group;
    
        // display title
        if(!$this->has_enhanced_ui()){
            
            acf_render_field_wrap(array(
                'label'         => __('Display title', 'acfe'),
                'instructions'  => __('Render this title on edit post screen', 'acfe'),
                'type'          => 'text',
                'name'          => 'acfe_display_title',
                'prefix'        => 'acf_field_group',
                'value'         => acf_maybe_get($field_group, 'acfe_display_title'),
                'placeholder'   => '',
                'prepend'       => '',
                'append'        => ''
            ), 'div', 'label', true);
            
        }
        
        // autosync available
        if(acfe_is_sync_available($field_group)){
            
            $json_already_active = 0;
            
            if(in_array('json', acf_get_array(acf_maybe_get($field_group, 'acfe_autosync', array())))){
                $json_already_active = 1;
            }
            
            ?>
            <div class="acf-field" data-name="acfe_sync_available">
                <div class="acf-label">
                    <label><?php _e('Sync available', 'acf'); ?></label>
                    <p class="description"><?php _e('Local json file is different from the version in database.', 'acf'); ?></p>
                </div>
                <div class="acf-input">
                    
                    <?php
                    
                    if(acf_version_compare(acf_get_setting('version'),  '<', '5.9')){
                        
                        $url = admin_url('edit.php?post_type=acf-field-group&post_status=sync&acfsync=' . $field_group['key'] . '&_wpnonce=' . wp_create_nonce('bulk-posts'));
                        ?>
                        <a href="<?php echo esc_url($url); ?>" class="button" data-acfe-autosync-json-active="<?php echo $json_already_active; ?>">
                            <?php _e('Synchronize', 'acf'); ?>
                        </a>
                        <?php
                        
                    }else{
                        
                        $url = admin_url('edit.php?post_type=acf-field-group&acfsync=' . $field_group['key'] . '&_wpnonce=' . wp_create_nonce('bulk-posts'));
                        ?>
                        <a href="#" data-event="review-sync" data-id="<?php echo esc_attr($field_group['ID']); ?>" data-href="<?php echo esc_url($url); ?>" class="button" data-acfe-autosync-json-active="<?php echo $json_already_active; ?>">
                            <?php _e('Review changes', 'acf'); ?>
                        </a>
                        <?php
                        
                    }
                    
                    ?>
                </div>
            </div>
            <?php
            
        }
        
        
        $value = $this->get_autosync_value($field_group);
        $choices = $this->get_autosync_choices($field_group);
        
        if(!empty($choices)){
            
            // autosync
            acf_render_field_wrap(array(
                'label'         => __('Auto Sync'),
                'instructions'  => '',
                'type'          => 'checkbox',
                'name'          => 'acfe_autosync',
                'prefix'        => 'acf_field_group',
                'value'         => $value,
                'choices'       => $choices
            ));
            
        }
    
        // permissions
        if(!$this->has_enhanced_ui()){
            
            if(acf_maybe_get($field_group, 'acfe_permissions') || acf_is_filter_enabled('acfe/field_group/advanced')){
    
                acf_render_field_wrap(array(
                    'label'         => __('Permissions', 'acfe'),
                    'name'          => 'acfe_permissions',
                    'prefix'        => 'acf_field_group',
                    'type'          => 'checkbox',
                    'instructions'  => __('Select user roles that are allowed to view and edit this field group in post edition', 'acfe'),
                    'required'      => false,
                    'default_value' => false,
                    'choices'       => acfe_get_roles(),
                    'value'         => acf_maybe_get($field_group, 'acfe_permissions', array()),
                    'layout'        => 'vertical'
                ), 'div', 'label', true);
                
            }
            
        }
        
        ?>
        <script type="text/javascript">
            if(typeof acf !== 'undefined'){
                acf.postbox.render({
                    'id':       'acf-field-group-acfe-side',
                    'label':    'top'
                });
            }

            (function($){

                var $json = $('#acf_field_group-acfe_autosync-json');
                var $php = $('#acf_field_group-acfe_autosync-php');
                var $sync_available = $('[data-name=acfe_sync_available]');

                if($sync_available.length){

                    if($sync_available.find('[data-acfe-autosync-json-active]').attr('data-acfe-autosync-json-active') === '0'){

                        $json.change(function(){

                            if($(this).prop('checked')){

                                if(!confirm('Local json file was found and is different from the version in database.' + "\n" + 'Enabling Json Sync will replace the local file with the current settings' + "\n\n" + 'Do you want to continue?')){
                                    $(this).prop('checked', false);
                                    return false;
                                }

                            }

                        });

                    }else{

                        $('#publish').click(function(e){
                            if(!confirm('Local json file is different from the version in database.' + "\n" + 'Do you want to replace the local file with the current settings?'))
                                e.preventDefault();
                        });

                    }

                }

                // Displays a modal comparing local changes.
                function reviewSync( props ) {

                    var modal = acf.newModal({
                        title: acf.__('Review local JSON changes'),
                        content: '<p class="acf-modal-feedback"><i class="acf-loading"></i> ' + acf.__('Loading diff') + '</p>',
                        toolbar: '<a href="' + props.href + '" class="button button-primary button-sync-changes disabled">' + acf.__('Sync changes') + '</a>',
                    });

                    // Call AJAX.
                    var xhr = $.ajax({
                        url: acf.get('ajaxurl'),
                        method: 'POST',
                        dataType: 'json',
                        data: acf.prepareForAjax({
                            action: 'acf/ajax/local_json_diff',
                            id:     props.id
                        })
                    })
                        .done(function( data, textStatus, jqXHR ) {
                            modal.content( data.html );
                            modal.$('.button-sync-changes').removeClass('disabled');
                        })
                        .fail(function( jqXHR, textStatus, errorThrown ) {
                            if( error = acf.getXhrError(jqXHR) ) {
                                modal.content( '<p class="acf-modal-feedback error">' + error + '</p>' );
                            }
                        });

                }

                // Add event listener.
                $(document).on('click', 'a[data-event="review-sync"]', function(e){
                    e.preventDefault();
                    reviewSync( $(this).data() );
                });

            })(jQuery);
        </script>
        <?php
    }
    
    
    /**
     * get_autosync_value
     *
     * @param $field_group
     *
     * @return array
     */
    function get_autosync_value($field_group){
        
        // autosync: get local
        acf_enable_filter('local');
        
        $json_file = acfe_get_local_json_file($field_group);
        $php_file = acfe_get_local_php_file($field_group);
        
        acf_disable_filter('local');
        
        // autosync: values
        $value = (array) acf_maybe_get($field_group, 'acfe_autosync');
        
        // selected value: json
        if($json_file && !in_array('json', $value)){
            $value[] = 'json';
        }
        
        // selected value: php
        if($php_file && !in_array('php', $value)){
            $value[] = 'php';
        }
        
        return $value;
        
    }
    
    
    /**
     * get_autosync_choices
     *
     * @param $field_group
     *
     * @return array
     */
    function get_autosync_choices($field_group){
        
        // global
        global $pagenow;
        
        // default
        $choices = array();
        
        // check php setting
        if(acf_get_setting('acfe/php')){
            $choices['php'] = 'PHP';
        }
        
        // check json setting
        if(acf_get_setting('acfe/json')){
            $choices['json'] = 'JSON';
        }
        
        foreach(array_keys($choices) as $type){
            
            // $instance->get_json_data() | $instance->get_php_data()
            $method = "get_{$type}_data";
            
            // make sure method exists
            if(!method_exists(acf_get_instance('ACFE_Field_Groups'), $method)){
                continue;
            }
            
            acf_enable_filter('local');
            
            // retrieve data
            $data = acf_get_instance('ACFE_Field_Groups')->$method($field_group);
            
            acf_disable_filter('local');
            
            $wrapper = array(
                'class' => 'acf-js-tooltip',
                'title' => $data['file'],
            );
            
            if($data['class']){
                $wrapper['class'] .= ' ' . $data['class'];
            }
            
            if($data['message']){
                $wrapper['title'] = $data['message'];
            }
            
            $icons = array();
            
            if($data['warning'] && $pagenow !== 'post-new.php'){
                $icons[] = '<span class="dashicons dashicons-warning"></span>';
            }
            
            ob_start();
            ?>
            <span <?php echo acf_esc_atts($wrapper); ?>>
                
                <?php echo $choices[ $type ]; ?>
                
                <?php if(!empty($icons)){ ?>
                    <?php echo implode('', $icons); ?>
                <?php } ?>
                
            </span>
            <?php
            
            $choices[ $type ] = ob_get_clean();
            
        }
        
        return $choices;
        
    }
    
    
    /**
     * validate_field_group
     *
     * @param $field_group
     *
     * @return mixed
     */
    function validate_field_group($field_group){
        
        // validate screen
        if(!acf_is_screen('acf-field-group')){
            return $field_group;
        }
        
        // only new field groups
        // location is empty on new field groups
        if(acf_maybe_get($field_group, 'location')){
            return $field_group;
        }
            
        // default label placement
        $field_group['label_placement'] = 'left';
        
        // autoSync
        $acfe_autosync = array();
        
        if(acf_get_setting('acfe/json_found', false)){
            $acfe_autosync[] = 'json';
        }
        
        if(acf_get_setting('acfe/php_found', false)){
            $acfe_autosync[] = 'php';
        }
        
        if(!empty($acfe_autosync)){
            $field_group['acfe_autosync'] = $acfe_autosync;
        }
        
        // filter
        $field_group = apply_filters('acfe/default_field_group', $field_group);
        
        return $field_group;
        
    }
    
}

acf_new_instance('ACFE_Field_Group');

endif;