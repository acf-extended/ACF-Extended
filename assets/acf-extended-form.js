(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    acf.addAction('prepare', function(){

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
    
    // Allow conditions to work within wrapped div
    acf.newCondition = function( rule, conditions ){
        
        // currently setting up conditions for fieldX, this field is the 'target'
        var target = conditions.get('field');
        
        // use the 'target' to find the 'trigger' field. 
        // - this field is used to setup the conditional logic events
        
        // before: var field = target.getField( rule.field );
        var field = acf.getField( rule.field );
        
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
    
    // Datepicker: Add field class
    acf.addAction('new_field/type=date_picker', function(field){
        
        var $form = field.$el.closest('.acfe-form');
        
        if(!$form.length)
            return;
        
        var field_class = $form.data('fields-class');
        
        if(field_class)
            field.$inputText().addClass(field_class);
        
    });
    
    // Google Maps: Add field class
    acf.addAction('new_field/type=google_map', function(field){
        
        var $form = field.$el.closest('.acfe-form');
        
        if(!$form.length)
            return;
        
        var field_class = $form.data('fields-class');
        
        if(field_class)
            field.$search().addClass(field_class);
        
    });
    
    // Error: Move error
    acf.addAction('invalid_field', function(field){
        
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
        
        if(typeof $form === 'undefined')
            return;
        
        $form.find('.acf-error-message').remove();
        
    });
    
})(jQuery);