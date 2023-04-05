"use strict";

( function($,getFieldDefault){
	var UPDATE_FIELD_BUTTON_TEXT = salonCheckoutFieldsEditor_l10n['update_field'];
	var REQUIRED_BY_DEFAULT = ['label','type'];

	var ROW_SELECTOR = '.sln-checkout-fields--row';
	var EDIT_BUTTON_SELECTOR = '.sln-custom-fields-edit';
	var DELETE_BUTTON_SELECTOR = '.sln-custom-fields-delete';
	var EDITOR_BUTTON_SELECTOR = '.field-editor-button';
	var EDITOR_CLOSE_BUTTON_SELECTOR = '.fields-editor-close';
	var ROW_WRAPPER_SELECTOR = '.sln-checkout-fields--row-wrapper';
	var LABEL_INPUT_SELECTOR = '#fields_editor_label';
	var TYPE_SELECT_SELECTOR = '#fields_editor_type';
	var OPTIONS_TEXT_AREA_SELECTOR = '#fields_editor_options';
	var GRIP_CELL_SELECTOR = '.sln-checkout-fields--grip--cell';
	var DEFAULT_VALUE_TEXT_COL_SELECTOR = '#sln-fields-editor-default-field-text-wrapper';
	var DEFAULT_VALUE_CHECKBOX_COL_SELECTOR = '#sln-fields-editor-default-field-checkbox-wrapper';
	var FILE_TYPE_COL_SELECTOR = '#sln-field-editor-file-type-select-wrapper';

	var slugify = function (str) {
		var separator = "_";
		return str
			.toString()
			.normalize('NFD')                   // split an accented letter in the base letter and the accent
			.replace(/[\u0300-\u036f]/g, '')   // remove all previously split accents
			.toLowerCase()
			.trim()
			.replace(/[^a-z0-9 ]/g, '')   // remove all chars not letters, numbers and spaces (to be replaced)
			.replace(/\s+/g, separator);
	};

	var getAllKeys = function(){
		var ret = [];
		$(ROW_SELECTOR).each(function(i,v){
			ret.push($(v).data().index);
		})
		return ret;
	}

	var getKey = function($el){
		return $el.parents(ROW_SELECTOR).data().index
	}

	var fieldProxy = {
		getField: function(key){
			return Object.keys(getFieldDefault()).reduce(function(defaultField, k) {
				var el = this.getSelector(k,key);
				defaultField[k] = this.setFieldValue(el);
				if(Array.isArray(defaultField[k])){
					defaultField[k] = defaultField[k].join(',');
				}
				return defaultField;
			}.bind(this),{});
		},
		setFieldValue: function(el) {
			return el.is(':checkbox') ? el.is(':checked') : el.val();
		},
		updateRow: function(field,key){
			Object.keys(field).forEach(function(k) {
				var el = this.getSelector(k, field);
				var isCheckbox = field.type === 'checkbox';
				if(isCheckbox && k === 'default_value') field[k] = field[k] && field[k] !== 'false';
				this.setCellValue(el,field[k]);
				if(!field.additional){
					var isReadOnly = fieldTable.getSelector(k, key).prop('disabled');
					if(isReadOnly || 'type' === k) el.prop('disabled',true);
				}
			},this);
		},
		updateTableRow: function(field,key){
			Object.keys(field).forEach(function(k) {
				var el = fieldTable.getSelector(k, key);
				if(['additional','default_value'].indexOf(k) !== -1 && [true,false].indexOf(field[k]) !== -1){
					field[k] = field[k] === true ? 'true' : 'false';
				}
				this.setCellValue(el,field[k]);
				if(REQUIRED_BY_DEFAULT.indexOf(k) !== -1){
					var val = k === 'type' ? this.$el.find( TYPE_SELECT_SELECTOR + ' option[value='+field[k]+']').text() : field[k];
					$('.sln_'+key+'_'+k+'_cell').text(val)
				}
			},this);
		},
		setCellValue: function(el,val){
			el.is(':checkbox') ? el.prop("checked", val) : el.val(val);
			if (el.is('select')) {
				el.trigger('change')
			}

		}
	}

	var fieldTable = $.extend({},fieldProxy,{
		getSelector: function(k,currentKey){
			return $('#salon_settings_checkout_fields_'+ currentKey + '_' + k)
		}
	});

	var editor = {
		isValid: true
	}

	var editorPrototype = {
		init: function(){
			var editor = this;
			this.$el.find(LABEL_INPUT_SELECTOR).on('change', function(){
				if(!editor.isValid){
					var val = $(this).val();
					if(val){
						editor.$el.removeClass('invalid');
						editor.isValid = true;
					}
				}
			});
			this.$el.find(EDITOR_BUTTON_SELECTOR).on('click', function(){
				editor.update();
				editor.onClickCloseButton()
			});
			this.$el.find(EDITOR_CLOSE_BUTTON_SELECTOR).on('click', this.onClickCloseButton);
			this.$el.find(TYPE_SELECT_SELECTOR).on('change', function(){
				var val = $(this).val();
				editor.onTypeChange(val)
			});
		},
		getSelector: function(k,field){
			var selector = '#fields_editor_'+ k;
			if(k === 'default_value'){
				var field_type = field ? field.type : this.$el.find('#fields-editor_type').find('option:selected').val();
				selector = '#sln-fields-editor-default-field-'+(field_type === 'checkbox' ? 'checkbox' : 'text') +'-wrapper input'
			}
			return this.$el.find(selector);
		},
		validate : function(field){
			if(!field.label){
				this.$el.addClass('invalid');
				this.isValid = false;
				return false;
			}
			return true
		},
		onTypeChange: function (val){
			var selectOptions = this.$el.find(OPTIONS_TEXT_AREA_SELECTOR).parent();
			var defaultValueText = this.$el.find(DEFAULT_VALUE_TEXT_COL_SELECTOR);
			var defaultValueCheckbox = this.$el.find(DEFAULT_VALUE_CHECKBOX_COL_SELECTOR);
			var fileTypeSelect = this.$el.find(FILE_TYPE_COL_SELECTOR);
			val === 'select' ? selectOptions.show() : selectOptions.hide();
			if (val === 'checkbox') {
				defaultValueText.children('input').prop('disabled', true);
				defaultValueText.hide();
				fileTypeSelect.children('select').prop('disabled', true);
				fileTypeSelect.hide();
				defaultValueCheckbox.children('input').prop('disabled', false);
				defaultValueCheckbox.show();
			} else if(val === 'file'){
				defaultValueCheckbox.children('input').prop('disabled', true);
				defaultValueCheckbox.hide();
				defaultValueText.children('input').prop('disabled', true);
				defaultValueText.hide();
				fileTypeSelect.children('select').prop('disabled', false);
				fileTypeSelect.show();
			} else {
				defaultValueCheckbox.children('input').prop('disabled', true);
				defaultValueCheckbox.hide();
				fileTypeSelect.children('select').prop('disabled', true);
				fileTypeSelect.hide();
				defaultValueText.children('input').prop('disabled', false);
				defaultValueText.show();
			}
		},
		clear : function(){
			this.updateRow(getFieldDefault());
		}
	}

	var createEditor = function(opts){
		var Constructor = function(){};
		Constructor.prototype = editorPrototype;
		var object = new Constructor();
		return $.extend(object,fieldProxy,editor,opts);
	}

	var newFieldEditor = createEditor({
		$el: $('.fields-editor[data-mode="new"]'),
		onClickCloseButton: function(){
			newFieldEditor.clear();
		},
		update: function(){
			var field = this.getField();
			var key = this.getNewKey();
			if(!this.validate(field)) return;
			var row = $(ROW_SELECTOR+'[data-index=firstname]').clone();
			var temp = $('<div>').append(row);
			var html = temp.html();
			temp.html(html.replace(/firstname/g,key));
			row = temp.children();
			row.find('.sln-checkbox label + input[type="hidden"]').remove();
			row.find('input,select,textarea').prop('disabled',false);
			row.hide().appendTo(ROW_WRAPPER_SELECTOR);
			this.updateTableRow(field,key);
			row.show();
		},
		getNewKey : function(){
			var name = slugify(this.$el.find(LABEL_INPUT_SELECTOR).val());
			var keys = getAllKeys();
			var x = 0;
			while(keys.indexOf(name) !== -1){
				name = name +'_'+ x++;
			}

			return name;
		}
	});

	var existingFieldEditor = createEditor({
		$el: '',
		init: function(){
			var parent = newFieldEditor.$el.parent().clone();
			var el = parent.children();
			el.data('mode','existing');
			el.attr('data-mode','existing');
			el.find(EDITOR_BUTTON_SELECTOR).text(UPDATE_FIELD_BUTTON_TEXT)
			$('.sln-checkout-fields').find('select').attr( 'data-select2-id', function(){ return this.id+'1'; } );
			parent.hide();
			parent.appendTo('body');
			this.$el = el;
			editorPrototype.init.call(this);
		},
		close: function(fn){
			existingFieldEditor.$el.parent().slideUp('slow',fn);
		},
		onClickCloseButton: function(){
			existingFieldEditor.close(function(){
				this.setKey('');
				$(ROW_SELECTOR).removeClass('selected');
				existingFieldEditor.clear()
				newFieldEditor.$el.parent().slideDown();
			}.bind(existingFieldEditor));
		},
		getKey: function (){
			return this.$el.data().key;
		},
		setKey: function (key){
			return this.$el.data('key',key);
		},
		update: function(){
			var field = this.getField();
			var key = this.getKey();
			if(!this.validate(field)) return;
			this.updateTableRow(field,key);
			this.$el.parent().slideUp(function(){ newFieldEditor.$el.parent().slideDown(); })
		},
		show: function(key){
			this.$el.parent().insertAfter($(ROW_SELECTOR+'[data-index="'+key+'"]'))
			this.$el.parent().slideDown(function(){
				this.setKey(key);
				$(ROW_SELECTOR).removeClass('selected');
				$(ROW_SELECTOR+'[data-index="'+key+'"]').addClass('selected');
			}.bind(this));
		},
		editField : function(field,key){
			this.updateRow(field,key);
			this.close(this.show.bind(this,key));
		}

	});

	newFieldEditor.init();
	existingFieldEditor.init();

	var body = $('body');

	body.on('click',EDIT_BUTTON_SELECTOR,function(){
		var $el = $(this);
		var key = getKey($el);
		newFieldEditor.$el.parent().slideUp();
		if( key !== existingFieldEditor.getKey() ){
			var field = fieldTable.getField(key);
			existingFieldEditor.editField(field, key)
		}
	});

	body.on('click',DELETE_BUTTON_SELECTOR,function(){
		var $el = $(this);
		if( getKey($el) === existingFieldEditor.getKey()){
			existingFieldEditor.onClickCloseButton();
		}
		$el.parents(ROW_SELECTOR).remove();
	});

	Sortable.create($(ROW_WRAPPER_SELECTOR).get(0),{handle: GRIP_CELL_SELECTOR});

})(jQuery,sln_getFieldDefault)