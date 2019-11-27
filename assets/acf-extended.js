(function($){
    
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
            
			$target.removeClass('-open -small -full');
            
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
        
        onClose: function($target, args){
            
            if(!args.onClose || !(args.onClose instanceof Function))
                return;
            
            args.onClose($target);
            
        }
        
    };
    
    acf.addAction('ready_field', function(field){
        
        if(!field.has('acfeInstructionsTooltip'))
            return;
        
        var $label = field.$el.find('> .acf-label > label');
        
        if($label.length){
            
            $label.before('<span class="acf-js-tooltip dashicons dashicons-info" style="float:right; font-size:16px; color:#ccc;" title="' + _.escape(field.get('acfeInstructionsTooltip')) + '"></span>');
            
        }
            
    });
    
    acf.addAction('new_field/name=acfe_form_custom_action', function(field){
        
        var $instructions = field.$el.find('> .acf-label > .description');
        
        field.$el.find('> .acf-input').append($instructions);
            
    });
    
    acf.addAction('new_field/name=acfe_form_email_files', function(field){
        
        field.$el.find('> .acf-input > .acf-repeater > .acf-actions > .acf-button').removeClass('button-primary');
            
    });
    
})(jQuery);