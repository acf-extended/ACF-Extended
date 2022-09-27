<?php

if(!defined('ABSPATH')){
    exit;
}

// check setting
if(!acf_get_setting('acfe/modules/ui')){
    return;
}

if(!class_exists('acfe_enhanced_ui_term')):
    
class acfe_enhanced_ui_term extends acfe_enhanced_ui{
    
    // vars
    var $taxonomy;
    
    /**
     * initialize
     */
    function initialize(){
        
        // load
        add_action('acfe/load_term',            array($this, 'load_term'));
        add_action('acfe/load_terms',           array($this, 'load_terms'));
        
        // meta boxes
        add_action('acfe/add_term_meta_boxes',  array($this, 'add_term_meta_boxes'), 10, 2);
        
    }
    
    
    /**
     * load_term
     *
     * @param $taxonomy
     */
    function load_term($taxonomy){
    
        // enqueue
        $this->enqueue_scripts();
        
        // var
        $this->taxonomy = $taxonomy;
        
        // hooks
        add_action('admin_enqueue_scripts', array($this, 'term_enqueue_scripts'), 15); // must be priority 15
        add_action('admin_footer',          array($this, 'term_footer'));
        
    }
    
    
    /**
     * load_terms
     *
     * @param $taxonomy
     */
    function load_terms($taxonomy){
    
        // enqueue
        $this->enqueue_scripts();
        
        // hooks
        add_action('admin_footer', array($this, 'terms_footer'));
        
    }
    
    
    /**
     * add_term_meta_boxes
     *
     * @param $taxonomy
     * @param $term
     */
    function add_term_meta_boxes($taxonomy, $term){
        
        // post id
        $post_id = 'term_' . $term->term_id;
        
        // screen
        $screen = get_current_screen();
        
        // field groups
        $field_groups = acf_get_field_groups(array(
            'taxonomy' => $taxonomy
        ));
        
        if($field_groups){
    
            // form data
            acf_form_data(array(
                'screen'    => 'taxonomy',
                'post_id'   => $post_id,
            ));
            
            $this->add_metaboxes($field_groups, $post_id, $screen);
            
        }
        
        // Sidebar submit
        add_meta_box('submitdiv', __('Edit'), array($this, 'render_metabox_submit'), $screen, 'side', 'high', 'term');
        
    }
    
    
    /**
     * term_enqueue_scripts
     */
    function term_enqueue_scripts(){
        
        // remove acf render
        // advanced-custom-fields-pro/includes/forms/form-taxonomy.php
        acfe_remove_action("{$this->taxonomy}_edit_form", array('acf_form_taxonomy', 'edit_term'));
        
    }
    
    
    /**
     * term_footer
     */
    function term_footer(){
        
        global $tag, $tax;
        
        ?>
        <div class="permalink">
            <?php if(acfe_maybe_get($tax, 'publicly_queryable')){ ?>
                <div id="edit-slug-box">
                    <strong>Permalink:</strong> <a href="<?php echo get_term_link($tag, $tax); ?>"><?php echo get_term_link($tag, $tax); ?></a>
                </div>
            <?php } ?>
        </div>
        
        <script type="text/javascript">
        (function($){

            acfe.enhancedEditUI({
                screen:     'term-edit',
                submit:     '> .edit-tag-actions',
                pageTitle:  true
            });

        })(jQuery);
        </script>
        <?php
        
    }
    
    
    /**
     * terms_footer
     */
    function terms_footer(){
    
        global $tax;
        $can_edit_terms = current_user_can($tax->cap->edit_terms);
    
        ?>
        <script type="text/html" id="tmpl-button-add-term">
        <?php if($can_edit_terms){ ?>
            <a href="#" class="page-title-action acfe-bt-admin-button-add"><?php echo $tax->labels->add_new_item; ?></a>
        <?php } ?>
        </script>

        <script type="text/javascript">
        (function($){

            acfe.enhancedListUI({
                taxonomy: '<?php echo $tax->name; ?>'
            });

            // Polylang + WPML Compatibility New Lang
            <?php if((acf_maybe_get_GET('from_tag') && acf_maybe_get_GET('new_lang')) || acf_maybe_get_GET('trid')){ ?>
            
                var $button = $('.acfe-bt-admin-button-add');
                
                if($button.length){
                    $button.click();
                }
                
            <?php } ?>

        })(jQuery);
        </script>
        <?php
        
    }

}

new acfe_enhanced_ui_term();

endif;