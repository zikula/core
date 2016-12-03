webshims.register('form-message', function($, webshims, window, document, undefined, options){
	"use strict";
	if(options.lazyCustomMessages){
		options.customMessages = true;
	}
	var validityMessages = webshims.validityMessages;
	
	var implementProperties = options.customMessages ? ['customValidationMessage'] : [];
	
	validityMessages.en = $.extend(true, {
		typeMismatch: {
			defaultMessage: 'Please enter a valid value.',
			email: 'Please enter an email address.',
			url: 'Please enter a URL.'
		},
		badInput: {
			defaultMessage: 'Please enter a valid value.',
			number: 'Please enter a number.',
			date: 'Please enter a date.',
			time: 'Please enter a time.',
			range: 'Invalid input.',
			month: 'Please enter a valid value.',
			"datetime-local": 'Please enter a datetime.'
		},
		rangeUnderflow: {
			defaultMessage: 'Value must be greater than or equal to {%min}.'
		},
		rangeOverflow: {
			defaultMessage: 'Value must be less than or equal to {%max}.'
		},
		stepMismatch: 'Invalid input.',
		tooLong: 'Please enter at most {%maxlength} character(s). You entered {%valueLen}.',
		tooShort: 'Please enter at least {%minlength} character(s). You entered {%valueLen}.',
		patternMismatch: 'Invalid input. {%title}',
		valueMissing: {
			defaultMessage: 'Please fill out this field.',
			checkbox: 'Please check this box if you want to proceed.'
		}
	}, (validityMessages.en || validityMessages['en-US'] || {}));
	
	if(typeof validityMessages['en'].valueMissing == 'object'){
		['select', 'radio'].forEach(function(type){
			validityMessages.en.valueMissing[type] = validityMessages.en.valueMissing[type] || 'Please select an option.';
		});
	}
	if(typeof validityMessages.en.rangeUnderflow == 'object'){
		['date', 'time', 'datetime-local', 'month'].forEach(function(type){
			validityMessages.en.rangeUnderflow[type] = validityMessages.en.rangeUnderflow[type] || 'Value must be at or after {%min}.';
		});
	}
	if(typeof validityMessages.en.rangeOverflow == 'object'){
		['date', 'time', 'datetime-local', 'month'].forEach(function(type){
			validityMessages.en.rangeOverflow[type] = validityMessages.en.rangeOverflow[type] || 'Value must be at or before {%max}.';
		});
	}
	if(!validityMessages['en-US']){
		validityMessages['en-US'] = $.extend(true, {}, validityMessages.en);
	}
	if(!validityMessages['en-GB']){
		validityMessages['en-GB'] = $.extend(true, {}, validityMessages.en);
	}
	if(!validityMessages['en-AU']){
		validityMessages['en-AU'] = $.extend(true, {}, validityMessages.en);
	}
	validityMessages[''] = validityMessages[''] || validityMessages['en-US'];
	
	validityMessages.de = $.extend(true, {
		typeMismatch: {
			defaultMessage: '{%value} ist in diesem Feld nicht zulässig.',
			email: '{%value} ist keine gültige E-Mail-Adresse.',
			url: '{%value} ist kein(e) gültige(r) Webadresse/Pfad.'
		},
		badInput: {
			defaultMessage: 'Geben Sie einen zulässigen Wert ein.',
			number: 'Geben Sie eine Nummer ein.',
			date: 'Geben Sie ein Datum ein.',
			time: 'Geben Sie eine Uhrzeit ein.',
			month: 'Geben Sie einen Monat mit Jahr ein.',
			range: 'Geben Sie eine Nummer.',
			"datetime-local": 'Geben Sie ein Datum mit Uhrzeit ein.'
		},
		rangeUnderflow: {
			defaultMessage: '{%value} ist zu niedrig. {%min} ist der unterste Wert, den Sie benutzen können.'
		},
		rangeOverflow: {
			defaultMessage: '{%value} ist zu hoch. {%max} ist der oberste Wert, den Sie benutzen können.'
		},
		stepMismatch: 'Der Wert {%value} ist in diesem Feld nicht zulässig. Hier sind nur bestimmte Werte zulässig. {%title}',
		tooLong: 'Der eingegebene Text ist zu lang! Sie haben {%valueLen} Zeichen eingegeben, dabei sind {%maxlength} das Maximum.',
		tooShort: 'Der eingegebene Text ist zu kurz! Sie haben {%valueLen} Zeichen eingegeben, dabei sind {%minlength} das Minimum.',
		patternMismatch: '{%value} hat für dieses Eingabefeld ein falsches Format. {%title}',
		valueMissing: {
			defaultMessage: 'Bitte geben Sie einen Wert ein.',
			checkbox: 'Bitte aktivieren Sie das Kästchen.'
		}
	}, (validityMessages.de || {}));
	
	if(typeof validityMessages.de.valueMissing == 'object'){
		['select', 'radio'].forEach(function(type){
			validityMessages.de.valueMissing[type] = validityMessages.de.valueMissing[type] || 'Bitte wählen Sie eine Option aus.';
		});
	}
	if(typeof validityMessages.de.rangeUnderflow == 'object'){
		['date', 'time', 'datetime-local', 'month'].forEach(function(type){
			validityMessages.de.rangeUnderflow[type] = validityMessages.de.rangeUnderflow[type] || '{%value} ist zu früh. {%min} ist die früheste Zeit, die Sie benutzen können.';
		});
	}
	if(typeof validityMessages.de.rangeOverflow == 'object'){
		['date', 'time', 'datetime-local', 'month'].forEach(function(type){
			validityMessages.de.rangeOverflow[type] = validityMessages.de.rangeOverflow[type] || '{%value} ist zu spät. {%max} ist die späteste Zeit, die Sie benutzen können.';
		});
	}
	
	var currentValidationMessage =  validityMessages[''];
	var getMessageFromObj = function(message, elem){
		if(message && typeof message !== 'string'){
			message = message[ $.prop(elem, 'type') ] || message[ (elem.nodeName || '').toLowerCase() ] || message[ 'defaultMessage' ];
		}
		return message || '';
	};
	var lReg = /</g;
	var gReg = />/g;
	var valueVals = {
		value: 1,
		min: 1,
		max: 1
	};
	var toLocale = (function(){
		var monthFormatter;
		var transforms = {
			number: function(val){
				var num = val * 1;
				if(num.toLocaleString && !isNaN(num)){
					val = num.toLocaleString() || val;
				}
				return val;
			}
		};
		var _toLocale = function(val, elem, attr){
			var type, widget;
			if(valueVals[attr]){
				type = $.prop(elem, 'type');
				widget = $(elem).getShadowElement().data('wsWidget'+ type );
				if(widget && widget.formatValue){
					val = widget.formatValue(val, false);
				} else if(transforms[type]){
					val = transforms[type](val);
				}
			}
			return val;
		};

		[{n: 'date', f: 'toLocaleDateString'}, {n: 'time', f: 'toLocaleTimeString'}, {n: 'datetime-local', f: 'toLocaleString'}].forEach(function(desc){
			transforms[desc.n] = function(val){
				var date = new Date(val);
				if(date && date[desc.f]){
					val = date[desc.f]() || val;
				}
				return val;
			};
		});

		if(window.Intl && Intl.DateTimeFormat){
			monthFormatter = new Intl.DateTimeFormat(navigator.browserLanguage || navigator.language, {year: "numeric", month: "2-digit"}).format(new Date());
			if(monthFormatter && monthFormatter.format){
				transforms.month = function(val){
					var date = new Date(val);
					if(date){
						val = monthFormatter.format(date) || val;
					}
					return val;
				};
			}
		}

		webshims.format =  {};

		['date', 'number', 'month', 'time', 'datetime-local'].forEach(function(name){
			webshims.format[name] = function(val, opts){
				if(opts && opts.nodeType){
					return _toLocale(val, opts, name);
				}
				if(name == 'number' && opts && opts.toFixed ){
					val = (val * 1);
					if(!opts.fixOnlyFloat || val % 1){
						val = val.toFixed(opts.toFixed);
					}
				}
				if(webshims._format && webshims._format[name]){
					return webshims._format[name](val, opts);
				}
				return transforms[name](val);
			};
		});

		return _toLocale;
	})();

	webshims.replaceValidationplaceholder = function(elem, message, name){
		var val = $.prop(elem, 'title');
		if(message){
			if(name == 'patternMismatch' && !val){
				webshims.error('no title for patternMismatch provided. Always add a title attribute.');
			}
			if(val){
				val = '<span class="ws-titlevalue">'+ val.replace(lReg, '&lt;').replace(gReg, '&gt;') +'</span>';
			}

			if(message.indexOf('{%title}') != -1){
				message = message.replace('{%title}', val);
			} else if(val) {
				message = message+' '+val;
			}
		}

		if(message && message.indexOf('{%') != -1){
			['value', 'min', 'max', 'maxlength', 'minlength', 'label'].forEach(function(attr){
				if(message.indexOf('{%'+attr) === -1){return;}
				var val = ((attr == 'label') ? $.trim($('label[for="'+ elem.id +'"]', elem.form).text()).replace(/\*$|:$/, '') : $.prop(elem, attr) || $.attr(elem, attr) || '') || '';
				val = ''+val;


				val = toLocale(val, elem, attr);

				message = message.replace('{%'+ attr +'}', val.replace(lReg, '&lt;').replace(gReg, '&gt;'));
				if('value' == attr){
					message = message.replace('{%valueLen}', val.length);
				}

			});
		}
		return message;
	};
	
	webshims.createValidationMessage = function(elem, name){

		var message = getMessageFromObj(currentValidationMessage[name], elem);
		if(!message && name == 'badInput'){
			message = getMessageFromObj(currentValidationMessage.typeMismatch, elem);
		}
		if(!message && name == 'typeMismatch'){
			message = getMessageFromObj(currentValidationMessage.badInput, elem);
		}
		if(!message){
			message = getMessageFromObj(validityMessages[''][name], elem) || ($.prop(elem, 'validationMessage') || '').replace(lReg, '&lt;').replace(gReg, '&gt;');
			if(name != 'customError'){
				webshims.info('could not find errormessage for: '+ name +' / '+ $.prop(elem, 'type') +'. in language: '+webshims.activeLang());
			}
		}
		message = webshims.replaceValidationplaceholder(elem, message, name);
		
		return message || '';
	};
	
	
	if(!webshims.support.formvalidation || webshims.bugs.bustedValidity){
		implementProperties.push('validationMessage');
	}
	
	currentValidationMessage = webshims.activeLang(validityMessages);
		
	$(validityMessages).on('change', function(e, data){
		currentValidationMessage = validityMessages.__active;
	});
	
	implementProperties.forEach(function(messageProp){
		
		webshims.defineNodeNamesProperty(['fieldset', 'output', 'button'], messageProp, {
			prop: {
				value: '',
				writeable: false
			}
		});
		['input', 'select', 'textarea'].forEach(function(nodeName){
			var desc = webshims.defineNodeNameProperty(nodeName, messageProp, {
				prop: {
					get: function(){
						var elem = this;
						var message = '';
						if(!$.prop(elem, 'willValidate')){
							return message;
						}
						
						var validity = $.prop(elem, 'validity') || {valid: 1};
						
						if(validity.valid){return message;}
						message = webshims.getContentValidationMessage(elem, validity);
						
						if(message){return message;}
						
						if(validity.customError && elem.nodeName){
							message = (webshims.support.formvalidation && !webshims.bugs.bustedValidity && desc.prop._supget) ? desc.prop._supget.call(elem) : webshims.data(elem, 'customvalidationMessage');
							if(message){return message;}
						}
						$.each(validity, function(name, prop){
							if(name == 'valid' || !prop){return;}
							
							message = webshims.createValidationMessage(elem, name);
							if(message){
								return false;
							}
						});
						
						return message || '';
					},
					writeable: false
				}
			});
		});
		
	});
});
