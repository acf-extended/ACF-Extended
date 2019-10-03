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
        var $modal = $layout.find('> .acfe-modal');
        var $handle = $layout.find('> .acf-fc-layout-handle');
        
        var $layout_order = $handle.find('> .acf-fc-layout-order').outerHTML();
        var $layout_title = $handle.find('.acfe-layout-title-text').text();
        
        var close = false;
        if(flexible.has('acfeFlexibleCloseButton')){
            
            close = 'Close';
        
        }
        
        // Open modal
        acfe.modal.open($modal, {
            title: $layout_order + ' ' + $layout_title,
            footer: close,
            onClose: function(){
                
                flexible.acfeCloseLayoutInit($layout);
                
            }
        });
        
    };
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        if(!flexible.has('acfeFlexibleModalEdition'))
            return;
        
        if(flexible.has('acfeFlexiblePlaceholder') || flexible.has('acfeFlexiblePreview')){
            
            // Remove Collapse Action
            flexible.removeEvents({'click [data-name="collapse-layout"]': 'onClickCollapse'});
            
            // Remove placeholder Collapse Action
            flexible.removeEvents({'click .acfe-flexible-collapsed-placeholder': 'onClickCollapse'});
            
        }
        
    });
    
    /*
     * Remove Legacy Collapse
     */
    acf.addAction('acfe/flexible/layouts', function($layout, flexible){
        
        if(!flexible.has('acfeFlexibleModalEdition'))
            return;
        
        // var
        var $controls = $layout.find('> .acf-fc-layout-controls');
        
        // Remove collapse button
        $controls.find('> a.-collapse').remove();
        
    });
    
})(jQuery);