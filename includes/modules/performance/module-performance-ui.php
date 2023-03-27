<?php

if(!defined('ABSPATH')){
    exit;
}

if(!class_exists('acfe_performance_ui')):

class acfe_performance_ui{
    
    /**
     * construct
     */
    function __construct(){
        
        add_action('acfe/dev/add_meta_boxes',         array($this, 'add_meta_boxes'), 10, 3);
        add_filter('acfe/dev/meta_ref',               array($this, 'meta_ref'), 10, 6);
        add_filter('acfe/modules/performance/config', array($this, 'get_config'), 99);
        
    }
    
    
    /**
     * add_meta_boxes
     *
     * @param $post_id
     * @param $screen
     * @param $type
     */
    function add_meta_boxes($post_id, $screen, $type){
        
        // bail early
        if(!acfe_is_object_performance_enabled($post_id)){
            return;
        }
        
        // check ui
        if(!acfe_get_performance_config('ui')){
            return;
        }
    
        // enable filters to force sidebar on list screen
        acf_enable_filter('acfe/post_type_list/side');
        acf_enable_filter('acfe/taxonomy_list/side');
        
        // add meta box
        add_meta_box('acfe-performance', __('Performance Mode', 'acfe'), array($this, 'render_metabox'), $screen, 'side', 'core', array('post_id' => $post_id));
        
    }
    
    
    /**
     * render_metabox
     *
     * @param $post
     * @param $metabox
     */
    function render_metabox($post, $metabox){
        
        // post id
        $post_id = $metabox['args']['post_id'];
        $config = acfe_get_performance_config();
        
        $fields = array(
            
            array(
                'key' => 'field_mode',
                'label' => '',
                'name' => 'options',
                'prefix' => 'acfe_performance',
                'aria-label' => '',
                'type' => 'radio',
                'value' => $config['mode'],
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'default_value' => 0,
                'choices' => array(
                    'test'       => __('Test Drive', 'acfe'),
                    'production' => __('Production', 'acfe'),
                    'rollback'   => __('Rollback', 'acfe'),
                ),
            ),
            
        );
        
        // vars
        $status = acfe_get_object_performance_status($post_id);
        $conflict = acfe_get_object_performance_conflict($post_id);
        ?>
        <style>
        .acf-field-mode{
            padding:8px 12px !important;
        }
        </style>

        <div class="misc-pub-section misc-pub-acfe-object misc-pub-acfe-dashboard" style="padding-top:11px; padding-bottom:0px;">
            <?php _e('Engine', 'acfe'); ?>: <strong><?php echo ucfirst($config['engine']); ?></strong>
        </div>
        
        <?php if(!empty($status)): ?>
            <div class="misc-pub-section misc-pub-acfe-object misc-pub-acfe-<?php echo $status['name'] === 'active' ? 'yes' : 'info'; ?> acf-js-tooltip" title="<?php echo $status['message']; ?>" style="padding-top:6px; padding-bottom:0px;">
                <?php _e('Status', 'acfe'); ?>: <strong><?php echo $status['title']; ?></strong>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($conflict)): ?>
            <div class="misc-pub-section misc-pub-acfe-object misc-pub-acfe-info acf-js-tooltip" title="<?php echo $conflict['message']; ?>" style="padding-top:6px; padding-bottom:0px;">
                <strong><?php echo $conflict['title']; ?></strong>
            </div>
        <?php endif; ?>
        
        <div style="padding-bottom:11px;"></div>

        <?php acf_render_fields($fields, false); ?>
        
        <script type="text/javascript">
            if(typeof acf !== 'undefined'){
                acf.newPostbox(<?php echo json_encode(array(
                    'id'         => 'acfe-performance',
                    'key'        => '',
                    'style'      => '',
                    'label'      => '',
                    'editLink'   => '',
                    'editTitle'  => '',
                    'visibility' => true,
                )); ?>);
            }
        </script>
        <?php
        
    }
    
    
    /**
     * meta_ref
     *
     * @param $ref
     * @param $wp_meta
     * @param $type
     * @param $key
     * @param $id
     * @param $post_id
     *
     * @return mixed
     */
    function meta_ref($ref, $wp_meta, $type, $key, $id, $post_id){
        
        // check enabled
        if(acfe_get_object_performance_engine_name($post_id) !== 'hybrid'){
            return $ref;
        }
        
        // engine
        $engine = acfe_get_performance_engine('hybrid');
        
        // meta key
        $meta_key = $engine->get_meta_key($post_id);
    
        // meta ref
        if(isset($wp_meta[ $meta_key ])){
            
            $_key = $meta_key; // '_acf' or '_options_acf'
            $_f_key = "_$key"; // '_my_field'
        
            if($type === 'option'){
                
                // convert 'options_my_field' > '_my_field'
                $_f_key = substr_replace($key, '_', 0, strlen($id) + 1);
            
            }
        
            $_acf = maybe_unserialize($wp_meta[ $_key ]['value']);
        
            if(isset($_acf[ $_f_key ])){
            
                $ref = $wp_meta[ $_key ];
                $ref['key'] = $_f_key;
                $ref['value'] = $_acf[ $_f_key ];
                
                if($type === 'option'){
                    $ref['key'] = "_$id" . $ref['key'];
                }
            
            }
        
        }
        
        return $ref;
        
    }
    
    
    /**
     * get_config
     *
     * @param $config
     *
     * @return mixed
     */
    function get_config($config){
    
        if($config['ui']){
    
            $post_config = acf_maybe_get_POST('acfe_performance');
            
            if(!empty($post_config)){
                $config['mode'] = $post_config['field_mode'];
            }
            
        }
        
        return $config;
        
        
    }
    
}

acf_new_instance('acfe_performance_ui');

endif;