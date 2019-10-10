(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    // Datepicker: Add field class
    acf.addAction('new_field/type=date_picker', function(field){
        
        var $form = field.$el.closest('form.acfe-form');
        
        if(!$form.length)
            return;
        
        var field_class = $form.data('acfe-form-fields-class');
        
        if(field_class)
            field.$inputText().addClass(field_class);
        
    });
    
    // Google Maps: Add field class
    acf.addAction('new_field/type=google_map', function(field){
        
        var $form = field.$el.closest('form.acfe-form');
        
        if(!$form.length)
            return;
        
        var field_class = $form.data('acfe-form-fields-class');
        
        if(field_class)
            field.$search().addClass(field_class);
        
    });
    
    // Error: Move error
    acf.addAction('invalid_field', function(field){
        
        var $form = field.$el.closest('form.acfe-form');
        
        if(!$form.length)
            return;
        
        var errors_position = $form.data('acfe-form-errors-position');
        var errors_class = $form.data('acfe-form-errors-class');
        
        // Class
        if(errors_class && errors_class.length){
            
            field.$el.find('.acf-notice.-error').addClass(errors_class);
            
        }
        
        // Move below
        if(errors_position && errors_position === 'below'){
            
            field.$el.find('.acf-notice.-error').insertAfter(field.$el.find('.acf-input-wrap'));
            
        }
        
        // Group errors
        else if(errors_position && errors_position === 'group'){
            
            var label = field.$el.find('.acf-label label').text().trim();
            var placeholder = field.$el.find('.acf-input-wrap [placeholder!=""]').attr('placeholder');
            var message = field.$el.find('.acf-notice.-error').text().trim();
            
            field.$el.find('.acf-notice.-error').remove();
            
            if(label && label.length && label !== '*'){
                
                message = label + ': ' + message;
                
            }
            
            else if(placeholder && placeholder.length){
                
                message = placeholder + ': ' + message;
                
            }
            
            var $form_error = $form.find('> .acfe-form-error')
            
            if(!$form_error.length)
                $form_error = $('<div class="acf-notice -error acf-error-message acfe-form-error" />').prependTo($form);
            
            $form_error.append('<p>' + message + '</p>');
            
        }
        
    });
    
    // Remove error message on validation
    acf.addAction('validation_begin', function($form){
        
        $form.find('.acf-error-message').remove();
        
    });
    
})(jQuery);