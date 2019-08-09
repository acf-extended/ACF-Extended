<div class="wrap acf-settings-wrap">
    
    <?php
    $title = __('Edit Option');
    if($_REQUEST['action'] === 'add')
        $title = __('Add Option');
    ?>
    <h1 class="wp-heading-inline"><?php echo $title; ?></h1>
    
    <hr class="wp-header-end" />
    
    <form id="post" method="post" name="post">
        
        <?php 
        
        // render post data
        acf_form_data(array(
            'screen'	=> 'acfe-options-edit',
            'post_id'	=> 'acfe_options_edit',
        ));
        
        wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false);
        wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false);
        
        ?>
        
        <div id="poststuff">
            
            <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">
                
                <!--
                <div id="post-body-content">
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" name="post_title" size="30" value="Post 4" id="title" spellcheck="true" autocomplete="off" />
                        </div>
                    </div>
                </div>
                -->
                
                <div id="postbox-container-1" class="postbox-container">
                    
                    <?php do_meta_boxes('acf_options_page', 'side', null); ?>
                        
                </div>
                
                <div id="postbox-container-2" class="postbox-container">
                    
                    <?php do_meta_boxes('acf_options_page', 'normal', null); ?>
                    
                </div>
            
            </div>
            
            <br class="clear" />
        
        </div>
        
    </form>
    
</div>