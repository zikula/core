// Copyright Zikula Foundation 2009 - license GNU/LGPLv2.1 (or at your option, any later version).

/**
 * Onload function adds droppable locations to all the tabs as well as context
 * menus and inplace editors.
 */
Event.observe(window, 'load', function() {
    context_menu = Array();
    editors = Array();
    droppables = Array();
    var list = document.getElementById('admintabs');
    if (list.hasChildNodes) {
        var nodes = list.getElementsByTagName("a");
        for ( var i = 0; i < nodes.length; i++) {
            var nid = nodes[i].getAttribute('id');
            if (nid != null && nodes[i].id != 'addcatlink') {
                addContext(nid);
                addEditor(nid);
                if ($(nodes[i]).up('li').hasClassName('active'))
                    continue;
                var droppable = Droppables.add(nid, {
                    accept : 'draggable',
                    hoverclass : 'ajaxhover',
                    onDrop : function(drag, drop) {
                        moveModule(drag.id, drop.id);
                }
                });
                droppables.push(droppable);
            }
        }
    }
});

/**
 * Add context menu to element nid.
 * @param nid the id of the element
 * @return void
 */
function addContext(nid)
{
    context_menu.push(new Control.ContextMenu(nid, {animation: false}));
    context_menu[context_menu.length - 1].addItem( {
        label : lblEdit,
        callback : function(nid) {
            var cid = nid.href.match(/acid=(\d+)/)[1];
            if (cid) {
                getEditor("C" + cid).enterEditMode();
            }
            return;
        }
    });
    context_menu[context_menu.length - 1].addItem( {
        label : lblDelete,
        callback : function(nid) {
            var cid = nid.href.match(/acid=(\d+)/)[1];
            if (cid) {
                deleteTab(cid);
            }
            return;
        }
    });
}

/**
 * Add an inplace editor to element nid.
 * @param nid id of element.
 * @return void
 */
function addEditor(nid) {
    var nelement = document.getElementById(nid);
    var tLength = nelement.innerHTML.length;
    var editor = new Ajax.InPlaceEditor(nid,"index.php?module=Admin&type=ajax&func=editCategory",{
        clickToEditText: lblclickToEdit,
        savingText: lblSaving,
        externalControl: "admintabs-none",
        externalControlOnly: true,
        rows:1,cols: tLength,
        submitOnBlur: true,
        okControl: false,
        cancelControl: false,
        // in webkit browsers , when submitOnBlur is true
        // enter press causes form submission twice so catch this event, stop it and call blur on input
        onFormCustomization: function(obj, form) {
            $(form).observe('keypress',function(e) {
                if(e.keyCode == Event.KEY_RETURN) {
                    e.stop();
                    e.element().blur();
                }
            });
        },
        callback: function(form, value) {
            var authid = document.getElementById('admintabsauthid').value;
            var cid = form.id.substring(1,form.id.indexOf('-inplaceeditor'));
            //this check should stop the form from submitting if the catname is the same, it doesnt work
            //if (getOrig("C" + cid) == value) {
            //    alert ("cat name the same!");
            //    return false;
            //}
            return 'catname='+encodeURIComponent(value)+'&cid='+cid+'&authid='+authid;
        },
        onComplete: function(transport, element) {
            if(transport.status != 200 ) {
                this.element.innerHTML = getOrig(element.id);
                pnshowajaxerror(transport.responseText);
                return;
            }
            var json = pndejsonize(transport.responseText);
            if (json.alerttext !== '' || json.response == '-1') {
                this.element.innerHTML = getOrig(element.id);
                pnshowajaxerror(json.alerttext);
            } else {
                this.element.innerHTML = json.response;
            }
            var aid = json.authid;
            if (aid !== '') {
                document.getElementById('admintabsauthid').value = aid;
                pnupdateauthids(aid);
            }
        }
    });
    editors.push(Array(nid, editor, nelement.innerHTML));
}

/**
 * Gets a specific editor belonging to element nid.
 * @param nid element to get editor for.
 * @return editor
 */
function getEditor(nid) {
    for (var row = 0; row < editors.length; row++) {
        if (editors[row][0] == nid) {
            return editors[row][1];
        }
    }
}

/**
 * Gets original content of tab nid.
 * @param nid element to get original content for.
 * @return content
 */
function getOrig(nid) {
    for (var row = 0; row < editors.length; row++) {
        if (editors[row][0] == nid) {
            return editors[row][2];
        }
    }
}

//-----------------------Deleting Tabs----------------------------------------
/**
 * Makes ajax request to delete category specified by id.
 *
 * @param id the cid of the category to be deleted
 * @return void
 */
function deleteTab(id) {
    var authid = document.getElementById('admintabsauthid').value;
    var pars = "module=Admin&type=ajax&func=deleteCategory&cid=" + id + '&authid=' + authid;
    var myAjax = new Ajax.Request("ajax.php", {
        method : 'get',
        parameters : pars,
        onComplete : deleteTabResponse
    });
}

/**
 * Gets the response of a deleteTab request.
 *
 * @param  req     The request handle.
 * @return Boolean False always, removes tab from dom on success.
 */
function deleteTabResponse(req) {
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return false;
    }
    var json = pndejsonize(req.responseText);
    if (json.alerttext !== '' || json.response == '-1') {
        pnshowajaxerror(json.alerttext);
    } else {
        var element = $("C" + json.response);
        element.up('li').remove();
    }
    var aid = json.authid;
    if (aid !== '') {
        document.getElementById('admintabsauthid').value = aid;
        pnupdateauthids(aid);
    }
    return false;
}

//----------------------Moving Modules----------------------------------------
/**
 * makes an ajax request to move a module to a new category.
 *
 * @param id  Integer The id of the module to move.
 * @param cid Integer The cid of the category to move to.
 */
function moveModule(id, cid) {
    var id = id.substr(1);
    var cid = cid.substr(1);
    var authid = document.getElementById('admintabsauthid').value;
    var pars = "module=Admin&type=ajax&func=changeModuleCategory&modid=" + id
    + "&cat=" + cid + '&authid=' + authid;
    var myAjax = new Ajax.Request("ajax.php", {
        method : 'get',
        parameters : pars,
        onComplete : changeModuleCategoryResponse
    });
}

/**
 * Response handler for moveModule.
 *
 * @param req Ajax request.
 * @return void, module is removed from dom on success.
 */
function changeModuleCategoryResponse(req) {
    if (req.status != 200) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    console.log(json);
    if (json.alerttext !== '') {
        pnshowajaxerror(json.alerttext);
        var aid = json.authid;
        document.getElementById('admintabsauthid').value = aid;
        pnupdateauthids(aid);
        return;
    }
    $('z-admincontainer').highlight();
    var aid = json.authid;
    document.getElementById('admintabsauthid').value = aid;
    pnupdateauthids(aid);
    var element = document.getElementById('A' + json.response);
    if(json.newParentCat != element.parentNode.id) {}
    eval("context_catcontext" + json.newParentCat + ".addItem({label: \'" + json.modulename + "',callback: function(){window.location = document.location.pnbaseURL + \'" + json.url + "\';}});");
    element.parentNode.removeChild(element);
    return;
}
//--------------------Creating Categories-------------------------------------
/**
 * Presents user with the new category form.
 *
 * @param cat The calling element. (EG call like: newCategory(this); from html)
 * @return Boolean False.
 */
function newCategory(cat) {
    var parent = cat.parentNode;
    old = parent.innerHTML;
    var innerhtml = document.getElementById('ajaxNewCatHidden').innerHTML;
    parent.innerHTML = innerhtml;
    parent.setAttribute("class", "newCat");
    return false;
}

/**
 * Creates the AJAX request to create the new category.
 *
 * @param cat The calling element. (see above)
 * @return Boolean false.
 */
function addCategory(cat) {
    var oldcat = document.getElementById('ajaxCatImage');
    catname = document.getElementById('ajaxNewCatForm').elements['catName'].value;
    if (catname == '') {
        pnshowajaxerror('You must enter a name for the new category');
        cancelCategory(oldcat);
        return false;
    }
    var authid = document.getElementById('admintabsauthid').value;
    var pars = "module=Admin&type=ajax&func=addCategory&catname=" + catname + "&authid=" + authid;
    var myAjax = new Ajax.Request("ajax.php", {
        method : 'get',
        parameters : pars,
        onComplete : addCategoryResponse
    });
    return false;
}

/**
 * Cancel the addition of a new category, puts widget back to normal.
 *
 * @param cat the current element
 * (EG cancelCategory must be called: cancelCategory(this) from html)
 * @return Boolean False.
 */
function cancelCategory(cat) {
    var parent = document.getElementById('addcat');
    parent.innerHTML = old;
    parent.setAttribute("class", "");
    return false;
}

/**
 * Ajax response handler for addCategory.
 *
 * @param req Ajax request.
 * @return False, new tab is added on success.
 */
function addCategoryResponse(req) {
    if (req.status != 200) {
        cancelCategory();
        pnshowajaxerror(req.responseText);
        return false;
    }
    var json = pndejsonize(req.responseText);
    var aid = json.authid;
    if (json.alerttext !== '' || json.response == '0') {
        pnshowajaxerror(json.alerttext);
        document.getElementById('admintabsauthid').value = aid;
        pnupdateauthids(aid);
    } else {
        newcat = document.getElementById('addcat');
        newcat.innerHTML = '<a id="C' + json.response + '" href="'
            + json.url + '">' + catname + '</a><span id="catcontext' 
            + json.response + '" class="z-admindrop">&nbsp;</span>';
        newcat.setAttribute("class","");
        newcat.setAttribute("id", "");
        eval("context_catcontext" + json.response + " =  new Control.ContextMenu('catcontext' + json.response,{leftClick: true,animation: false });");

        var newelement = document.createElement('li');
        newelement.innerHTML = old;
        newelement.setAttribute('id', 'addcat');
        document.getElementById('admintabs').appendChild(newelement);
        addContext('C'+json.response);
        addEditor('C'+json.response);
        document.getElementById('admintabsauthid').value = aid;
        pnupdateauthids(aid);
        Droppables.add('C'+json.response, {
            accept: 'draggable',
            hoverclass: 'ajaxhover',
            onDrop: function(drag, drop) {moveModule(drag.id, drop.id);}
        });
    }
    return false;
}