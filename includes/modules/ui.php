<?php

if(!defined('ABSPATH'))
    exit;

// Check setting
if(!acf_get_setting('acfe/modules/ui'))
    return;

if(!class_exists('acfe_enhanced_ui')):

class acfe_enhanced_ui{
    
    var $suffix = '';
    var $version = '';
    
    function __construct(){
    
        // Vars
        $this->suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        $this->version = ACFE_VERSION;
    
        add_action('current_screen', array($this, 'current_screen'));
    
    }
    
    function current_screen(){
        
        $screens = array(
    
            // Users
            'profile'               => 'user_edit',
            'user-edit'             => 'user_edit',
            'user'                  => 'user_new',
            
            // Terms
            'edit-tags'             => 'term_list',
            'term'                  => 'term_edit',
            
            // Settings
            'options-general'       => 'settings',
            'options-writing'       => 'settings',
            'options-reading'       => 'settings',
            'options-discussion'    => 'settings',
            'options-media'         => 'settings',
            'options-permalink'     => 'settings',
            
        );
        
        $array = array(
    
            // WPMU Users
            'profile-network',
            'user-edit-network',
            'user-network',
    
            // WPMU Settings
            'settings-network',
            'site-info-network',
            'site-users-network',
            'site-settings-network',
            'site-new-network',
        );
        
        foreach($screens as $screen => $action){
            
            if(!$this->is_screen($screen))
                continue;
            
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
            add_action('admin_footer',          array($this, $action));
            
            break;
            
        }
        
    }
    
    function admin_enqueue(){
    
        wp_enqueue_style('acf-extended-ui', acfe_get_url('assets/css/acfe-ui' . $this->suffix . '.css'), false, $this->version);
        
    }
    
    /*
     * Edit User
     */
    function user_edit(){
        
        ?>
        <script type="text/html" id="tmpl-acf-column-2">
            <div class="acf-column-2">

                <div id="poststuff" class="acfe-acfe-bt-admin-column">

                    <div class="postbox">

                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Edit', 'acfe'); ?></span></h2>

                        <div class="inside">
                            <div class="submitbox">

                                <div id="major-publishing-actions">

                                    <div id="publishing-action">

                                        <div class="acfe-form-submit">
                                            <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Update', 'acfe'); ?>" />
                                            <span class="acf-spinner"></span>
                                        </div>

                                    </div>
                                    <div class="clear"></div>

                                </div>

                            </div>

                        </div>

                    </div>
                    
                    <?php do_meta_boxes('user-edit', 'side', array()); ?>

                </div>
            </div>
        </script>
        <script type="text/javascript">
            (function($){

                // ACF Extended UI
                $('.wrap').addClass('acfe-ui');

                // wrap form
                $('.acfe-ui > form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');

                // add column side
                $('.acfe-ui > form .acf-columns-2').append($('#tmpl-acf-column-2').html());

                // hide native button
                $('.acfe-ui > form p.submit').hide();

                // Yoast Settings
                var $yoastSettings = $('.acfe-ui > form .yoast.yoast-settings');

                if($yoastSettings.length){

                    $yoastSettings.find('> h2 ~ *').wrapAll('<div class="yoast-settings-table"></div>');
                    $yoastSettings.find('.yoast-settings-table > label:nth-of-type(1), .yoast-settings-table > input:nth-of-type(1)').wrapAll('<div class="yoast-settings-row"></div>');
                    $yoastSettings.find('.yoast-settings-table > label:nth-of-type(1), .yoast-settings-table > label:nth-of-type(1) ~ *').wrapAll('<div class="yoast-settings-row"></div>');
                    $yoastSettings.find('.yoast-settings-table > br').remove();

                    $yoastSettings.find('.yoast-settings-table .yoast-settings-row').each(function(){

                        var $this = $(this);

                        $this.find('label:nth-of-type(1)').wrapAll('<div class="yoast-settings-label"></div>');
                        $this.find('.yoast-settings-label ~ *').wrapAll('<div class="yoast-settings-input"></div>');
                        $this.find('br').replaceWith('<div class="yoast-settings-spacer"></div>');

                    });

                }

            })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * New User
     */
    function user_new(){
        
        ?>
        <script type="text/html" id="tmpl-acf-column-2">
            <div class="acf-column-2">

                <div id="poststuff" class="acfe-acfe-bt-admin-column">

                    <div class="postbox">

                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Edit', 'acfe'); ?></span></h2>

                        <div class="inside">
                            <div class="submitbox">

                                <div id="major-publishing-actions">

                                    <div id="publishing-action">

                                        <div class="acfe-form-submit">
                                            <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Add New User'); ?>" />
                                            <span class="acf-spinner"></span>
                                        </div>

                                    </div>
                                    <div class="clear"></div>

                                </div>

                            </div>

                        </div>

                    </div>
                    
                    <?php do_meta_boxes(get_current_screen(), 'side', array()); ?>

                </div>
            </div>
        </script>
        <script type="text/javascript">
            (function($){

                // ACF Extended UI
                $('.wrap').addClass('acfe-ui');

                // wrap form
                $('.acfe-ui > form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');

                // add column side
                $('.acfe-ui > form .acf-columns-2').append($('#tmpl-acf-column-2').html());

                // add title
                var title = $('.wrap > h1').text();
                $('.acfe-ui > form > div > div > table:first').before('<h2>' + title + '</h2>');

                // Hide native button
                $('.acfe-ui > form p.submit').hide();

            })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Term List
     */
    function term_list(){
        
        global $tax;
        $can_edit_terms = current_user_can($tax->cap->edit_terms);
        
        ?>
        <script type="text/html" id="tmpl-acfe-bt-admin-button-add">
            <?php if($can_edit_terms){ ?>
                <a href="#" class="page-title-action acfe-bt-admin-button-add"><?php echo $tax->labels->add_new_item; ?></a>
            <?php } ?>
        </script>

        <script type="text/html" id="tmpl-acfe-bt-wrapper">
            <div id="poststuff"></div>
        </script>

        <script type="text/javascript">
            (function($){
                // Add button
                $('.wrap .wp-heading-inline').after($('#tmpl-acfe-bt-admin-button-add').html());

                // Move form
                $('#ajax-response').after($('#col-container #col-left').addClass('acfe-bt'));

                // Hide form
                $('.acfe-bt').hide();

                // Create wrapper
                $('.acfe-bt .form-wrap').append($('#tmpl-acfe-bt-wrapper').html());

                // Append form inside wrapper
                var $newForm = $('.acfe-bt .form-wrap form');

                $('.acfe-bt #poststuff').append($newForm);
                $newForm.wrapInner('<div class="postbox" id="acfe-bt-form"><div class="inside"></div></div>');

                // Append new title
                var $nativeTitle = $('.acfe-bt .form-wrap > h2');

                $('.acfe-bt .postbox').prepend('<h2 class="hndle">' + $nativeTitle.text() + '</h2>');
                $nativeTitle.remove();

                // ACF class
                var $fields = $('.acfe-bt .inside .form-field, .acfe-bt .inside .submit');
                $fields.addClass('acf-field');

                $fields.each(function(){

                    $(this).append('<div class="acf-input"></div>');
                    $(this).find('.acf-input').append($(this).find('> :not("label")'));

                    // Add spacing when a meta field has no label
                    var $label = $(this).find('> label');
                    if($label.length){

                        $label.wrap('<div class="acf-label"></div>');

                    }else{

                        $(this).addClass('acfe-bt-no-label');

                    }

                });

                // Remove ACF Fields id
                $('#acf-term-fields').contents().unwrap();

                // Button
                var $newButton = $('.acfe-bt-admin-button-add');

                $newButton.click(function(e){

                    e.preventDefault();
                    var $wrap = $('.acfe-bt');

                    if($wrap.is(':visible'))
                        $wrap.hide();
                    else
                        $wrap.show();

                });

                // Label to left
                if(typeof acf !== 'undefined'){
                    acf.postbox.render({
                        'id':       'acfe-bt-form',
                        'label':    'left'
                    });
                }

                $('#acfe-bt-form .acf-tab-wrap.-left').removeClass('-left').addClass('-top');

                // Polylang Compatibility Fix
                <?php if(isset($_GET['from_tag']) && !empty($_GET['from_tag']) && isset($_GET['new_lang']) && !empty($_GET['new_lang'])){ ?>

                $newButton.click();
                
                <?php } ?>

            })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Term Edit
     */
    function term_edit(){
        
        ?>
        <script type="text/html" id="tmpl-acf-column-2">
            <div class="acf-column-2">

                <div id="poststuff" class="acfe-acfe-bt-admin-column">

                    <div class="postbox">

                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Edit', 'acfe'); ?></span></h2>

                        <div class="inside">
                            <div class="submitbox">

                                <div id="major-publishing-actions">

                                    <div id="publishing-action">

                                        <div class="acfe-form-submit">
                                            <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Update', 'acfe'); ?>" />
                                            <span class="acf-spinner"></span>
                                        </div>

                                    </div>
                                    <div class="clear"></div>

                                </div>

                            </div>

                        </div>

                    </div>
                    
                    <?php do_meta_boxes(get_current_screen(), 'side', array()); ?>

                </div>
            </div>
        </script>
        <script type="text/javascript">
            (function($){

                // ACF Extended UI
                $('.wrap').addClass('acfe-ui');

                // wrap form
                $('.acfe-ui > form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');

                // add column side
                $('.acfe-ui > form .acf-columns-2').append($('#tmpl-acf-column-2').html());

                // add title
                var title = $('.wrap > h1').text();
                $('.acfe-ui > form > div > div > table:first').before('<h2>' + title + '</h2>');

                // Hide native button
                $('.acfe-ui > form .edit-tag-actions').hide();

                // WPML Widget
                $wpmlWidget = $('#icl_tax_category_lang');

                if($wpmlWidget.length){

                    $wpmlWidget.appendTo('.acfe-acfe-bt-admin-column');
                    $('tr.wpml-term-languages-wrap').remove();

                }

            })(jQuery);
        </script>
        <?php
        
    }
    
    /*
     * Settings
     */
    function settings(){
        
        global $pagenow;
        
        ?>
        <script type="text/html" id="tmpl-acf-column-2">
            <div class="acf-column-2">

                <div id="poststuff" class="acfe-acfe-bt-admin-column">

                    <div class="postbox">

                        <h2 class="hndle ui-sortable-handle"><span><?php _e('Settings'); ?></span></h2>

                        <div class="inside">
                            <div class="submitbox">

                                <div id="major-publishing-actions">

                                    <div id="publishing-action">

                                        <div class="acfe-form-submit">
                                            <input type="submit" class="acf-button button button-primary button-large" value="<?php _e('Save Changes'); ?>" />
                                            <span class="acf-spinner"></span>
                                        </div>

                                    </div>
                                    <div class="clear"></div>

                                </div>

                            </div>

                        </div>

                    </div>
                    
                    <?php do_meta_boxes(get_current_screen(), 'side', array()); ?>

                </div>
            </div>
        </script>
        <script type="text/javascript">
            (function($){

                // ACF Extended UI
                $('.wrap').addClass('acfe-ui');

                // wrap form
                $('.acfe-ui > form').wrapInner('<div class="acf-columns-2"><div class="acf-column-1"></div></div>');

                // add column side
                $('.acfe-ui > form .acf-columns-2').append($('#tmpl-acf-column-2').html());
                
                <?php if(!in_array($pagenow, array('options-permalink.php', 'options-media.php', 'settings.php'))){ ?>

                // add title
                var title = $('.wrap > h1').text();
                $('.acfe-ui > form > div > div > table:first').before('<h2>' + title + '</h2>');
                
                <?php } ?>

                $('.acfe-ui > h1').css('margin-bottom', '13px');

                if($('#ping_sites').length){

                    $('#ping_sites').wrap('<table class="form-table"><tbody><td class="td-full"></td></tbody></table>');
                    $('#ping_sites').css('width', '100%');

                }

                // Hide native button
                $('.acfe-ui > form p.submit').hide();

            })(jQuery);
        </script>
        <?php
        
    }
    
    function is_screen($id = ''){
        
        // bail early if not defined
        if(!function_exists('get_current_screen'))
            return false;
        
        // vars
        $current_screen = get_current_screen();
        
        // no screen
        if(!$current_screen){
            
            return false;
            
            // array
        }elseif(is_array($id)){
            
            return in_array($current_screen->base, $id);
            
            // string
        }else{
            
            return ($id === $current_screen->base);
            
        }
        
    }
    
}

new acfe_enhanced_ui();

endif;