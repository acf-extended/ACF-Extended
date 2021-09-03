<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_dynamic_forms')):

class acfe_dynamic_forms extends acfe_dynamic_module{
    
    // vars
    public $field_groups = array();
    
    /*
     * Initialize
     */
    function initialize(){
        
        $this->active = acf_get_setting('acfe/modules/forms');
        $this->post_type = 'acfe-form';
        $this->label = 'Form Title';
        
        $this->tool = 'acfe_dynamic_forms_export';
        $this->tools = array('json');
        $this->columns = array(
            'name'          => __('Name', 'acf'),
            'field_groups'  => __('Field groups', 'acf'),
            'actions'       => __('Actions', 'acf'),
            'shortcode'     => __('Shortcode', 'acf'),
        );
        
    }
    
    /*
     * Actions
     */
    function actions(){
        
        // TinyMCE
        add_filter('mce_external_plugins',                      array($this, 'mce_plugins'));
        
        // Validate
        add_filter('acf/validate_value/name=acfe_form_name',    array($this, 'validate_name'), 10, 4);
        
        // Save
        add_action('acfe/form/save',                            array($this, 'save'), 10, 2);
        
        // Import
        add_action('acfe/form/import_fields',                   array($this, 'import_fields'), 10, 3);
        
        // Includes
        acfe_include('includes/modules/forms-cheatsheet.php');
        acfe_include('includes/modules/forms-front.php');
        acfe_include('includes/modules/forms-helpers.php');
        acfe_include('includes/modules/forms-hooks.php');
        
        acfe_include('includes/modules/forms-action-custom.php');
        acfe_include('includes/modules/forms-action-email.php');
        acfe_include('includes/modules/forms-action-post.php');
        acfe_include('includes/modules/forms-action-redirect.php');
        acfe_include('includes/modules/forms-action-term.php');
        acfe_include('includes/modules/forms-action-user.php');
    
        do_action('acfe/include_form_actions');
        
    }
    
    /*
     * TinyMCE Plugin JS
     */
    function mce_plugins($plugins){
        
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        $plugins['acfe_form'] = acfe_get_url('assets/inc/tinymce/acfe-form' . $suffix . '.js');
        
        return $plugins;
        
    }
    
    /*
     * Get Name
     */
    function get_name($post_id){
        
        return get_field('acfe_form_name', $post_id);
        
    }
    
    /*
     * Init
     */
    function init(){
    
        $capability = acf_get_setting('capability');
        
        if(!acf_get_setting('show_admin'))
            $capability = false;
        
        register_post_type($this->post_type, array(
            'label'                 => __('Forms', 'acf'),
            'description'           => __('Forms', 'acf'),
            'labels'                => array(
                'name'          => __('Forms', 'acf'),
                'singular_name' => __('Form', 'acf'),
                'menu_name'     => __('Forms', 'acf'),
                'edit_item'     => 'Edit Form',
                'add_new_item'  => 'New Form',
            ),
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => 'edit.php?post_type=acf-field-group',
            'menu_icon'             => 'dashicons-feedback',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => false,
            'has_archive'           => false,
            'rewrite'               => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capabilities'          => array(
                'publish_posts'         => $capability,
                'edit_posts'            => $capability,
                'edit_others_posts'     => $capability,
                'delete_posts'          => $capability,
                'delete_others_posts'   => $capability,
                'read_private_posts'    => $capability,
                'edit_post'             => $capability,
                'delete_post'           => $capability,
                'read_post'             => $capability,
            ),
            'acfe_admin_ppp'        => 999,
            'acfe_admin_orderby'    => 'title',
            'acfe_admin_order'      => 'ASC',
        ));
        
    }
    
    /*
     * Post Head
     */
    function post_head(){
        
        global $pagenow;
        
        if($pagenow === 'post-new.php')
            return;
        
        $this->field_groups = acf_get_instance('acfe_dynamic_forms_helpers')->get_field_groups();
    
        // Add Instructions
        add_meta_box('acfe-form-integration', 'Integration', array($this, 'meta_box_side'), $this->post_type,'side', 'core');
    
        if($this->field_groups){
            add_meta_box('acfe-form-details', __('Fields', 'acf'), array($this, 'meta_box_field_groups'), $this->post_type, 'normal');
        }
        
    }
    
    /*
     * Metabox: Sidebar
     */
    function meta_box_side($post){
        
        $form_id = $post->ID;
        $form_name = $this->get_name($form_id);
        
        ?>

        <div class="acf-field">

            <div class="acf-label">
                <label><?php _e('Documentation', 'acfe'); ?>:</label>
            </div>

            <div class="acf-input">

                <ul style="list-style:inside;">
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms" target="_blank"><?php _e('Forms', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms/form-cheatsheet" target="_blank"><?php _e('Cheatsheet', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms/form-hooks" target="_blank"><?php _e('Hooks', 'acfe'); ?></a></li>
                    <li><a href="https://www.acf-extended.com/features/modules/dynamic-forms/form-helpers" target="_blank"><?php _e('Helpers', 'acfe'); ?></a></li>
                </ul>

            </div>

        </div>
        
        <div class="acf-field">

            <div class="acf-label">
                <label><?php _e('Shortcodes', 'acfe'); ?>:</label>
            </div>

            <div class="acf-input">

                <code>[acfe_form ID="<?php echo $form_id; ?>"]</code><br /><br />
                <code>[acfe_form name="<?php echo $form_name; ?>"]</code>

            </div>

        </div>

        <div class="acf-field">

            <div class="acf-label">
                <label><?php _e('PHP code', 'acfe'); ?>:</label>
            </div>

            <div class="acf-input">
                
                <pre>&lt;?php get_header(); ?&gt;

&lt;!-- <?php echo get_the_title($form_id); ?> --&gt;
&lt;?php acfe_form(&apos;<?php echo $form_name; ?>&apos;); ?&gt;

&lt;?php get_footer(); ?&gt;</pre>

            </div>

        </div>

        <script type="text/javascript">
            if(typeof acf !== 'undefined'){

                acf.newPostbox(<?php echo wp_json_encode(array(
                    'id'    => 'acfe-form-integration',
                    'key'   => '',
                    'style' => 'default',
                    'label' => 'top',
                    'edit'  => false
                )); ?>);

            }
        </script>
        <?php
    }
    
    /*
     * Metabox: Field Groups
     */
    function meta_box_field_groups(){
        
        foreach($this->field_groups as $field_group){ ?>

            <div class="acf-field">

                <div class="acf-label">
                    <label><a href="<?php echo admin_url("post.php?post={$field_group['ID']}&action=edit"); ?>"><?php echo $field_group['title']; ?></a></label>
                    <p class="description"><?php echo $field_group['key']; ?></p>
                </div>

                <div class="acf-input">
                    
                    <?php if(acf_maybe_get($field_group, 'fields')){ ?>

                        <table class="acf-table">
                            <thead>
                            <th class="acf-th" width="25%"><strong>Label</strong></th>
                            <th class="acf-th" width="25%"><strong>Name</strong></th>
                            <th class="acf-th" width="25%"><strong>Key</strong></th>
                            <th class="acf-th" width="25%"><strong>Type</strong></th>
                            </thead>

                            <tbody>
                            <?php
                            
                            $array = array();
                            foreach($field_group['fields'] as $field){
    
                                $this->get_fields_labels_recursive($array, $field);
                                
                            }
                            
                            foreach($array as $field_key => $field_label){
                                
                                $field = acf_get_field($field_key);
                                $type = acf_get_field_type($field['type']);
                                $type_label = '-';
                                if(isset($type->label))
                                    $type_label = $type->label;
                                ?>

                                <tr class="acf-row">
                                    <td width="25%"><?php echo $field_label; ?></td>
                                    <td width="25%"><code style="font-size:12px;"><?php echo $field['name']; ?></code></td>
                                    <td width="25%"><code style="font-size:12px;"><?php echo $field_key; ?></code></td>
                                    <td width="25%"><?php echo $type_label; ?></td>
                                </tr>
                            
                            <?php } ?>
                            </tbody>
                        </table>
                    
                    <?php } ?>
                </div>

            </div>
        
        <?php } ?>

        <script type="text/javascript">
            if(typeof acf !== 'undefined'){

                acf.newPostbox(<?php echo wp_json_encode(array(
                    'id'    => 'acfe-form-details',
                    'key'   => '',
                    'style' => 'default',
                    'label' => 'left',
                    'edit'  => false
                )); ?>);

            }
        </script>
        <?php
        
    }
    
    /*
     * Edit Columns HTML
     */
    function edit_columns_html($column, $post_id){
    
        switch($column){
            
            // Name
            case 'name':
    
                echo '<code style="font-size: 12px;">' . $this->get_name($post_id) . '</code>';
                break;
                
            // Field Groups
            case 'field_groups':
                
                $return = '—';
                
                $field_groups = acf_get_array(get_field('acfe_form_field_groups', $post_id));
    
                if(!empty($field_groups)){
        
                    $links = array();
        
                    foreach($field_groups as $key){
            
                        $field_group = acf_get_field_group($key);
            
                        if(!$field_group)
                            continue;
            
                        if(acf_maybe_get($field_group, 'ID')){
    
                            $links[] = '<a href="' . admin_url("post.php?post={$field_group['ID']}&action=edit") . '">' . $field_group['title'] . '</a>';
                
                        }else{
    
                            $links[] = $field_group['title'];
                
                        }
            
                    }
        
                    $return = implode(', ', $links);
                
                }
                
                echo $return;
                break;
                
            // Actions
            case 'actions':
                
                $return = '—';
    
                $icons = array();
    
                if(have_rows('acfe_form_actions', $post_id)):
                    while(have_rows('acfe_form_actions', $post_id)): the_row();
            
                        // Custom
                        if(get_row_layout() === 'custom'){
                
                            $action_name = get_sub_field('acfe_form_custom_action');
    
                            $icons[] = '<span class="acf-js-tooltip dashicons dashicons-editor-code" title="Custom action: ' . $action_name . '"></span>';
                
                        }
            
                        // E-mail
                        elseif(get_row_layout() === 'email'){
                            $icons[] = '<span class="acf-js-tooltip dashicons dashicons-email" title="E-mail"></span>';
                        }
            
                        // Post
                        elseif(get_row_layout() === 'post'){
                
                            $action = get_sub_field('acfe_form_post_action');
                
                            // Insert
                            if($action === 'insert_post'){
                                $icons[] = '<span class="acf-js-tooltip dashicons dashicons-edit" title="Create post"></span>';
                            }
                
                            // Update
                            elseif($action === 'update_post'){
                                $icons[] = '<span class="acf-js-tooltip dashicons dashicons-update" title="Update post"></span>';
                            }
                
                        }
            
                        // Term
                        elseif(get_row_layout() === 'term'){
                
                            $action = get_sub_field('acfe_form_term_action');
                
                            // Insert
                            if($action === 'insert_term'){
                                $icons[] = '<span class="acf-js-tooltip dashicons dashicons-category" title="Create term"></span>';
                            }
                
                            // Update
                            elseif($action === 'update_term'){
                                $icons[] = '<span class="acf-js-tooltip dashicons dashicons-category" title="Update term"></span>';
                            }
                
                        }
            
                        // User
                        elseif(get_row_layout() === 'user'){
                
                            $action = get_sub_field('acfe_form_user_action');
                
                            // Insert
                            if($action === 'insert_user'){
                                $icons[] = '<span class="acf-js-tooltip dashicons dashicons-admin-users" title="Create user"></span>';
                            }
                
                            // Update
                            elseif($action === 'update_user'){
                                $icons[] = '<span class="acf-js-tooltip dashicons dashicons-admin-users" title="Update user"></span>';
                            }
                
                            // Update
                            elseif($action === 'log_user'){
                                $icons[] = '<span class="acf-js-tooltip dashicons dashicons-migrate" title="Log user"></span>';
                            }
                
                        }
        
                    endwhile;
                endif;
    
                if(!empty($icons)){
                    $return = implode('', $icons);
                }
                
                echo $return;
                break;
    
            // Shortcode
            case 'shortcode':
    
                echo '<code style="font-size: 12px;">[acfe_form name="' . $this->get_name($post_id) . '"]</code>';
                break;
                
        }
        
    }
    
    /*
     * ACF Save post
     */
    function save_post($post_id){
    
        // Get Post
        $name = $this->get_name($post_id);
        
        // Actions
        do_action("acfe/form/save",                 $name, $post_id);
        do_action("acfe/form/save/name={$name}",    $name, $post_id);
        do_action("acfe/form/save/id={$post_id}",   $name, $post_id);
        
    }
    
    /*
     * Save
     */
    function save($name, $post_id){
        
        // Update post
        wp_update_post(array(
            'ID'            => $post_id,
            'post_name'     => $name,
            'post_status'   => 'publish',
        ));
        
        // Get generated post name (possible name-2)
        $_name = get_post_field('post_name', $post_id);
        
        // Update the meta if different
        if($_name !== $name)
            update_field('acfe_form_name', $_name, $post_id);
        
    }
    
    /*
     * Validate Name
     */
    function validate_name($valid, $value, $field, $input){
    
        if($valid !== true)
            return $valid;
    
        // Check current name
        $post_id = acfe_get_post_id();
    
        if(empty($post_id))
            return $valid;
    
        $name = get_field($field['name'], $post_id);
    
        if($value === $name)
            return $valid;
        
        $get_posts = get_posts(array(
            'post_type'         => $this->post_type,
            'name'              => $value,
            'post__not_in'      => array($post_id),
            'fields'            => 'ids',
            'post_status'       => array('publish', 'acf-disabled'),
            'posts_per_page'    => 1
        ));
        
        if(!empty($get_posts))
            $valid = 'This form name already exists';
        
        return $valid;
        
    }
    
    /*
     * Import
     */
    function import($name, $args){
        
        // Vars
        $title = acf_extract_var($args, 'title');
        $name = $args['acfe_form_name'];
    
        // Already exists
        if(get_page_by_path($name, OBJECT, $this->post_type)){
            return new WP_Error('acfe_form_import_already_exists', __("Form \"{$title}\" already exists. Import aborted."));
        }
    
        // Import Post
        $post_id = false;
    
        $post = array(
            'post_title'    => $title,
            'post_name'     => $name,
            'post_type'     => $this->post_type,
            'post_status'   => 'publish'
        );
    
        $post = apply_filters("acfe/form/import_post",                 $post, $name);
        $post = apply_filters("acfe/form/import_post/name={$name}",    $post, $name);
    
        if($post !== false){
            $post_id = wp_insert_post($post);
        }
    
        if(!$post_id || is_wp_error($post_id)){
            return new WP_Error('acfe_form_import_error', __("Something went wrong with the form \"{$title}\". Import aborted."));
        }
    
        // Import Args
        $args = apply_filters("acfe/form/import_args",                  $args, $name, $post_id);
        $args = apply_filters("acfe/form/import_args/name={$name}",     $args, $name, $post_id);
        $args = apply_filters("acfe/form/import_args/id={$post_id}",    $args, $name, $post_id);
    
        if($args === false)
            return $post_id;
        
        // Import Fields
        acf_enable_filter('local');
        
        do_action("acfe/form/import_fields",               $name, $args, $post_id);
        do_action("acfe/form/import_fields/name={$name}",  $name, $args, $post_id);
        do_action("acfe/form/import_fields/id={$post_id}", $name, $args, $post_id);
        
        acf_disable_filter('local');
    
        // Save
        $this->save_post($post_id);
        
        return $post_id;
        
    }
    
    /*
     * Import Fields
     */
    function import_fields($name, $args, $post_id){
    
        // Update
        acf_update_values($args, $post_id);
        
    }
    
    /*
     * Export: Choices
     */
    function export_choices(){
        
        $choices = array();
        
        $get_posts = get_posts(array(
            'post_type'         => 'acfe-form',
            'posts_per_page'    => -1,
            'fields'            => 'ids'
        ));
        
        if(!$get_posts)
            return $choices;
        
        foreach($get_posts as $post_id){
            
            $name = $this->get_name($post_id);
            $choices[$name] = esc_html(get_the_title($post_id));
            
        }
        
        return $choices;
        
    }
    
    /*
     * Export: Data
     */
    function export_data($name){
    
        if(!$form = get_page_by_path($name, OBJECT, $this->post_type))
            return false;
    
        acf_enable_filter('local');
        
        $args = array_merge(array('title' => get_the_title($form->ID)), get_fields($form->ID, false));
    
        // Filters
        $args = apply_filters("acfe/form/export_args",                 $args, $name);
        $args = apply_filters("acfe/form/export_args/name={$name}",    $args, $name);
    
        acf_disable_filter('local');
        
        return $args;
        
    }
    
    /*
     * Add Local Field Group
     */
    function add_local_field_group(){
    
        $actions_layouts = apply_filters('acfe/form/actions', array());
        ksort($actions_layouts);
    
        acf_add_local_field_group(array(
            'key' => 'group_acfe_dynamic_form',
            'title' => 'Dynamic Form',
            'acfe_display_title' => '',
            'fields' => array(
            
                /*
                 * Actions
                 */
                array(
                    'key' => 'field_acfe_form_tab_general',
                    'label' => 'General',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_name',
                    'label' => 'Form name',
                    'name' => 'acfe_form_name',
                    'type' => 'acfe_slug',
                    'instructions' => 'The unique form slug',
                    'required' => 1,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_field_groups',
                    'label' => 'Field groups',
                    'name' => 'acfe_form_field_groups',
                    'type' => 'select',
                    'instructions' => 'Render & map fields of the following field groups',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                    ),
                    'default_value' => array(
                    ),
                    'allow_null' => 0,
                    'multiple' => 1,
                    'ui' => 1,
                    'ajax' => 0,
                    'return_format' => 'value',
                    'placeholder' => '',
                ),
                array(
                    'key' => 'field_acfe_form_actions',
                    'label' => 'Actions',
                    'name' => 'acfe_form_actions',
                    'type' => 'flexible_content',
                    'instructions' => 'Add actions on form submission',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_flexible_stylised_button' => 1,
                    'layouts' => $actions_layouts,
                    'button_label' => 'Add action',
                    'min' => '',
                    'max' => '',
                ),
            
                /*
                 * Settings
                 */
                array(
                    'key' => 'field_acfe_form_tab_settings',
                    'label' => 'Settings',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_field_groups_rules',
                    'label' => 'Field groups locations rules',
                    'name' => 'acfe_form_field_groups_rules',
                    'type' => 'true_false',
                    'instructions' => 'Apply field groups locations rules for front-end display',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_form_element',
                    'label' => 'Form element',
                    'name' => 'acfe_form_form_element',
                    'type' => 'true_false',
                    'instructions' => 'Whether or not to create a <code>&lt;form&gt;</code> element',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_attributes',
                    'label' => 'Form attributes',
                    'name' => 'acfe_form_attributes',
                    'type' => 'group',
                    'instructions' => 'Form class and id',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_element',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_attributes_class',
                            'label' => '',
                            'name' => 'acfe_form_attributes_class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => 'acf-form',
                            'placeholder' => '',
                            'prepend' => 'class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_form_attributes_id',
                            'label' => '',
                            'name' => 'acfe_form_attributes_id',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'id',
                            'append' => '',
                            'maxlength' => '',
                        ),
                
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_fields_attributes',
                    'label' => 'Fields class',
                    'name' => 'acfe_form_fields_attributes',
                    'type' => 'group',
                    'instructions' => 'Add class to all fields',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'layout' => 'block',
                    'acfe_seamless_style' => true,
                    'acfe_group_modal' => 0,
                    'conditional_logic' => array(),
                    'sub_fields' => array(
                        array(
                            'key' => 'field_acfe_form_fields_wrapper_class',
                            'label' => '',
                            'name' => 'acfe_form_fields_wrapper_class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'wrapper class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                        array(
                            'key' => 'field_acfe_form_fields_class',
                            'label' => '',
                            'name' => 'acfe_form_fields_class',
                            'type' => 'text',
                            'instructions' => '',
                            'required' => 0,
                            'conditional_logic' => array(),
                            'wrapper' => array(
                                'width' => '33',
                                'class' => '',
                                'id' => '',
                            ),
                            'acfe_permissions' => '',
                            'default_value' => '',
                            'placeholder' => '',
                            'prepend' => 'input class',
                            'append' => '',
                            'maxlength' => '',
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_form_submit',
                    'label' => 'Submit button',
                    'name' => 'acfe_form_form_submit',
                    'type' => 'true_false',
                    'instructions' => 'Whether or not to create a form submit button. Defaults to true',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_submit_value',
                    'label' => 'Submit value',
                    'name' => 'acfe_form_submit_value',
                    'type' => 'text',
                    'instructions' => 'The text displayed on the submit button',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_submit',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => 'Submit',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_html_submit_button',
                    'label' => 'Submit button',
                    'name' => 'acfe_form_html_submit_button',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'HTML used to render the submit button.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_submit',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '<input type="submit" class="acf-button button button-primary button-large" value="%s" />',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                array(
                    'key' => 'field_acfe_form_html_submit_spinner',
                    'label' => 'Submit spinner',
                    'name' => 'acfe_form_html_submit_spinner',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'HTML used to render the submit button loading spinner.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_form_submit',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '<span class="acf-spinner"></span>',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                array(
                    'key' => 'field_acfe_form_honeypot',
                    'label' => 'Honeypot',
                    'name' => 'acfe_form_honeypot',
                    'type' => 'true_false',
                    'instructions' => 'Whether to include a hidden input field to capture non human form submission. Defaults to true.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_kses',
                    'label' => 'Kses',
                    'name' => 'acfe_form_kses',
                    'type' => 'true_false',
                    'instructions' => 'Whether or not to sanitize all $_POST data with the wp_kses_post() function. Defaults to true.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_uploader',
                    'label' => 'Uploader',
                    'name' => 'acfe_form_uploader',
                    'type' => 'radio',
                    'instructions' => 'Whether to use the WP uploader or a basic input for image and file fields. Defaults to \'wp\'
    Choices of \'wp\' or \'basic\'.',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'default' => 'Default',
                        'wp' => 'WordPress',
                        'basic' => 'Browser',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'default',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_form_field_el',
                    'label' => 'Field element',
                    'name' => 'acfe_form_form_field_el',
                    'type' => 'radio',
                    'instructions' => 'Determines element used to wrap a field. Defaults to \'div\'',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'div' => '&lt;div&gt;',
                        'tr' => '&lt;tr&gt;',
                        'td' => '&lt;td&gt;',
                        'ul' => '&lt;ul&gt;',
                        'ol' => '&lt;ol&gt;',
                        'dl' => '&lt;dl&gt;',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'div',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_label_placement',
                    'label' => 'Label placement',
                    'name' => 'acfe_form_label_placement',
                    'type' => 'radio',
                    'instructions' => 'Determines where field labels are places in relation to fields. Defaults to \'top\'. <br />
    Choices of \'top\' (Above fields) or \'left\' (Beside fields)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'top' => 'Top',
                        'left' => 'Left',
                        'hidden' => 'Hidden',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'top',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_instruction_placement',
                    'label' => 'Instruction placement',
                    'name' => 'acfe_form_instruction_placement',
                    'type' => 'radio',
                    'instructions' => 'Determines where field instructions are places in relation to fields. Defaults to \'label\'. <br />
    Choices of \'label\' (Below labels) or \'field\' (Below fields)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'label' => 'Label',
                        'field' => 'Field',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'label',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
            
                /*
                 * HTML
                 */
                array(
                    'key' => 'field_acfe_form_tab_html',
                    'label' => 'HTML',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_custom_html_enable',
                    'label' => 'Override Form render',
                    'name' => 'acfe_form_custom_html_enable',
                    'type' => 'true_false',
                    'instructions' => 'Override the native field groups HTML render',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => false,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_html_before_fields',
                    'label' => 'HTML Before render',
                    'name' => 'acfe_form_html_before_fields',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'Extra HTML to add before the fields',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
                array(
                    'key' => 'field_acfe_form_custom_html',
                    'label' => 'HTML Form render',
                    'name' => 'acfe_form_custom_html',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'Render your own customized HTML.<br /><br />
    Field groups may be included using <code>{field_group:group_key}</code><br/><code>{field_group:Group title}</code><br/><br/>
    Fields may be included using <code>{field:field_key}</code><br/><code>{field:field_name}</code>',
                    'required' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 12,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_custom_html_enable',
                                'operator' => '==',
                                'value' => '1',
                            ),
                        ),
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_html_after_fields',
                    'label' => 'HTML After render',
                    'name' => 'acfe_form_html_after_fields',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'Extra HTML to add after the fields',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
            
                /*
                 * Validation
                 */
                array(
                    'key' => 'field_acfe_form_tab_validation',
                    'label' => 'Validation',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_hide_error',
                    'label' => 'Hide general error',
                    'name' => 'acfe_form_hide_error',
                    'type' => 'true_false',
                    'instructions' => 'Hide the general error message: "Validation failed. 1 field requires attention"',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_hide_revalidation',
                    'label' => 'Hide successful re-validation',
                    'name' => 'acfe_form_hide_revalidation',
                    'type' => 'true_false',
                    'instructions' => 'Hide the successful notice when an error has been thrown',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_hide_unload',
                    'label' => 'Hide confirmation on exit',
                    'name' => 'acfe_form_hide_unload',
                    'type' => 'true_false',
                    'instructions' => 'Do not prompt user on page refresh',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_errors_position',
                    'label' => 'Fields errors position',
                    'name' => 'acfe_form_errors_position',
                    'type' => 'radio',
                    'instructions' => 'Choose where to display field errors',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'choices' => array(
                        'above' => 'Above fields',
                        'below' => 'Below fields',
                        'group' => 'Group errors',
                        'hide' => 'Hide errors',
                    ),
                    'allow_null' => 0,
                    'other_choice' => 0,
                    'default_value' => 'above',
                    'layout' => 'vertical',
                    'return_format' => 'value',
                    'save_other_choice' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_errors_class',
                    'label' => 'Fields errors class',
                    'name' => 'acfe_form_errors_class',
                    'type' => 'text',
                    'instructions' => 'Add class to error message',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_errors_position',
                                'operator' => '!=',
                                'value' => 'group',
                            ),
                            array(
                                'field' => 'field_acfe_form_errors_position',
                                'operator' => '!=',
                                'value' => 'hide',
                            ),
                        )
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
            
                /*
                 * Submission
                 */
                array(
                    'key' => 'field_acfe_form_tab_submission',
                    'label' => 'Success Page',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_return',
                    'label' => 'Redirection',
                    'name' => 'acfe_form_return',
                    'type' => 'text',
                    'instructions' => 'The URL to be redirected to after the form is submitted. See "Cheatsheet" tab for all available template tags.<br/><br/><u>This setting is deprecated, use the new "Redirect Action" instead.</u>',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-enable-switch' => true
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                ),
                array(
                    'key' => 'field_acfe_form_updated_hide_form',
                    'label' => 'Hide form',
                    'name' => 'acfe_form_updated_hide_form',
                    'type' => 'true_false',
                    'instructions' => 'Hide form on successful submission',
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'acfe_permissions' => '',
                    'message' => '',
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
                array(
                    'key' => 'field_acfe_form_updated_message',
                    'label' => 'Success message',
                    'name' => 'acfe_form_updated_message',
                    'type' => 'wysiwyg',
                    'instructions' => 'A message displayed above the form after being redirected. See "Cheatsheet" tab for all available template tags.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => __('Post updated', 'acf'),
                    'tabs' => 'all',
                    'toolbar' => 'full',
                    'media_upload' => 1,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_acfe_form_html_updated_message',
                    'label' => 'Success wrapper HTML',
                    'name' => 'acfe_form_html_updated_message',
                    'type' => 'acfe_code_editor',
                    'instructions' => 'HTML used to render the updated message.<br />
If used, you have to include the following code <code>%s</code> to print the actual "Success message" above.',
                    'required' => 0,
                    'conditional_logic' => array(
                        array(
                            array(
                                'field' => 'field_acfe_form_return',
                                'operator' => '==',
                                'value' => '',
                            ),
                        ),
                    ),
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                        'data-instruction-placement' => 'field'
                    ),
                    'acfe_permissions' => '',
                    'default_value' => '<div id="message" class="updated">%s</div>',
                    'placeholder' => '',
                    'maxlength' => '',
                    'rows' => 2,
                ),
            
                /*
                 * Cheatsheet
                 */
                array(
                    'key' => 'field_acfe_form_tab_cheatsheet',
                    'label' => 'Cheatsheet',
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
                    'acfe_permissions' => '',
                    'placement' => 'top',
                    'endpoint' => 0,
                ),
            
                array(
                    'key' => 'field_acfe_form_cheatsheet_field',
                    'label' => 'Field',
                    'name' => 'acfe_form_cheatsheet_field',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve user input from the current form',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_fields',
                    'label' => 'Fields',
                    'name' => 'acfe_form_cheatsheet_fields',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve all user inputs from the current form',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_get_field',
                    'label' => 'Get Field',
                    'name' => 'acfe_form_cheatsheet_get_field',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve ACF field value from database',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_get_option',
                    'label' => 'Get Option',
                    'name' => 'acfe_form_cheatsheet_get_option',
                    'type' => 'acfe_dynamic_render',
                    'value' => '',
                    'instructions' => 'Retrieve option value from database',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_request',
                    'label' => 'Request',
                    'name' => 'acfe_form_cheatsheet_request',
                    'type' => 'acfe_dynamic_render',
                    'value' => '',
                    'instructions' => 'Retrieve <code>$_REQUEST</code> value',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_query_var',
                    'label' => 'Query Var',
                    'name' => 'acfe_form_cheatsheet_query_var',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve query var values. Can be used to get data from previous action',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_form',
                    'label' => 'Form Settings',
                    'name' => 'acfe_form_cheatsheet_current_form',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current Dynamic Form data',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_actions_post',
                    'label' => 'Action Output: Post',
                    'name' => 'acfe_form_cheatsheet_actions_post',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'acfe_form_cheatsheet_actions_term',
                    'label' => 'Action Output: Term',
                    'name' => 'acfe_form_cheatsheet_actions_term',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'acfe_form_cheatsheet_actions_user',
                    'label' => 'Action Output: User',
                    'name' => 'acfe_form_cheatsheet_actions_user',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'acfe_form_cheatsheet_actions_email',
                    'label' => 'Action Output: Email',
                    'name' => 'acfe_form_cheatsheet_actions_email',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve actions output',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_post',
                    'label' => 'Current Post',
                    'name' => 'acfe_form_cheatsheet_current_post',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current post data (where the form is being printed)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_term',
                    'label' => 'Current Term',
                    'name' => 'acfe_form_cheatsheet_current_term',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current term data (where the form is being printed)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_user',
                    'label' => 'Current User',
                    'name' => 'acfe_form_cheatsheet_current_user',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve currently logged user data',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
                array(
                    'key' => 'field_acfe_form_cheatsheet_current_author',
                    'label' => 'Current Author',
                    'name' => 'acfe_form_cheatsheet_current_author',
                    'type' => 'acfe_dynamic_render',
                    'instructions' => 'Retrieve current post author data (where the form is being printed)',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $this->post_type,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'acf_after_title',
            'style' => 'default',
            'label_placement' => 'left',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'acfe_permissions' => '',
            'acfe_form' => 0,
            'acfe_meta' => '',
            'acfe_note' => '',
        ));
        
    }
    
}

acf_new_instance('acfe_dynamic_forms');

endif;

/*
 * ACFE: Import Form
 */
function acfe_import_form($args){
    
    // json
    if(is_string($args))
        $args = json_decode($args, true);
    
    if(!is_array($args) || empty($args))
        return new WP_Error('acfe_import_form_invalid_input', __("Input is invalid: Must be a json string or an array."));
    
    // Instance
    $instance = acf_get_instance('acfe_dynamic_forms');
    
    // Single
    if(acf_maybe_get($args, 'title')){
        
        $name = acf_maybe_get($args, 'acfe_form_name');
        
        $args = array(
            $name => $args
        );
        
    }
    
    $result = array();
    
    foreach($args as $name => $data){
        
        // Import
        $post_id = $instance->import($name, $data);
        
        $return = array(
            'success'   => true,
            'post_id'   => $post_id,
            'message'   => 'Form "' . acf_maybe_get($data, 'title') . '" successfully imported.',
        );
        
        // Error
        if(is_wp_error($post_id)){
            
            $return['post_id'] = 0;
            $return['success'] = false;
            $return['message'] = $post_id->get_error_message();
            
            if($post_id->get_error_code() === 'acfe_form_import_already_exists'){
                
                $get_post = get_page_by_path($name, OBJECT, $instance->post_type);
                
                if($get_post){
                    $return['post_id'] = $get_post->ID;
                }
                
            }
            
        }
        
        $result[] = $return;
        
    }
    
    if(count($result) === 1){
        $result = $result[0];
    }
    
    return $result;
    
}

/*
 * Deprecated ACFE: Import Forms
 */
function acfe_import_forms($forms){
    return acfe_import_form($forms);
}

/*
 * Deprecated ACFE: Import Dynamic Form
 */
function acfe_import_dynamic_form($forms = false){
    return acfe_import_form($forms);
}