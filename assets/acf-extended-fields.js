(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /**
     * Field: Slug
     */
    acf.addAction('prepare_field/type=acfe_slug', function(field){
        
        field.$el.bind('input', function(e){
            field.val(field.val().toLowerCase()
            .replace(/\s+/g, '-')       // Replace spaces with -
            .replace(/[^\w\-]+/g, '')   // Remove all non-word chars
            .replace(/\-\-+/g, '-')     // Replace multiple - with single -
            .replace(/\_\_+/g, '_')     // Replace multiple _ with single _
            .replace(/^-+/, ''));       // Trim - from start of text
        });
        
        field.$el.on('focusout', function(e){
            field.val(field.val().toLowerCase()
            .replace(/-+$/, '')         // Trim - from end of text
            .replace(/_+$/, ''));       // Trim _ from end of text
        });
        
        
    });
    
})(jQuery);