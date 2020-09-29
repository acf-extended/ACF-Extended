(function($){

    if(typeof acf === 'undefined')
        return;
    
    // init
    var acfe = {};

    window.acfe = acfe;
    
    acfe.modal = {
        
        modals: [],
        
        // Open
        open: function($target, args){
            
            var model = this;
            
            args = acf.parseArgs(args, {
                title: '',
                footer: false,
                size: false,
                destroy: false,
                onOpen: false,
                onClose: false,
            });
            
            model.args = args;
            
            $target.addClass('-open');
            
            if(args.size){
                
                $target.addClass('-' + args.size);
                
            }
            
            if(!$target.find('> .acfe-modal-wrapper').length){
                
                $target.wrapInner('<div class="acfe-modal-wrapper" />');
                
            }
            
            if(!$target.find('> .acfe-modal-wrapper > .acfe-modal-content').length){
                
                $target.find('> .acfe-modal-wrapper').wrapInner('<div class="acfe-modal-content" />');
                
            }
            
            $target.find('> .acfe-modal-wrapper').prepend('<div class="acfe-modal-wrapper-overlay"></div><div class="acfe-modal-title"><span class="title">' + args.title + '</span><button class="close"></button></div>');
            
            $target.find('.acfe-modal-title > .close').click(function(e){
                
                e.preventDefault();
                model.close(args);
                
            });
            
            if(args.footer){
                
                $target.find('> .acfe-modal-wrapper').append('<div class="acfe-modal-footer"><button class="button button-primary">' + args.footer + '</button></div>');
                
                $target.find('.acfe-modal-footer > button').click(function(e){
                    
                    e.preventDefault();
                    model.close(args);
                    
                });
                
            }
            
            acfe.modal.modals.push($target);
            
            var $body = $('body');
            
            if(!$body.hasClass('acfe-modal-opened')){
				
				var overlay = $('<div class="acfe-modal-overlay" />');
                
				$body.addClass('acfe-modal-opened').append(overlay);
                
                $body.find('.acfe-modal-overlay').click(function(e){
                    
                    e.preventDefault();
                    model.close(model.args);
                    
                });
                
                $(window).keydown(function(e){
            
                    if(e.keyCode !== 27 || !$('body').hasClass('acfe-modal-opened'))
                        return;
                    
                    e.preventDefault();
                    model.close(model.args);
                    
                });
				
			}
            
            acfe.modal.multiple();
            
            acfe.modal.onOpen($target, args);

            acf.doAction('acfe/modal/open', $target, args);
            
            return $target;
			
		},
		
        // Close
		close: function(args){
            
            args = acf.parseArgs(args, {
                destroy: false,
                onClose: false,
            });
            
            var $target = acfe.modal.modals.pop();
			
			$target.find('.acfe-modal-wrapper-overlay').remove();
			$target.find('.acfe-modal-title').remove();
			$target.find('.acfe-modal-footer').remove();
            
			$target.removeAttr('style');
            
			//$target.removeClass('-open -small -medium -full');
			$target.removeClass('-open');
            
            if(args.destroy){
                
                $target.remove();
                
            }
            
			if(!acfe.modal.modals.length){
                
				$('.acfe-modal-overlay').remove();
                $('body').removeClass('acfe-modal-opened');
                
			}
            
            acfe.modal.multiple();

            acf.doAction('acfe/modal/close', $target, args);
            
            acfe.modal.onClose($target, args);

		},
        
        // Multiple
        multiple: function(){
            
            var last = acfe.modal.modals.length - 1;
            
            $.each(acfe.modal.modals, function(i){
                
                if(last == i){
                    $(this).removeClass('acfe-modal-sub').css('margin-left', '');
                    return;
                }
                
                $(this).addClass('acfe-modal-sub').css('margin-left',  - (500 / (i+1)));
                
			});
            
        },
        
        onOpen: function($target, args){
            
            if(!args.onOpen || !(args.onOpen instanceof Function))
                return;
            
            args.onOpen($target);
            
        },
        
        onClose: function($target, args){
            
            if(!args.onClose || !(args.onClose instanceof Function))
                return;
            
            args.onClose($target);
            
        }
        
        
        
    };

    acfe.filters = [];

    acfe.disableFilters = function(){

        acfe.filters = [];

    };

    acfe.enableFilter = function(name){

        if(acfe.filters.indexOf(name) === -1)
            acfe.filters.push(name);

    };

    acfe.disableFilter = function(name){

        for(var i = acfe.filters.length; i--;){

            if(acfe.filters[i] !== name)
                continue;

            acfe.filters.splice(i, 1);

        }

    };

    acfe.isFilterEnabled = function(name){

        return acfe.filters.indexOf(name) > -1;

    };

    acfe.getFilters = function(){

        return acfe.filters;

    };
    
    acf.addAction('ready_field', function(field){
        
        if(!field.has('acfeInstructionsTooltip'))
            return;
        
        var $label = field.$labelWrap().find('> label');
        var $instructions = field.$labelWrap().find('> .description');
        var instructions_html = $instructions.html();
        var instructions_html_2 = field.get('acfeInstructionsTooltip');

        var instructions = instructions_html_2;

        if($instructions.length){

            $instructions.remove();
            instructions = instructions_html;

        }
        $label.before('<span class="acfe-field-tooltip acf-js-tooltip dashicons dashicons-info" title="' + _.escape(instructions) + '"></span>');
            
    });
    
    var acfe_form_move_instructions_above = function(field){
        
        var $instructions = field.$el.find('> .acf-label > .description');
        
        field.$el.find('> .acf-input').prepend($instructions);
            
    };
    
    var acfe_form_move_instructions_below = function(field){
        
        var $instructions = field.$el.find('> .acf-label > .description');
        
        field.$el.find('> .acf-input').append($instructions);
            
    };
    
    acf.addAction('new_field/name=acfe_form_updated_message',   acfe_form_move_instructions_below);
    acf.addAction('new_field/name=acfe_form_return',            acfe_form_move_instructions_below);
    
    acf.addAction('new_field/name=acfe_form_custom_alias',      acfe_form_move_instructions_below);
    acf.addAction('new_field/name=acfe_form_custom_query_var',  acfe_form_move_instructions_below);
    
    acf.addAction('new_field/name=acfe_form_email_content',     acfe_form_move_instructions_below);
    
    acf.addAction('new_field/name=acfe_form_post_save_target',  acfe_form_move_instructions_below);
    acf.addAction('new_field/name=acfe_form_post_load_source',  acfe_form_move_instructions_below);
    
    acf.addAction('new_field/name=acfe_form_term_save_target',  acfe_form_move_instructions_below);
    acf.addAction('new_field/name=acfe_form_term_load_source',  acfe_form_move_instructions_below);
    
    acf.addAction('new_field/name=acfe_form_user_save_target',  acfe_form_move_instructions_below);
    acf.addAction('new_field/name=acfe_form_user_load_source',  acfe_form_move_instructions_below);
    
    acf.addAction('new_field/name=acfe_form_email_files', function(field){
        
        field.$el.find('> .acf-input > .acf-repeater > .acf-actions > .acf-button').removeClass('button-primary');
            
    });
    
    acf.addAction('new_field/name=acfe_form_email_files_static', function(field){
        
        field.$el.find('> .acf-input > .acf-repeater > .acf-actions > .acf-button').removeClass('button-primary');
            
    });

    function acfe_dev_meta_count(){

        var $wp_meta_count = $('#acfe-wp-custom-fields .acfe_dev_meta_count');
        var $acf_meta_count = $('#acfe-acf-custom-fields .acfe_dev_meta_count');

        $wp_meta_count.text($('#acfe-wp-custom-fields tbody tr').length);
        $acf_meta_count.text($('#acfe-acf-custom-fields tbody tr').length);

    }

    acf.addAction('prepare', function(){

        var $acf_meta = $('#acfe-acf-custom-fields');
        var $wp_meta = $('#acfe-wp-custom-fields');
        var $bulk_actions = $('.acfe_dev_bulk_actions');

        // Move Bulk Button
        $('#acfe-wp-custom-fields .tablenav.bottom').insertAfter($wp_meta);
        $('#acfe-acf-custom-fields .tablenav.bottom').insertAfter($acf_meta);

        if(!$acf_meta.is(':visible') && !$wp_meta.is(':visible')){

            $bulk_actions.hide();

        }

        // Bulk Delete
        $('#acfe_bulk_deleta_meta_submit').click(function(e){

            e.preventDefault();
            var $this = $(this);

            var action = $this.prevAll('.acfe_bulk_delete_meta_action').val();
            var type = $this.prevAll('.acfe_bulk_delete_meta_type').val();
            var nonce = $this.prevAll('.acfe_bulk_delete_meta_nonce').val();

            if(action === 'delete'){

                var ids = [];
                var trs = [];

                $('#acfe-wp-custom-fields input.acfe_bulk_delete_meta:checked').each(function(){

                    ids.push($(this).val());
                    trs.push($(this).closest('tr'));

                });

                $('#acfe-acf-custom-fields input.acfe_bulk_delete_meta:checked').each(function(){

                    ids.push($(this).val());
                    trs.push($(this).closest('tr'));

                });

                if(ids.length){

                    var ajaxData = {
                        action: 'acfe/bulk_delete_meta',
                        ids: ids,
                        type: type,
                        _wpnonce: nonce,
                    };

                    $.ajax({
                        url: acf.get('ajaxurl'),
                        data: ajaxData,
                        type: 'post',
                        beforeSend: function(){

                            $.each(trs, function(){

                                $(this).css({backgroundColor:'#faafaa'}).fadeOut(350, function(){
                                    $(this).remove();
                                });

                            });

                            setTimeout(function(){

                                if(!$('#acfe-wp-custom-fields tbody tr').length){

                                    $wp_meta.remove();

                                }

                                if(!$('#acfe-acf-custom-fields tbody tr').length){

                                    $acf_meta.remove();

                                }

                                if(!$('#acfe-wp-custom-fields tbody tr').length && !$('#acfe-acf-custom-fields tbody tr').length){

                                    $bulk_actions.remove();

                                }

                                acfe_dev_meta_count();

                            }, 351);

                        },
                        success: function(response){

                            if(response !== '1'){

                            }

                        }
                    });

                }

            }

        });

        // Single Delete
        $('.acfe_delete_meta').click(function(e){

            e.preventDefault();
            var $this = $(this);
            var $tr = $this.closest('tr');
            var $tbody = $this.closest('tbody');
            var $postbox = $this.closest('.postbox');

            var ajaxData = {
                action: 'acfe/delete_meta',
                id: $this.attr('data-meta-id'),
                key: $this.attr('data-meta-key'),
                type: $this.attr('data-type'),
                _wpnonce: $this.attr('data-nonce'),
            };

            $.ajax({
                url: acf.get('ajaxurl'),
                data: ajaxData,
                type: 'post',
                beforeSend: function(){

                    var $tr = $this.closest('tr');

                    $tr.css({backgroundColor:'#faafaa'}).fadeOut(350, function(){
                        $(this).remove();
                    });

                    setTimeout(function(){

                        if(!$tbody.find('tr').length){

                            $postbox.remove();

                        }

                        if(!$('#acfe-wp-custom-fields tbody tr').length && !$('#acfe-acf-custom-fields tbody tr').length){

                            $bulk_actions.remove();

                        }

                        acfe_dev_meta_count();

                    }, 351);

                },
                success: function(response){

                    if(response !== '1'){

                        $tr.css({backgroundColor:''});
                        $tr.show();

                    }

                }
            });

        });

        /*
         * Screen preference for builk actions
         */
        $('.hide-postbox-tog').bind('click.postboxes', function(){

            var $el = $(this),
                boxId = $el.val();

            if(boxId !== 'acfe-wp-custom-fields' && boxId !== 'acfe-acf-custom-fields')
                return;

            if($el.prop('checked')){

                if(!$bulk_actions.is(':visible'))
                    $bulk_actions.show();

            }else{

                if((boxId === 'acfe-wp-custom-fields' && !$acf_meta.is(':visible')) || (boxId === 'acfe-acf-custom-fields' && !$wp_meta.is(':visible'))){

                    $bulk_actions.hide();

                }

            }

        });

    });
    
})(jQuery);
(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;
    
    /*
     * Drag & Drop
     */
    model.addSortable = function( self ){
        
        // bail early if max 1 row
        if( this.get('max') == 1 ) {
            return;
        }
        
        // add sortable
        this.$layoutsWrap().sortable({
            items: ' > .layout',
            handle: '> .acf-fc-layout-handle',
            forceHelperSize: false,     // Changed to false
            forcePlaceholderSize: true,
            tolerance: "pointer",       // Changed to pointer
            scroll: true,
            stop: function(event, ui) {
                self.render();
            },
            update: function(event, ui) {
                self.$input().trigger('change');
            }
        });
        
    };
    
    /*
     * Actions
     */
    model.acfeOneClick = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $clones = flexible.$clones();
        var $layout_name = $($clones[0]).data('layout');
        
        // Source
        var $layout_source = null;
        if($el.hasClass('acf-icon'))
            $layout_source = $el.closest('.layout');
        
        // Add
        var $layout_added = flexible.add({
            layout: $layout_name,
            before: $layout_source
        });
        
        // Hide native tooltip
        if($('.acf-fc-popup').length)
            $('.acf-fc-popup').hide();
        
    };
    
    model.acfeLayoutInit = function($layout){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $controls = $layout.find('> .acf-fc-layout-controls');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        
        // Placeholder
        var $placeholder = $layout.find('> .acfe-fc-placeholder');
        
        // Placeholder: Show
        $placeholder.removeClass('acf-hidden');
        
        // If no modal edition & opened: Hide Placeholder
        if(!flexible.has('acfeFlexibleModalEdition') && !flexible.isLayoutClosed($layout)){
            
            $placeholder.addClass('acf-hidden');
        
        }
        
        // Flexible has Preview
        if(flexible.isLayoutClosed($layout) && flexible.has('acfeFlexiblePreview') && !$placeholder.hasClass('-loading')){
            
            $placeholder.addClass('acfe-fc-preview -loading').find('> .acfe-flexible-placeholder').prepend('<span class="spinner"></span>');
            $placeholder.find('> .acfe-fc-overlay').addClass('-hover');
            
            // vars
			var $input = $layout.children('input');
			var prefix = $input.attr('name').replace('[acf_fc_layout]', '');
			
			// ajax data
			var ajaxData = {
				action: 	'acfe/flexible/layout_preview',
				field_key: 	flexible.get('key'),
				i: 			$layout.index(),
				layout:		$layout.data('layout'),
				value:		acf.serialize($layout, prefix)
			};
            
            acf.doAction('acfe/fields/flexible_content/before_preview',                                                                     flexible.$el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/name=' + flexible.get('name'),                                        flexible.$el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/key=' + flexible.get('key'),                                          flexible.$el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/name=' + flexible.get('name') + '&layout=' + $layout.data('layout'),  flexible.$el, $layout, ajaxData);
            acf.doAction('acfe/fields/flexible_content/before_preview/key=' + flexible.get('key') + '&layout=' + $layout.data('layout'),    flexible.$el, $layout, ajaxData);
			
			// ajax
			$.ajax({
		    	url: acf.get('ajaxurl'),
		    	data: acf.prepareForAjax(ajaxData),
				dataType: 'html',
				type: 'post',
				success: function(response){
                    
					if(response){
                        
						$placeholder.find('> .acfe-flexible-placeholder').html(response);
                        
					}else{
                        
                        $placeholder.removeClass('acfe-fc-preview');
                        
                    }
                    
                    acf.doAction('acfe/fields/flexible_content/preview',                                                                     response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/name=' + flexible.get('name'),                                        response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/key=' + flexible.get('key'),                                          response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/name=' + flexible.get('name') + '&layout=' + $layout.data('layout'),  response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/key=' + flexible.get('key') + '&layout=' + $layout.data('layout'),    response, flexible.$el, $layout, ajaxData);
                    
				},
                complete: function(){
                    
                    $placeholder.find('> .acfe-fc-overlay').removeClass('-hover');
                    $placeholder.removeClass('-loading').find('> .acfe-flexible-placeholder > .spinner').remove();
                    
                }
			});
            
        }
        
    };

    /*
     * WYSIWYG
     */
    var wysiwyg = acf.getFieldType('wysiwyg').prototype;
    wysiwyg.initialize = function(){

        // initializeEditor if no delay
        if( !this.has('id') && !this.$control().hasClass('delay') ) {
            this.initializeEditor();
        }

    };

    var acfeFlexibleDelayInit = function(editor){

        if(editor.has('id') || !editor.$el.is(':visible') || acfe.isFilterEnabled('acfeForceOpen'))
            return;

        var $wrap = editor.$control();

        if($wrap.hasClass('delay')){

            $wrap.removeClass('delay');
            $wrap.find('.acf-editor-toolbar').remove();

            // initialize
            editor.initializeEditor();

        }

    };

    acf.addAction('show_field/type=wysiwyg', acfeFlexibleDelayInit);
    acf.addAction('ready_field/type=wysiwyg', acfeFlexibleDelayInit);

    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        // Vars
        var $clones = flexible.$clones();
        var $layouts = flexible.$layouts();
        
        // Merge
        var $all_layouts = $.merge($layouts, $clones);
        
        // Do Actions
        $layouts.each(function(){
            
            var $layout = $(this);
            var $name = $layout.data('layout');
            
            acf.doAction('acfe/flexible/layouts', $layout, flexible);
            acf.doAction('acfe/flexible/layout/name=' + $name, $layout, flexible);
            
        });
        
        // ACFE: 1 layout available - OneClick
        if($clones.length === 1){
            
            // Remove native ACF Tooltip action
            flexible.removeEvents({'click [data-name="add-layout"]': 'onClickAdd'});
            
            // Add ACF Extended Modal action
            flexible.addEvents({'click [data-name="add-layout"]': 'acfeOneClick'});
        
        }
        
        flexible.addEvents({'click .acfe-fc-placeholder': 'onClickCollapse'});
        
        flexible.addEvents({'click .acfe-flexible-opened-actions > a': 'onClickCollapse'});
        
        // Flexible: Ajax
        if(flexible.has('acfeFlexibleAjax')){
            
            flexible.add = function(args){
                
                // Get Flexible
                var flexible = this;
                
                // defaults
                args = acf.parseArgs(args, {
                    layout: '',
                    before: false
                });
                
                // validate
                if( !this.allowAdd() ) {
                    return false;
                }

                // ajax
                $.ajax({
                    url: acf.get('ajaxurl'),
                    data: acf.prepareForAjax({
                        action: 	'acfe/flexible/models',
                        field_key: 	this.get('key'),
                        layout:		args.layout,
                    }),
                    dataType: 'html',
                    type: 'post',
                    beforeSend: function(){
                        $('body').addClass('-loading');
                    },
                    success: function(html){
                        if(html){
                            
                            var $layout = $(html);
                            var uniqid = acf.uniqid();
                            
                            var search = 'acf[' + flexible.get('key') + '][acfcloneindex]';
                            var replace = flexible.$control().find('> input[type=hidden]').attr('name') + '[' + uniqid + ']';
                            
                            // add row
                            var $el = acf.duplicate({
                                target: $layout,
                                search: search,
                                replace: replace,
                                append: flexible.proxy(function( $el, $el2 ){
                                    
                                    // append
                                    if( args.before ) {
                                        args.before.before( $el2 );
                                    } else {
                                        flexible.$layoutsWrap().append( $el2 );
                                    }
                                    
                                    // enable 
                                    acf.enable( $el2, flexible.cid );
                                    
                                    // render
                                    flexible.render();
                                })
                            });
                            
                            // Fix data-id
                            $el.attr('data-id', uniqid);
                            
                            // trigger change for validation errors
                            flexible.$input().trigger('change');
                            
                            // return
                            return $el;
                            
                        }
                    },
                    'complete': function(){
                        $('body').removeClass('-loading');
                    }
                });
                
            };
            
        }

    });
    
    acf.addAction('acfe/flexible/layouts', function($layout, flexible){
        
        // Layout Closed
        if(flexible.isLayoutClosed($layout)){
        
            // Placeholder
            $layout.find('> .acfe-fc-placeholder').removeClass('acf-hidden');

            if(flexible.has('acfeFlexibleOpen')){

                acfe.enableFilter('acfeForceOpen');

                flexible.openLayout($layout);

                acfe.disableFilter('acfeForceOpen');

            }
        
        }
        
    });
    
    acf.addAction('show', function($layout, type){
        
        if(type !== 'collapse' || !$layout.is('.layout'))
            return;
        
        var flexible = acf.getInstance($layout.closest('.acf-field-flexible-content'));
        
        // Hide Placeholder
        if(!flexible.has('acfeFlexibleModalEdition')){
    
            // Placeholder
            $layout.find('> .acfe-fc-placeholder').addClass('acf-hidden');
        
        }
        
    });
    
    acf.addAction('hide', function($layout, type){
        
        if(type !== 'collapse' || !$layout.is('.layout') || $layout.is('.acf-clone'))
            return;
        
        // Get Flexible
        var flexible = acf.getInstance($layout.closest('.acf-field-flexible-content'));
        
        // Remove Ajax Title
        if(flexible.has('acfeFlexibleRemoveAjaxTitle')){

            flexible.renderLayout = function($layout){};

        }
        
        // Preview Ajax
        flexible.acfeLayoutInit($layout);
        
    });
    
    acf.addAction('append', function($el){
        
        // Bail early if layout is not layout
        if(!$el.is('.layout'))
            return;
        
        // Get Flexible
        var flexible = acf.getInstance($el.closest('.acf-field-flexible-content'));
        
        // Open Layout
        if(!$el.is('.acfe-layout-duplicated')){
            
            // Modal Edition: Open
            if(flexible.has('acfeFlexibleModalEdition')){
                
                $el.find('> [data-action="acfe-flexible-modal-edit"]:first').trigger('click');
                
            }
            
            // Normal Edition: Open
            else{
                
                flexible.openLayout($el);
                
            }
            
        }
        
        flexible.acfeLayoutInit($el);
        
        var $modal = flexible.$el.closest('.acfe-modal.-open');
        
        if($modal.length){
        
            // Scroll to new layout
            $modal.find('> .acfe-modal-wrapper > .acfe-modal-content').animate({
                scrollTop: parseInt($el.offset().top) - 200
            }, 200);
        
        }else{

            var acfVersion = parseFloat(acf.get('acf_version'));

            if(acfVersion < 5.9){

                // Scroll to new layout
                $('html, body').animate({
                    scrollTop: parseInt($el.offset().top) - 200
                }, 200);

            }

        }
        
    });
    
    /*
     * Field Error
     */
    acf.addAction('invalid_field', function(field){
        
        field.$el.parents('.layout').addClass('acfe-flexible-modal-edit-error');
        
    });
    
    /*
     * Field Valid
     */
    acf.addAction('valid_field', function(field){
        
        field.$el.parents('.layout').each(function(){
            
            var $layout = $(this);
            
            if(!$layout.find('.acf-error').length)
                $layout.removeClass('acfe-flexible-modal-edit-error');
            
        });
        
    });
    
})(jQuery);
(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;

    /*
     * Actions
     */
    model.events['click .acf-fc-layout-handle'] = 'acfeEditLayoutTitleToggleHandle';
    model.acfeEditLayoutTitleToggleHandle = function(e, $el){
        
        var flexible = this;
        
        // Title Edition
        if(!flexible.has('acfeFlexibleTitleEdition'))
            return;
        
        // Vars
        var $layout = $el.closest('.layout');
        
        if($layout.hasClass('acfe-flexible-title-edition')){
            
            $layout.find('> .acf-fc-layout-handle > .acfe-layout-title > input.acfe-flexible-control-title').trigger('blur');
            
        }
        
    }
    
    model.events['click .acfe-layout-title-text'] = 'acfeEditLayoutTitle';
    model.acfeEditLayoutTitle = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Title Edition
        if(!flexible.has('acfeFlexibleTitleEdition'))
            return;
        
        // Stop propagation
        e.stopPropagation();
        
        // Toggle
        flexible.acfeEditLayoutTitleToggle(e, $el);
        
    }
    
    model.events['blur input.acfe-flexible-control-title'] = 'acfeEditLayoutTitleToggle';
    model.acfeEditLayoutTitleToggle = function(e, $el){
        
        var flexible = this;
        
        // Vars
        var $layout = $el.closest('.layout');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        var $title = $handle.find('.acfe-layout-title');
        
        if($layout.hasClass('acfe-flexible-title-edition')){
            
            var $input = $title.find('> input[data-acfe-flexible-control-title-input]');
            
            if($input.val() === '')
                $input.val($input.attr('placeholder')).trigger('input');
            
            $layout.removeClass('acfe-flexible-title-edition');
            
            $input.insertAfter($handle);
            
        }
        
        else{
            
            var $input = $layout.find('> input[data-acfe-flexible-control-title-input]');
            
            var $input = $input.appendTo($title);
            
            $layout.addClass('acfe-flexible-title-edition');
            $input.focus().attr('size', $input.val().length);
            
        }
        
    }
    
    // Layout: Edit Title
    model.events['click input.acfe-flexible-control-title'] = 'acfeEditLayoutTitlePropagation';
    model.acfeEditLayoutTitlePropagation = function(e, $el){
        
        e.stopPropagation();
        
    }
    
    // Layout: Edit Title Input
    model.events['input [data-acfe-flexible-control-title-input]'] = 'acfeEditLayoutTitleInput';
    model.acfeEditLayoutTitleInput = function(e, $el){
        
        // Vars
        var $layout = $el.closest('.layout');
        var $title = $layout.find('> .acf-fc-layout-handle .acfe-layout-title .acfe-layout-title-text');
        
        var val = $el.val();
        
        $el.attr('size', val.length);
        
        $title.html(val);
        
    }
    
    // Layout: Edit Title Input Enter
    model.events['keypress [data-acfe-flexible-control-title-input]'] = 'acfeEditLayoutTitleInputEnter';
    model.acfeEditLayoutTitleInputEnter = function(e, $el){
        
        // Enter Key
        if(e.keyCode !== 13)
            return;
        
        e.preventDefault();
        $el.blur();
        
    }
    
    // Layout: Settings
    model.events['click [data-acfe-flexible-settings]'] = 'acfeLayoutSettings';
    model.acfeLayoutSettings = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $layout = $el.closest('.layout');
        
        // Modal data
        var $modal = $layout.find('> .acfe-modal.-settings');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        
        var $layout_order = $handle.find('> .acf-fc-layout-order').outerHTML();
        var $layout_title = $handle.find('.acfe-layout-title-text').text();
        
        // Open modal
        acfe.modal.open($modal, {
            title: $layout_order + ' ' + $layout_title,
            footer: acf.__('Close'),
            onOpen: function(){
                
            },
            onClose: function(){
                
                if(flexible.has('acfeFlexiblePreview')){
                    
                    flexible.closeLayout($layout);
                    
                }
                
            }
        });
        
    }

    /*
     * Layout: Toggle Action
     */
    model.events['click [data-acfe-flexible-control-toggle]'] = 'acfeLayoutToggle';
    model.acfeLayoutToggle = function(e, $el){

        // Get Flexible
        var flexible = this;

        // Vars
        var $layout = $el.closest('.layout');

        var $field = $layout.find('> .acfe-flexible-layout-toggle');

        if(!$field.length)
            return;

        if($field.val() === '1'){

            $layout.removeClass('acfe-flexible-layout-hidden');
            $field.val('');

        }else{

            $layout.addClass('acfe-flexible-layout-hidden');
            $field.val('1');

        }

    }

    /*
     * Layout: Toggle Spawn
     */
    acf.addAction('acfe/flexible/layouts', function($layout, flexible){

        if(!flexible.has('acfeFlexibleToggle'))
            return;

        // Layout Closed
        var $field = $layout.find('> .acfe-flexible-layout-toggle');

        if(!$field.length)
            return;

        if($field.val() === '1'){

            $layout.addClass('acfe-flexible-layout-hidden');

        }else{

            $layout.removeClass('acfe-flexible-layout-hidden');

        }

    });
    
    // Layout: Clone
    model.events['click [data-acfe-flexible-control-clone]'] = 'acfeCloneLayout';
    model.acfeCloneLayout = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $layout = $el.closest('.layout');
        var layout_name = $layout.data('layout');
        
        // Popup min/max
        var $popup = $(flexible.$popup().html());
        var $layouts = flexible.$layouts();

        var countLayouts = function(name){
            return $layouts.filter(function(){
                return $(this).data('layout') === name;
            }).length;
        };
        
         // vars
        var $a = $popup.find('[data-layout="' + layout_name + '"]');
        var min = $a.data('min') || 0;
        var max = $a.data('max') || 0;
        var count = countLayouts(layout_name);
        
        // max
        if(max && count >= max){
            
            $el.addClass('disabled');
            return false;
            
        }else{
            
            $el.removeClass('disabled');
            
        }
        
        // Fix inputs
        flexible.acfeFixInputs($layout);
        
        var $_layout = $layout.clone();
        
        // Clean Layout
        flexible.acfeCleanLayouts($_layout);
        
        var parent = $el.closest('.acf-flexible-content').find('> input[type=hidden]').attr('name');
        
        // Clone
        var $layout_added = flexible.acfeDuplicate({
            layout: $_layout,
            before: $layout,
            parent: parent
        });
        
    }
    
    // Layout: Copy
    model.events['click [data-acfe-flexible-control-copy]'] = 'acfeCopyLayout';
    model.acfeCopyLayout = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $layout = $el.closest('.layout').clone();
        var source = flexible.$control().find('> input[type=hidden]').attr('name');
        
        // Fix inputs
        flexible.acfeFixInputs($layout);
        
        // Clean layout
        flexible.acfeCleanLayouts($layout);
        
        // Get layout data
        var data = JSON.stringify({
            source: source,
            layouts: $layout[0].outerHTML
        });
        
        // Append Temp Input
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:rect(0,0,0,0);position:absolute;" value="" />').appendTo($('body'));
        $input.attr('value', data).select();

        // Command: Copy
        if(document.execCommand('copy'))
            alert('Layout has been transferred to your clipboard');
            
        // Prompt
        else
            prompt('Copy the following layout data to your clipboard', data);
        
        // Remove the temp input
        $input.remove();
        
    }
    
    // Flexible: Copy Layouts
    model.acfeCopyLayouts = function(){
        
        // Get Flexible
        var flexible = this;
        
        // Get layouts
        var $layouts = flexible.$layoutsWrap().clone();
        var source = flexible.$control().find('> input[type=hidden]').attr('name');
        
        // Fix inputs
        flexible.acfeFixInputs($layouts);
        
        // Clean layout
        flexible.acfeCleanLayouts($layouts);
        
        // Get layouts data
        var data = JSON.stringify({
            source: source,
            layouts: $layouts.html()
        });
        
        // Append Temp Input
        var $input = $('<input type="text" style="clip:rect(0,0,0,0);clip-path:rect(0,0,0,0);position:absolute;" value="" />').appendTo(flexible.$el);
        $input.attr('value', data).select();
        
        // Command: Copy
        if(document.execCommand('copy'))
            alert('Layouts have been transferred to your clipboard');
            
        // Prompt
        else
            prompt('Copy the following layouts data to your clipboard', data);
        
        $input.remove();
        
    }
    
    // Flexible: Paste Layouts
    model.acfePasteLayouts = function(){
        
        // Get Flexible
        var flexible = this;
        
        var paste = prompt('Paste layouts data in the following field');
        
        // No input
        if(paste == null || paste === '')
            return;
        
        try{
            
            // Paste HTML
            var data = JSON.parse(paste);
            var source = data.source;
            var $html = $(data.layouts);
            
            // Parsed layouts
            var $html_layouts = $html.closest('[data-layout]');
            
            if(!$html_layouts.length)
                return alert('No layouts data available');
            
            // Popup min/max
            var $popup = $(flexible.$popup().html());
            var $layouts = flexible.$layouts();
            
            var countLayouts = function(name){
                return $layouts.filter(function(){
                    return $(this).data('layout') === name;
                }).length;
            };
            
            // init
            var validated_layouts = [];
            
            // Each first level layouts
            $html_layouts.each(function(){
                
                var $this = $(this);
                var layout_name = $this.data('layout');
                
                // vars
                var $a = $popup.find('[data-layout="' + layout_name + '"]');
                var min = $a.data('min') || 0;
                var max = $a.data('max') || 0;
                var count = countLayouts(layout_name);
                
                // max
                if(max && count >= max)
                    return;
                
                // Validate layout against available layouts
                var get_clone_layout = flexible.$clone($this.attr('data-layout'));
                
                // Layout is invalid
                if(!get_clone_layout.length)
                    return;
                
                // Add validated layout
                validated_layouts.push($this);
                
            });
            
            // Nothing to add
            if(!validated_layouts.length)
                return alert('No layouts could be pasted');
            
            // Add layouts
            $.each(validated_layouts, function(){
                
                var $layout = $(this);
                var search = source + '[' + $layout.attr('data-id') + ']';
                var target = flexible.$control().find('> input[type=hidden]').attr('name');
                
                flexible.acfeDuplicate({
                    layout: $layout,
                    before: false,
                    search: search,
                    parent: target
                });
                
            });
            
        }catch(e){
            
            console.log(e);
            alert('Invalid data');
            
        }
        
    }
    
    // Flexible: Dropdown
    model.events['click [data-name="acfe-flexible-control-button"]'] = 'acfeControl';
    model.acfeControl = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $dropdown = $el.next('.tmpl-acfe-flexible-control-popup').html();
        
        // Init Popup
        var Popup = acf.models.TooltipConfirm.extend({
            render: function(){
                this.html(this.get('text'));
                this.$el.addClass('acf-fc-popup');
            }
        });
        
        // New Popup
        var popup = new Popup({
            target: $el,
            targetConfirm: false,
            text: $dropdown,
            context: flexible,
            confirm: function(e, $el){
                
                if($el.attr('data-acfe-flexible-control-action') === 'paste')
                    flexible.acfePasteLayouts();
                
                else if($el.attr('data-acfe-flexible-control-action') === 'copy')
                    flexible.acfeCopyLayouts();
                
            }
        });
        
        popup.on('click', 'a', 'onConfirm');
        
    }
    
    // Flexible: Duplicate
    model.acfeDuplicate = function(args){
        
        // Arguments
        args = acf.parseArgs(args, {
            layout: '',
            before: false,
            parent: false,
            search: '',
            replace: '',
        });
        
        // Validate
        if(!this.allowAdd())
            return false;
        
        var uniqid = acf.uniqid();
        
        if(args.parent){
            
            if(!args.search){
                
                args.search = args.parent + '[' + args.layout.attr('data-id') + ']';
                
            }
            
            args.replace = args.parent + '[' + uniqid + ']';
            
        }

        var duplicate_args = {
            target: args.layout,
            search: args.search,
            replace: args.replace,
            append: this.proxy(function($el, $el2){

                // Add class to duplicated layout
                $el2.addClass('acfe-layout-duplicated');

                // Reset UniqID
                $el2.attr('data-id', uniqid);

                // append before
                if(args.before){

                    // Fix clone: Use after() instead of native before()
                    args.before.after($el2);

                }

                // append end
                else{

                    this.$layoutsWrap().append($el2);

                }

                // enable
                acf.enable($el2, this.cid);

                // render
                this.render();

            })
        }

        var acfVersion = parseFloat(acf.get('acf_version'));

        if(acfVersion < 5.9){

            // Add row
            var $el = acf.duplicate(duplicate_args);

        // Hotfix for ACF Pro 5.9
        }else{

            // Add row
            var $el = model.acfeNewAcfDuplicate(duplicate_args);

        }
        
        // trigger change for validation errors
        this.$input().trigger('change');

        // Fix tabs conditionally hidden
        var tabs = acf.getFields({
            type: 'tab',
            parent: $el,
        });

        if(tabs.length){

            $.each(tabs, function(){

                if(this.$el.hasClass('acf-hidden')){

                    this.tab.$el.addClass('acf-hidden');

                }

            });

        }

        
        // return
        return $el;
        
    }

    /*
     * Based on acf.duplicate (5.9)
     *
     * doAction('duplicate) has been commented out
     * This fix an issue with the WYSIWYG editor field during copy/paste since ACF 5.9
     */
    model.acfeNewAcfDuplicate = function( args ){

        // allow jQuery
        if( args instanceof jQuery ) {
            args = {
                target: args
            };
        }

        // defaults
        args = acf.parseArgs(args, {
            target: false,
            search: '',
            replace: '',
            rename: true,
            before: function( $el ){},
            after: function( $el, $el2 ){},
            append: function( $el, $el2 ){
                $el.after( $el2 );
            }
        });

        // compatibility
        args.target = args.target || args.$el;

        // vars
        var $el = args.target;

        // search
        args.search = args.search || $el.attr('data-id');
        args.replace = args.replace || acf.uniqid();

        // before
        // - allow acf to modify DOM
        // - fixes bug where select field option is not selected
        args.before( $el );
        acf.doAction('before_duplicate', $el);

        // clone
        var $el2 = $el.clone();

        // rename
        if( args.rename ) {
            acf.rename({
                target:		$el2,
                search:		args.search,
                replace:	args.replace,
                replacer:	( typeof args.rename === 'function' ? args.rename : null )
            });
        }

        // remove classes
        $el2.removeClass('acf-clone');
        $el2.find('.ui-sortable').removeClass('ui-sortable');

        // after
        // - allow acf to modify DOM
        args.after( $el, $el2 );
        acf.doAction('after_duplicate', $el, $el2 );

        // append
        args.append( $el, $el2 );

        /**
         * Fires after an element has been duplicated and appended to the DOM.
         *
         * @date	30/10/19
         * @since	5.8.7
         *
         * @param	jQuery $el The original element.
         * @param	jQuery $el2 The duplicated element.
         */
        //acf.doAction('duplicate', $el, $el2 );

        // append
        acf.doAction('append', $el2);

        // return
        return $el2;
    };
    
    // Flexible: Fix Inputs
    model.acfeFixInputs = function($layout){
        
        $layout.find('input').each(function(){
            
            $(this).attr('value', this.value);
            
        });
        
        $layout.find('textarea').each(function(){
            
            $(this).html(this.value);
            
        });
        
        $layout.find('input:radio,input:checkbox').each(function() {
            
            if(this.checked)
                $(this).attr('checked', 'checked');
            
            else
                $(this).attr('checked', false);
            
        });
        
        $layout.find('option').each(function(){
            
            if(this.selected)
                $(this).attr('selected', 'selected');
                
            else
                $(this).attr('selected', false);
            
        });
        
    }
    
    // Flexible: Clean Layout
    model.acfeCleanLayouts = function($layout){
        
        // Clean WP Editor
        $layout.find('.acf-editor-wrap').each(function(){
            
            var $input = $(this);
            
            $input.find('.wp-editor-container div').remove();
            $input.find('.wp-editor-container textarea').css('display', '');
            
        });
        
        // Clean Date
        $layout.find('.acf-date-picker').each(function(){
            
            var $input = $(this);
            
            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
            
        });
        
        // Clean Time
        $layout.find('.acf-time-picker').each(function(){
            
            var $input = $(this);
            
            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
            
        });
        
        // Clean DateTime
        $layout.find('.acf-date-time-picker').each(function(){
            
            var $input = $(this);
            
            $input.find('input.input').removeClass('hasDatepicker').removeAttr('id');
            
        });

        // Clean Code Editor
        $layout.find('.acfe-field-code-editor').each(function(){

            var $input = $(this);

            $input.find('.CodeMirror').remove();

        });
        
        // Clean Color Picker
        $layout.find('.acf-color-picker').each(function(){
            
            var $input = $(this);
            
            var $color_picker = $input.find('> input');
            var $color_picker_proxy = $input.find('.wp-picker-container input.wp-color-picker').clone();
            
            $color_picker.after($color_picker_proxy);
            
            $input.find('.wp-picker-container').remove();
            
        });
        
        // Clean Post Object
        $layout.find('.acf-field-post-object').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
            
        });
        
        // Clean Page Link
        $layout.find('.acf-field-page-link').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
            
        });
        
        // Clean Select2
        $layout.find('.acf-field-select').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden').removeClass();
            
        });
        
        // Clean FontAwesome
        $layout.find('.acf-field-font-awesome').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-input span').remove();
            
            $input.find('> .acf-input select').removeAttr('tabindex aria-hidden');
            
        });


        // Clean Tab
        $layout.find('.acf-tab-wrap').each(function(){
            
            var $wrap = $(this);
            
            var $content = $wrap.closest('.acf-fields');
            
            var tabs = [];
            $.each($wrap.find('li a'), function(){
                
                tabs.push($(this));
                
            });
            
            $content.find('> .acf-field-tab').each(function(){
                
                $current_tab = $(this);
                
                $.each(tabs, function(){
                    
                    var $this = $(this);
                    
                    if($this.attr('data-key') !== $current_tab.attr('data-key'))
                        return;
                    
                    $current_tab.find('> .acf-input').append($this);
                    
                });
                
            });
            
            $wrap.remove();
            
        });
        
        // Clean Accordion
        $layout.find('.acf-field-accordion').each(function(){
            
            var $input = $(this);
            
            $input.find('> .acf-accordion-title > .acf-accordion-icon').remove();
            
            // Append virtual endpoint after each accordion
            $input.after('<div class="acf-field acf-field-accordion" data-type="accordion"><div class="acf-input"><div class="acf-fields" data-endpoint="1"></div></div></div>');
            
        });
        
    }
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        // ACFE: Lock
        if(flexible.has('acfeFlexibleLock')){
            
            flexible.removeEvents({'mouseover': 'onHover'});
            
        }
        
    });
    
})(jQuery);
(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;
    
    /*
     * Actions
     */
    model.acfeModalSelect = function(e, $el){
        
        // Get Flexible
        var flexible = this;
        
        // Validate
        if(!flexible.validateAdd())
            return false;
        
        // Layout
        var $layout_source = null;
        
        if($el.hasClass('acf-icon'))
            $layout_source = $el.closest('.layout');
        
        // Get Available Layouts
        var layouts = flexible.getPopupHTML();
        
        // Init Categories
        var categories = {
            array: [],
            html: ''
        };

        function SearchArray(element, array){

            var len = array.length,
                str = element.toString().toLowerCase();

            for(var i = 0; i < len; i++){
                if(array[i].toLowerCase() === str){
                    return i;
                }
            }

            return -1;

        }
        
        // Get Categories
        $(layouts).find('li a span').each(function(){
            
            var $link = $(this);

            if(!$link.data('acfe-flexible-category'))
                return true;

            var category = $link.data('acfe-flexible-category');

            $.each(category, function(i, c){

                if(SearchArray(c, categories.array) !== -1)
                    return true;

                categories.array.push(c);

            });
            
        });
        
        // Categories HTML
        if(categories.array.length){
        
            categories.array.sort();
                
            categories.html += '<h2 class="acfe-flexible-categories nav-tab-wrapper">';
            
            categories.html += '<a href="#" data-acfe-flexible-category="acfe-all" class="nav-tab nav-tab-active"><span class="dashicons dashicons-menu"></span></a>';
            
            $(categories.array).each(function(k, category){
                
                categories.html += '<a href="#" data-acfe-flexible-category="' + category + '" class="nav-tab">' + category + '</a>';
                
            });
            
            categories.html += '</h2>';
        
        }
        
        // Modal Title
        var $modal_title = 'Add Row';
        
        if(flexible.has('acfeFlexibleModalTitle'))
            $modal_title = flexible.get('acfeFlexibleModalTitle');
        
        // Create Modal
        var $modal = $('' + 
            '<div class="acfe-modal">' + 
            
                categories.html + 
                '<div class="acfe-flex-container">' + 
                    layouts +        
                '</div>' +
                
            '</div>'
        
        ).appendTo('body');
        
        // Open Modal
        var $modal = acfe.modal.open($modal, {
            title: $modal_title,
            size: 'full',
            destroy: true
        });
        
        // Modal: Columns
        if(flexible.has('acfeFlexibleModalCol'))
            $modal.find('.acfe-modal-content .acfe-flex-container').addClass('acfe-col-' + flexible.get('acfeFlexibleModalCol'));
        
        // Modal: ACF autofocus fix
        $modal.find('li:first-of-type a').blur();
        
        // count layouts
        var $layouts = flexible.$layouts();
        var countLayouts = function(name){
            
            return $layouts.filter(function(){
                return $(this).data('layout') === name;
            }).length;
            
        };
        
        $modal.find('a[data-layout]').each(function(){
            
            // vars
            var $a = $(this);
            var min = $a.data('min') || 0;
            var max = $a.data('max') || 0;
            var name = $a.data('layout') || '';
            var count = countLayouts( name );
            
            // max
            if(max && count >= max){
                $a.addClass('disabled');
                return;
            }
            
            // min
            if(min && count < min){
                
                // vars
                var required = min - count;
                var title = acf.__('{required} {label} {identifier} required (min {min})');
                var identifier = acf._n('layout', 'layouts', required);
                                    
                // translate
                title = title.replace('{required}', required);
                title = title.replace('{label}', name); // 5.5.0
                title = title.replace('{identifier}', identifier);
                title = title.replace('{min}', min);
                
                // badge
                $a.append('<span class="badge" title="' + title + '">' + required + '</span>');
                
            }
        
        });
        
        // Modal: Click Categories
        $modal.find('.acfe-flexible-categories a').click(function(e){
            
            e.preventDefault();
            
            var $link = $(this);
            
            $link.closest('.acfe-flexible-categories').find('a').removeClass('nav-tab-active');
            $link.addClass('nav-tab-active');
            
            var selected_category = $link.data('acfe-flexible-category');
            
            $modal.find('a[data-layout] span').each(function(){
                
                // Get span
                var $span = $(this);
                
                // Show All
                $span.closest('li').show();
                
                var category = $span.data('acfe-flexible-category');
                
                // Specific category
                if(selected_category !== 'acfe-all'){
                    
                    // Hide All
                    $span.closest('li').hide();

                    $.each(category, function(i, c){

                        if(selected_category.toLowerCase() === c.toLowerCase()){

                            $span.closest('li').show();

                            return false;

                        }

                    });
                    
                }
                
            });
            
        });
        
        // Modal: Click Add Layout
        $modal.on('click', 'a[data-layout]', function(e){
            
            e.preventDefault();
            
            // Close modal
            acfe.modal.close(true);
            
            // Add layout
            var $layout_added = flexible.add({
                layout: $(this).data('layout'),
                before: $layout_source
            });
            
        });
        
    }
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        if(!flexible.has('acfeFlexibleModal'))
            return;
        
        // Vars
        var $clones = flexible.$clones();
        
        if($clones.length <= 1)
            return;
        
        // Remove native ACF Tooltip action
        flexible.removeEvents({'click [data-name="add-layout"]': 'onClickAdd'});
        
        // Add ACF Extended Modal action
        flexible.addEvents({'click [data-name="add-layout"]': 'acfeModalSelect'});

    });
    
})(jQuery);
(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /*
     * Init
     */
    var flexible = acf.getFieldType('flexible_content');
    var model = flexible.prototype;
    
    /*
     * Actions
     */
    model.events['click [data-action="acfe-flexible-modal-edit"]'] = 'acfeModalEdit';
    model.acfeModalEdit = function(e, $el){
        
        var flexible = this;
        
        // Layout
        var $layout = $el.closest('.layout');
        
        // Modal data
        var $modal = $layout.find('> .acfe-modal.-fields');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        
        var $layout_order = $handle.find('> .acf-fc-layout-order').outerHTML();
        var $layout_title = $handle.find('.acfe-layout-title-text').text();
        
        var close = false;
        if(flexible.has('acfeFlexibleCloseButton')){
            
            close = acf.__('Close');
        
        }
        
        // Open modal
        acfe.modal.open($modal, {
            title: $layout_order + ' ' + $layout_title,
            footer: close,
            onOpen: function(){
                
                flexible.openLayout($layout);
                
            },
            onClose: function(){
                
                flexible.closeLayout($layout);
                
            }
        });
        
    };
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        if(flexible.has('acfeFlexibleModalEdition') && (flexible.has('acfeFlexiblePlaceholder') || flexible.has('acfeFlexiblePreview'))){
            
            // Remove Collapse Action
            flexible.removeEvents({'click [data-name="collapse-layout"]': 'onClickCollapse'});
            
            // Remove placeholder Collapse Action
            flexible.removeEvents({'click .acfe-fc-placeholder': 'onClickCollapse'});
            
        }
        
    });
    
})(jQuery);
function acfe_recaptcha(){
    
    (function($){
        
        if(typeof acf === 'undefined')
            return;
        
        /**
         * Field: reCaptcha (render)
         */
        $.each(acf.getFields({type: 'acfe_recaptcha'}), function(i, field){
            
            field.render();
            
        });
    
    })(jQuery);
    
}

(function($){
    
    if(typeof acf === 'undefined')
        return;

    /**
     * Field: reCaptcha
     */
    var reCaptcha = acf.Field.extend({

        type: 'acfe_recaptcha',

        actions: {
            'validation_failure' : 'validationFailure'
        },

        $control: function(){
            return this.$('.acfe-field-recaptcha');
        },

        $input: function(){
            return this.$('input[type="hidden"]');
        },

        $selector: function(){
            return this.$control().find('> div');
        },

        selector: function(){
            return this.$selector()[0];
        },

        version: function(){
            return this.get('version');
        },

        render: function(){

            var field = this;

            if(this.version() === 'v2'){

                this.recaptcha = grecaptcha.render(field.selector(), {
                    'sitekey':  field.$control().data('site-key'),
                    'theme':    field.$control().data('theme'),
                    'size':     field.$control().data('size'),


                    'callback': function(response){

                        field.$input().val(response).change();
                        field.$input().closest('.acf-input').find('> .acf-notice.-error').hide();

                    },

                    'error-callback': function(){

                        field.$input().val('error').change();

                    },

                    'expired-callback': function(){

                        field.$input().val('expired').change();

                    }
                });

            }

            else if(this.version() === 'v3'){

                grecaptcha.ready(function(){
                    grecaptcha.execute(field.$control().data('site-key'), {action: 'homepage'}).then(function(response){

                        field.$input().val(response).change();
                        field.$input().closest('.acf-input').find('> .acf-notice.-error').hide();

                    });
                });

            }

        },

        validationFailure: function($form){

            if(this.version() === 'v2'){

                grecaptcha.reset(this.recaptcha);

            }

        }

    });

    acf.registerFieldType(reCaptcha);
    
    /**
     * Field: Code Editor
     */
    var CodeEditor = acf.Field.extend({
        
        wait: 'ready',
        
        type: 'acfe_code_editor',
        
        events: {
			'showField': 'onShow',
		},
        
        $control: function(){
            
            return this.$el.find('> .acf-input > .acf-input-wrap');
            
        },
        
        $input: function(){
            
            return this.$el.find('> .acf-input > .acf-input-wrap > textarea');
            
        },
        
        input: function(){
            
            return this.$input()[0];
            
        },
        
        rows: function(){
            
            return this.$input().attr('rows');
            
        },
        
        initialize: function(){
            
            this.rows = this.$control().data('rows');
            this.max_rows = this.$control().data('max-rows');
            
            this.mode = this.$control().data('mode');
            this.lines = this.$control().data('lines');
            this.indentUnit = this.$control().data('indent-unit');

            var codeEditor = [];

            // Default WP settings
            var wpCodeMirror = wp.codeEditor.defaultSettings.codemirror;

            // Field settings
            var CodeMirror = {
                lineNumbers: this.lines,
                lineWrapping: true,
                styleActiveLine: false,
                continueComments: true,
                indentUnit: this.indentUnit,
                tabSize: 1,
                indentWithTabs: true,
                mode: this.mode,
                extraKeys: {
                    Tab: function(cm){
                        cm.execCommand("indentMore")
                    },
                    "Shift-Tab": function(cm){
                        cm.execCommand("indentLess")
                    },
                },
            };

            // Merge settings
            var codeMirror = jQuery.extend(wpCodeMirror, CodeMirror);

            // Push CodeMirror settings to codemirror property
            codeEditor.codemirror = codeMirror;

            // Init WP Code Editor
            this.editor = wp.codeEditor.initialize(this.input(), codeEditor);
            
            if(this.rows || this.max_rows){
                
                if(this.rows){
                    
                    this.editor.codemirror.getScrollerElement().style.minHeight = this.rows * 18.5 + 'px';
                    
                }
                
                if(this.max_rows){
                    
                    this.editor.codemirror.getScrollerElement().style.maxHeight = this.max_rows * 18.5 + 'px';
                    
                }
                
                this.editor.codemirror.refresh();
                
            }
            
            field = this;
            
            this.editor.codemirror.on('change', function(){
                
                field.editor.codemirror.save();
                field.$input().change();
                
            });
            
        },
        
        onShow: function(){
            
            if(this.editor.codemirror){
                
                this.editor.codemirror.refresh();
                
            }
            
        },
        
    });

    acf.registerFieldType(CodeEditor);
    
    acf.registerConditionForFieldType('equalTo',        'acfe_code_editor');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_code_editor');
    acf.registerConditionForFieldType('patternMatch',   'acfe_code_editor');
    acf.registerConditionForFieldType('contains',       'acfe_code_editor');
    acf.registerConditionForFieldType('hasValue',       'acfe_code_editor');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_code_editor');

    /**
     * Field Group Admin: Code Editor
     * Fix duplicate action
     */
    acf.addAction('append_field_object', function(field){

        if(field.get('type') !== 'acfe_code_editor')
            return;

        field.$setting('default_value').find('> .acf-input > .acf-input-wrap > .CodeMirror:last').remove();
        field.$setting('placeholder').find('> .acf-input > .acf-input-wrap > .CodeMirror:last').remove();

    });
    
    /*
    var preCodeMirror = new acf.Model({
        codemirror: false,
        data: {
            type: 'pre',
            mode: 'htmlmixed',
            codemirror: false,
        },
        actions: {
            'new_field': 'new_field',
        },
        new_field: function(field){
            
            var self = this;
            
            field.$('pre[data-codemirror]:visible, code[data-codemirror]:visible').each(function(){
                
                self.setup(this);
                
            });
            
        },
        setup: function(el){
            
            this.$el = $(el);
            
            this.set('type', this.$el.prop('tagName').toLowerCase());
            this.set('mode', this.$el.attr('data-codemirror'));
            
            if(this.get('mode') === 'php'){
                
                this.set('mode', 'application/x-httpd-php');
                
            }else if(this.get('mode') === 'php-plain'){
                
                this.set('mode', 'text/x-php');
                
            }else if(this.get('mode') === 'html'){
                
                this.set('mode', 'text/html');
                
            }else if(this.get('mode') === 'javascript'){
                
                this.set('mode', 'javascript');
                
            }else if(this.get('mode') === 'css'){
                
                this.set('mode', 'css');
                
            }
            
            this.render();
            
            this.getClosestField();
            
        },
        initialize: function(){
            // ...
        },
        render: function(){
            
            var $code = this.$el.html();
            var $div = $('<div class="' + this.get('type') + '-codemirror" />').insertAfter(this.$el);
            var $unescaped = this.$el.html($code).text();
            
            this.codemirror = wp.CodeMirror($div[0], {
                value: $unescaped,
                mode: this.get('mode'),
                lineNumbers: false,
                lineWrapping: false,
                styleActiveLine: false,
                continueComments: true,
                indentUnit: 4,
                tabSize: 1,
                readOnly: true,
            });
            
            this.$el.remove();
            
        },
        refresh: function(){
            
            if(this.codemirror)
                this.codemirror.refresh();
            
        },
        getClosestField: function(){
            
            var self = this;
            
            field = acf.getClosestField(this.$el);
            
            if(field){
                
                field.on('showField', function(){
                    
                    self.refresh();
                    
                });
                
            }
            
        }
        
    });
    */
    
    /**
     * Field: Textarea
     */
    var Textarea = acf.Field.extend({
        
        type: 'textarea',
        
        events: {
            'keydown textarea': 'onInput',
        },
        
        onInput: function(e, $el){
            
            if(!this.has('acfeTextareaCode'))
                return;
            
            if(e.keyCode !== 9)
                return;
            
            e.preventDefault();
            
            var input = this.$el.find('textarea')[0];

            var s = input.selectionStart;

            this.$el.find('textarea').val(function(i, v){

                return v.substring(0, s) + "    " + v.substring(input.selectionEnd)

            });

            input.selectionEnd = s + 4;
            
        },
        
    });

    acf.registerFieldType(Textarea);
    
    /**
     * Field: Slug
     */
    var ACFE_Slug = acf.Field.extend({
        
        type: 'acfe_slug',
        
        events: {
            'input input': 'onInput',
            'focusout input': 'onFocusOut',
        },
        
        onInput: function(e, $el){
            
            $el.val($el.val().toLowerCase()
            .replace(/\s+/g, '-')       // Replace spaces with -
            .replace(/[^\w\-]+/g, '')   // Remove all non-word chars
            .replace(/\-\-+/g, '-')     // Replace multiple - with single -
            .replace(/\_\_+/g, '_')     // Replace multiple _ with single _
            .replace(/^-+/, ''));       // Trim - from start of text
            
        },
        
        onFocusOut: function(e, $el){
            
            $el.val($el.val().toLowerCase()
            .replace(/-+$/, '')         // Trim - from end of text
            .replace(/_+$/, ''));       // Trim _ from end of text
            
        },
        
    });

    acf.registerFieldType(ACFE_Slug);
    
    acf.registerConditionForFieldType('equalTo',        'acfe_slug');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_slug');
    acf.registerConditionForFieldType('patternMatch',   'acfe_slug');
    acf.registerConditionForFieldType('contains',       'acfe_slug');
    acf.registerConditionForFieldType('hasValue',       'acfe_slug');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_slug');
    
    /**
     * Field: Button
     */
    var ACFE_Button = acf.Field.extend({
        
        type: 'acfe_button',
        
        events: {
            'click input': 'onClick',
            'click button': 'onClick',
        },
        
        $input: function(){
            
            if(this.$('input').length){
                
                return this.$('input');
                
            }else if(this.$('button').length){
                
                return this.$('button');
                
            }
            
        },
        
        initialize: function(){
            
            // vars
			var $button = this.$input();
			
			// inherit data
			this.inherit($button);
            
        },
        
        onClick: function(e, $el){
            
            if(this.get('ajax')){
                
                e.preventDefault();

                // serialize form data
                var data = {
                    action: 'acfe/fields/button',
                    field_name: this.get('name'),
                    field_key: this.get('key')
                };
                
                // Deprecated
                acf.doAction('acfe/fields/button/before_ajax',                      this.$el, data);

                // Actions
                acf.doAction('acfe/fields/button/before',                           this.$el, data);
                acf.doAction('acfe/fields/button/before/key=' + this.get('key'),    this.$el, data);
                acf.doAction('acfe/fields/button/before/name=' + this.get('name'),  this.$el, data);
                
                // ajax
                $.ajax({
                    url: acf.get('ajaxurl'),
                    data: acf.prepareForAjax(data),
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    
                    // Success
                    success: function(response){
                        
                        // Deprecated
                        acf.doAction('acfe/fields/button/ajax_success',                     response, this.$el, data);

                        // Actions
                        acf.doAction('acfe/fields/button/success',                          response, this.$el, data);
                        acf.doAction('acfe/fields/button/success/key=' + this.get('key'),   response, this.$el, data);
                        acf.doAction('acfe/fields/button/success/name=' + this.get('name'), response, this.$el, data);
                        
                    },
                    
                    // Complete
                    complete: function(xhr){

                        var response = xhr.responseText;

                        // Actions
                        acf.doAction('acfe/fields/button/complete',                             response, this.$el, data);
                        acf.doAction('acfe/fields/button/complete/key=' + this.get('key'),      response, this.$el, data);
                        acf.doAction('acfe/fields/button/complete/name=' + this.get('name'),    response, this.$el, data);

                    }
                    
                    
                });
                
            }
            
        }
        
    });

    acf.registerFieldType(ACFE_Button);
    
    /**
     * Field: Advanced Link
     */
    var ACFE_Advanced_Link = acf.Field.extend({
		
		type: 'acfe_advanced_link',
		
		events: {
			'click a[data-name="add"]': 	'onClickEdit',
			'click a[data-name="edit"]': 	'onClickEdit',
			'click a[data-name="remove"]':	'onClickRemove',
		},
		
		$control: function(){
			return this.$('.acf-link');
		},
        
        initialize: function(){
            // ...
        },
		
		getValue: function(){

			// return
            var data = {
				type:   this.$('.input-type :checked').val(),
				title:  this.$('.input-title').val(),
				url:    this.$('.input-url').val(),
				post:   this.$('.input-post :selected').text(),
				term:   this.$('.input-term :selected').text(),
				target: this.$('.input-target').is(':checked')
			};
            
            if(data.type === 'post'){
                
                data.url = data.post;
                
            }else if(data.type === 'term'){
                
                data.url = data.term;
                
            }
            
            return data;
            
		},
		
		setValue: function( val ){
			
			// default
			val = acf.parseArgs(val, {
				remove:	false,
				title:	'',
				url:	'',
				target:	false
			});
			
			// vars
			var $div = this.$control();
			
			// remove class
			$div.removeClass('-value -external');
			
			// add class
			if(val.url)
                $div.addClass('-value');
            
			if(val.target)
                $div.addClass('-external');
			
			// update text
			this.$('.link-title').html( val.title );
			this.$('.link-url').attr('href', val.url).html( val.url );
			
			// remove inputs data
            if(val.remove){
                
                this.$('.input-type :checked').prop('checked', false);
                this.$('.input-type [value="url"]').prop('checked', true).trigger('change');
                this.$('.input-title').val('');
                this.$('.input-target').prop('checked', false);
                this.$('.input-url').val('').trigger('change');
                this.$('.input-post').val('').trigger('change');
                this.$('.input-term').val('').trigger('change');
                
            }
            
		},
		
		onClickEdit: function(e, $el){
            
            var $modal = $el.closest('.acf-input').find('.acfe-modal');
            
            var title = $modal.attr('data-modal-title');
            
            var model = this;
            
            acfe.modal.open($modal, {
                title: title,
                size: 'medium',
                footer: acf.__('Close'),
                onClose: function(){
                    model.onChange();
                }
            });
            
		},
		
		onClickRemove: function( e, $el ){
            
			this.setValue({
				remove:	true
			});
            
		},
		
		onChange: function( e, $el ){
			
			// get the changed value
			var val = this.getValue();
			
			// update inputs
			this.setValue(val);
            
		},
		
	});
	
	acf.registerFieldType(ACFE_Advanced_Link);
    
    /**
     * Field: Advanced Link - Post Object Ajax
     */
    acf.addFilter('select2_ajax_data/type=post_object', function(ajaxData, data, $el, field, select){
        
        if(field.get('key') !== 'post')
            return ajaxData;
        
        var advanced_link = acf.getInstance($el.closest('.acf-field-acfe-advanced-link'));
        
        if(advanced_link){
            
            ajaxData.field_key = advanced_link.get('key');
            
        }
        
        return ajaxData;
        
    });
    
    /*
     * Field: Advanced Link - Error
     */
    acf.addAction('invalid_field', function(field){
        
        var $advanced_link = field.$el.closest('.acf-field-acfe-advanced-link').not('.acf-error');
        
        if($advanced_link.length){
            
            var advanced_link_field = acf.getInstance($advanced_link);
            
            advanced_link_field.showError(field.notice.get('text'));
            
        }
        
    });
    
    /**
     * Field: Group
     */
    var Group = acf.Field.extend({
        
        type: 'group',
        
        events: {
            'click [data-name="edit"]': 'onClick',
        },
        
        initialize: function(){
            
            if(this.has('acfeGroupModal')){
                
                var edit = this.get('acfeGroupModalButton');
                
                this.$el.find('> .acf-input > .acf-fields, > .acf-input > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$el.find('> .acf-input').append('<a data-name="edit" class="acf-button button" href="#">' + edit + '</a>');
                
            }
            
        },
        
        onClick: function(e, $el){
            
            var title = this.$el.find('> .acf-label').text().trim();
            var $modal = this.$el.find('> .acf-input > .acfe-modal');
            
            // Title
            if(!title.length){
                
                title = this.get('acfeGroupModalButton');
                
            }
            
            // Close
            var close = false;
            
            if(this.has('acfeGroupModalClose')){
                
                close = acf.__('Close');
                
            }
            
            // Size
            var size = 'large';
            
            if(this.has('acfeGroupModalSize')){
                
                size = this.get('acfeGroupModalSize');
                
            }
            
            // Open modal
            acfe.modal.open($modal, {
                title:  title,
                size:   size,
                footer: close
            });
            
        },
        
    });

    acf.registerFieldType(Group);
    
    /**
     * Field: Clone
     */
    var Clone = acf.Field.extend({
        
        type: 'clone',
        
        events: {
            'click [data-name="edit"]': 'onClick',
        },
        
        initialize: function(){
            
            if(this.has('acfeCloneModal')){
                
                var edit = this.get('acfeCloneModalButton');
                
                this.$el.find('> .acf-input > .acf-fields, > .acf-input > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$el.find('> .acf-input').append('<a data-name="edit" class="acf-button button" href="#">' + edit + '</a>');
                
            }
            
        },
        
        onClick: function(e, $el){
            
            var title = this.$el.find('> .acf-label').text().trim();
            var $modal = this.$el.find('> .acf-input > .acfe-modal');
            
            
            // Title
            if(!title.length){
                
                title = this.get('acfeCloneModalButton');
                
            }
            
            // Close
            var close = false;
            
            if(this.has('acfeCloneModalClose')){
                
                close = acf.__('Close');
                
            }
            
            // Size
            var size = 'large';
            
            if(this.has('acfeCloneModalSize')){
                
                size = this.get('acfeCloneModalSize');
                
            }
            
            // Open modal
            acfe.modal.open($modal, {
                title:  title,
                size:   size,
                footer: close
            });
            
        },
        
    });

    acf.registerFieldType(Clone);
    
    /**
     * Field: Column
     */
    var Column = acf.Field.extend({
        
        wait: 'new_field',
        
        type: 'acfe_column',

        $control: function(){
			return this.$('.acf-fields:first');
		},
        
        initialize: function(){
            
			if(this.$el.is('td')){
                
                var $table = this.$el.closest('.acf-table').find('th[data-type="acfe_column"]').remove();
                this.remove();
                
            }
            
            if(this.get('endpoint')){
                
                this.$el.find('> .acf-label').remove();
                this.$el.find('> .acf-input').remove();
                
                return;
                
            }
            
            var $field = this.$el;
            var $label = this.$el.find('> .acf-label');
			var $input = this.$inputWrap();
			var $wrap = this.$control();
            
            $label.remove();
            
            var $parent = $field.parent();
			$wrap.addClass($parent.hasClass('-left') ? '-left' : '');
			$wrap.addClass($parent.hasClass('-clear') ? '-clear' : '');
            
            $wrap.append($field.nextUntil('.acf-field-acfe-column', '.acf-field'));
            
        }
        
    });

    acf.registerFieldType(Column);
    
    /**
     * Field: Taxonomy Terms - Ajax
     */
    acf.addFilter('select2_ajax_data/action=acfe/fields/taxonomy_terms/allow_query', function(ajaxData, data, $el, field, select){
        
        // Taxonomies
        var $taxonomies = $el.closest('.acf-field-settings').find('> .acf-field-setting-taxonomy > .acf-input > select > option:selected');
        
        var tax = [];
        
        $taxonomies.each(function(){
            tax.push($(this).val());
        });
        
        ajaxData.taxonomies = tax;
        
        // Terms level
        var $level = $el.closest('.acf-field-settings').find('> .acf-field-setting-allow_terms > .acf-input input[type="number"]');
        
        ajaxData.level = $level.val();
        
        return ajaxData;
        
    });
    
    /**
     * Fields: Select2 - Placeholder
     */
    acf.addAction('select2_init', function($select, options, data, field, instance){
        
        // Search Placeholder
        if(field.get('acfeSearchPlaceholder')){
            
            var search_placeholder = field.get('acfeSearchPlaceholder');
        
            $select.on('select2:open', function(e){
                
                if(field.get('multiple')){
                    
                    if(!$select.val()){
                        
                        field.$('.select2-search__field').attr('placeholder', search_placeholder);
                    
                    }
                    
                }else{
                    
                    $('.select2-search.select2-search--dropdown > .select2-search__field').attr('placeholder', search_placeholder);
                    
                }
                
            });
            
            if(field.get('multiple')){
                
                $select.on('select2:close', function(e){
                    
                    if(!$select.val()){
                    
                        field.$('.select2-search__field').attr('placeholder', field.get('placeholder'));
                    
                    }
                    
                });
                
            }
        
        }
        
    });
    
    /**
     * Fields: Select2 - Allow Custom
     */
    acf.addFilter('select2_args', function(options, $select, fieldData, field, instance){
        
        if(field.get('acfeAllowCustom')){
            
            options.tags = true;

            options.createTag = function(params){

                var term = $.trim(params.term);

                if(term === '')
                    return null;

                var optionsMatch = false;

                this.$element.find('option').each(function(){

                    if(this.value.toLowerCase() === term.toLowerCase()){
                        optionsMatch = true;
                    }

                });

                if(optionsMatch)
                    return null;

                return {
                    id: term,
                    text: term
                };
                
            };


            options.insertTag = function(data, tag){
                
                var found = false;
                
                $.each(data, function(index, value){
                    
                    if($.trim(tag.text).toUpperCase() === $.trim(value.text).toUpperCase()){
                        
                        found = true;
                        return false;
                        
                    }
                    
                });

                if(!found)
                    data.unshift(tag);
                
            };
            
            options.templateSelection = function(state){
                
                if(!state.id){
                    return state.text;
                }

                var text = state.text;
                
                var match_field = /{field:(.*)}/g;
                var match_fields = /{fields}/g;
                var match_get_field = /{get_field:(.*)}/g;
                var match_query_var = /{query_var:(.*)}/g;
                var match_request = /{request:(.*)}/g;
                var match_current = /{current:(.*)}/g;
                
                text = text.replace(match_field, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{field:$1}</code>");
                text = text.replace(match_fields, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{fields}</code>");
                text = text.replace(match_current, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{current:$1}</code>");
                text = text.replace(match_get_field, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{get_field:$1}</code>");
                text = text.replace(match_query_var, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{query_var:$1}</code>");
                text = text.replace(match_request, "<code style='font-size:12px;padding:3px;vertical-align: 1px;line-height: 12px;'>{request:$1}</code>");


                return text;
                
            };
            
            options.templateResult = function(state){
                
                if(!state.id){
                    return state.text;
                }

                var text = state.text;
                
                var match_field = /{field:(.*?)}/g;
                var match_fields = /{fields}/g;
                var match_get_field = /{get_field:(.*?)}/g;
                var match_query_var = /{query_var:(.*?)}/g;
                var match_request = /{request:(.*?)}/g;
                var match_current = /{current:(.*?)}/g;
                
                text = text.replace(match_field, "<code style='font-size:12px;'>{field:$1}</code>");
                text = text.replace(match_fields, "<code style='font-size:12px;'>{fields}</code>");
                text = text.replace(match_get_field, "<code style='font-size:12px;'>{get_field:$1}</code>");
                text = text.replace(match_query_var, "<code style='font-size:12px;'>{query_var:$1}</code>");
                text = text.replace(match_request, "<code style='font-size:12px;'>{request:$1}</code>");
                text = text.replace(match_current, "<code style='font-size:12px;'>{current:$1}</code>");

                return text;
                
            };
        
        }
        
        return options;
        
    });
    
    /**
     * Fields: Select2 - Args Variations
     */
    acf.addFilter('select2_args', function(options, $select, data, field, instance){
        
        options = acf.applyFilters('select2_args/type=' +   field.get('type'),  options, $select, data, field, instance);
        options = acf.applyFilters('select2_args/name=' +   field.get('name'),  options, $select, data, field, instance);
        options = acf.applyFilters('select2_args/key=' +    field.get('key'),   options, $select, data, field, instance);
        
        return options;
        
    });
    
    /**
     * Fields: Select2 - Init Variations
     */
    acf.addAction('select2_init', function($select, options, data, field, instance){
        
        acf.doAction('select2_init/type=' +   field.get('type'),  $select, options, data, field, instance);
        acf.doAction('select2_init/name=' +   field.get('name'),  $select, options, data, field, instance);
        acf.doAction('select2_init/key=' +    field.get('key'),   $select, options, data, field, instance);
        
    });

    /**
     * Fields: Select2 - Ajax Data Variations
     */
    acf.addFilter('select2_ajax_data', function(ajaxData, data, $el, field, instance){
        
        ajaxData = acf.applyFilters('select2_ajax_data/type=' +   field.get('type'), ajaxData, data, $el, field, instance);
        ajaxData = acf.applyFilters('select2_ajax_data/name=' +   field.get('name'), ajaxData, data, $el, field, instance);
        ajaxData = acf.applyFilters('select2_ajax_data/key=' +    field.get('key'),  ajaxData, data, $el, field, instance);
        
        if(ajaxData.action){
            
            ajaxData = acf.applyFilters('select2_ajax_data/action=' + ajaxData.action, ajaxData, data, $el, field, instance);
            
        }
        
        return ajaxData;
        
    });

    /**
     * Field Conditions: Datepicker
     */
    acf.registerConditionForFieldType('equalTo',        'date_picker');
    acf.registerConditionForFieldType('notEqualTo',     'date_picker');
    acf.registerConditionForFieldType('patternMatch',   'date_picker');
    acf.registerConditionForFieldType('contains',       'date_picker');
    acf.registerConditionForFieldType('greaterThan',    'date_picker');
    acf.registerConditionForFieldType('lessThan',       'date_picker');

    /**
     * Field Conditions: Taxonomy
     */
    acf.registerConditionForFieldType('equalTo',        'taxonomy');
    acf.registerConditionForFieldType('notEqualTo',     'taxonomy');
    acf.registerConditionForFieldType('patternMatch',   'taxonomy');
    acf.registerConditionForFieldType('contains',       'taxonomy');
    acf.registerConditionForFieldType('hasValue',       'taxonomy');
    acf.registerConditionForFieldType('hasNoValue',     'taxonomy');
    
    /**
     * Field Conditions: Forms
     */
    acf.registerConditionForFieldType('equalTo',        'acfe_forms');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_forms');
    acf.registerConditionForFieldType('patternMatch',   'acfe_forms');
    acf.registerConditionForFieldType('contains',       'acfe_forms');
    acf.registerConditionForFieldType('hasValue',       'acfe_forms');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_forms');
    
    /**
     * Field Conditions: Post Status
     */
    acf.registerConditionForFieldType('equalTo',        'acfe_post_statuses');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_post_statuses');
    acf.registerConditionForFieldType('patternMatch',   'acfe_post_statuses');
    acf.registerConditionForFieldType('contains',       'acfe_post_statuses');
    acf.registerConditionForFieldType('hasValue',       'acfe_post_statuses');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_post_statuses');
    
    /**
     * Field Conditions: Post Types
     */
    acf.registerConditionForFieldType('equalTo',        'acfe_post_types');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_post_types');
    acf.registerConditionForFieldType('patternMatch',   'acfe_post_types');
    acf.registerConditionForFieldType('contains',       'acfe_post_types');
    acf.registerConditionForFieldType('hasValue',       'acfe_post_types');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_post_types');
    
    /**
     * Field Conditions: Taxonomies
     */
    acf.registerConditionForFieldType('equalTo',        'acfe_taxonomies');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_taxonomies');
    acf.registerConditionForFieldType('patternMatch',   'acfe_taxonomies');
    acf.registerConditionForFieldType('contains',       'acfe_taxonomies');
    acf.registerConditionForFieldType('hasValue',       'acfe_taxonomies');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_taxonomies');
    
    /**
     * Field Conditions: Taxonomy Terms
     */
    acf.registerConditionForFieldType('equalTo',        'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('patternMatch',   'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('contains',       'acfe_taxonomy_terms');
    acf.registerConditionForFieldType('hasValue',       'acfe_taxonomy_terms');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_taxonomy_terms');
    
    /**
     * Field Conditions: User Roles
     */
    acf.registerConditionForFieldType('equalTo',        'acfe_user_roles');
    acf.registerConditionForFieldType('notEqualTo',     'acfe_user_roles');
    acf.registerConditionForFieldType('patternMatch',   'acfe_user_roles');
    acf.registerConditionForFieldType('contains',       'acfe_user_roles');
    acf.registerConditionForFieldType('hasValue',       'acfe_user_roles');
	acf.registerConditionForFieldType('hasNoValue',     'acfe_user_roles');
    
    /**
     * Module: Author
     */
    acf.add_action('new_field/name=acfe_author', function(field){
        
        field.on('change', function(e){
            
            e.stopPropagation();
            
        });
        
    });
    
    /**
     * Module: Dynamic Forms - Fields Mapping
     */
    var acfe_form_map_fields = function(field){
        
        var name = field.get('name');
        var $layout = field.$el.closest('.layout');
        var $message = $layout.find('> .acf-fields > .acf-field[data-name="' + name + '_message"] > .acf-input');

        var selected = field.$input().find('option:selected').text();
        
        if(selected.length){
            $message.html(selected);
        }
        
        field.$input().on('change', function(){

            // Message
            var text = $(this).find('option:selected').text();

            $message.html(text);
            
        });
        
    };
    
    acf.addAction('new_field/name=acfe_form_post_map_target',       acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_type',    acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_status',  acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_title',   acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_name',    acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_content', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_author',  acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_parent',  acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_terms',   acfe_form_map_fields);
    
    acf.addAction('new_field/name=acfe_form_user_map_email',        acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_username',     acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_password',     acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_first_name',   acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_last_name',    acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_nickname',     acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_display_name', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_website',      acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_description',  acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_role',         acfe_form_map_fields);
    
    acf.addAction('new_field/name=acfe_form_term_map_name',         acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_slug',         acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_taxonomy',     acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_parent',       acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_description',  acfe_form_map_fields);
    
    /**
     * Module: Dynamic Forms (actions)
     */
    acf.addAction('new_field/name=acfe_form_actions', function(field){
        
        var $tab = $('a[data-key=field_acfe_form_tab_actions]');
        
        var $layouts = field.$layouts();
        
        $tab.html('Actions <span class="acfe-tab-badge">' + $layouts.length + '</span>');
        
        field.on('change', function(){
            
            var $layouts = field.$layouts();
            
            $tab.html('Actions <span class="acfe-tab-badge">' + $layouts.length + '</span>');
            
        });
        
        field.on('click', '[data-name="add-layout"]', function(e){
            
            $('body').find('.acf-fc-popup').addClass('acfe-fc-popup-grey');
            
        });
        
    });

    var acfe_tab_forget_tab_preference = function(field){

        var $tabs = field.findTabs();
        var tabs = acf.getInstances($tabs);
        var key = field.get('key');

        if(tabs.length){

            var preference = acf.getPreference('this.tabs');

            if(!preference)
                return;

            $.each(tabs, function(){

                var group = this;
                var groupIndex = group.get('index');

                if(group.data.key === key){
                    preference[groupIndex] = 0;
                }

            });

            // update
            acf.setPreference('this.tabs', preference);

        }

    };

    acf.addAction('prepare_field/key=field_acfe_form_custom_action_tab_action', acfe_tab_forget_tab_preference);
    acf.addAction('prepare_field/key=field_acfe_form_email_tab_action',         acfe_tab_forget_tab_preference);
    acf.addAction('prepare_field/key=field_acfe_form_post_tab_action',          acfe_tab_forget_tab_preference);
    acf.addAction('prepare_field/key=field_acfe_form_term_tab_action',          acfe_tab_forget_tab_preference);
    acf.addAction('prepare_field/key=field_acfe_form_user_tab_action',          acfe_tab_forget_tab_preference);

    acf.addAction('prepare_field/key=field_acfe_dpt_tab_general',               acfe_tab_forget_tab_preference);
    acf.addAction('prepare_field/key=field_acfe_dt_tab_general',                acfe_tab_forget_tab_preference);

    acf.addAction('show_postbox', function(postbox){
        postbox.$el.removeClass('acfe-postbox-left acfe-postbox-top');
    });
    
    // Allow conditions to work within wrapped div
    acf.newCondition = function( rule, conditions ){

        // currently setting up conditions for fieldX, this field is the 'target'
        var target = conditions.get('field');

        // use the 'target' to find the 'trigger' field.
        // - this field is used to setup the conditional logic events
        var field = target.getField( rule.field );

        // ACF Extended: Check in all form if targeted field not found
        if( target && !field ) {

            field = acf.getField( rule.field );

        }

        // bail ealry if no target or no field (possible if field doesn't exist due to HTML error)
        if( !target || !field ) {
            return false;
        }

        // vars
        var args = {
            rule: rule,
            target: target,
            conditions: conditions,
            field: field
        };

        // vars
        var fieldType = field.get('type');
        var operator = rule.operator;

        // get avaibale conditions
        var conditionTypes = acf.getConditionTypes({
            fieldType: fieldType,
            operator: operator,
        });

        // instantiate
        var model = conditionTypes[0] || acf.Condition;

        // instantiate
        var condition = new model( args );

        // return
        return condition;

    };

    /**
     * Field: Checkbox
     */
    acf.addAction('new_field/type=checkbox', function(field){

        if(!field.has('acfeLabels'))
            return;

        $.each(field.get('acfeLabels'), function(group, key){

            field.$control().find('input[type=checkbox][value="' + key + '"]').closest('ul').before('<strong>' + group + '</strong>');

        });

    });

    /**
     * Field: Radio
     */
    acf.addAction('new_field/type=radio', function(field){

        if(!field.has('acfeLabels'))
            return;

        $.each(field.get('acfeLabels'), function(group, key){

            field.$control().find('input[type=radio][value="' + key + '"]').closest('li').addClass('parent').prepend('<strong>' + group + '</strong>');

        });

        if(field.$control().hasClass('acf-hl')){

            field.$control().find('li.parent').each(function(){

                $(this).nextUntil('li.parent').addBack().wrapAll('<li><ul></ul></li>');

            });

        }

    });

})(jQuery);
(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    acf.addAction('prepare', function(){

        if(acf.get('is_admin'))
            return;

        // Fix Image/File WP Media upload
        if(acf.isset(window, 'wp', 'media', 'view', 'settings', 'post')){

            // Unset Post ID
            wp.media.view.settings.post = false;

        }

        if($('.acfe-form[data-hide-unload="1"]').length){

            acf.unload.disable();

        }

        if($('.acfe-form-success').length){

            if(window.history.replaceState){
                window.history.replaceState(null, null, window.location.href);
            }

            $('.acfe-form-success').each(function(){

                var form_name = $(this).data('form-name');
                var form_id = $(this).data('form-id');

                acf.doAction('acfe/form/submit/success');
                acf.doAction('acfe/form/submit/success/id=' + form_id);
                acf.doAction('acfe/form/submit/success/name=' + form_name);

            });

        }
    
    });
    
    // Datepicker: Add field class
    acf.addAction('new_field/type=date_picker', function(field){

        if(acf.get('is_admin'))
            return;
        
        var $form = field.$el.closest('.acfe-form');
        
        if(!$form.length)
            return;
        
        var field_class = $form.data('fields-class');
        
        if(field_class)
            field.$inputText().addClass(field_class);
        
    });
    
    // Google Maps: Add field class
    acf.addAction('new_field/type=google_map', function(field){

        if(acf.get('is_admin'))
            return;
        
        var $form = field.$el.closest('.acfe-form');
        
        if(!$form.length)
            return;
        
        var field_class = $form.data('fields-class');
        
        if(field_class)
            field.$search().addClass(field_class);
        
    });
    
    // Error: Move error
    acf.addAction('invalid_field', function(field){

        if(acf.get('is_admin'))
            return;
        
        var $form = field.$el.closest('.acfe-form');
        
        if(!$form.length)
            return;
        
        var errors_position = $form.data('errors-position');
        var errors_class = $form.data('errors-class');
        
        // Class
        if(errors_class && errors_class.length){
            
            field.$el.find('.acf-notice.-error').addClass(errors_class);
            
        }
        
        // Move below
        if(errors_position && errors_position === 'below'){
            
            if(field.$control().length){
                
                field.$el.find('.acf-notice.-error').insertAfter(field.$control());
                
            }else if(field.$input().length){
                
                field.$el.find('.acf-notice.-error').insertAfter(field.$input());
                
            }
            
            var $selector = false;
            
            if(field.$control().length){
                
                $selector = field.$control();
                
            }else if(field.$input().length){
                
                $selector = field.$input();
                
            }
            
            if($selector)
                field.$el.find('.acf-notice.-error').insertAfter($selector);
            
        }
        
        // Group errors
        else if(errors_position && errors_position === 'group'){
            
            var label = field.$el.find('.acf-label label').text().trim();
            var placeholder = field.$el.find('.acf-input-wrap [placeholder!=""]').attr('placeholder');
            var message = field.$el.find('.acf-notice.-error').text().trim();
            
            field.$el.find('.acf-notice.-error').remove();
            
            // Try label
            if(label && label.length && label !== '*'){
                
                message = label + ': ' + message;
                
            }
            
            // Try placeholder
            else if(placeholder && placeholder.length && placeholder !== ''){
                
                message = placeholder + ': ' + message;
                
            }
            
            // If everything fails, use field name
            else{
                
                message = field.get('name') + ': ' + message;
                
            }
            
            var $form_error = $form.find('> .acfe-form-error')
            
            if(!$form_error.length)
                $form_error = $('<div class="acf-notice -error acf-error-message acfe-form-error" />').prependTo($form);
            
            $form_error.append('<p>' + message + '</p>');
            
        }
        
        // Hide errors
        else if(errors_position && errors_position === 'hide'){
            
            field.$el.find('.acf-notice.-error').remove();
            
        }
        
    });
    
    // Remove error message on validation
    acf.addAction('validation_begin', function($form){

        if(acf.get('is_admin'))
            return;
        
        if(typeof $form === 'undefined')
            return;
        
        $form.find('.acf-error-message').remove();
        
    });
    
})(jQuery);
(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /*
     * Init
     */
    var repeater = acf.getFieldType('repeater');
    var model = repeater.prototype;
    
    // Repeater: Lock Layouts
    model.acfeOnHover = function(){
        
        var repeater = this;
        
        // remove event
        repeater.off('mouseover');
        
    }
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=repeater', function(repeater){
        
        // ACFE: Lock
        if(repeater.has('acfeRepeaterLock')){
            
            repeater.removeEvents({'mouseover': 'onHover'});
            
            repeater.addEvents({'mouseover': 'acfeOnHover'});
            
        }
        
        // ACFE: Remove Actions
        if(repeater.has('acfeRepeaterRemoveActions')){
            
            repeater.$actions().remove();
            
            repeater.$el.find('thead:first > tr > th.acf-row-handle:last').remove();
            repeater.$rows().find('> .acf-row-handle:last').remove();
            
            repeater.$control().find('> .acfe-repeater-stylised-button').remove();
            
            
        }
        
        // ACFE: Stylised button
        if(repeater.has('acfeRepeaterStylisedButton')){
            
            repeater.$button().removeClass('button-primary');
            repeater.$actions().wrap('<div class="acfe-repeater-stylised-button" />');
        
        }

    });
    
})(jQuery);