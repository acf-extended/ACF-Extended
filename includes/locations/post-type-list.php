<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_location_post_type_list')):

class acfe_location_post_type_list{
    
    public $post_type;
    
    public $field_groups;
    
    function __construct(){
        
        add_action('load-edit.php',                             array($this, 'load'));
        
        add_filter('acf/location/rule_types',                   array($this, 'location_types'));
        add_filter('acf/location/rule_values/post_type_list',   array($this, 'location_values'));
        add_filter('acf/location/rule_match/post_type_list',    array($this, 'location_match'), 10, 3);
        
    }
    
    function load(){
        
        // Get post type
        global $typenow;
        
        $get_post_types = acf_get_post_types(array(
            'show_ui' => 1,
            'exclude' => array('attachment')
        ));
        
        // Check post type
        if(!in_array($typenow, $get_post_types))
            return;
        
        // vars
        $this->post_type = $typenow;
        
        $this->post_id = acf_get_valid_post_id($this->post_type . '_options');
        
        $this->field_groups = acf_get_field_groups(array(
            'post_type_list' => $this->post_type
        ));
        
        if(empty($this->field_groups))
            return;
        
        // Submit
        if(acf_verify_nonce('post_type_list')){
            
            // Validate
            if(acf_validate_save_post(true)){
            
                // Autoload
                acf_update_setting('autoload', false);

                // Save
                acf_save_post($this->post_id);
                
                // Redirect
                wp_redirect(add_query_arg(array('message' => 'acfe_post_type_list')));
                exit;
            
            }
        
        }
        
        // Enqueue ACF JS
        acf_enqueue_scripts();
        
        // Success message
        if(isset($_GET['message']) && $_GET['message'] === 'acfe_post_type_list'){
            
            $object = get_post_type_object($this->post_type);
            
            acf_add_admin_notice($object->label . ' List Saved.', 'success');
            
        }
        
        add_action('in_admin_header', array($this, 'in_admin_header'));
        
        add_action('admin_footer', array($this, 'admin_footer'));
        
    }
    
    function admin_footer(){
        
        // Init field groups by position
        $field_groups = array();
        
        foreach($this->field_groups as $field_group){
            
            $field_groups[$field_group['position']][] = $field_group;
            
        }
        
        // Position: After Title
        if(acf_maybe_get($field_groups, 'acf_after_title')){
            
            $total = count($field_groups['acf_after_title']);
            
            $current = 0; foreach($field_groups['acf_after_title'] as $field_group){ $current++;
                    
                add_meta_box(
                
                    // ID
                    'acf-' . $field_group['ID'], 
                    
                    // Title
                    $field_group['title'], 
                    
                    // Render
                    array($this, 'metabox_render'), 
                    
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
        if(acf_maybe_get($field_groups, 'normal')){
            
            $total = count($field_groups['normal']);
            
            $current = 0; foreach($field_groups['normal'] as $field_group){ $current++;
            
                add_meta_box(
                
                    // ID
                    'acf-' . $field_group['ID'], 
                    
                    // Title
                    $field_group['title'], 
                    
                    // Render
                    array($this, 'metabox_render'), 
                    
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
        if(acf_maybe_get($field_groups, 'side')){
            
            $total = count($field_groups['side']);
            
            $current = 0; foreach($field_groups['side'] as $field_group){ $current++;
            
                add_meta_box(
                
                    // ID
                    'acf-' . $field_group['ID'], 
                    
                    // Title
                    $field_group['title'], 
                    
                    // Render
                    array($this, 'metabox_render'), 
                    
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
    
    function metabox_render($array, $args){
        
        $total = $args['args']['total'];
        $current = $args['args']['current'];
        $field_group = $args['args']['field_group'];
        
        // Set post_id
        $post_id = $this->post_id;
        
        // Set form data
        acf_form_data(array(
            'screen'    => 'post_type_list', 
            'post_id'   => $post_id, 
        ));
        
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
            'id'    => 'acf-' . $field_group['ID'],
            'key'   => $field_group['key'],
            'style' => $field_group['style'],
            'label' => $field_group['label_placement'],
            'edit'  => acf_get_field_group_edit_link($field_group['ID'])
        );
        
        ?>
        <script type="text/javascript">
        if( typeof acf !== 'undefined' ) {
            acf.newPostbox(<?php echo wp_json_encode($data); ?>);
        }
        </script>
        
    <?php
        
    }
    
    function in_admin_header(){
        
        acf_enqueue_uploader();
        
    }
    
    function location_types($choices){
        
        $name = __('Post', 'acf');
        
        $choices[$name] = acfe_array_insert_after('post_type', $choices[$name], 'post_type_list', __('Post Type List'));

        return $choices;
        
    }

    
    function location_values($choices){
        
        $post_types = acf_get_post_types(array(
            'show_ui'    => 1,
            'exclude'    => array('attachment')
        ));
        
        $pretty_post_types = array();
        
        if(!empty($post_types)){
            
            $pretty_post_types = acf_get_pretty_post_types($post_types);
            
        }
        
        $choices = array('all' => __('All', 'acf'));
        $choices = array_merge($choices, $pretty_post_types);
        
        return $choices;
        
    }
    
    function location_match($match, $rule, $screen){
        
        if(!acf_maybe_get($screen, 'post_type_list') || !acf_maybe_get($rule, 'value'))
            return $match;
        
        $match = ($screen['post_type_list'] === $rule['value']);
        
        if($rule['value'] === 'all')
            $match = true;
        
        if($rule['operator'] === '!=')
            $match = !$match;
        
        return $match;

    }
    
}

new acfe_location_post_type_list();

endif;