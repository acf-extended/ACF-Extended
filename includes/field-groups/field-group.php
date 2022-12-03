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
        add_filter('acf/validate_field_group',   array($this, 'validate_default_autosync'));
        add_filter('acf/get_field_types',        array($this, 'reorder_field_types'));
        
    }
    
    
    /**
     * reorder_field_types
     *
     * @param $groups
     *
     * @return array|mixed
     */
    function reorder_field_types($groups){
        
        // sort fields
        foreach($groups as $group => &$fields){
            asort($fields);
        }
    
        if(isset($groups['E-Commerce'])){
            $groups = acfe_array_insert_after($groups, 'jQuery', 'E-Commerce', $groups['E-Commerce']);
        }
        
        if(isset($groups['ACF'])){
            $groups = acfe_array_insert_after($groups, 'jQuery', 'ACF', $groups['ACF']);
        }
    
        if(isset($groups['WordPress'])){
            $groups = acfe_array_insert_after($groups, 'jQuery', 'WordPress', $groups['WordPress']);
        }
    
        return $groups;
        
    }
    
    
    /**
     * admin_head
     */
    function admin_head(){
    
        add_action('post_submitbox_misc_actions', array($this, 'submitbox'), 11);
    
        add_meta_box('acf-field-group-acfe-side', __('Advanced Settings', 'acfe'), array($this, 'render_sidebar_metabox'), 'acf-field-group', 'side');
        
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
    
        // setting
        $has_enhanced_ui = acfe_get_setting('modules/field_group_ui') ? true : false;
    
        // display title
        if(!$has_enhanced_ui){
            
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
            
            if(in_array('json', acf_maybe_get($field_group, 'acfe_autosync', array()))){
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
        
        // autosync: get local
        acf_enable_filter('local');
    
        $json_file = acfe_get_local_json_file($field_group);
        $php_file = acfe_get_local_php_file($field_group);
    
        $data = array(
            'php' => acf_get_instance('ACFE_Field_Groups')->get_php_data($field_group),
            'json' => acf_get_instance('ACFE_Field_Groups')->get_json_data($field_group),
        );
    
        acf_disable_filter('local');
    
        // autosync: values
        $acfe_autosync = (array) acf_maybe_get($field_group, 'acfe_autosync');
    
        // Json
        if($json_file){
            if(!in_array('json', $acfe_autosync)){
                $acfe_autosync[] = 'json';
            }
        }
    
        // PHP
        if($php_file){
            if(!in_array('php', $acfe_autosync)){
                $acfe_autosync[] = 'php';
            }
        }
    
        // autosync: choices
        $choices = array(
            'php' => 'PHP',
            'json' => 'JSON',
        );
        
        global $pagenow;
        
        foreach($data as $type => $info){
            
            $wrapper = array(
                'class' => 'acf-js-tooltip',
                'title' => $info['file'],
            );
            
            if($info['class']){
                $wrapper['class'] .= ' ' . $info['class'];
            }
            
            if($info['message']){
                $wrapper['title'] = $info['message'];
            }
            
            $icons = array();
            
            if($info['warning'] && $pagenow !== 'post-new.php')
                $icons[] = '<span class="dashicons dashicons-warning"></span>';
            
            ob_start();
            ?>
            <span <?php echo acf_esc_attrs($wrapper); ?>>
                
                <?php echo $choices[$type]; ?>

                <?php if(!empty($icons)){ ?>
                    <?php echo implode('', $icons); ?>
                <?php } ?>
                
            </span>
            <?php
            
            $choices[$type] = ob_get_clean();
            
        }
        
        // autosync
        acf_render_field_wrap(array(
            'label'         => __('Auto Sync'),
            'instructions'  => '',
            'type'          => 'checkbox',
            'name'          => 'acfe_autosync',
            'prefix'        => 'acf_field_group',
            'value'         => $acfe_autosync,
            'choices'       => array(
                'php'   => $choices['php'],
                'json'  => $choices['json'],
            )
        ));
    
    
        // permissions
        if(!$has_enhanced_ui){
            
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
                
                <?php if($json_file){ ?>

                $json.prop('readonly', true).addClass('disabled').click(function(){
                    return false;
                });

                $json.closest('label').css('color', '#999');
                
                <?php } ?>
                
                <?php if($php_file){ ?>

                $php.prop('readonly', true).addClass('disabled').click(function(){
                    return false;
                });

                $php.closest('label').css('color', '#999');
                
                <?php } ?>

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
     * validate_default_autosync
     *
     * @param $field_group
     *
     * @return mixed
     */
    function validate_default_autosync($field_group){
        
        // validate screen
        if(!acf_is_screen('acf-field-group')){
            return $field_group;
        }
        
        // only new field groups (location is empty on new field groups)
        if(acf_maybe_get($field_group, 'location')){
            return $field_group;
        }
            
        // default label placement: Left
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
        
        return $field_group;
        
    }
    
}

acf_new_instance('ACFE_Field_Group');

endif;