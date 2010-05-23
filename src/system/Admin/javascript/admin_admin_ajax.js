
//---------------------------Set Up-------------------------------------------
/**
 * Onload function adds droppable locations to all the tabs as well as context
 * menus and inplace editors.
 */
window.onload = function() {
    context_menu = Array();
    editors = Array();
    droppables = Array();
    var list = document.getElementById('admintabs');
    if (list.hasChildNodes) {
        var nodes = list.getElementsByTagName("a");
        for ( var i = 0; i < nodes.length; i++) {
            var nid = nodes[i].getAttribute('id');
            if (nid != null) {
                addContext(nid);
                addEditor(nid);
                if (nodes[i].className == 'active')
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
}

/**
 * Add context menu to element nid.
 * @param nid the id of the element
 * @return void
 */
function addContext(nid)
{
    context_menu.push(new Control.ContextMenu(nid));
    context_menu[context_menu.length - 1].addItem( {
        label : lblEdit,
        callback : function(nid) {
        var match = /acid=([0-9]*){1,}/.exec(nid);
        if (match instanceof Array) {
            if (match.length == 2) {
                cid = match[match.length - 1];
                getEditor("C" + cid).enterEditMode('click');
            }
        }
        return;
    }
    });
    context_menu[context_menu.length - 1].addItem( {
        label : lblDelete,
        callback : function(nid) {
        var match = /acid=([0-9]*){1,}/.exec(nid);
        if (match instanceof Array) {
            if (match.length == 2) {
                var cid = match[match.length - 1];
                deleteTab(cid);
            }
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
        externalControl: "none",
        externalControlOnly: true,
        rows:1,cols: tLength,
        submitOnBlur: true,
        okControl: false,
        cancelControl: false,
        callback: function(form, value) { 
        var authid = document.getElementById('authid').value;
        var cid = form.id.substring(1,form.id.indexOf('-inplaceeditor'));
        //this check should stop the form from submitting if the catname is the same, it doesnt work
        //if (getOrig("C" + cid) == value) {
        //    alert ("cat name the same!");
        //    return false;
        //}
        return 'catname='+encodeURIComponent(value)+'&cid='+cid+'&authid='+authid;
    },
    onComplete: function(transport, element) {
        var json = pndejsonize(transport.responseText);
        if (json.alerttext !== '' || json.response == '-1') {
            this.element.innerHTML = getOrig(element.id);
            pnshowajaxerror("Oops something went wrong: " + json.alerttext);
        } else {
            this.element.innerHTML = json.response;
        }
        var aid = json.authid;
        if (aid !== '') {
        	document.getElementById('authid').value = aid;
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
    var authid = document.getElementById('authid').value;
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
        pnshowajaxerror("Oops something went wrong! " + json.alerttext + "response: "
                + json.response);
    } else {
        var element = document.getElementById("C" + json.response);
        element.parentNode.removeChild(element);
    }
    var aid = json.authid;
    if (aid !== '') {
        document.getElementById('authid').value = aid;
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
    var authid = document.getElementById('authid').value;
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
    if (json.alerttext !== '') {
        pnshowajaxerror(json.alerttext);
    }
    if (json.response == '-1') {
        pnshowajaxerror("Oops something went wrong!");
        var aid = json.authid;
        document.getElementById('authid').value = aid;
        pnupdateauthids(aid);
        return;
    }
    $('z-admincontainer').highlight();
    var aid = json.authid;
    document.getElementById('authid').value = aid;
    pnupdateauthids(aid);
    var element = document.getElementById('A' + json.response);
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
    parent.setAttribute("className", "newCat");
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
    var authid = document.getElementById('authid').value;
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
	parent = document.getElementById('addcat');
    parent.innerHTML = old;
    parent.setAttribute("class", "");
    parent.setAttribute("className", "");
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
        pnshowajaxerror("Oops something went wrong! " + json.alerttext);
        document.getElementById('authid').value = aid;
        pnupdateauthids(aid);
    } else {
        newcat = document.getElementById('addcat');
        newcat.innerHTML = '<a id="C'+json.response+'" href="'+json.url+'">'+catname+'</a>';
        newcat.setAttribute("class","");
        newcat.setAttribute("className","");
        newcat.setAttribute("id", "");

        var newelement = document.createElement('li');
        newelement.innerHTML = old;
        newelement.setAttribute('id', 'addcat');
        document.getElementById('admintabs').appendChild(newelement);
        addContext('C'+json.response);
        addEditor('C'+json.response);
        document.getElementById('authid').value = aid;
        pnupdateauthids(aid);
        Droppables.add('C'+json.response, { 
            accept: 'draggable',
            hoverclass: 'ajaxhover',
            onDrop: function(drag, drop) {moveModule(drag.id, drop.id);}
        });
    }
    return false;    
}