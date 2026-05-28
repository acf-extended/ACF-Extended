<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('ACFE_Field_Group_AutoSync')):

class ACFE_Field_Group_AutoSync{
    
    /**
     * construct
     */
    function __construct(){
        add_filter('acfe/default_field_group',                array($this, 'default_field_group'), 9);
        add_action('acfe/field_group/render_sidebar_metabox', array($this, 'render_sidebar_metabox'));
        add_action('acfe/field_group/render_sidebar_metabox', array($this, 'render_sidebar_metabox_after'), 50);
    }


    /**
     * default_field_group
     *
     * Add default value for acfe.autosync setting if local file is found
     *
     * @param $field_group
     *
     * @return mixed
     */
    function default_field_group($field_group){

        // default value
        $value = array();

        if(acf_get_setting('acfe/json_found', false)){
            $value[] = 'json';
        }

        if(acf_get_setting('acfe/php_found', false)){
            $value[] = 'php';
        }

        if(!empty($value)){
            acfe_set($field_group, 'acfe.autosync', $value);
        }

        // return
        return $field_group;

    }
    
    
    /**
     * render_sidebar_metabox
     */
    function render_sidebar_metabox($field_group){
        
        // autosync available
        if(acfe_is_sync_available($field_group)){
            
            $json_already_active = 0;
            $sync = acfe_get($field_group, 'acfe.autosync');

            if(in_array('json', $sync)){
                $json_already_active = 1;
            }
            
            ?>
            <div class="acf-field" data-name="acfe_sync_available">
                <div class="acf-label">
                    <label><?php _e('Sync available', 'acf'); ?></label>
                    <p class="description"><?php _e('Local file is different from the version in database.', 'acfe'); ?></p>
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
        
        // get values and choices
        $value = $this->get_autosync_value($field_group);
        $choices = $this->get_autosync_choices($field_group);
        
        if(!empty($choices)){
            
            // autosync
            acfe_render_group_setting($field_group, array(
                'label'         => __('Auto Sync', 'acfe'),
                'instructions'  => '',
                'type'          => 'checkbox',
                'name'          => 'acfe.autosync',
                'value'         => $value,
                'choices'       => $choices
            ));
            
        }
    }


    /**
     * render_sidebar_metabox_after
     *
     * @param $field_group
     *
     * @return void
     */
    function render_sidebar_metabox_after($field_group){
        ?>
        <script type="text/javascript">
            if(typeof acf !== 'undefined'){
                acf.postbox.render({
                    'id':       'acf-field-group-acfe-side',
                    'label':    'top'
                });
            }

            (function($){

                var $json = $('#acf_field_group-acfe-sync-json');
                var $php = $('#acf_field_group-acfe-sync-php');
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
        $value = acfe_get($field_group, 'acfe.autosync');
        $value = acfe_as_array($value);

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
                'class' => 'acfe-js-tooltip',
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
    
}

acf_new_instance('ACFE_Field_Group_AutoSync');

endif;