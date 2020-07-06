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