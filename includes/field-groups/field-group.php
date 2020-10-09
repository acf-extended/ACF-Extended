<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('ACFE_Field_Group')):

class ACFE_Field_Group{
    
    /*
     * Construct
     */
    function __construct(){
        
        // Actions
        add_action('acf/update_field_group',                        array($this, 'update_field_group'), 0);
        
        add_action('acf/field_group/admin_head',                    array($this, 'render_field_group_settings'));
        add_action('post_submitbox_misc_actions',                   array($this, 'submitbox'), 11);
    
        add_action('acf/render_field_group_settings',               array($this, 'render_field_group_advanced_settings'));
        add_action('acf/render_field/name=acfe_data',               array($this, 'render_field_group_data'));
        
        add_filter('acf/load_field_groups',                         array($this, 'render_field_group_alternative_title'), 999);
        add_filter('acf/load_field_groups',                         array($this, 'render_field_group_permissions'), 999);
        
        add_filter('acf/prepare_field/name=instruction_placement',  array($this, 'render_field_group_instructions_settings'));
        add_filter('acf/prepare_field/name=hide_on_screen',         array($this, 'render_field_group_hide_on_screen_settings'));
        add_filter('acf/validate_field_group',                      array($this, 'render_field_group_default_autosync'));
        
        add_filter('acf/prepare_field_group_for_export',            array($this, 'prepare_field_group_export_categories'));
        add_action('acf/import_field_group',                        array($this, 'prepare_field_group_import_categories'));
        add_action('load-post.php',                                 array($this, 'render_disable_block_editor'));
        add_action('load-post-new.php',	                            array($this, 'render_disable_block_editor'));
    
        add_filter('acf/prepare_field/name=acfe_meta',              array($this, 'prepare_repeater'));
        add_filter('acf/prepare_field/name=acfe_meta_key',          array($this, 'prepare_repeater'));
        add_filter('acf/prepare_field/name=acfe_meta_value',        array($this, 'prepare_repeater'));
        
    }
    
    /*
     * Update Field Group
     */
    function update_field_group($field_group){
        
        // Get Fields
        $fields = acf_get_fields($field_group);
        
        if(empty($fields))
            return;
        
        // Add acfe_form
        if(acf_maybe_get($field_group, 'acfe_form')){
            
            $this->add_field_advanced_settings($fields);
    
        // Remove acfe_form
        }else{
            
            $this->add_field_advanced_settings($fields, false);
            
        }
        
    }
    
    /*
     * Add Field Advanced Settings
     */
    function add_field_advanced_settings($fields, $add = true){
        
        if(empty($fields))
            return;
        
        foreach($fields as $field){
            
            // bypass clone
            if($field['type'] === 'clone')
                continue;
            
            // Group / Clone
            if(isset($field['sub_fields']) && !empty($field['sub_fields'])){
    
                $this->add_field_advanced_settings($field['sub_fields'], $add);
                
            }
            
            // Flexible Content
            elseif(isset($field['layouts']) && !empty($field['layouts'])){
                
                foreach($field['layouts'] as $layout){
                    
                    if(isset($layout['sub_fields']) && !empty($layout['sub_fields'])){
    
                        $this->add_field_advanced_settings($layout['sub_fields'], $add);
                        
                    }
                    
                }
                
            }
            
            // Add
            if($add){
                
                if(acf_maybe_get($field, 'acfe_form'))
                    continue;
                
                $field['acfe_form'] = true;
    
            // Remove
            }else{
                
                if(!acf_maybe_get($field, 'acfe_form'))
                    continue;
                
                acfe_unset($field, 'acfe_form');
                acfe_unset($field, 'acfe_settings');
                acfe_unset($field, 'acfe_validate');
                
            }
            
            acf_update_field($field);
            
        }
        
    }
    
    /*
     * Advanced Settings
     */
    function render_field_group_advanced_settings($field_group){
        
        // Form settings
        acf_render_field_wrap(array(
            'label'         => __('Advanced settings'),
            'name'          => 'acfe_form',
            'prefix'        => 'acf_field_group',
            'type'			=> 'true_false',
            'ui'			=> 1,
            'instructions'	=> __('Enable advanced fields settings & validation'),
            'value'         => (isset($field_group['acfe_form'])) ? $field_group['acfe_form'] : '',
            'required'      => false,
            'wrapper'       => array(
                'data-after' => 'active'
            )
        ));
        
    }
    
    /**
     * Metabox
     */
    function render_field_group_settings(){
        
        add_meta_box('acf-field-group-acfe', __('Field group', 'acf'), array($this, 'render_field_group_setting_metabox'), 'acf-field-group', 'normal');
    
        add_meta_box('acf-field-group-acfe-side', __('Advanced Settings', 'acfe'), array($this, 'render_field_group_setting_sidebar_metabox'), 'acf-field-group', 'side');
        
    }
    
    function render_field_group_setting_metabox(){
        
        global $field_group;
        
        // Meta
        acf_render_field_wrap(array(
            'label'         => __('Custom meta data'),
            'name'          => 'acfe_meta',
            'key'           => 'acfe_meta',
            'instructions'  => __('Add custom meta data to the field group.'),
            'prefix'        => 'acf_field_group',
            'type'          => 'repeater',
            'button_label'  => __('+ Meta'),
            'required'      => false,
            'layout'        => 'table',
            'value'         => (isset($field_group['acfe_meta'])) ? $field_group['acfe_meta'] : array(),
            'sub_fields'    => array(
                array(
                    'ID'            => false,
                    'label'         => __('Key'),
                    'name'          => 'acfe_meta_key',
                    'key'           => 'acfe_meta_key',
                    'prefix'        => '',
                    '_name'         => '',
                    '_prepare'      => '',
                    'type'          => 'text',
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                ),
                array(
                    'ID'            => false,
                    'label'         => __('Value'),
                    'name'          => 'acfe_meta_value',
                    'key'           => 'acfe_meta_value',
                    'prefix'        => '',
                    '_name'         => '',
                    '_prepare'      => '',
                    'type'          => 'text',
                    'instructions'  => false,
                    'required'      => false,
                    'wrapper'       => array(
                        'width' => '',
                        'class' => '',
                        'id'    => '',
                    ),
                ),
            )
        ));
        
        // Data
        acf_render_field_wrap(array(
            'label'         => __('Field group data'),
            'instructions'  => __('View raw field group data, for development use'),
            'type'          => 'acfe_dynamic_message',
            'name'          => 'acfe_data',
            'prefix'        => 'acf_field_group',
            'value'         => $field_group['key'],
        ));
        
        // Note
        acf_render_field_wrap(array(
            'label'         => __('Note'),
            'name'          => 'acfe_note',
            'prefix'        => 'acf_field_group',
            'type'          => 'textarea',
            'instructions'	=> __('Add personal note. Only visible to administrators'),
            'value'         => (isset($field_group['acfe_note'])) ? $field_group['acfe_note'] : '',
            'required'      => false,
        ));
        
        ?>
        <script type="text/javascript">
            if(typeof acf !== 'undefined'){
                acf.postbox.render({
                    'id':       'acf-field-group-acfe',
                    'label':    'left'
                });
            }

            jQuery(document).ready(function($){

                $('#post_name').on('keyup', function(){
                    var val = $(this).val();

                    if(!val.startsWith('group_')){
                        val = 'group_' + val;
                        $(this).val(val);
                    }

                    $('[name="acf_field_group[key]"]').val(val);
                    $('.misc-pub-acfe-field-group-key code').html(val);
                });

            });
        </script>
        <?php
    }
    
    function render_field_group_setting_sidebar_metabox(){
        
        // Global
        global $field_group;
        
        /*
         * Alternative Title
         */
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
        ));
        
        /*
         * Sync available
         */
        if(acfe_is_sync_available($field_group)){
            
            $json_already_active = 0;
            
            if(in_array('json', acf_maybe_get($field_group, 'acfe_autosync', array())))
                $json_already_active = 1;
            
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
        
        /*
         * AutoSync: Get Local
         */
        acf_enable_filter('local');
    
        $json_file = acfe_get_local_json_file($field_group);
        $php_file = acfe_get_local_php_file($field_group);
    
        $data = array(
            'php' => acf_get_instance('ACFE_Field_Groups')->get_php_data($field_group),
            'json' => acf_get_instance('ACFE_Field_Groups')->get_json_data($field_group),
        );
    
        acf_disable_filter('local');
    
        /*
         * AutoSync: Values
         */
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
    
        /*
         * AutoSync: Choices
         */
        $choices = array(
            'php' => 'PHP',
            'json' => 'Json',
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
            <span <?php echo acf_esc_atts($wrapper); ?>>
                
                <?php echo $choices[$type]; ?>

                <?php if(!empty($icons)){ ?>
                    <?php echo implode('', $icons); ?>
                <?php } ?>
                
            </span>
            <?php
            
            $choices[$type] = ob_get_clean();
            
        }
        
        /*
         * AutoSync
         */
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
        
        /*
         * Permissions
         */
        acf_render_field_wrap(array(
            'label'         => __('Permissions'),
            'name'          => 'acfe_permissions',
            'prefix'        => 'acf_field_group',
            'type'          => 'checkbox',
            'instructions'	=> __('Select user roles that are allowed to view and edit this field group in post edition'),
            'required'      => false,
            'default_value' => false,
            'choices'       => acfe_get_roles(),
            'value'         => acf_maybe_get($field_group, 'acfe_permissions', array()),
            'layout'        => 'vertical'
        ));
        
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
                            action:	'acf/ajax/local_json_diff',
                            id: props.id
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
     * Submit Box
     */
    function submitbox($post){
        
        if($post->post_type !== 'acf-field-group')
            return;
        
        global $field_group;
        ?>
        <div class="misc-pub-section misc-pub-acfe-field-group-key" style="padding-top:2px;">
            <span style="font-size:16px;color: #82878c;width: 20px;margin-right: 2px;" class="dashicons dashicons-tag"></span> <code style="font-size: 12px;"><?php echo $field_group['key']; ?></code>
        </div>
        <div class="misc-pub-section misc-pub-acfe-field-group-export" style="padding-top:2px;">
            <span style="font-size:17px;color: #82878c;line-height: 1.3;width: 20px;margin-right: 2px;" class="dashicons dashicons-editor-code"></span> Export: <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=php&keys=' . $field_group['key']); ?>">PHP</a> <a href="<?php echo admin_url('edit.php?post_type=acf-field-group&page=acf-tools&tool=export&action=json&keys=' . $field_group['key']); ?>">Json</a>
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
     * Render: Data button
     */
    function render_field_group_data($field){
        
        $field_group = acf_get_field_group($field['value']);
        $field_group_raw = get_post($field_group['ID']);
        
        if(!$field_group){
            
            echo '<a href="#" class="button disabled" disabled>' . __('Data') . '</a>';
            return;
            
        }
        
        echo '<a href="#" class="acf-button button acfe_modal_open" data-modal-key="' . $field_group['key'] . '">' . __('Data') . '</a>';
        echo '<div class="acfe-modal" data-modal-key="' . $field_group['key'] . '"><div style="padding:15px;"><pre style="margin-bottom:15px;">' . print_r($field_group, true) . '</pre><pre>' . print_r($field_group_raw, true) . '</pre></div></div>';
        
    }
    
    /**
     * Alternative Title
     */
    function render_field_group_alternative_title($field_groups){
        
        if(!is_admin())
            return $field_groups;
        
        if(acfe_is_admin_screen())
            return $field_groups;
        
        foreach($field_groups as &$field_group){
            
            if(!acf_maybe_get($field_group, 'acfe_display_title'))
                continue;
            
            $field_group['title'] = $field_group['acfe_display_title'];
            
        }
        
        return $field_groups;
        
    }
    
    /**
     * Permissions
     */
    function render_field_group_permissions($field_groups){
        
        if(!is_admin())
            return $field_groups;
        
        if(acfe_is_admin_screen())
            return $field_groups;
        
        $current_user_roles = acfe_get_current_user_roles();
        
        foreach($field_groups as $key => $field_group){
            
            if(!acf_maybe_get($field_group, 'acfe_permissions'))
                continue;
            
            $render_field_group = false;
            
            foreach($current_user_roles as $current_user_role){
                
                foreach($field_group['acfe_permissions'] as $field_group_role){
                    
                    if($current_user_role !== $field_group_role)
                        continue;
                    
                    $render_field_group = true;
                    break;
                    
                }
                
                if($render_field_group)
                    break;
                
            }
            
            if(!$render_field_group)
                unset($field_groups[$key]);
            
        }
        
        return $field_groups;
        
    }
    
    /*
     * Instructions
     */
    function render_field_group_instructions_settings($field){
        
        $field['choices'] = array_merge($field['choices'], array('acfe_instructions_tooltip' => 'Tooltip'));
        
        return $field;
        
    }
    
    /**
     * Default AutoSync
     */
    function render_field_group_default_autosync($field_group){
        
        // Only new field groups
        if(!acf_maybe_get($field_group, 'location')){
            
            // Default label placement: Left
            $field_group['label_placement'] = 'left';
            
            // AutoSync
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
            
        }
        
        return $field_group;
        
    }
    
    /**
     * Hide on Screen Settings
     */
    function render_field_group_hide_on_screen_settings($field){
        
        $choices = array();
        
        foreach($field['choices'] as $key => $value){
            
            if($key == 'the_content'){
                
                $choices['block_editor'] = __('Block Editor');
                
            }
            
            
            $choices[$key] = $value;
            
        }
        
        $field['choices'] = $choices;
        
        return $field;
        
    }
    
    /*
     * Prepare Export Categories
     */
    function prepare_field_group_export_categories($field_group){
        
        $_field_group = acf_get_field_group($field_group['key']);
        
        if(empty($_field_group))
            return $field_group;
        
        if(!acf_maybe_get($_field_group, 'ID'))
            return $field_group;
        
        $categories = get_the_terms($_field_group['ID'], 'acf-field-group-category');
        
        if(empty($categories) || is_wp_error($categories))
            return $field_group;
        
        $field_group['acfe_categories'] = array();
        
        foreach($categories as $term){
            
            $field_group['acfe_categories'][$term->slug] = $term->name;
            
        }
        
        return $field_group;
        
    }
    
    
    function prepare_field_group_import_categories($field_group){
        
        if(!$categories = acf_maybe_get($field_group, 'acfe_categories'))
            return;
        
        foreach($categories as $term_slug => $term_name){
            
            $new_term_id = false;
            $get_term = get_term_by('slug', $term_slug, 'acf-field-group-category');
            
            // Term doesn't exists
            if(empty($get_term)){
                
                $new_term = wp_insert_term($term_name, 'acf-field-group-category', array(
                    'slug' => $term_slug
                ));
                
                if(!is_wp_error($new_term)){
                    
                    $new_term_id = $new_term['term_id'];
                    
                }
    
            // Term already exists
            }else{
                
                $new_term_id = $get_term->term_id;
                
            }
            
            if($new_term_id){
                
                wp_set_post_terms($field_group['ID'], array($new_term_id), 'acf-field-group-category', true);
                
            }
            
        }
        
    }
    
    
    function render_disable_block_editor(){
        
        // globals
        global $typenow;
        
        // Restrict
        $restricted = array('acf-field-group', 'attachment');
        
        if(in_array($typenow, $restricted))
            return;
        
        $post_type = $typenow;
        $post_id = 0;
        
        if ( isset( $_GET['post'] ) ) {
            $post_id = (int) $_GET['post'];
        } elseif ( isset( $_POST['post_ID'] ) ) {
            $post_id = (int) $_POST['post_ID'];
        }
        
        $field_groups = acf_get_field_groups(array(
            'post_id'	=> $post_id,
            'post_type'	=> $post_type
        ));
        
        $hide_block_editor = false;
        
        foreach($field_groups as $field_group){
            
            $hide_on_screen = acf_get_array($field_group['hide_on_screen']);
            
            if(!in_array('block_editor', $hide_on_screen))
                continue;
            
            $hide_block_editor = true;
            break;
            
        }
        
        if($hide_block_editor){
            
            add_filter('use_block_editor_for_post_type', '__return_false');
            
        }
        
    }
    
    function prepare_repeater($field){
        
        $field['prefix'] = str_replace('row-', '', $field['prefix']);
        $field['name'] = str_replace('row-', '', $field['name']);
        
        return $field;
        
    }
    
}

acf_new_instance('ACFE_Field_Group');

endif;