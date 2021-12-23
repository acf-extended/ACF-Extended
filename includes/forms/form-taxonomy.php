<?php

if(!defined('ABSPATH'))
    exit;

if(!class_exists('acfe_screen_taxonomy')):

class acfe_screen_taxonomy{
    
    // vars
    var $taxonomy;
    
    /*
     * Construct
     */
    function __construct(){
    
        /*
         * acfe/load_term               $taxonomy, $term_id
         * acfe/add_term_meta_boxes     $taxonomy, $term
         *
         * acfe/load_terms              $taxonomy
         * acfe/add_terms_meta_boxes    $taxonomy
         */
    
        // edit
        add_action('load-term.php',         array($this, 'term_load'));
    
        // list
        add_action('load-edit-tags.php',    array($this, 'terms_load'));
        
    }
    
    /*
     * Term: Load
     */
    function term_load(){
        
        // global
        global $taxnow;
        
        // vars
        $taxonomy = $taxnow;
        $term_id = (int) acfe_get_post_id(false);
    
        // set vars
        $this->taxonomy = $taxonomy;
        
        // actions
        do_action("acfe/load_term",                         $taxonomy, $term_id);
        do_action("acfe/load_term/taxonomy={$taxonomy}",    $taxonomy, $term_id);
        
        // hooks
        add_action("{$taxonomy}_term_edit_form_top",    array($this, 'add_term_meta_boxes'), 10, 2);
        add_action("{$taxonomy}_edit_form",             array($this, 'do_term_meta_boxes'), 10, 2);
        
    }
    
    /*
     * Term: Meta Boxes
     */
    function add_term_meta_boxes($term, $taxonomy){
        
        do_action("acfe/add_term_meta_boxes",                       $taxonomy, $term);
        do_action("acfe/add_term_meta_boxes/taxonomy={$taxonomy}",  $taxonomy, $term);
        
        // enhanced ui
        if(acf_get_setting('acfe/modules/ui')){
            
            do_meta_boxes(get_current_screen(), 'acf_after_title', $term);
            
        }
        
    }
    
    /*
     * Term: Do Meta Boxes
     */
    function do_term_meta_boxes($term, $taxonomy){
    
        // enhanced ui
        if(acf_get_setting('acfe/modules/ui')){
    
            $screen = get_current_screen();
            
            do_meta_boxes($screen, 'normal', $term);
            do_meta_boxes($screen, 'side', $term);
            
        }
        
    }
    
    /*
     * Terms: Load
     */
    function terms_load(){
        
        // global
        global $pagenow, $taxnow;
        
        // validate (wordpress also load this hook on term.php)
        if($pagenow !== 'edit-tags.php'){
            return;
        }
        
        // vars
        $taxonomy = $taxnow;
    
        // set vars
        $this->taxonomy = $taxonomy;
        
        // actions
        do_action("acfe/load_terms",                        $taxonomy);
        do_action("acfe/load_terms/taxonomy={$taxonomy}",   $taxonomy);
    
        // hooks
        add_action('admin_footer', array($this, 'terms_footer'));
        
    }
    
    /*
     * Terms: Admin Footer
     */
    function terms_footer(){
        
        do_action('acfe/add_terms_meta_boxes', $this->taxonomy);
    
        $this->terms_do_meta_boxes();
        
    }
    
    /*
     * Terms: Do Meta Boxes
     */
    function terms_do_meta_boxes(){
        
        // check filter
        if(!acf_is_filter_enabled('acfe/taxonomy_list')){
            return;
        }
        
        ?>
        <template id="tmpl-acf-after-title">
            
            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'acf_after_title', $this->taxonomy); ?>
                </form>
            </div>
        
        </template>
        
        <template id="tmpl-acf-normal">
            
            <div id="poststuff" class="acfe-list-postboxes">
                <form method="post">
                    <?php do_meta_boxes('edit', 'normal', $this->taxonomy); ?>
                </form>
            </div>
        
        </template>
        
        <template id="tmpl-acf-side">
            
            <div class="acf-column-2">
                
                <div id="poststuff" class="acfe-list-postboxes -side">
                    <form method="post">
                        <?php do_meta_boxes('edit', 'side', $this->taxonomy); ?>
                    </form>
                </div>
            
            </div>
        
        </template>
        <script type="text/javascript">
        (function($){

            // main form
            var $main = $('#posts-filter');

            $main.wrap('<div class="acf-columns-2" />');
            $main.wrap('<div class="acf-column-1" />');

            // field groups
            var $column_1 = $('.acf-column-1');

            $column_1.prepend($('.search-form'));
            $column_1.prepend($('#tmpl-acf-after-title').html());
            $column_1.append($('#tmpl-acf-normal').html());
            $column_1.after($('#tmpl-acf-side').html());
            
            <?php if(!acf_get_setting('acfe/modules/ui') || !acf_is_filter_enabled('acfe/taxonomy_list/side')): ?>
                $('.acf-columns-2').removeClass('acf-columns-2');
            <?php endif; ?>

        })(jQuery);
        </script>
        <?php
    }
    
}

new acfe_screen_taxonomy();

endif;