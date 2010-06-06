/**
 * Zikula Application Framework
 *
 * @copyright (c) 2001, Zikula Development Team
 * @link http://www.zikula.com
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_Core
 * @subpackage Javascript
*/

var Zikula = {};


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
    $A($(id).options).each(function(opt){ opt.selected = (opt.value == sel);});
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
    } catch(error) {
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

    check_state = objgroup.select('input[type=radio][value=1]').pluck('checked').any();

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
