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