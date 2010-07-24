// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

if (typeof(Zikula) == 'undefined') {
    /**
     * @class Zikula global object
     *
     */
    Zikula = {};
}

/**
 * Zikula.define
 * Creates namespace in Zikula scope through nested chain of objects, based on the given path
 * Example:
 * Zikula.define('Module.Component') will create object chain: Zikula.Module.Component
 * If object in chain already exists it will be extended, not overwritten
 * 
 * @member Zikula
 * @param {string} path Dot separated path to define.
 * 
 * @return {object} Zikula extended object
 */
Zikula.define = function(path) {
    return path.split('.').inject(Zikula, function(object, prop) {
        return object[prop] = object[prop] || { };
    })
}

/**
 * Zikula.init
 * Load what's needed on dom loaded.
 *
 * @return void
 */
Zikula.init = function()
{
    if(Zikula.Browser.IE) {
        Zikula.fixbuttons();
    }
}
document.observe('dom:loaded',Zikula.init);

/**
 * Zikula.Browser
 * extends prototype Browser detection.
 *
 * @return {object} Object with browsers info
 */
Zikula.Browser = (function(){
    var IES = {IE6:false,IE7:false,IE8:false,IE8e7:false};
    if(Prototype.Browser.IE) {
        if (document.documentMode != 'undefined' && document.documentMode == 8) {
            IES.IE8 = true;
        } else if (typeof document.documentElement.style.maxHeight != 'undefined'){
            IES.IE7 = true;
            IES.IE8e7 = (typeof document.documentMode != 'undefined'); //IE8 in IE7 mode
        } else {
            IES.IE6 = true;
        }
    }
    return Object.extend(IES,Prototype.Browser);
  })()

/**
 * Zikula.dejsonize
 * Decodes json data to original format
 *
 * @param {string} jsondata JSONized array in utf-8 (as created by AjaxUtil::output).
 *
 * @return {mixed} Decoded data
 */
Zikula.dejsonize = function(jsondata)
{
    var result;
    try {
        result = jsondata.evalJSON(true);
    } catch(error) {
        alert('illegal JSON response: \n' + error + 'in\n' + jsondata);
    }
    return result;
}

/**
 * Zikula.showajaxerror
 * Shows an error message with alert().
 *
 * @todo beautify this
 *
 * @param {string} errortext The text to show.
 *
 * @return void
 */
Zikula.showajaxerror = function(errortext)
{
    alert(errortext);
    return;
}

/**
 * Zikula.ajaxResponseError
 * Manage ajax error responses returned by AjaxUtil.
 *
 * @param {object}  transport    Transport object returned by Ajax.Request.
 * @param {bool}    supresserror Should error message be supressed.
 *
 * @return {mixed} Decoded transport data or void
 */
Zikula.ajaxResponseError = function(transport, supresserror)
{
	var json = pndejsonize(transport.responseText);
	if ("authid" in json) {
		if (json.authid != '') {
		    Zikula.updateauthids(json.authid);
		}
	}
	if (json.displayalert == '1' && supresserror != true) {
		Zikula.showajaxerror(json.errormessage);
	}
	return json;
}

/**
 * Zikula.setselectoption
 * Sets a select to a given value.
 *
 * @param {string} id  Select id.
 * @param {string} sel The value that should be selected.
 *
 * @return void
 */
Zikula.setselectoption = function(id, sel)
{
    $A($(id).options).each(function(opt){opt.selected = (opt.value == sel);});
}

/**
 * Zikula.getcheckboxvalue
 * Gets the value of a checkbox depending on the state.
 *
 * @deprecated
 * @see Prototype $F
 *
 * @param {string} id Checkbox id.
 *
 * @return {string} Checkbox value
 */
Zikula.getcheckboxvalue = function(id)
{
    return $F(id) || '';
}

/**
 * Zikula.updateauthids
 * Updates all hidden authid fields with a new authid obtained with an ajax call.
 *
 * @param {string} authid The new authid.
 * 
 * @return void
 */
Zikula.updateauthids = function(authid)
{
    if(authid.length != 0) {
        $$('form input[name=authid]').invoke('writeAttribute','value',authid);
    }
    return;
}

/**
 * Zikula.recolor
 * Set z-odd / z-even on each li after append, move and delete.
 *
 * @param   {string} listclass   Class applied to the list of items.
 * @param   {string} headerclass Class applied to the header of the list.
 * 
 * @return  void
 */
Zikula.recolor = function(listclass, headerclass)
{
    var pnodd = true;

    $A($(listclass).childNodes).each(
        function(node)
        {
            if (Element.hasClassName(node, headerclass)) {
            } else {
                Element.removeClassName(node, 'z-odd');
                Element.removeClassName(node, 'z-even');

                if (pnodd == true) {
                    Element.addClassName(node, 'z-odd');
                } else {
                    Element.addClassName(node, 'z-even');
                }
                pnodd = !pnodd;
            }
        }
        );
}

/**
 * Zikula.switchdisplaystate
 * Change the display attribute of an specific object.
 *
 * @param   {string} id Id of the object to hide/show.
 * 
 * @return  void
 */
Zikula.switchdisplaystate = function(id)
{
    var pntmpobj = $(id);

    if (pntmpobj.getStyle('display') == 'none') {
        if (typeof(Effect) != "undefined") {
            Effect.BlindDown(pntmpobj);
        } else {
            pntmpobj.show();
        }
    } else {
        if (typeof(Effect) != "undefined") {
            Effect.BlindUp(pntmpobj);
        } else {
            pntmpobj.hide();
        }
    }
}

/**
 * Zikula.radioswitchdisplaystate
 * Change the display attribute of an specific container depending of a radio input.
 *
 * @param  {string} idgroup       Id of the container where the radio input to observe are.
 * @param  {string} idcontainer   Id of the container to hide/show.
 * @param  {bool}   state         State of the radio to show the idcontainer.
 *
 * @return void
 */
Zikula.radioswitchdisplaystate = function(idgroup, idcontainer, state)
{
    var objgroup = $(idgroup);
    var objcont = $(idcontainer);

    check_state = objgroup.select('input[type=radio][value="1"]').pluck('checked').any();

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
}

/**
 * Zikula.checkboxswitchdisplaystate
 * Change the display attribute of an specific container depending of a checkbox input.
 *
 * @param  {string} idcheckbox    Id of the checkbox input to observe.
 * @param  {string} idcontainer   Id of the container to hide/show.
 * @param  {bool}   state         State of the checkbox to show the idcontainer.
 *
 * @return void
 */
Zikula.checkboxswitchdisplaystate = function(idcheckbox, idcontainer, state)
{
    var objcont = $(idcontainer);

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
}

/**
 * Zikula.fixbuttons
 * Workaround for wrong buttons values in IE and multiple submit buttons in IE6/7.
 *
 * @return void
 */
Zikula.fixbuttons = function()
{
    $$('button').invoke('observe','click',function(e){
        var form = e.element().up('form');
        if(form) {
            form.store('buttonClicked',e.element().identify());
        }
    });

    $$('form').invoke('observe','submit',function(e){
        var form = e.element(),
            buttonClicked = form.retrieve('buttonClicked',null);
        form.select('button').each(function(b){
            b.disabled = true;
            if(b.identify() == buttonClicked) {
                form.insert(new Element('input',{type:'hidden',name:b.name,value:b.attributes.getNamedItem('value').nodeValue}));
            }
        });
    });
}

/**
 * Ajax timeout detection. We set the time out to 5 seconds
 * taken from http://codejanitor.com/wp/2006/03/23/ajax-timeouts-with-prototype/
 *
 * @param {object} xmlhttp Transport object returned by Ajax.Request.
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
}

// Register global responders that will occur on all AJAX requests
Ajax.Responders.register({
    onCreate: function(request) {
        if($('ajax_indicator')) {
            Element.show('ajax_indicator');
        }
        request['timeoutId'] = window.setTimeout(
            function() {
                // If we have hit the timeout and the AJAX request is active, abort it and let the user know
                if (Zikula.callInProgress(request.transport)) {
                    request.transport.abort();
                    if($('ajax_indicator') && $('ajax_indicator').tagName == 'IMG') {
                        $('ajax_indicator').src = Zikula.Config.baseURL + 'images/icons/extrasmall/error.gif';
                    }
                    pnshowajaxerror('Ajax connection time out!');
                    // Run the onFailure method if we set one up when creating the AJAX object
                    if (request.options['onFailure']) {
                        request.options['onFailure'](request.transport, request.json);
                    }
                }
            },
            (typeof(Zikula.Config.ajaxtimeout)!='undefined' && Zikula.Config.ajaxtimeout!=0)  ? Zikula.Config.ajaxtimeout : 5000 // per default five seconds - can be changed in the settings
        );
    },
    onComplete: function(request) {
        if($('ajax_indicator')) {
            Element.hide('ajax_indicator');
        }
        // Clear the timeout, the request completed ok
        window.clearTimeout(request['timeoutId']);
    }
});


/**
 * @deprecated
 * @see Zikula.dejsonize
 */
function pndejsonize(jsondata)
{
    return Zikula.dejsonize(jsondata)
}

/**
 * @deprecated
 * @see Zikula.showajaxerror
 */
function pnshowajaxerror(errortext)
{
    return Zikula.showajaxerror(errortext);
}

/**
 * @deprecated
 * @see Zikula.setselectoption
 */
function pnsetselectoption(id, sel)
{
    return Zikula.setselectoption(id, sel);
}

/**
 * @deprecated
 * @see Zikula.getcheckboxvalue
 */
function pngetcheckboxvalue(id)
{
    return Zikula.getcheckboxvalue(id);
}

/**
 * @deprecated
 * @see Zikula.updateauthids
 */
function pnupdateauthids(authid)
{
    return Zikula.updateauthids(authid);
}

/**
 * @deprecated
 * @see Zikula.callInProgress
 */
function callInProgress(xmlhttp)
{
    return Zikula.callInProgress(xmlhttp);
}

/**
 * @deprecated
 * @see Zikula.recolor
 */
function pnrecolor(listclass, headerclass)
{
    return Zikula.recolor(listclass, headerclass);
}

/**
 * @deprecated
 * @see Zikula.switchdisplaystate
 */
function switchdisplaystate(id)
{
    return Zikula.switchdisplaystate(id);
}

/**
 * @deprecated
 * @see Zikula.radioswitchdisplaystate
 */
function radioswitchdisplaystate(idgroup, idcontainer, state)
{
    return Zikula.radioswitchdisplaystate(idgroup, idcontainer, state);
}

/**
 * @deprecated
 * @see Zikula.checkboxswitchdisplaystate
 */
function checkboxswitchdisplaystate(idcheckbox, idcontainer, state)
{
    return Zikula.checkboxswitchdisplaystate(idcheckbox, idcontainer, state);
}

/**
 * Zikula.str_repeat
 * Javascript implementation of PHP str_repeat function.
 *
 * @param {string} i The string to be repeated.
 * @param {int}    m Number of time the input string should be repeated.
 *
 * @return {string} The repeated string.
 * @author Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
 * @link http://www.diveintojavascript.com/projects/sprintf-for-javascript
 */
Zikula.str_repeat = function(i, m) {
    for (var o = []; m > 0; o[--m] = i);
    return o.join('');
}

/**
 * Zikula.sprintf
 * Javascript implementation of PHP sprintf function.
 *
 * @param {string} format Format string. PHP sprintf syntax is used.
 * @param {mixed}  args   Zero or more replacements to be made according to format string.
 *
 * @return {string} A string produced according to the formatting string format.
 * @author Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
 * @link http://www.diveintojavascript.com/projects/sprintf-for-javascript
 */
Zikula.sprintf = function () {
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
                case 'b':a = a.toString(2);break;
                case 'c':a = String.fromCharCode(a);break;
                case 'd':a = parseInt(a);break;
                case 'e':a = m[6] ? a.toExponential(m[6]) : a.toExponential();break;
                case 'f':a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a);break;
                case 'o':a = a.toString(8);break;
                case 's':a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a);break;
                case 'u':a = Math.abs(a);break;
                case 'x':a = a.toString(16);break;
                case 'X':a = a.toString(16).toUpperCase();break;
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
}

/**
 * Zikula.vsprintf
 * Javascript implementation of PHP vsprintf function.
 *
 * @param {string} format Format string. PHP sprintf syntax is used.
 * @param {array}  args   Array with zero or more replacements to be made according to format string.
 *
 * @return {string} A string produced according to the formatting string format.
 */
Zikula.vsprintf = function(format, args) {
    return Zikula.sprintf.apply(this,[format].concat(args));
}

/**
 * Merge two objects recursively.
 *
 * Copies all properties from source to destination object and returns new object.
 * If proprety exists in destination it is extended not overwritten
 *
 * @param {object} destination Destination object
 * @param {object} source      Source object
 *
 * @return {object} Extended object
 */
Zikula.mergeObjects = function(destination,source)
{
    destination = destination || {};
    for (var prop in source) {
        try {
            if (source[prop].constructor==Object ) {
                destination[prop] = Zikula.mergeObjects(destination[prop], source[prop]);
            } else {
                destination[prop] = source[prop];
            }
        } catch(e) {
            destination[prop] = source[prop];
        }
    }
    return destination;
}

/**
 * Javascript gettext implementation
 *
 * @class Zikula.Gettext
 * @constructor
 *
 * @param {string} lang Language for translations
 * @param {object} data Data with translations
 *
 * @return New gettext object
 */
Zikula.Gettext = Class.create({
    /**
     * Defaults options
     * 
     * @memberOf Zikula.Gettext
     * @private
     * @type object
     */
    defaults: {
        lang: 'en',
        domain: 'zikula',
        pluralForms: 'nplurals=2; plural=n == 1 ? 0 : 1;'
    },
    /**
     * Regexp used for validating plural forms
     *
     * @memberOf Zikula.Gettext
     * @private
     * @type RegExp
     */
    pluralsPattern: /^(nplurals=\d+;\s{0,}plural=[\s\d\w\(\)\?:%><=!&\|]+)\s{0,};\s{0,}$/i,
    /**
     * Null char used as delimiter for plural forms
     * 
     * @memberOf Zikula.Gettext
     * @private
     * @type string
     */
    nullChar: '\u0000',
    /**
     * Zikula.Gettext constructor
     *
     * @memberOf Zikula.Gettext
     * @private
     * @param {string} lang Language for translations
     * @param {object} data Data with translations
     *
     * @return New gettext object
     */
    initialize: function(lang,data) {
        this.data = {};
        this.setup(lang,data);
        /**
         * Alias for {@link Zikula.Gettext.getMessage}
         * @memberOf Zikula.Gettext
         * @name __
         * @function
         */
        this.__ = this.getMessage.bind(this);
        this.__f = this.getMessageFormatted.bind(this);
        this._n = this.getPluralMessage.bind(this);
        this._fn = this.getPluralMessageFormatted.bind(this);
    },
    /**
     * Allows to re-init already initialized gettet instance
     *
     * @memberOf Zikula.Gettext
     * @param {string} lang   Language for translations
     * @param {object} data   Data with translations
     * @param {string} domain Default domain to use, optional
     *
     * @return void
     */
    setup: function(lang,data,domain) {
        this.setLang(lang);
        this.setDomain(domain);
        this.addTranslations(data || {})
    },
    /**
     * Adds translations to gettext instance
     *
     * @memberOf Zikula.Gettext
     * @param {object} obj Data with translations
     *
     * @return void
     */
    addTranslations: function(obj) {
        Zikula.mergeObjects(this.data,obj)
    },
    /**
     * Setup current gettext language
     *
     * @memberOf Zikula.Gettext
     * @param {string} lang   Language for translations
     *
     * @return void
     */
    setLang: function(lang) {
        this.lang = lang || this.defaults.lang;
    },
    /**
     * Setup current gettext defaul domain
     *
     * @memberOf Zikula.Gettext
     * @param {string} domain Default domain to use, optional
     *
     * @return void
     */
    setDomain: function(domain) {
        this.domain = domain || this.defaults.domain;
    },
    /**
     * Reads from translations data
     *
     * @memberOf Zikula.Gettext
     * @private
     * @param {string} domain The domain in which given key will be searched
     * @param {string} key    Data key to search
     *
     * @return void
     */
    getData: function(domain,key) {
        domain = domain || this.domain;
        try {
            return this.data[this.lang][domain][key];
        } catch (e) {
            return {};
        }
    },
    /**
     * Translates message.
     *
     * @memberOf Zikula.Gettext
     * @param {string} msgid  The message to translate
     * @param {string} domain Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getMessage: function(msgid, domain) {
        return this.getData(domain,'translations')[msgid] || msgid;
    },
    /**
     * Translates and format message using sprintf formatting rules.
     *
     * @memberOf Zikula.Gettext
     * @param {string} msgid  The message to translate
     * @param {array}  params Array with zero or more replacements to be made in msgid
     * @param {string} domain Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getMessageFormatted: function(msgid, params, domain) {
        return Zikula.vsprintf(this.getMessage(msgid, domain), params);
    },
    /**
     * Plural translation.
     *
     * @memberOf Zikula.Gettext
     * @param {string} singular Singular message
     * @param {string} plural   Plural message
     * @param {int}    count    Count
     * @param {string} domain   Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getPluralMessage: function(singular, plural, count, domain) {
        var offset = this.getPluralOffset(count, domain),
            key = singular + this.nullChar + plural,
            messages = this.getMessage(key, domain);
        if(messages) {
            return messages.split(this.nullChar)[offset];
        } else {
            return key.split(this.nullChar)[offset];
        }
    },
    /**
     * Plural formatted translation.
     *
     * @memberOf Zikula.Gettext
     * @param {string} singular Singular message
     * @param {string} plural   Plural message
     * @param {int}    count    Count
     * @param {array}  params   Array with zero or more replacements to be made in singular/plural message
     * @param {string} domain   Gettext domain, if no domain is given deafult one is used
     *
     * @return {string} Translated message
     */
    getPluralMessageFormatted: function(singular, plural, count, params, domain) {
        return Zikula.vsprintf(this.getPluralMessage(singular, plural, count, domain), params);
    },
    /**
     * Calculates plural offset depending on plural forms
     *
     * @memberOf Zikula.Gettext
     * @private
     * @param {int}    count  Count
     * @param {string} domain The domain to be used, if no domain is given deafult one is used
     *
     * @return {int} Plural offset
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
Zikula.Gettext = new Zikula.Gettext(Zikula.Config.lang,Zikula._translations);
// shortcuts
Object.extend(Zikula,{
    __: Zikula.Gettext.__,
    __f: Zikula.Gettext.__f,
    _n: Zikula.Gettext._n,
    _fn: Zikula.Gettext._fn
});

Zikula.Cookie = {
    cookie: '#{name}=#{value};expires=#{expires};path=#{path}',
    set: function(name, value, expires, path){
        document.cookie = Zikula.Cookie.cookie.interpolate({
            name: name,
            value: Zikula.Cookie.encode(value),
            expires: expires instanceof Date ? expires.toGMTString() : Zikula.Cookie.secondsFromNow(expires),
            path: path ? path : Zikula.Config.baseURI
        });
    },
    get: function(name){
        var cookie = document.cookie.match(name + '=(.*?)(;|$)');
        return cookie ? Zikula.Cookie.decode(cookie[1]) : null
    },
    remove: function(name){
        Zikula.Cookie.set(name,'',-1);
    },
    secondsFromNow: function(seconds) {
        var d = new Date();
        d.setTime(d.getTime() + (seconds * 1000));
        return d.toGMTString();
    },
    encode: function(value) {
        return encodeURI(encodeURI(Object.toJSON(value)));
    },
    decode: function(value) {
        return decodeURI(decodeURI(value)).evalJSON(true);
    }
};

//http://github.com/kangax/protolicious/blob/master/element.methods.js
/**
* Element.getContentWidth(@element) -> Number
* returns element's "inner" width - without padding/border dimensions
*
* $(someElement).getContentWidth(); // 125
*
**/
Element.Methods.getContentWidth = function(element) {
  return ['paddingLeft', 'paddingRight', 'borderLeftWidth', 'borderRightWidth']
    .inject(Element.getWidth(element), function(total, prop) {
      return total - parseInt(Element.getStyle(element, prop), 10);
    });
};

/**
* Element.getContentHeight(@element) -> Number
* returns element's "inner" height - without padding/border dimensions
*
* $(someElement).getContentHeight(); // 141
*
**/
Element.Methods.getContentHeight = function(element) {
  return ['paddingTop', 'paddingBottom', 'borderTopWidth', 'borderBottomWidth']
    .inject(Element.getHeight(element), function(total, prop) {
      return total - parseInt(Element.getStyle(element, prop), 10);
    });
};

/**
* Element.setWidth(@element, width) -> @element
* sets element's width to a specified value
* or to a value of its content width (if value was not supplied)
*
* $(someElement).setWidth();
* $(someOtherElement).setWidth(100);
*
**/
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
* $(someElement).setHeight();
* $(someOtherElement).setHeight(68);
*
**/
Element.Methods.setHeight = function(element, height) {
  return Element.setStyle(element, {
    height: (Object.isUndefined(height) ? Element.getContentHeight(element) : height) + 'px'
  });
};


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
      return total + parseInt(Element.getStyle(element, prop), 10);
    });
};
Element.addMethods();
Object.extend(String.prototype, (function() {
  function toUnits(unit) {
    return (parseInt(this) || 0) + (unit || 'px');
  }
  return {
    toUnits:         toUnits
  };
})());
Object.extend(Number.prototype, (function() {
  function toUnits(unit) {
    return (this.valueOf() || 0) + (unit || 'px');
  }
  return {
    toUnits:         toUnits
  };
})());
