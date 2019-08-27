(function($){
    
    // init
    var acfe = {};
	
	window.acfe = acfe;
    
    acfe.modal = {
        
        modals: [],
        
        // Open
        open: function($target, args){
            
            args = acf.parseArgs(args, {
                title: '',
                footer: false,
                size: false,
                destroy: false,
                onClose: false,
            });
            
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
            
            $target.find('> .acfe-modal-wrapper').prepend('<div class="acfe-modal-title"><span class="title">' + args.title + '</span><button class="close"></button></div>');
            
            $target.find('.acfe-modal-title > .close').click(function(e){
                e.preventDefault();
                acfe.modal.close(args);
            });
            
            if(args.footer){
                
                $target.find('> .acfe-modal-wrapper').append('<div class="acfe-modal-footer"><button class="button button-primary">' + args.footer + '</button></div>');
                
                $target.find('.acfe-modal-footer > button').click(function(e){
                    e.preventDefault();
                    acfe.modal.close(args);
                });
                
            }
            
            acfe.modal.modals.push($target);
            
            var $body = $('body');
            
            if(!$body.hasClass('acfe-modal-opened')){
				
				var overlay = $('<div class="acfe-modal-overlay" />').click(function(){
                    acfe.modal.close(args);
                });
                
				$body.addClass('acfe-modal-opened').append(overlay);
				
			}
            
            $(window).keydown(function(e){
        
                if((e.keyCode != 27) || !$('body').hasClass('acfe-modal-opened'))
                    return;
                
                acfe.modal.close(args);
                return false;
                
            });
            
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
                    $(this).css('margin-left', '');
                    return;
                }
                
                $(this).css('margin-left',  - (500 / (i+1)));
                
			});
            
        },
        
        onClose: function($target, args){
            
            if(!args.onClose || !(args.onClose instanceof Function))
                return;
            
            args.onClose($target);
            
        }
        
    };
    
})(jQuery);