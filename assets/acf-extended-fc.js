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
    
    model.acfeCloseLayoutInit = function($layout){
        
        $layout.addClass('-collapsed');
        acf.doAction('hide', $layout, 'collapse');
        
    };
    
    model.acfeLayoutInit = function($layout){
        
        // Get Flexible
        var flexible = this;
        
        // Vars
        var $controls = $layout.find('> .acf-fc-layout-controls');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        
        // Remove duplicate
        $layout.find('> .acfe-flexible-opened-actions').remove();
        
        // Placeholder
        var $placeholder = $layout.find('> .acfe-flexible-collapsed-placeholder');
        
        // Placeholder: Not found - Create new element
        if(!$placeholder.length && (flexible.has('acfeFlexiblePlaceholder') || flexible.has('acfeFlexiblePreview'))){
            
            var placeholder_icon = 'dashicons dashicons-edit';
            
            if(flexible.has('acfeFlexiblePlaceholderIcon'))
                placeholder_icon = flexible.get('acfeFlexiblePlaceholderIcon');
            
            // Placeholder
            var $placeholder = $('' +
                '<div class="acfe-flexible-collapsed-placeholder" title="Edit layout">' +
                '   <button class="button" onclick="return false;">' +
                '       <span class="' + placeholder_icon + '"></span>' +
                '   </button>' +
                '   <div class="acfe-flexible-collapsed-overlay"></div>' +
                '   <div class="acfe-flexible-placeholder"></div>' +
                '</div>'
            ).insertAfter($controls);
        
        }
        
        // Placeholder: Show
        $placeholder.show();
        
        // Modal Edition Wrap
        if(flexible.has('acfeFlexibleModalEdition')){
            
            if(!$layout.find('> .acfe-modal').length){
        
                // Wrap content
                $layout.find('> .acf-fields, > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                
                // Handle
                $handle.attr('data-action', 'acfe-flexible-modal-edit');
                
                // Placeholder
                if(flexible.has('acfeFlexiblePlaceholder') || flexible.has('acfeFlexiblePreview'))
                    $placeholder.attr('data-action', 'acfe-flexible-modal-edit');
            
            }
        
        }
        
        else{
            
            if(!flexible.isLayoutClosed($layout)){
                
                $placeholder.hide();
                
            }
            
        }
        
        // Flexible has Preview
        if(flexible.has('acfeFlexiblePreview')){
            
            $placeholder.addClass('acfe-flexible-collapsed-preview acfe-is-loading').find('> .acfe-flexible-placeholder').prepend('<span class="spinner"></span>');
            $placeholder.find('> .acfe-flexible-collapsed-overlay').addClass('-hover');
            
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
                        
                        $placeholder.removeClass('acfe-flexible-collapsed-preview');
                        
                    }
                    
                    acf.doAction('acfe/fields/flexible_content/preview',                                                                     response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/name=' + flexible.get('name'),                                        response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/key=' + flexible.get('key'),                                          response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/name=' + flexible.get('name') + '&layout=' + $layout.data('layout'),  response, flexible.$el, $layout, ajaxData);
                    acf.doAction('acfe/fields/flexible_content/preview/key=' + flexible.get('key') + '&layout=' + $layout.data('layout'),    response, flexible.$el, $layout, ajaxData);
                    
				},
                complete: function(){
                    
                    $placeholder.find('> .acfe-flexible-collapsed-overlay').removeClass('-hover');
                    $placeholder.removeClass('acfe-is-loading').find('> .acfe-flexible-placeholder > .spinner').remove();
                    
                }
			});
            
        }
        
    };
    
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
        $all_layouts.each(function(){
            
            var $layout = $(this);
            var $name = $layout.data('layout');
            
            acf.doAction('acfe/flexible/layouts', $layout, flexible);
            acf.doAction('acfe/flexible/layout/name=' + $name, $layout, flexible);
            
        });
        
        // ACFE: Stylised button
        if(flexible.has('acfeFlexibleStylisedButton')){
            
            flexible.$button().removeClass('button-primary');
            flexible.$actions().wrap('<div class="acfe-flexible-stylised-button" />');
        
        }
        
        // ACFE: 1 layout available - OneClick
        if($clones.length === 1){
            
            // Remove native ACF Tooltip action
            flexible.removeEvents({'click [data-name="add-layout"]': 'onClickAdd'});
            
            // Add ACF Extended Modal action
            flexible.addEvents({'click [data-name="add-layout"]': 'acfeOneClick'});
        
        }
        
        flexible.addEvents({'click .acfe-flexible-collapsed-placeholder': 'onClickCollapse'});
        
        flexible.addEvents({'click .acfe-flexible-opened-actions > a': 'onClickCollapse'});

    });
    
    acf.addAction('acfe/flexible/layouts', function($layout, flexible){
        
        // Flexible has Modal Edition
        if(flexible.has('acfeFlexibleModalEdition')){
            
            $layout.addClass('-collapsed');
            flexible.acfeLayoutInit($layout);
            
            return;
            
        }
        
        // Flexible has Remove Collapse
        if(flexible.has('acfeFlexibleRemoveCollapse')){
            
            flexible.removeEvents({'click [data-name="collapse-layout"]': 'onClickCollapse'});
            $layout.find('> .acf-fc-layout-controls > [data-name="collapse-layout"]').remove();
            
        }
        
        // Bail early if layout is clone
        if($layout.is('.acf-clone'))
            return;
            
        // Layout State: Collapse
        if(flexible.has('acfeFlexibleClose')){
            
            flexible.acfeCloseLayoutInit($layout);
            
        }
        
        // Layout State: Open
        else if(flexible.has('acfeFlexibleOpen')){
            
            flexible.openLayout($layout);
            
        }
        
        // Others
        else{
        
            // Action: Close for closed layouts
            if(flexible.isLayoutClosed($layout)){
                
                flexible.acfeCloseLayoutInit($layout);
                
            }
            
            // Action: Show for opened layouts
            else{
                
                flexible.openLayout($layout);
                
            }
        
        }
        
    });
    
    acf.addAction('show', function($layout, type){
        
        if(type !== 'collapse' || !$layout.is('.layout'))
            return;
        
        var flexible = acf.getInstance($layout.closest('.acf-field-flexible-content'));
        
        // Bail early if Modal Edit
        if(flexible.has('acfeFlexibleModalEdition'))
            return;
    
        // Placeholder
        $layout.find('> .acfe-flexible-collapsed-placeholder').hide();
        
        // Close Button
        if(flexible.has('acfeFlexibleCloseButton')){
            
            $layout.find('> .acfe-flexible-opened-actions').remove();
            
            $('<div class="acfe-flexible-opened-actions"><a href="javascript:void(0);" class="button">' + acf.get('close') + '</button></a>').appendTo($layout);
        
        }
        
    });
    
    acf.addAction('hide', function($layout, type){
        
        if(type !== 'collapse' || !$layout.is('.layout') || $layout.is('.acf-clone'))
            return;
        
        // Get Flexible
        var flexible = acf.getInstance($layout.closest('.acf-field-flexible-content'));
        
        flexible.acfeLayoutInit($layout);
        
    });
    
    acf.addAction('append', function($el){
        
        // Bail early if layout is not clone
        if(!$el.is('.layout'))
            return;
        
        // Get Flexible
        var flexible = acf.getInstance($el.closest('.acf-field-flexible-content'));
        
        flexible.acfeLayoutInit($el);
        
        // Scroll to new layout
        $('html, body').animate({
            scrollTop: parseInt($el.offset().top) - 200
        }, 200);
        
        // Modal Edition: Open
        if(flexible.has('acfeFlexibleModalEdition') && !$el.is('.acfe-layout-duplicated')){
            
            $el.find('> [data-action="acfe-flexible-modal-edit"]:first').trigger('click');
            
        }
        
        // Normal Edition: Open
        else if(!flexible.isLayoutClosed($el)){
            
            flexible.openLayout($el);
            
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