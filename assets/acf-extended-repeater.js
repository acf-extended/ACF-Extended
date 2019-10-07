(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /*
     * Spawn
     */
    acf.addAction('new_field/type=repeater', function(repeater){
        
        // ACFE: Stylised button
        if(repeater.has('acfeRepeaterStylisedButton')){
            
            repeater.$button().removeClass('button-primary');
            repeater.$actions().wrap('<div class="acfe-repeater-stylised-button" />');
        
        }

    });
    
})(jQuery);