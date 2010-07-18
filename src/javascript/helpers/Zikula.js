// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

if (typeof(Zikula) == 'undefined') {
    Zikula = {};
}

/**
 * Zikula.define
 * Creates namespace in Zikula scope through nested chain of objects, based on the given path
 * Example:
 * Zikula.define('Module.Component') will create object chain: Zikula.Module.Component
 * If object in chain already exists it will be extended, not overwritten
 *
 * @param  path dot separated path to define
 * @return object
 */
Zikula.define = function(path) {
    return path.split('.').inject(Zikula, function(object, prop) {
        return object[prop] = object[prop] || { };
    })
}

/**
 * Zikula.init
 * Load what's needed on dom loaded
 *
 * @return array
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
 * extends prototype Browser detection
 *
 * @return array
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
 * unserializes an array
 *
 * @param jsondata JSONized array in utf-8 (as created by AjaxUtil::output
 * @return array
 */
Zikula.dejsonize = function(jsondata)
{
    var result;
    try {
        result = eval('(' + jsondata + ')');
    } catch(error) {
        alert('illegal JSON response: \n' + error + 'in\n' + jsondata);
    }
    return result;
}

/**
 * Zikula.showajaxerror
 * shows an error message with alert()
 * todo: beautify this
 *
 * @param errortext the text to show
 * @return void
 */
Zikula.showajaxerror = function(errortext)
{
    alert(errortext);
    return;
}

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
 * sets a select to a given value
 *
 * @param id select id or object
 * @param sel the value that should be selected
 * @return void
 */
Zikula.setselectoption = function(id, sel)
{
    $A($(id).options).each(function(opt){opt.selected = (opt.value == sel);});
}

/**
 * Zikula.getcheckboxvalue
 * gets the value of a checkbox depending on the state
 *
 * @param id checkbox id or object
 * @return string
 */
Zikula.getcheckboxvalue = function(id)
{
    try {
        if($(id)) {
            if($(id).checked==true) {
                return $(id).value;
            }
            return '';
        }
    }catch(error) {
        alert("Zikula.getcheckboxvalue: unknown checkbox '" + id +"'");
    }
}

/**
 * Zikula.updateauthids
 * updates all hidden authid fields with a new authid obtained with an ajax call
 *
 * @param authid the new authid
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
 * set z-odd / z-even on each li after append, move and delete
 *
 * @param   string listclass class applied to the list of items
 * @param   string headerclass class applied to the header of the list
 * @return  none;
 * @author  Frank Schummertz
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
 * change the display attribute of an specific object
 *
 * @param   string id of the object to hide/show
 * @return  void
 * @author  Axel Guckelsberger
 * @author  Mateo Tibaquira
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
 * change the display attribute of an specific container depending of a radio input
 *
 * @param  string idgroup       id of the container where the radio input to observe are
 * @param  string idcontainer   id of the container to hide/show
 * @param  bool   state         state of the radio to show the idcontainer
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
 * change the display attribute of an specific container depending of a checkbox input
 *
 * @param  string idcheckbox    id of the checkbox input to observe
 * @param  string idcontainer   id of the container to hide/show
 * @param  bool   state         state of the checkbox to show the idcontainer
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
 * Workaround for wrong buttons values in IE and multiple submit buttons in IE6/7
 *
 * @param none
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
 * @param none
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
                        $('ajax_indicator').src = document.baseURI + 'images/icons/extrasmall/error.gif';
                    }
                    pnshowajaxerror('Ajax connection time out!');
                    // Run the onFailure method if we set one up when creating the AJAX object
                    if (request.options['onFailure']) {
                        request.options['onFailure'](request.transport, request.json);
                    }
                }
            },
            (typeof(document.location.ajaxtimeout)!='undefined' && document.location.ajaxtimeout!=0)  ? document.location.ajaxtimeout : 5000 // per default five seconds - can be changed in the settings
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
 */
function pndejsonize(jsondata)
{
    return Zikula.dejsonize(jsondata)
}

/**
 * @deprecated
 */
function pnshowajaxerror(errortext)
{
    return Zikula.showajaxerror(errortext);
}

/**
 * @deprecated
 */
function pnsetselectoption(id, sel)
{
    return Zikula.setselectoption(id, sel);
}

/**
 * @deprecated
 */
function pngetcheckboxvalue(id)
{
    return Zikula.getcheckboxvalue(id);
}

/**
 * @deprecated
 */
function pnupdateauthids(authid)
{
    return Zikula.updateauthids(authid);
}

/**
 * @deprecated
 */
function callInProgress(xmlhttp)
{
    return Zikula.callInProgress(xmlhttp);
}

/**
 * @deprecated
 */
function pnrecolor(listclass, headerclass)
{
    return Zikula.recolor(listclass, headerclass);
}

/**
 * @deprecated
 */
function switchdisplaystate(id)
{
    return Zikula.switchdisplaystate(id);
}

/**
 * @deprecated
 */
function radioswitchdisplaystate(idgroup, idcontainer, state)
{
    return Zikula.radioswitchdisplaystate(idgroup, idcontainer, state);
}

/**
 * @deprecated
 */
function checkboxswitchdisplaystate(idcheckbox, idcontainer, state)
{
    return Zikula.checkboxswitchdisplaystate(idcheckbox, idcontainer, state);
}


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

//http://www.diveintojavascript.com/projects/sprintf-for-javascript
Zikula.str_repeat = function(i, m) {
    for (var o = []; m > 0; o[--m] = i);
    return o.join('');
}
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
Zikula.vsprintf = function(format, args) {
    return Zikula.sprintf.apply(this,[format].concat(args));
}
Zikula.mergeObjects = function(destination,source)
{
    for (var prop in source) {
        try {
            if ( source[prop].constructor==Object ) {
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

/* GETTEXT */
Zikula.Gettext = Class.create({
    defaults: {
        lang: 'en',
        domain: 'zikula',
        pluralForms: 'nplurals=2; plural=n == 1 ? 0 : 1;'
    },
    pluralsPattern: /^(nplurals=\d+;\s{0,}plural=[\s\d\w\(\)\?:%><=!&\|]+)\s{0,};\s{0,}$/i,
    nullChar: '\u0000',
    initialize: function(lang,data) {
        this.data = {};
        this.setup(lang,data);
        this.__ = this.getMessage.bind(this);
        this.__f = this.getMessageFormatted.bind(this);
        this._n = this.getPluralMessage.bind(this);
        this._fn = this.getPluralMessageFormatted.bind(this);
    },
    setup: function(lang,data,domain) {
        this.setLang(lang);
        this.setDomain(domain);
        this.addTranslations(data || {})
    },
    addTranslations: function(obj) {
        Zikula.mergeObjects(this.data,obj)
    },
    setLang: function(lang) {
        this.lang = lang || this.defaults.lang;
    },
    setDomain: function(domain) {
        this.domain = domain || this.defaults.domain;
    },
    getData: function(domain,key) {
        domain = domain || this.domain;
        try {
            return this.data[this.lang][domain][key];
        } catch (e) {
            return {};
        }
    },
    getMessage: function(msgid, domain) {
        return this.getData(domain,'translations')[msgid] || msgid;
    },
    getMessageFormatted: function(msgid, params, domain) {
        return Zikula.vsprintf(this.getMessage(msgid, domain), params);
    },
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
    getPluralMessageFormatted: function(singular, plural, count, params, domain) {
        return Zikula.vsprintf(this.getPluralMessage(singular, plural, count, domain), params);
    },
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
