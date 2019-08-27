<?php

if(!defined('ABSPATH'))
    exit;

/**
 * ACFE Location: Post Type Archive Choices
 */
add_filter('acf/location/rule_values/post_type', 'acfe_location_post_type_archive_choices', 999);
function acfe_location_post_type_archive_choices($choices){
    
    $return = array();
    
    foreach($choices as $choice => $choice_label){
        
        $return[$choice] = $choice_label;
        $return[$choice . '_archive'] = $choice_label . ' Archive' . ($choice === 'all' ? 's' : '');
        
    }
    
	$choices = $return;
    
    return $choices;
    
}

/**
 * ACFE Location: Post Type Archive Save
 */
add_action('load-edit.php', 'acfe_location_post_type_archive_save');
function acfe_location_post_type_archive_save(){
    
    // Enqueue ACF JS
    acf_enqueue_scripts();
    
    // Success message
    if(isset($_GET['message']) && $_GET['message'] === 'acfe_post_type_archive')
        acf_add_admin_notice('Options have been saved', 'success');
    
    // Verify Nonce
    if(!acf_verify_nonce('post_type_archive_options'))
        return;
    
    // Get post type
    global $typenow;
    
    // Check post type
    $post_type = $typenow;
    if(empty($post_type))
        return;
    
    // Validate
    if(acf_validate_save_post(true)){
    
        // Autoload
        acf_update_setting('autoload', false);
        
        // Post ID
        $post_id = $post_type . '_options';
        
        // Languages Support
        $dl = acf_get_setting('default_language');
        $cl = acf_get_setting('current_language');

        if($cl && $cl !== $dl)
            $post_id .= '_' . $cl;

        // Save
        acf_save_post($post_id);
        
        // Redirect
        wp_redirect(add_query_arg(array('message' => 'acfe_post_type_archive')));
        exit;
    
    }
    
}

/**
 * ACFE Location: Post Type Archive Footer
 */
add_action('admin_footer', 'acfe_location_post_type_archive_footer');
function acfe_location_post_type_archive_footer(){
    
    // Check current screen
    global $pagenow;
    if($pagenow !== 'edit.php')
        return;
    
    // Get post type
    global $typenow;
    
    // Check post type
    $post_type = $typenow;
    if(empty($post_type) || !in_array($post_type, acf_get_post_types()))
        return;
    
    // Check location = All archives
    $field_groups_all = acf_get_field_groups(array(
        'post_type' => 'all_archive',
    ));
    
    // Check location = Post type archive
    $field_groups_specific = acf_get_field_groups(array(
        'post_type' => $post_type . '_archive'
    ));
    
    $field_groups = array_merge($field_groups_all, $field_groups_specific);
    
    // Check field groups
    if(empty($field_groups))
        return;
    
    // Init field groups by position
    $field_groups_position = array(
        'acf_after_title'   => array(), 
        'normal'            => array(), 
        'side'              => array()
    );
    
    foreach($field_groups as $field_group){
        
        $field_groups_position[$field_group['position']][] = $field_group;
        
    }
    
    // Reset to $field_groups
    $field_groups = $field_groups_position;
    
    // Position: After Title
    if(!empty($field_groups['acf_after_title'])){
        
        $total = count($field_groups['acf_after_title']);
        
        $current = 0; foreach($field_groups['acf_after_title'] as $field_group){ $current++;
                
            add_meta_box(
            
                // ID
                'acf-' . $field_group['ID'], 
                
                // Title
                $field_group['title'], 
                
                // Render
                'acfe_post_type_archive_render_mb', 
                
                // Screen
                'edit', 
                
                // Position
                $field_group['position'], 
                
                // Priority
                'default', 
                
                // Args
                array(
                    'total'         => $total, 
                    'current'       => $current, 
                    'field_group'   => $field_group
                )
                
            );
        
        }
        
        ?>
        <div id="tmpl-acf-after-title" class="acfe-postbox acfe-postbox-no-handle">
            <form class="acf-form" action="" method="post">
            
                <div id="poststuff" style="padding-top:0;">
                
                    <?php do_meta_boxes('edit', 'acf_after_title', array()); ?>
                    
                </div>
                
            </form>
        </div>
        <script type="text/javascript">
        (function($){
            
            // add after title
            $('.subsubsub').before($('#tmpl-acf-after-title'));
            
        })(jQuery);
        </script>
        <?php
        
    }
    
    // Position: Normal
    if(!empty($field_groups['normal'])){
        
        $total = count($field_groups['normal']);
        
        $current = 0; foreach($field_groups['normal'] as $field_group){ $current++;
        
            add_meta_box(
            
                // ID
                'acf-' . $field_group['ID'], 
                
                // Title
                $field_group['title'], 
                
                // Render
                'acfe_post_type_archive_render_mb', 
                
                // Screen
                'edit', 
                
                // Position
                $field_group['position'], 
                
                // Priority
                'default', 
                
                // Args
                array(
                    'total'         => $total, 
                    'current'       => $current, 
                    'field_group'   => $field_group
                )
                
            );
        
        }
        
        ?>
        <div id="tmpl-acf-normal" class="acfe-postbox acfe-postbox-no-handle">
            <form class="acf-form" action="" method="post">
            
                <div id="poststuff">
                
                    <?php do_meta_boxes('edit', 'normal', array()); ?>
                    
                </div>
                
            </form>
        </div>
        <script type="text/javascript">
        (function($){
            
            // add normal
            $('#posts-filter').after($('#tmpl-acf-normal'));
            
        })(jQuery);
        </script>
        <?php
        
    }
    
    // Position: Side
    if(!empty($field_groups['side'])){
        
        $total = count($field_groups['side']);
        
        $current = 0; foreach($field_groups['side'] as $field_group){ $current++;
        
            add_meta_box(
            
                // ID
                'acf-' . $field_group['ID'], 
                
                // Title
                $field_group['title'], 
                
                // Render
                'acfe_post_type_archive_render_mb', 
                
                // Screen
                'edit', 
                
                // Position
                $field_group['position'], 
                
                // Priority
                'default', 
                
                // Args
                array(
                    'total'         => $total, 
                    'current'       => $current, 
                    'field_group'   => $field_group
                )
                
            );
        
        }
        
        ?>
        <div id="tmpl-acf-side" class="acfe-postbox acfe-postbox-no-handle">
            <div class="acf-column-2">
                <form class="acf-form" action="" method="post">
                
                    <div id="poststuff" style="padding-top:0; min-width:auto;">
                    
                        <?php do_meta_boxes('edit', 'side', array()); ?>
                        
                    </div>
                    
                </form>
            </div>
        </div>
        <script type="text/javascript">
        (function($){
            
            // wrap form
            $('#posts-filter').wrap('<div class="acf-columns-2" />');
            
            // Move subsubsub inside column
            $('#posts-filter').prepend($('.subsubsub'));
            
            // Move After title field group
            $('#posts-filter').prepend($('#tmpl-acf-after-title'));
            
            // Move Normal field group
            $('#posts-filter').append($('#tmpl-acf-normal'));
            
            // add column main
            $('#posts-filter').addClass('acf-column-1');
            
            // add column side
            $('#posts-filter').after($('#tmpl-acf-side'));
            
        })(jQuery);
        </script>
        <?php
        
    }
    
}

function acfe_post_type_archive_render_mb($array, $args){
    
    global $typenow;
    
    $total = $args['args']['total'];
    $current = $args['args']['current'];
    $field_group = $args['args']['field_group'];
    
    // Set post_id
    $post_id = $typenow . '_options';
    
    // Languages Support
    $dl = acf_get_setting('default_language');
    $cl = acf_get_setting('current_language');

    if($cl && $cl !== $dl)
        $post_id .= '_' . $cl;
    
    // Set form data
    acf_form_data(array(
        'screen'    => 'post_type_archive_options', 
        'post_id'   => $post_id, 
    ));
    
    // Fix WP media upload conflict with underscore.json_decode
    // Force basic uploader
    acf_update_setting('uploader', 'basic');
    
    // Get fields
    $fields = acf_get_fields($field_group);
    
    // Render fields
    acf_render_fields($fields, $post_id, 'div', $field_group['instruction_placement']);
    
    if($current === $total){ ?>
    
    <?php 
    $id = ($field_group['style'] != 'seamless') ? 'major-publishing-actions' : '';
    $style = ($field_group['style'] === 'seamless') ? 'padding:0 12px;' : '';
    ?>
    
        <div id="<?php echo $id; ?>" style="<?php echo $style; ?>">
        
            <div id="publishing-action">
            
                <div class="acf-form-submit">
                    <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Update', 'acfe'); ?>" />
                    <span class="acf-spinner"></span>
                </div>
                
            </div>
            <div class="clear"></div>
            
        </div>
        
    <?php }
    
    // Create metabox localized data.
    $data = array(
        'id'		=> 'acf-' . $field_group['ID'],
        'key'		=> $field_group['key'],
        'style'		=> $field_group['style'],
        'label'		=> $field_group['label_placement'],
        'edit'		=> acf_get_field_group_edit_link($field_group['ID'])
    );
    
    ?>
    <script type="text/javascript">
    if( typeof acf !== 'undefined' ) {
        acf.newPostbox(<?php echo wp_json_encode($data); ?>);
    }	
    </script>
    
<?php
    
}