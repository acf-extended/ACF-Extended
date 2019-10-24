(function($){
    
    if(typeof acf === 'undefined')
        return;
    
    /**
     * Field: Textarea
     */
    var Textarea = acf.Field.extend({
        
        type: 'textarea',
        
        events: {
            'keydown textarea': 'onInput',
        },
        
        onInput: function(e, $el){
            
            if(!this.has('acfeTextareaCode'))
                return;
            
            if(e.keyCode !== 9)
                return;
            
            e.preventDefault();
            
            var input = this.$el.find('textarea')[0];

            var s = input.selectionStart;

            this.$el.find('textarea').val(function(i, v){

                return v.substring(0, s) + "    " + v.substring(input.selectionEnd)

            });

            input.selectionEnd = s + 4;
            
        },
        
    });

    acf.registerFieldType(Textarea);
    
    
    /**
     * Field: Slug
     */
    var ACFE_Slug = acf.Field.extend({
        
        type: 'acfe_slug',
        
        events: {
            'input input': 'onInput',
            'focusout input': 'onFocusOut',
        },
        
        onInput: function(e, $el){
            
            $el.val($el.val().toLowerCase()
            .replace(/\s+/g, '-')       // Replace spaces with -
            .replace(/[^\w\-]+/g, '')   // Remove all non-word chars
            .replace(/\-\-+/g, '-')     // Replace multiple - with single -
            .replace(/\_\_+/g, '_')     // Replace multiple _ with single _
            .replace(/^-+/, ''));       // Trim - from start of text
            
        },
        
        onFocusOut: function(e, $el){
            
            $el.val($el.val().toLowerCase()
            .replace(/-+$/, '')         // Trim - from end of text
            .replace(/_+$/, ''));       // Trim _ from end of text
            
        },
        
    });

    acf.registerFieldType(ACFE_Slug);
    
    
    /**
     * Field: Button
     */
    var ACFE_Button = acf.Field.extend({
        
        type: 'acfe_button',
        
        events: {
            'click input': 'onClick',
            'click button': 'onClick',
        },
        
        $input: function(){
            
            if(this.$('input').length){
                
                return this.$('input');
                
            }else if(this.$('button').length){
                
                return this.$('button');
                
            }
            
        },
        
        initialize: function(){
            
            // vars
			var $button = this.$input();
			
			// inherit data
			this.inherit($button);
            
        },
        
        onClick: function(e, $el){
            
            if(this.get('ajax')){
                
                e.preventDefault();

                // serialize form data
                var data = {
                    action: 'acfe/fields/button',
                    field_name: this.get('name'),
                    field_key: this.get('key')
                };
                
                acf.doAction('acfe/fields/button/before_ajax', this.$el, data);
                
                // ajax
                $.ajax({
                    url: acf.get('ajaxurl'),
                    data: acf.prepareForAjax(data),
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    success: function(response){
                        
                        acf.doAction('acfe/fields/button/ajax_success', response, this.$el, data);
                        
                    }
                });
                
            }
            
        }
        
    });

    acf.registerFieldType(ACFE_Button);
    
    
    /**
     * Field: Advanced Link
     */
    var ACFE_Advanced_Link = acf.Field.extend({
		
		type: 'acfe_advanced_link',
		
		events: {
			'click a[data-name="add"]': 	'onClickEdit',
			'click a[data-name="edit"]': 	'onClickEdit',
			'click a[data-name="remove"]':	'onClickRemove',
		},
		
		$control: function(){
			return this.$('.acf-link');
		},
        
        initialize: function(){
            
            this.on('change', '.input-post', this.onPostChange);
            
        },
		
		getValue: function(){

			// return
            var data = {
				type:   this.$('.input-type :checked').val(),
				title:  this.$('.input-title').val(),
				url:    this.$('.input-url').val(),
				post:   this.$('.input-post :selected').text(),
				target: this.$('.input-target').is(':checked')
			};
            
            if(data.type === 'post'){
                
                data.url = data.post;
                
            }
            
            return data;
            
		},
		
		setValue: function( val ){
			
			// default
			val = acf.parseArgs(val, {
				remove:	false,
				title:	'',
				url:	'',
				target:	false
			});
			
			// vars
			var $div = this.$control();
			
			// remove class
			$div.removeClass('-value -external');
			
			// add class
			if(val.url)
                $div.addClass('-value');
            
			if(val.target)
                $div.addClass('-external');
			
			// update text
			this.$('.link-title').html( val.title );
			this.$('.link-url').attr('href', val.url).html( val.url );
			
			// remove inputs data
            if(val.remove){
                
                this.$('.input-type :checked').prop('checked', false);
                this.$('.input-type [value="url"]').prop('checked', true).trigger('change');
                this.$('.input-title').val('');
                this.$('.input-target').prop('checked', false);
                this.$('.input-url').val('').trigger('change');
                this.$('.input-post').val('').trigger('change');
                
            }
            
		},
		
		onClickEdit: function(e, $el){
            
            var $modal = $el.closest('.acf-input').find('.acfe-modal');
            
            var title = $modal.attr('data-modal-title');
            
            var model = this;
            
            acfe.modal.open($modal, {
                title: title,
                size: 'small',
                onClose: function(){
                    model.onChange();
                }
            });
            
		},
		
		onClickRemove: function( e, $el ){
            
			this.setValue({
				remove:	true
			});
            
		},
		
		onChange: function( e, $el ){
			
			// get the changed value
			var val = this.getValue();
			
			// update inputs
			this.setValue(val);
            
		},
        
		onPostChange: function(e){

            var $el = $(this);
            var model = acf.getInstance($el.closest('.acf-field-acfe-advanced-link'));
            
		},
		
	});
	
	acf.registerFieldType(ACFE_Advanced_Link);
    
    
    /**
     * Field: Group
     */
    var Group = acf.Field.extend({
        
        type: 'group',
        
        events: {
            'click [data-name="edit"]': 'onClick',
        },
        
        initialize: function(){
            
            if(this.has('acfeGroupModal')){
                
                var edit = this.get('acfeGroupModalButton');
                
                this.$el.find('> .acf-input > .acf-fields, > .acf-input > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$el.find('> .acf-input').append('<a data-name="edit" class="acf-button button" href="#">' + edit + '</a>');
                
            }
            
        },
        
        onClick: function(e, $el){
            
            var title = this.$el.find('> .acf-label').text().trim();
            var $modal = this.$el.find('> .acf-input > .acfe-modal');
            
            if(!title.length)
                title = this.get('acfeGroupModalButton');
            
            // Open modal
            acfe.modal.open($modal, {
                title: title
            });
            
        },
        
    });

    acf.registerFieldType(Group);
    
    /**
     * Field: Clone
     */
    var Clone = acf.Field.extend({
        
        type: 'clone',
        
        events: {
            'click [data-name="edit"]': 'onClick',
        },
        
        initialize: function(){
            
            if(this.has('acfeCloneModal')){
                
                var edit = this.get('acfeCloneModalButton');
                
                this.$el.find('> .acf-input > .acf-fields, > .acf-input > .acf-table').wrapAll('<div class="acfe-modal"><div class="acfe-modal-wrapper"><div class="acfe-modal-content"></div></div></div>');
                this.$el.find('> .acf-input').append('<a data-name="edit" class="acf-button button" href="#">' + edit + '</a>');
                
            }
            
        },
        
        onClick: function(e, $el){
            
            var title = this.$el.find('> .acf-label').text().trim();
            var $modal = this.$el.find('> .acf-input > .acfe-modal');
            
            if(!title.length)
                title = this.get('acfeCloneModalButton');
            
            // Open modal
            acfe.modal.open($modal, {
                title: title
            });
            
        },
        
    });

    acf.registerFieldType(Clone);
    
    /**
     * Field: Column
     */
    var Column = acf.Field.extend({
        
        wait: 'new_field',
        
        type: 'acfe_column',
        
        $control: function(){
			return this.$('.acf-fields:first');
		},
        
        initialize: function(){
            
			if(this.$el.is('td')){
                
                var $table = this.$el.closest('.acf-table').find('th[data-type="acfe_column"]').remove();
                this.remove();
                
            }
            
            if(this.get('endpoint')){
                
                this.$el.find('> .acf-label').remove();
                this.$el.find('> .acf-input').remove();
                
                return;
                
            }
            
            var $field = this.$el;
            var $label = this.$el.find('> .acf-label');
			var $input = this.$inputWrap();
			var $wrap = this.$control();
            
            $label.remove();
            
            var $parent = $field.parent();
			$wrap.addClass($parent.hasClass('-left') ? '-left' : '');
			$wrap.addClass($parent.hasClass('-clear') ? '-clear' : '');
            
            $wrap.append($field.nextUntil('.acf-field-acfe-column', '.acf-field'));
            
        }
        
    });

    acf.registerFieldType(Column);
    
    var acfe_form_map_fields = function(field){
        
        var name = field.get('name');
        var $layout = field.$el.closest('.layout');
        var $message = $layout.find('.acf-field[data-name="' + name + '_message"] > .acf-input');
        
        var selected = field.$input().find('option:selected').text();
        
        if(selected.length){
            $message.html(selected);
        }
        
        field.$input().on('change', function(){
            
            $message.html($(this).find('option:selected').text());
            
        });
        
    };
    
    acf.addAction('new_field/name=acfe_form_post_map_target', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_type', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_status', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_title', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_name', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_content', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_author', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_parent', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_post_map_post_terms', acfe_form_map_fields);
    
    acf.addAction('new_field/name=acfe_form_user_map_email', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_username', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_password', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_first_name', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_last_name', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_nickname', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_display_name', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_website', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_description', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_user_map_role', acfe_form_map_fields);
    
    acf.addAction('new_field/name=acfe_form_term_map_name', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_slug', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_taxonomy', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_parent', acfe_form_map_fields);
    acf.addAction('new_field/name=acfe_form_term_map_description', acfe_form_map_fields);
    
    acf.addAction('new_field/type=flexible_content', function(flexible){
        
        if(flexible.get('name') !== 'acfe_form_actions')
            return;
        
        flexible.on('click', '[data-name="add-layout"]', function(e){
            
            $('body').find('.acf-fc-popup').addClass('acfe-fc-popup-grey');
            
        });
        
    });
    
})(jQuery);