// Copyright Zikula Foundation, licensed MIT.

/**
 * @fileOverview Zikula global helpers
 */

if (typeof(Zikula) == 'undefined') {
    /**
     * Zikula global object
     * 
     * @namespace Zikula global object
     * 
     * @borrows Zikula.Gettext#getMessage as #__
     * @borrows Zikula.Gettext#getMessageFormatted as #__f
     * @borrows Zikula.Gettext#getPluralMessage as #_n
     * @borrows Zikula.Gettext#getPluralMessageFormatted as #_fn
     */
    var Zikula = {};
}

/**
 * Global Zikula config object.
 * Zikula.Config is defined inline in HTML HEAD and is always avaiable.<br >
 * Contains following properties:<br >
 * - entrypoint<br >
 * - baseURL<br >
 * - baseURI<br >
 * - ajaxtimeout<br >
 * - lang
 *
 * @name Zikula.Config
 */

/**
 * Creates namespace in Zikula scope through nested chain of objects, based on the given path
 * Example:
 * Zikula.define('Module.Component') will create object chain: Zikula.Module.Component
 * If object in chain already exists it will be extended, not overwritten
 * 
 * @param {String} path Dot separated path to define.
 * 
 * @return {Object} Zikula extended object
 */
Zikula.define = function(path) {
    return path.split('.').inject(Zikula, function(object, prop) {
        object[prop] = object[prop] || { };
        return object[prop];
    });
};

/**
 * Load what's needed on dom loaded.
 *
 * @return void
 */
Zikula.init = function() {
    if (Zikula.Browser.IE) {
        Zikula.fixbuttons();
    }
};
document.observe('dom:loaded',Zikula.init);

/**
 * Extends prototype Browser detection.
 * Adds following properties to original ones:<br />
 * - IES.IE9 - true when IE9<br />
 * - IES.IE8 - true when IE8 or IE9 in IE8 mode<br />
 * - IES.IE7 - true when IE7 or IE8/IE9 in IE7 mode
 * - IES.IE6 - true for IE6 or older
 *
 * @return {Object} Object with browsers info
 */
Zikula.Browser = (function() {
    var IES = {
        IE6: false,
        IE7: false,
        IE8: false,
        IE9: false
    };
    if (Prototype.Browser.IE) {
        if (document.documentMode != 'undefined' && document.documentMode == 9) {
            IES.IE9 = true;
        } else if (document.documentMode != 'undefined' && document.documentMode == 8) {
            IES.IE8 = true;
        } else if (typeof(document.documentElement.style.maxHeight) != 'undefined'){
            IES.IE7 = true;
        } else {
            IES.IE6 = true;
        }
    }
    return Object.extend(IES, Prototype.Browser);
  })();

/**
 * Decodes json data to original format
 *
 * @param {String} jsondata JSONized array in utf-8 (as created by AjaxUtil::output).
 *
 * @return {mixed} Decoded data
 */
Zikula.dejsonize = function(jsondata) {
    var result;
    try {
        result = jsondata.evalJSON(true);
    } catch(error) {
        alert(Zikula.__f('illegal JSON response: \n%1$s in\n%2$s', [error, jsondata]));
    }
    return result;
};

/**
 * Shows an error message with alert().
 *
 * @todo beautify this
 *
 * @param {String} errortext The text to show.
 *
 * @return void
 */
Zikula.showajaxerror = function(errortext) {
    if (Object.isString(errortext) && errortext.isJSON()) {
        var decoded = errortext.evalJSON(true);
        if (decoded.core && decoded.core.statusmsg) {
            if (typeof(decoded.core.statusmsg) == 'object') {
                if (!Object.isArray(decoded.core.statusmsg)) {
                    decoded.core.statusmsg = Object.values(decoded.core.statusmsg);
                }
                errortext = decoded.core.statusmsg.join("\n");
            } else {
                errortext = decoded.core.statusmsg;
            }
        }
    } else if (Object.isArray(errortext)) {
        errortext = errortext.join("\n");
    } else if (typeof(errortext) == 'object') {
        errortext = Object.values(errortext).join("\n");
    }
    if (errortext) {
        alert(errortext);
    }
    return;
};

/**
 * Manage ajax error responses returned by AjaxUtil.
 *
 * @param {Object}  transport      Transport object returned by Ajax.Request.
 * @param {Boolean} [supresserror] Should error message be supressed.
 *
 * @return {mixed} Decoded transport data or void
 */
Zikula.ajaxResponseError = function(transport, supresserror) {
	var json = Zikula.dejsonize(transport.responseText);
	if ("authid" in json) {
		if (json.authid != '') {
		    Zikula.updateauthids(json.authid);
		}
	}
	if (json.displayalert == '1' && supresserror != true) {
		Zikula.showajaxerror(json.errormessage);
	}
	return json;
};

/**
 * Sets a select to a given value.
 *
 * @param {HTMLElement|String} id  Select id.
 * @param {String} sel The value that should be selected.
 *
 * @return void
 */
Zikula.setselectoption = function(id, sel) {
    $A($(id).options).each(function(opt){opt.selected = (opt.value == sel);});
};

/**
 * Zikula.getcheckboxvalue
 * Gets the value of a checkbox depending on the state.
 *
 * @deprecated Use Prototype $F
 *
 * @param {HTMLElement|String} id Checkbox id.
 *
 * @return {String} Checkbox value
 */
Zikula.getcheckboxvalue = function(id) {
    return $F(id) || '';
};

/**
 * Updates all hidden authid fields with a new authid obtained with an ajax call.
 *
 * @deprecated
 * 
 * @param {String} authid The new authid.
 * 
 * @return void
 */
Zikula.updateauthids = function(authid) {
    if (authid.length != 0) {
        $$('form input[name=authid]').invoke('writeAttribute','value',authid);
    }
    return;
};

/**
 * Set z-odd / z-even on each li after append, move and delete.
 *
 * @param   {String} listclass   Class applied to the list of items.
 * @param   {String} headerclass Class applied to the header of the list.
 * 
 * @return  void
 */
Zikula.recolor = function(listclass, headerclass) {
    var odd = true;

    $A($(listclass).childElements()).each(
        function(node) {
            if (!Element.hasClassName(node, headerclass)) {
                Element.removeClassName(node, 'z-odd');
                Element.removeClassName(node, 'z-even');

                if (odd == true) {
                    Element.addClassName(node, 'z-odd');
                } else {
                    Element.addClassName(node, 'z-even');
                }
                odd = !odd;
            }
        }
    );
};

/**
 * Change the display attribute of an specific object.
 *
 * @param   {HTMLElement|String} id Id of the object to hide/show.
 * 
 * @return  void
 */
Zikula.switchdisplaystate = function(id) {
    var tmpobj = $(id);

    if (tmpobj.getStyle('display') == 'none') {
        if (typeof(Effect) != "undefined") {
            Effect.BlindDown(tmpobj);
        } else {
            tmpobj.show();
        }
    } else {
        if (typeof(Effect) != "undefined") {
            Effect.BlindUp(tmpobj);
        } else {
            tmpobj.hide();
        }
    }
};

/**
 * Change the display attribute of an specific container depending of a radio input.
 *
 * @param  {HTMLElement|String}  idgroup       Id of the container where the radio input to observe are.
 * @param  {HTMLElement|String}  idcontainer   Id of the container to hide/show.
 * @param  {Boolean} state         State of the radio to show the idcontainer.
 *
 * @return void
 */
Zikula.radioswitchdisplaystate = function(idgroup, idcontainer, state) {
    var objgroup = $(idgroup);
    var objcont = $(idcontainer);

    var check_state = objgroup.select('input[type=radio][value="1"]').pluck('checked').any();

    if (check_state == state) {
        if (objcont.getStyle('display') == 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindDown(objcont);
            } else {
                objcont.show();
            }
        }
    } else {
        if (objcont.getStyle('display') != 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindUp(objcont);
            } else {
                objcont.hide();
            }
        }
    }
};

/**
 * Change the display attribute of an specific container depending of a checkbox input.
 *
 * @param  {HTMLElement|String} idcheckbox    Id of the checkbox input to observe.
 * @param  {HTMLElement|String} idcontainer   Id of the container to hide/show.
 * @param  {Boolean}   state      State of the checkbox to show the idcontainer.
 *
 * @return void
 */
Zikula.checkboxswitchdisplaystate = function(idcheckbox, idcontainer, state) {
    var objcont = $(idcontainer),
        check_state = !!$F(idcheckbox);

    if (check_state == state) {
        if (objcont.getStyle('display') == 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindDown(objcont);
            } else {
                objcont.show();
            }
        }
    } else {
        if (objcont.getStyle('display') != 'none') {
            if (typeof(Effect) != "undefined") {
                Effect.BlindUp(objcont);
            } else {
                objcont.hide();
            }
        }
    }
};

/**
 * Allows to check, uncheck or toggle given checkbox or radio inputs.
 *
 * If selector param is container ID all inputs of type radio or checkbox are affected.
 * If you need more specyfic conditions - use CSS selector for inputs (eg 'form.class input[type=radio]')
 *
 * @param {String} selector Container ID or CSS selector for inputs
 * @param {Boolean} [value=null] True to check, false to uncheck. Leave null to toggle status
 *
 * @return void
 */
Zikula.toggleInput = function(selector, value) {
    var setValue = value == null ? function(v) {return !v;} : function(v) {return value;},
        elements = $(selector) ? $(selector).select('input[type=radio],input[type=checkbox]') : $$(selector);
    if (elements) {
        elements.each(function(e) {e.checked = setValue(e.checked);});
    }
};

/**
 * Workaround for wrong buttons values in IE and multiple submit buttons in IE6/7.
 *
 * @return void
 */
Zikula.fixbuttons = function() {
    $$('button').invoke('observe', 'click', function(e) {
        var form = e.element().up('form');
        if (form) {
            form.store('buttonClicked', e.element().identify());
        }
    });

    $$('form').invoke('observe', 'submit', function(e) {
        var form = e.element(),
            buttonClicked = form.retrieve('buttonClicked', null);
        form.select('button').each(function(b) {
            b.disabled = true;
            if (b.identify() == buttonClicked) {
                form.insert(new Element('input', {
                    type: 'hidden',
                    name: b.name,
                    value: b.attributes.getNamedItem('value') ? b.attributes.getNamedItem('value').nodeValue : ''
                }));
            }
        });
    });
};

/**
 * Ajax timeout detection. We set the time out to 5 seconds
 * taken from http://codejanitor.com/wp/2006/03/23/ajax-timeouts-with-prototype/
 *
 * @param {Object} xmlhttp Transport object returned by Ajax.Request.
 *
 * @return void
 */
Zikula.callInProgress = function(xmlhttp) {
    switch (xmlhttp.readyState) {
        case 1:
        case 2:
        case 3:
            return true;
            break;
        // Case 4 and 0
        default:
            return false;
            break;
    }
};

// Register global responders that will occur on all AJAX requests
Ajax.Responders.register({
    onCreate: function(request) {
        if ($('ajax_indicator')) {
            Element.show('ajax_indicator');
        }
        request.timeoutId = window.setTimeout(
            function() {
                // If we have hit the timeout and the AJAX request is active, abort it and let the user know
                if (Zikula.callInProgress(request.transport)) {
                    request.transport.isAborted = true;
                    request.transport.abort();
                    if ($('ajax_indicator') && $('ajax_indicator').tagName == 'IMG') {
                        $('ajax_indicator').src = Zikula.Config.baseURL + 'images/icons/extrasmall/error.png';
                    }
                    Zikula.showajaxerror(Zikula.__('Ajax connection time out!'));
                    // Run the onFailure method if we set one up when creating the AJAX object
                    if (request.options.onFailure) {
                        request.options.onFailure(request.transport, request.json);
                    }
                }
            },
            (typeof(Zikula.Config.ajaxtimeout)!='undefined' && Zikula.Config.ajaxtimeout!=0)  ? Zikula.Config.ajaxtimeout : 5000 // per default five seconds - can be changed in the settings
        );
    },
    onComplete: function(request) {
        if ($('ajax_indicator')) {
            Element.hide('ajax_indicator');
        }
        // Clear the timeout, the request completed ok
        window.clearTimeout(request.timeoutId);
    }
});


/**
 * @deprecated Use {@link Zikula.dejsonize}
 */
function pndejsonize(jsondata)
{
    return Zikula.dejsonize(jsondata);
}

/**
 * @deprecated Use {@link Zikula.showajaxerror}
 */
function pnshowajaxerror(errortext)
{
    return Zikula.showajaxerror(errortext);
}

/**
 * @deprecated Use {@link Zikula.setselectoption}
 */
function pnsetselectoption(id, sel)
{
    return Zikula.setselectoption(id, sel);
}

/**
 * @deprecated Use {@link Zikula.getcheckboxvalue}
 */
function pngetcheckboxvalue(id)
{
    return Zikula.getcheckboxvalue(id);
}

/**
 * @deprecated Use {@link Zikula.updateauthids}
 */
function pnupdateauthids(authid)
{
    return Zikula.updateauthids(authid);
}

/**
 * @deprecated Use {@link Zikula.callInProgress}
 */
function callInProgress(xmlhttp)
{
    return Zikula.callInProgress(xmlhttp);
}

/**
 * @deprecated Use {@link Zikula.recolor}
 */
function pnrecolor(listclass, headerclass)
{
    return Zikula.recolor(listclass, headerclass);
}

/**
 * @deprecated Use {@link Zikula.switchdisplaystate}
 */
function switchdisplaystate(id)
{
    return Zikula.switchdisplaystate(id);
}

/**
 * @deprecated Use {@link Zikula.radioswitchdisplaystate}
 */
function radioswitchdisplaystate(idgroup, idcontainer, state)
{
    return Zikula.radioswitchdisplaystate(idgroup, idcontainer, state);
}

/**
 * @deprecated Use {@link Zikula.checkboxswitchdisplaystate}
 */
function checkboxswitchdisplaystate(idcheckbox, idcontainer, state)
{
    return Zikula.checkboxswitchdisplaystate(idcheckbox, idcontainer, state);
}

/**
 * Javascript implementation of PHP str_repeat function.
 *
 * @param {String} i The string to be repeated.
 * @param {Number} m Number of time the input string should be repeated.
 *
 * @return {String} The repeated string.
 * @author Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
 * @link http://www.diveintojavascript.com/projects/sprintf-for-javascript
 */
Zikula.str_repeat = function(i, m) {
    for (var o = []; m > 0; o[--m] = i){}
    return o.join('');
};

/**
 * Javascript implementation of PHP sprintf function.
 *
 * @param {String} format Format string. PHP sprintf syntax is used.
 * @param {mixed}  args   Zero or more replacements to be made according to format string.
 *
 * @return {String} A string produced according to the formatting string format.
 * @author Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
 * @link http://www.diveintojavascript.com/projects/sprintf-for-javascript
 */
Zikula.sprintf = function() {
    var i = 0, a, f = arguments[i++], o = [], m, p, c, x, s = '';
    while (f) {
        if (m = /^[^\x25]+/.exec(f)) {
            o.push(m[0]);
        }
        else if (m = /^\x25{2}/.exec(f)) {
            o.push('%');
        }
        else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
            if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) {
                throw('Too few arguments.');
            }
            if (/[^s]/.test(m[7]) && (typeof(a) != 'number')) {
                throw('Expecting number but found ' + typeof(a));
            }
            switch (m[7]) {
                case 'b':
                    a = a.toString(2);
                    break;
                case 'c':
                    a = String.fromCharCode(a);
                    break;
                case 'd':
                    a = parseInt(a);
                    break;
                case 'e':
                    a = m[6] ? a.toExponential(m[6]) : a.toExponential();
                    break;
                case 'f':
                    a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a);
                    break;
                case 'o':
                    a = a.toString(8);
                    break;
                case 's':
                    a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a)
                    ;break;
                case 'u':
                    a = Math.abs(a);
                    break;
                case 'x':
                    a = a.toString(16);
                    break;
                case 'X':
                    a = a.toString(16).toUpperCase();
                    break;
            }
            a = (/[def]/.test(m[7]) && m[2] && a >= 0 ? '+'+ a : a);
            c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
            x = m[5] - String(a).length - s.length;
            p = m[5] ? Zikula.str_repeat(c, x) : '';
            o.push(s + (m[4] ? a + p : p + a));
        }
        else {
            throw('Huh ?!');
        }
        f = f.substring(m[0].length);
    }
    return o.join('');
};

/**
 * Javascript implementation of PHP vsprintf function.
 *
 * @param {String} format Format string. PHP sprintf syntax is used.
 * @param {Array}  args   Array with zero or more replacements to be made according to format string.
 *
 * @return {String} A string produced according to the formatting string format.
 */
Zikula.vsprintf = function(format, args) {
    return Zikula.sprintf.apply(this,[format].concat(args));
};

/**
 * Merge two objects recursively.
 *
 * Copies all properties from source to destination object and returns new object.
 * If proprety exists in destination it is extended not overwritten
 *
 * @param {Object} destination Destination object
 * @param {Object} source      Source object
 *
 * @return {Object} Extended object
 */
Zikula.mergeObjects = function(destination, source) {
    destination = destination || {};
    for (var prop in source) {
        try {
            if (source[prop].constructor == Object ) {
                destination[prop] = Zikula.mergeObjects(destination[prop], source[prop]);
            } else {
                destination[prop] = source[prop];
            }
        } catch(e) {
            destination[prop] = source[prop];
        }
    }
    return destination;
};

/**
 * Encode json data to url safe format.
 * @param {mixed}   data Data to encode
 * @param {Boolean} [json=true] Should data be also encode to json
 *
 * @return {String} Encoded data
 */
Zikula.urlsafeJsonEncode = function(data, json) {
    json = Object.isUndefined(json) ? true : json;
    if (json) {
        data = Object.toJSON(data);
    }
    data = data.replace(/\+/g, '%20');
    return encodeURIComponent(data);
};

/**
 * Decode json data from url safe format.
 * @param {String}   data Data to encode
 * @param {Boolean} [json=true] Should data be also decode from json
 *
 * @return {mixed} Decoded data
 */
Zikula.urlsafeJsonDecode = function(data, json) {
    json = Object.isUndefined(json) ? true : json;
    data = data.replace(/\+/g, '%20');
    data = decodeURIComponent(data);
    if (json) {
        data = data.evalJSON(true);
    }
    return data;
};


Zikula.Gettext = Class.create(/** @lends Zikula.Gettext.prototype */{
    /**
     * Regexp used for validating plural forms
     *
     * @private
     * @type RegExp
     */
    pluralsPattern: /^(nplurals=\d+;\s{0,}plural=[\s\d\w\(\)\?:%><=!&\|]+)\s{0,};\s{0,}$/i,
    /**
     * Null char used as delimiter for plural forms
     * 
     * @private
     * @type String
     */
    nullChar: '\u0000',
    /**
     * Javascript Gettext implementation for Zikula.
     * 
     * Base class for javascript gettext implementation. It runs internal and
     * exports utility methods to global Zikula object.
     * This are {@link Zikula#__}, {@link Zikula#__f}, {@link Zikula#_n} and {@link Zikula#_fn}.<br />
     * Usage is quite the same as PHP gettext
     * @example
     * Zikula.__('hello','module_foo');
     * Zikula.__f('hello %s',['A'],'module_foo');
     * Zikula._n('hello my friend','hello my friends',2,'module_foo');
     * Zikula._fn('hello my friend %s','hello my friends %s',2,['A','B'],'module_foo')
     *
     * @class Zikula.Gettext
     * @constructs
     * 
     * @param {String} [lang] Language for translations
     * @param {Object} [data] Data with translations
     *
     * @return {Zikula.Gettext} New Zikula.Gettext instance
     */
    initialize: function(lang, data) {
        this.defaults = {
            lang: 'en',
            domain: 'zikula_js',
            pluralForms: 'nplurals=2; plural=n == 1 ? 0 : 1;'
        };

        this.data = {};
        this.setup(lang, data);

        this.__ = this.getMessage.bind(this);
        this.__f = this.getMessageFormatted.bind(this);
        this._n = this.getPluralMessage.bind(this);
        this._fn = this.getPluralMessageFormatted.bind(this);
    },
    /**
     * Allows to re-init already initialized gettet instance
     *
     * @param {String} lang   Language for translations
     * @param {Object} data   Data with translations
     * @param {String} domain Default domain to use, optional
     *
     * @return void
     */
    setup: function(lang, data, domain) {
        this.setLang(lang);
        this.setDomain(domain);
        this.addTranslations(data || {});
    },
    /**
     * Adds translations to gettext instance
     *
     * @param {Object} obj Data with translations
     *
     * @return void
     */
    addTranslations: function(obj) {
        Zikula.mergeObjects(this.data, obj);
    },
    /**
     * Setup current gettext language
     *
     * @param {String} lang   Language for translations
     *
     * @return void
     */
    setLang: function(lang) {
        this.lang = lang || this.defaults.lang;
    },
    /**
     * Setup current gettext defaul domain
     *
     * @param {String} domain Default domain to use, optional
     *
     * @return void
     */
    setDomain: function(domain) {
        this.domain = domain || this.defaults.domain;
    },
    /**
     * Reads from translations data
     *
     * @private
     * @param {String} domain The domain in which given key will be searched
     * @param {String} key    Data key to search
     *
     * @return {mixed} Given data key value or empty object
     */
    getData: function(domain, key) {
        domain = domain || this.domain;
        if (this.data[this.lang] && this.data[this.lang][domain] && this.data[this.lang][domain][key]) {
            return this.data[this.lang][domain][key];
        }
        return {};
    },
    /**
     * Gettext: translates message.
     *
     * @example
     * Zikula.__('hello','module_foo');
     * 
     * @param {String} msgid    The message to translate
     * @param {String} [domain] Gettext domain, if no domain is given deafult one is used
     *
     * @return {String} Translated message
     */
    getMessage: function(msgid, domain) {
        return this.getData(domain, 'translations')[msgid] || msgid;
    },
    /**
     * Gettext: translates and format message using sprintf formatting rules.
     *
     * @example
     * Zikula.__f('hello %s',['A'],'module_foo');
     *
     * @param {String} msgid    The message to translate
     * @param {Number} params   Array with zero or more replacements to be made in msgid
     * @param {String} [domain] Gettext domain, if no domain is given deafult one is used
     *
     * @return {String} Translated message
     */
    getMessageFormatted: function(msgid, params, domain) {
        return Zikula.vsprintf(this.getMessage(msgid, domain), params);
    },
    /**
     * Gettext: tlural translation.
     *
     * @example
     * Zikula._n('hello my friend','hello my friends',2,'module_foo');
     *
     * @param {String} singular   Singular message
     * @param {String} plural     Plural message
     * @param {Number} count      Count
     * @param {String} [domain]   Gettext domain, if no domain is given deafult one is used
     *
     * @return {String} Translated message
     */
    getPluralMessage: function(singular, plural, count, domain) {
        var offset = this.getPluralOffset(count, domain),
            key = singular + this.nullChar + plural,
            messages = this.getMessage(key, domain);
        if (messages) {
            return messages.split(this.nullChar)[offset];
        } else {
            return key.split(this.nullChar)[offset];
        }
    },
    /**
     * Gettext: plural formatted translation.
     *
     * @example
     * Zikula._fn('hello my friend %s','hello my friends %s',2,['A','B'],'module_foo')
     *
     * @param {String} singular Singular message
     * @param {String} plural   Plural message
     * @param {Number} count    Count
     * @param {Array}  params   Array with zero or more replacements to be made in singular/plural message
     * @param {String} [domain] Gettext domain, if no domain is given deafult one is used
     *
     * @return {String} Translated message
     */
    getPluralMessageFormatted: function(singular, plural, count, params, domain) {
        return Zikula.vsprintf(this.getPluralMessage(singular, plural, count, domain), params);
    },
    /**
     * Calculates plural offset depending on plural forms
     *
     * @private
     * @param {Number}    count  Count
     * @param {String} domain The domain to be used, if no domain is given deafult one is used
     *
     * @return {Number} Plural offset
     */
    getPluralOffset: function(count, domain) {
        var eq = null,
            nplurals = 0,
            plural = 0,
            n = count || 0;
        try {
            eq = this.getData(domain,'plural-forms').match(this.pluralsPattern)[1];
            eval(eq);
        } catch(e) {
            eq = this.defaults.pluralForms;
            eval(eq);
        }
        if (plural >= nplurals) {
            plural = nplurals - 1;
        }
        return plural;
    }
});
Zikula.GettextInstance = new Zikula.Gettext(Zikula.Config.lang, Zikula._translations);
// Export shortcuts to Zikula global object.
Object.extend(Zikula,{
    __: Zikula.GettextInstance.__,
    __f: Zikula.GettextInstance.__f,
    _n: Zikula.GettextInstance._n,
    _fn: Zikula.GettextInstance._fn
});

Zikula.CookieUtil = Class.create(/** @lends Zikula.CookieUtil */{
    /**
     * Base util class for handling cookies.<br />
     * For standard usage use {@link Zikula.Cookie} - already initialized instance
     * of {@link Zikula.CookieUtil}
     * 
     * @class Zikula.CookieUtil
     * @constructs
     *
     * @param {Object} [options] Config object
     * @param {String} [options.path=Zikula.Config.baseURI] Default path for cookies, if not set Zikula.Config.baseURI will be used
     * @param {String} [options.domain=''] Domain for cookies, if not set current domain will be used
     * @param {Boolean} [options.secure=false] Should cookies be secured (transmitted over secure protocol as https)
     * @param {Boolean} [options.json=true] Should cookies values be encoded to and decoded from json
     *
     * @return {Zikula.CookieUtil} New Zikula.CookieUtil instance
     */
    initialize: function(options) {
        this.options = Object.extend({
            path: Zikula.Config.baseURI,
            domain: '',
            secure: false,
            json: true
        }, options || { });
    },
    /**
     * Create or update cookie.
     *
     * @param {String}       name     Cookie name.
     * @param {mixed}        value    Cookie value.
     * @param {Number|Date} [expires] Expiration date (Date object) or time in seconds, default is session.
     * @param {String}      [path]    Path for cookie, by default Zikula baseURI is set.
     *
     * @return {Boolean} Returns true on success, false otherwise
     */
    set: function(name, value, expires, path){
        try {
            value = this.options.json ? this.encode(value) : value;
            var cookieStr = {
                expires: expires instanceof Date ? expires.toGMTString() : this.secondsFromNow(expires),
                path: path ? path : this.options.path,
                domain: this.options.domain,
                secure: this.options.secure ? 'secure' : ''
            };
            cookieStr = Object.keys(cookieStr).inject(name+'='+value, function(str, key){
                return cookieStr[key] ? str + ';' + key + '=' + cookieStr[key] : str;
            });
            document.cookie = cookieStr;
        } catch (e) {
            return false;
        }
        return true;
    },
    /**
     * Get cookie value.
     * Cookie value is returned in original format as it was stored.
     *
     * @param {String} name Cookie name.
     * @param {Boolean} json Cookie name.
     *
     * @return {mixed} Returns cookie value or null.
     */
    get: function(name, json){
        json = Object.isUndefined(json) ? this.options.json : json;
        var cookie = document.cookie.match(name + '=(.*?)(;|$)');
        return cookie ? (json ? this.decode(cookie[1]) : cookie[1]) : null;
    },
    /**
     * Delete cookie
     *
     * @param {String} name Cookie name.
     *
     * @return {Boolean} Returns true on success, false otherwise
     */
    remove: function(name){
        return this.set(name,'',-1);
    },
    /**
     * Calculates date equal now plus given number of seconds
     *
     * @private
     * @param {Number} seconds Number of seconds
     *
     * @return {String} Date as GMT string
     */
    secondsFromNow: function(seconds) {
        if (!seconds) {
            return null;
        }
        var d = new Date();
        d.setTime(d.getTime() + (seconds * 1000));
        return d.toGMTString();
    },
    /**
     * Encode given value to format safe to store in cookies.
     * Due to PHPIDS original JSON format is encoded using encodeURI
     *
     * @private
     * @param {mixed} value Value to encode
     *
     * @return {String} Encoded value
     */
    encode: function(value) {
        return encodeURI(encodeURI(Object.toJSON(value)));
    },
    /**
     * Decode given string to original format
     *
     * @private
     * @param {String} value String to decode
     *
     * @return {mixed} Decoded value
     */
    decode: function(value) {
        return decodeURI(decodeURI(value)).evalJSON(true);
    }
});
/**
 * Util class for creating cookies.<br />
 * Cookie data is stored in JSON format so all data types valid for JSON can be stored
 * (this are string, number, object, array, true, false, null).<br />
 * Retruned data is always converted to original format. There is no need to prepare it for store.<br />
 *
 * @example
 * Zikula.Cookie.set('cookiename','cookievalue');
 * Zikula.Cookie.get('cookiename'); // 'cookievalue'
 * // you may also use more complicted values:
 * Zikula.Cookie.set('somedata', {arraydata: [1,2,3], bool: true, foo: 'bar'}
 * Zikula.Cookie.get('somedata'); // {arraydata: [1,2,3], bool: true, foo: 'bar'}
 * 
 * @class
 * 
 * @borrows Zikula.CookieUtil.set as #set
 * @borrows Zikula.CookieUtil.get as #get
 * @borrows Zikula.CookieUtil.remove as #remove
 */
Zikula.Cookie = new Zikula.CookieUtil();

/**
 * Extensions for prototype Element.Methods
 * @class
 * @name Element.Methods
 * @see http://github.com/kangax/protolicious/blob/master/element.methods.js
 */

/**
 * Element.getContentWidth(@element) -> Number
 * returns element's "inner" width - without padding/border dimensions
 *
 * @example $(someElement).getContentWidth(); // 125
 * @extends Element.Methods
 * @param {HTMLElement|String} element Element id
 *
 * @return {Number} Element content width
 */
Element.Methods.getContentWidth = function(element) {
    return ['paddingLeft', 'paddingRight', 'borderLeftWidth', 'borderRightWidth']
        .inject(Element.getWidth(element), function(total, prop) {
            return total - (parseInt(Element.getStyle(element, prop), 10) || 0);
        });
};

/**
 * Element.getContentHeight(@element) -> Number
 * returns element's "inner" height - without padding/border dimensions
 *
 * @example $(someElement).getContentHeight(); // 141
 * @extends Element.Methods
 * @param {HTMLElement|String} element Element id
 *
 * @return {Number} Element content height
 */
Element.Methods.getContentHeight = function(element) {
    return ['paddingTop', 'paddingBottom', 'borderTopWidth', 'borderBottomWidth']
        .inject(Element.getHeight(element), function(total, prop) {
            return total - (parseInt(Element.getStyle(element, prop), 10) || 0);
        });
};

/**
 * Element.setWidth(@element, width) -> @element
 * sets element's width to a specified value
 * or to a value of its content width (if value was not supplied)
 *
 * @example $(someElement).setWidth(); <br />$(someOtherElement).setWidth(100);
 * @extends Element.Methods
 * @param {HTMLElement|String} element Element id
 * @param {Number} [width] Element width, default is current element content width
 *
 * @return {HTMLElement} Element
 */
Element.Methods.setWidth = function(element, width) {
    return Element.setStyle(element, {
        width: (Object.isUndefined(width) ? Element.getContentWidth(element) : width) + 'px'
    });
};

/**
 * Element.setHeight(@element, height) -> @element
 * sets element's height to a specified value
 * or to a value of its content height (if value was not supplied)
 *
 * @example $(someElement).setHeight(); <br />$(someOtherElement).setHeight(68);
 * @extends Element.Methods
 * @param {HTMLElement|String} element Element id
 * @param {Number} [height] Element height, default is current element content height
 *
 * @return {HTMLElement} Element
 */
Element.Methods.setHeight = function(element, height) {
    return Element.setStyle(element, {
        height: (Object.isUndefined(height) ? Element.getContentHeight(element) : height) + 'px'
    });
};

/**
 * Element.getOutlineSize(@element, type) -> Number
 * calculates element's "outline" size based on given type.
 * As "outline" properties, such as borders and margins are counted.
 * Type can be:
 * - vertical (v) - top and bottom are counted
 * - horizontal (h) - left and right are counded
 * - left, right, top, bottom
 *
 * @example $(someElement).getOutlineSize('vertical'); // 10
 * @extends Element.Methods
 * @param {HTMLElement|String} element Element id
 * @param {String} type    Type of outline
 *
 * @return {Number} The sum of the given properties
 */
Element.Methods.getOutlineSize = function(element, type) {
    type = type ? type.toLowerCase() : 'vertical';
    var props;
    switch(type) {
        case 'vertical':
        case 'v':
            props = ['borderTopWidth','borderBottomWidth','marginTop','marginBottom'];
            break;
        case 'horizontal':
        case 'h':
            props = ['borderLeftWidth','borderRightWidth','marginLeft','marginRight'];
            break;
        default:
            props = [('margin-'+type).camelize(),('border-'+type+'-Width').camelize()];
    }
    return props
        .inject(0, function(total, prop) {
            return total + (parseInt(Element.getStyle(element, prop), 10) || 0);
        });
};
/**
 * Fix for https://prototype.lighthouseapp.com/projects/8886-prototype/tickets/771
 * @private
 * @param {HTMLElement|String} element
 * @return {HTMLElement} "Offset" parent element
 */
Element.Methods.getOffsetParent = function(element) {
    if (element.offsetParent) {
        return $(element.offsetParent);
    }
    if (element == document.body) {
        return $(element);
    }
    //    while ((element = element.parentNode) && element != document.body)
    while ((element = element.parentNode) && element != document.body && element != document) {
        if (Element.getStyle(element, 'position') != 'static') {
            return $(element);
        }
    }
    return $(document.body);
};
// Apply new methods to prototype Element
Element.addMethods();

/**
 * This will be removed.
 * @ignore
 */
Object.extend(String.prototype, (function() {
    function toUnits(unit) {
        return (parseInt(this) || 0) + (unit || 'px');
    }
    return {
        toUnits:         toUnits
    };
})());
/**
 * This will be removed.
 * @ignore
 */
Object.extend(Number.prototype, (function() {
    function toUnits(unit) {
        return (this.valueOf() || 0) + (unit || 'px');
    }
    return {
        toUnits:         toUnits
    };
})());


/**
 * Zikula Ajax namespace
 *
 * @name Zikula.Ajax
 * @namespace Zikula UI namespace
 *
 */
Zikula.define('Ajax');

Zikula.Ajax.Request = Class.create(Ajax.Request,/** @lends Zikula.Ajax.Request.prototype */{
    /**
     * Custom extension for Prototype Ajax.Request
     * Inherits all of the methods, options and events from
     * <a href="http://api.prototypejs.org/ajax/ajax/request/">Prototype Ajax.Request</a>.
     * First of all this extensions adds form request http header containing csrf token,
     * so there's no need to add it maunaly.
     * However, if for some reason it is necessary to send a csrf token - this extension
     * adds new options 'authid' and 'csrfToken'.
     * This extension also extend response object with {@link Zikula.Ajax.Response}, which adds
     * new methods to read data retruned by php controlers.
     * When 'authid' or 'csrfToken' param is added then it's value will be automatically added to request
     * and if new one will come with response - it will be also updated.
     * This is recommended way for handling authids or csrfTokens.
     *
     * @example
     * // note - $super param is omitted
     * new Zikula.Ajax.Request('index.php?module=mymodule&type=ajax&func=myfunc',{
     *     authid: 'authidElementId',
     *     parameters: {
     *         id: someID,
     *         foo: bar
     *     }
     *     onComplete: yourCompleteCallbackFunction
     * });
     *
     * @class Zikula.Ajax.Request
     * @constructs
     *
     * @param {Ajax.Request} $super Reference to super class, this is private param, do not use it.
     * @param {String} url Url for request
     * @param {Object} [options] Config object
     * @param {String} [options.authid=null] ID for authid - it may point to authid input or it's parent (eg form id)
     * @param {String} [options.csrfToken=null] ID for csrfToken - it may point to csrfToken input or it's parent (eg form)
     *
     * @return {Zikula.Ajax.Request} New Zikula.Ajax.Request instance
     */
    initialize: function($super, url, options) {
        options = this.initResponseHandlers(Zikula.Ajax.Request.defaultOptions(options));
        options = Object.extend({
            authid: null,
            csrfToken: null
        }, options || { });
        if (options.authid || options.csrfToken) {
            this.token = {
                name: options.csrfToken ? 'csrftoken' : 'authid',
                source: options.csrfToken ? options.csrfToken : options.authid
            };
            if (Object.isFunction($(this.token.source).getValue)) {
                this.token.element = $(this.token.source);
            } else {
                this.token.element = $(this.token.source).down('input[name='+this.token.name+']').identify();
            }
        } else {
            this.token = false;
        }
        if (this.token) {
            var pars = options.parameters || {};
            this.token.value = $F(this.token.element);
            if (Object.isString(pars)) {
                options.parameters = pars + '&'+this.token.name+'=' + this.token.value;
            } else {
                pars[this.token.name] = this.token.value;
                options.parameters = pars;
            }
            options.onComplete = this.responseComplete.bind(this);
        }
        $super(url, options);
    },
    /**
     * Adds internal callbacks for ajax request
     * Each callback provided in Zikula.Ajax.Request constructor need to be prefetched
     * to extend response object.
     *
     * @private
     * @param {Object} options Options
     *
     * @return {Object} Modyfied options object
     */
    initResponseHandlers: function(options) {
        options = options || {};
        this.observers = {};
        // ugly hack to find all callbacks in options as properties which names starts with "on"
        for (var prop in options) {
            if (prop.startsWith('on') && Object.isFunction(options[prop])) {
                this.observers[prop] = options[prop];
                options[prop] = this.responseHandler.curry(prop).bind(this);
            }
        }
        return options;
    },
    /**
     * Internal response handler
     * Extends response object with {Zikula.Ajax.Response} and calls original callback
     *
     * @private
     * @param {String} event Callback event name
     * @param {Ajax.Response} response Response object returned by Ajax.Request
     * @param {Object|Array} headerJSON
     *
     * @return void
     */
    responseHandler: function(event, response, headerJSON) {
        if (this.observers[event]) {
            response = Object.extend(response, Zikula.Ajax.Response);
            this.observers[event](response, headerJSON);
        }
    },
    /**
     * Internal response handler for onComplete event
     * Updates authid element, if new value is given in response
     *
     * @private
     * @param {Ajax.Response} response Response object returned by Ajax.Request
     * @param {Object|Array} headerJSON
     *
     * @return void
     */
    responseComplete: function(response, headerJSON) {
        response = Object.extend(response, Zikula.Ajax.Response);
        if (this.token) {
            $(this.token.element).setValue(response.getToken(this.token.name));
        }
        if (this.observers.onComplete) {
            this.observers.onComplete(response,headerJSON);
        }
    }
});

Object.extend(Zikula.Ajax.Request,/** @lends Zikula.Ajax.Request.prototype */{
    /**
     * Static method allowing to extend Zikula.Ajax.Request options with default values.
     * In particular it:
     * - adds custom request header with csrf token
     * - sets request method to post
     * 
     * @static
     * @name Zikula.Ajax.Request.defaultOptions
     * @function
     * 
     * @param {Object} options Options object for Zikula.Ajax.Request
     *
     * @return {Object} Options object extended with default values
     */
    defaultOptions: function(options){
        options = Object.extend({
            method: 'POST'
        }, options || { });
        if (Zikula.Config.sessionName) {
            var sessionId = Zikula.Cookie.get(Zikula.Config.sessionName, false);
            if (sessionId) {
                options.requestHeaders = Object.extend({
                    'X-ZIKULA-AJAX-TOKEN': sessionId
                }, options.requestHeaders || { });
            }
        }
        return options;
    }
});

/**
 * Custom extension for Prototype Ajax.Response
 * Inherit all of the methods, options and events from
 * <a href="http://api.prototypejs.org/ajax/ajax/response/">Prototype Ajax.Response</a>.
 * This extension adds new public methods to response object.
 * It's recommended to obtain data from response using this methods rather than
 * reading responseText directly.
 *
 * @class Zikula.Ajax.Response
 */
Zikula.Ajax.Response = /** @lends Zikula.Ajax.Response */{
    /**
     * Get authid token from response
     *
     * @return {String|null} Authid token
     */
    getAuthid: function() {
        return this.getToken('authid');
    },
    /**
     * Get csrf token token from response.
     * By default it returns new csrf tokens, but it may return legacy 'authid' token,
     * when 'authid' is passed as tokenName
     *
     * @param {String} [tokenName=token] Name of the token
     * 
     * @return {String|null} Csrf token value
     */
    getToken: function(tokenName) {
        this.tokenName = tokenName || 'token';
        return this.decodeResponse().core ? this.decodeResponse().core[this.tokenName] : null;
    },
    /**
     * Get status or error messages from response
     * Note - it is possible to get more then one message from response, so this method
     * may return simple string or object with numeric keys and multiple messages.
     *
     * @return {String|Object} Message or object with multiple messages
     */
    getMessage: function() {
        return this.decodeResponse().core ? this.decodeResponse().core.statusmsg : null;
    },
    /**
     * Get data returned by module controller
     *
     * @return {mixed} Data returned by module controller
     */
    getData: function() {
        return this.decodeResponse().data;
    },
    /**
     * Get core data from response
     *
     * @private
     *
     * @return {mixed}
     */
    getCoreData: function() {
        return this.decodeResponse().core;
    },
    /**
     * Tests whether the request was successful.
     *
     * @return {Boolean} True on success, false otherwise
     */
    isSuccess: function() {
        var status = this.getStatus();
        return !this.transport.isAborted && (!status || (status >= 200 && status < 300) || status == 304);
    },
    /**
     * Decodes responseText
     *
     * @private
     *
     * @return {Object} Decoded response text
     */
    decodeResponse: function() {
        if (!this.ZikulaResponse) {
            try {
                this.ZikulaResponse = this.responseText.evalJSON(true);
            } catch(e) {
                this.ZikulaResponse = {
                    data: this.responseText,
                    core: null
                };
            }
        }
        return this.ZikulaResponse;
    }
};

Object.extend(Zikula.Ajax.Response,/** @lends Zikula.Ajax.Response.prototype */{
    /**
     * Static method allowing to extend prototype Ajax.Response with Zikula.Ajax.Response methods
     * 
     * @static
     * @name Zikula.Ajax.Response.extend
     * @function
     * 
     * @param {Object} response Ajax.Response object
     *
     * @return {Zikula.Ajax.Request} Response extended with Zikula.Ajax.Request methods
     */
    extend: function(response){
        return Object.extend(response,Zikula.Ajax.Response);
    }
});

Zikula.Ajax.Queue = Class.create(/** @lends Zikula.Ajax.Queue.prototype */{
    /**
     * Class for creating ajax requests queue.
     * Each request from queue is executed using {@link Zikula.Ajax.Request} class.
     * Requests are send when previous one is completed. By default queue will stop
     * on first non successful request (eg not found, forbidden etc).
     * Using requestOptions option it is possible to pass to queue common params for each request.
     *
     * @example
     * var queue = new Zikula.Ajax.Queue({
     *     onFinish: yourCallbackFunction,
     *     requestOptions: {
     *         onSuccess: yourCallbackForSuccessRequests
     *     }
     * });
     * queue.add('index.php?module=mymodule&type=ajax&func=myfunc',{
     *     authid: 'authidElementId',
     *     parameters: {
     *         id: someID,
     *         foo: bar
     *     }
     * );
     * queue.add('index.php?module=foo&type=ajax&func=bar');
     * queue.start();
     *
     * @class Zikula.Ajax.Queue
     * @constructs
     *
     * @param {Object} [options] Config object
     * @param {Boolean} [options.stopOnError=true] Should queue stop on first error response
     * @param {Boolean} [options.autoExecute=false] If set to true - each new request added to queue will be automaticaly executed without need to use {@link Zikula.Ajax.Queue#start} method
     * @param {Object} [options.requestOptions=null] Object with request option common for each request from queue
     * @param {Function} [options.onComplete=null] Callback called after each request is completed, with response and headerJSON params (the same as prototype Ajax.Request callbacks).
     * @param {Function} [options.onFinish=null] Callback called after queue is finished, with boolean flag param, which tells if queue was successfully finished (true) or stopped (false, due to request error or stop method).
     *
     * @return {Zikula.Ajax.Queue} New Zikula.Ajax.Queue instance
     */
    initialize: function(options) {
        this.options = Object.extend({
            stopOnError: true,
            autoExecute: false,
            requestOptions: {}
        }, options || { });
        this.queue = [];
        this.inProgress = false;
    },
    /**
     * Add new {@link Zikula.Ajax.Request} to queue.
     * All params are passed to Zikula.Ajax.Request constructor.
     * Options object can be extended with options.requestOptions from Zikula.Ajax.Queue constructor.
     * options param can be omitted.
     *
     * @example
     * // all params
     * queue.add('index.php?module=mymodule&type=ajax&func=myfunc',{
     *     authid: 'authidElementId',
     *     parameters: {
     *         id: someID,
     *         foo: bar
     *     },
     *     true
     * );
     *
     * // options param can be omitted
     * queue.add('index.php?module=mymodule&type=ajax&func=myfunc',true);
     *
     * // requests can be added as array
     * queue.add([
     *     'index.php?module=mymodule&type=ajax&func=myfunc&n=1', // this can be single url
     *     'index.php?module=mymodule&type=ajax&func=myfunc&n=2',
     *     ['index.php?module=mymodule&type=ajax&func=myfunc&n=3,{onComplete: doSomething}] // or complete array with url and other params
     * );
     *
     * @param {String|Array} url Request url or array of requests params (it can be simple array or urls or array or arrays with single entry containing url, options, execute params)
     * @param {Object} [options=null] Options for request
     * @param {Boolean} [execute=null] Should request be autoexecuted - if set to true, whole queue will be ececuted
     *
     * @return void
     */
    add: function(url, options, execute) {
        if (Object.isUndefined(execute) && typeof(options) != 'object') {
            execute = options;
            options = {};
        }
        if (Object.isArray(url)) {
            Array.prototype.push.apply(this.queue, url);
        } else {
            this.queue.push([url, options || {}]);
        }
        if (this.options.autoExecute || execute) {
            this.start();
        }
    },
    /**
     * Clears the queue and  stops queue execution on first, not send request.
     *
     * @return void
     */
    clear: function() {
        this.stop();
        this.queue = [];
    },
    /**
     * Starts queue execution.
     *
     * @return void
     */
    start: function() {
        if (this.inProgress) {
            return;
        }
        this.inProgress = true;
        this.stopped = false;
        this.send();
    },
    /**
     * Stops queue execution on first, not send request.
     *
     * @return void
     */
    stop: function() {
        this.stopped = true;
        this.inProgress = false;
    },
    /**
     * Sends next request from queue.
     *
     * @private
     *
     * @return void
     */
    send: function() {
        if (this.queue.size() == 0 || this.stopped) {
            this.inProgress = false;
            if (Object.isFunction(this.options.onFinish)) {
                this.options.onFinish(!this.stopped);
            }
            this.stopped = false;
            return;
        }
        var params = this.getParams();
        new Zikula.Ajax.Request(params[0], params[1]);
    },
    /**
     * Prepares request params.
     *
     * @private
     *
     * @return {Array} Array of params (url and options) form request
     */
    getParams: function() {
        var params = this.queue.shift(),
            url = Object.isArray(params) ? params[0] : params,
            options = Object.extend(Object.isArray(params) ? params[1] || {} : {}, this.options.requestOptions || {});
        if (!Object.isUndefined(options.onComplete)) {
            this.notify = options.onComplete;
        } else {
            this.notify = null;
        }
        options.onComplete = this.onComplete.bind(this);
        return [url, options];
    },
    /**
     * Internal callback called after request is complete
     *
     * @private
     * @param {Ajax.Response} response Response object returned by Ajax.Request
     * @param {Object|Array} headerJSON
     *
     * @return void
     */
    onComplete: function(response, headerJSON) {
        response = Zikula.Ajax.Response.extend(response);
        if (Object.isFunction(this.notify)) {
            this.notify(response,headerJSON);
        }
        if (Object.isFunction(this.options.onComplete)) {
            this.options.onComplete(response, headerJSON);
        }
        if (this.options.stopOnError && !response.isSuccess()) {
            this.stopped = true;
        }
        this.send();
    }
});

