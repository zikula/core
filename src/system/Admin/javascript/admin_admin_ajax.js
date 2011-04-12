// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

/**
 * Onload function adds droppable locations to all the tabs as well as context
 * menus and inplace editors.
 */
Event.observe(window, 'load', function() {
    context_menu = Array();
    editors = Array();
    droppables = Array();
    
    //make the admin tabs (categories) sortable
    make_tabs_sortable = function() {
        Sortable.create('admintabs',{
            tag:'li',
            constraint: 'horizontal',
            onChange: function(element) {
                //stops the default link action (change curent category) when dropping sortable
                $$("#"+element.id + " a").each(function(item) {
                    Event.observe(item.id, 'click', function(event) {
                        event.preventDefault();
                    });
                });
            },
            onUpdate: function(element){
                //reset the default action after drop has been completed
                Event.observe(element.id, 'mousemove', function(event) {  
                    $$("#"+element.id + " a").each(function(item) {
                        Event.stopObserving(item.id, 'click');
                    });
                    Event.stopObserving(element.id, 'mousemove');
                });
                var pars = Sortable.serialize("admintabs");
                //send the new sort order to the ajax controller
                new Zikula.Ajax.Request(
                    "ajax.php?module=Admin&type=ajax&func=sortCategories", {
                        parameters: pars,
                        onComplete: function (req) {
                            if (!req.isSuccess()) {
                                Zikula.showajaxerror(req.getMessage());
                                return;
                            }
                            return;
                        }
                    }
                );
                return;
            },
            //prevents sorting of the "add new category" link
            only: Array("admintab","active")
        });
    };
    make_tabs_sortable();
    
    //make the modules sortable
    make_modules_sortable = function() {
        Sortable.create('modules',{
            tag: 'div',
            constraint: "",
            only: Array("z-adminiconcontainer"),
            handle: 'z-dragicon',
            onUpdate: function(element){
                var pars = Sortable.serialize("modules");
                //send the new order to the ajax controller
                new Zikula.Ajax.Request("ajax.php?module=Admin&type=ajax&func=sortModules", {
                    parameters: pars,
                    onComplete: function (req) {
                        if (!req.isSuccess()) {
                            Zikula.showajaxerror(req.getMessage());
                            return;
                        }
                        return;
                    }
                });
            return;
            }
        });
    }
    if ( $$("#modules div").size() > 0) {
        make_modules_sortable();
    }
    
    //add context menus to tabs, as well as make them droppable
    var list = $('admintabs');
    if (list.hasChildNodes) {
        var nodes = list.getElementsByTagName("a");
        for ( var i = 0; i < nodes.length; i++) {
            var nid = nodes[i].getAttribute('id');
            Admin.Editor.Add(nid);
            var droppable = Droppables.add(nid, {
                    accept : 'draggable',
                    hoverclass : 'ajaxhover',
                    onDrop : function(drag, drop) {
                        Admin.Module.Move(drag.id, drop.id);
                }
            });
        }
        droppables.push(droppable);
        
        var nodes = list.getElementsByTagName("span");
        for ( var i = 0; i < nodes.length; i++) {
            var nid = nodes[i].getAttribute('id');
            if (nid != null && nodes[i].id != 'addcatlink') {
                Admin.Context.Add(nid);
                if ($(nodes[i]).up('li').hasClassName('active'))
                    continue;
            }
        }
    }
    //this isn't really needed because sortable makes it dragable as well.
    /*
    $$("#modules div").each(function(item) {
            alert("dragicon"+item.id.substr(7)); 
            new Draggable(item.id, {
                    revert: true,
                    handle: "dragicon"+item.id.substr(7),
                    zindex: 2200 // must be higher than the active minitab and all other admin icons
            });
    });*/
});


var Admin = {};
Admin.Context = {};
Admin.Tab = {};
Admin.Category = {};
Admin.Editor = {};
Admin.Module = {};

/**
 * Add context menu to element nid.
 * @param nid the id of the element
 * @return void
 */
Admin.Context.Add = function(nid)
{
context_menu.push(new Control.ContextMenu(nid, {animation: false}));
    context_menu[context_menu.length - 1].addItem( {
        label : lblEdit,
        callback : function(nid) {
            var cid = nid.id.match(/catcontext(\d+)/)[1];
            if (cid) {
                Admin.Editor.Get("C" + cid).enterEditMode();
            }
            return;
        }
    });
    context_menu[context_menu.length - 1].addItem( {
        label : lblDelete,
        callback : function(nid) {
            var cid = nid.id.match(/catcontext(\d+)/)[1];
            if (cid) {
                Admin.Tab.Delete(cid);
            }
            return;
        }
    });
    context_menu[context_menu.length - 1].addItem( {
        label : lblMakeDefault,
        callback : function(nid) {
            var cid = nid.id.match(/catcontext(\d+)/)[1];
            if (cid) {
                Admin.Tab.setDefault(cid);
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
Admin.Editor.Add = function(nid)
{
var nelement = $(nid);
    var tLength = nelement.innerHTML.length;
    var editor = new Ajax.InPlaceEditor(nid,"ajax.php?module=Admin&type=ajax&func=editCategory",{
        clickToEditText: lblclickToEdit,
        savingText: lblSaving,
        externalControl: "admintabs-none",
        externalControlOnly: true,
        rows:1,cols: tLength,
        submitOnBlur: true,
        okControl: false,
        cancelControl: false,
        ajaxOptions: Zikula.Ajax.Request.defaultOptions(),
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
            var cid = form.id.substring(1,form.id.indexOf('-inplaceeditor'));
            return {
                catname: value,
                cid: cid
            };
        },
        onComplete: function(transport, element) {
            transport = Zikula.Ajax.Response.extend(transport);
            if (!transport.isSuccess()) {
                this.element.innerHTML = Admin.Editor.getOrig(element.id);
                Zikula.showajaxerror(transport.getMessage());
                return;
            }
            var data = transport.getData();
            this.element.innerHTML = data.response;
        }
    });
    editors.push(Array(nid, editor, nelement.innerHTML));
}


/**
 * Gets a specific editor belonging to element nid.
 * @param nid element to get editor for.
 * @return editor
 */
Admin.Editor.Get = function(nid)
{
    for (var row = 0; row < editors.length; row++) {
        if (editors[row][0] == nid) {
            return editors[row][1];
        }
    }
}

/**	 	
 * Gets the original content of tab nid.
 * @param nid element to get original content for.
 * @return content
 */
Admin.Editor.getOrig = function(nid)
{
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
Admin.Tab.Delete = function(id)
{
    var pars = {
        cid: id
    }
    new Zikula.Ajax.Request("ajax.php?module=Admin&type=ajax&func=deleteCategory", {
        parameters: pars,
        onComplete : Admin.Tab.DeleteResponse
    });
}

/**
 * Gets the response of a deleteTab request.
 *
 * @param  req     The request handle.
 * @return Boolean False always, removes tab from dom on success.
 */
Admin.Tab.DeleteResponse = function(req)
{
    if (!req.isSuccess()) {
    	Zikula.showajaxerror(req.getMessage());
        return;
    }
    var data = req.getData();
    var element = $("C" + data.response);
    element.up('li').remove();
    return;
}

//-----------------------Make Default Tabs----------------------------------------
/**
 * Makes ajax request to make the category specified by id, the initially selected one.
 *
 * @param id the cid of the category to be made default
 * @return void
 */
Admin.Tab.setDefault = function(id)
{
    var pars = {
        cid: id
    }
    new Zikula.Ajax.Request("ajax.php?module=Admin&type=ajax&func=defaultCategory", {
        parameters: pars,
        onComplete : Admin.Tab.setDefaultResponse
    });
}

/**
 * Gets the response of a defaultTab request.
 *
 * @param  req     The request handle.
 * @return Boolean False always, removes tab from dom on success.
 */
Admin.Tab.setDefaultResponse = function(req)
{
    if (!req.isSuccess()) {
    	Zikula.showajaxerror(req.getMessage());
        return;
    }
    return;
}

//----------------------Moving Modules----------------------------------------
/**
 * makes an ajax request to move a module to a new category.
 *
 * @param id  Integer The id of the module to move.
 * @param cid Integer The cid of the category to move to.
 */
Admin.Module.Move = function(id,cid)
{
    var id = id.substr(7);
    var cid = cid.substr(1);
    var pars = {
        modid:  id,
        cat: cid
    }
    new Zikula.Ajax.Request("ajax.php?module=Admin&type=ajax&func=changeModuleCategory", {
        parameters: pars,
        onComplete : Admin.Module.moveResponse
    });
}

/**
 * Response handler for moveModule.
 *
 * @param req Ajax request.
 * @return void, module is removed from dom on success.
 */
Admin.Module.moveResponse = function(req)
{
    if (!req.isSuccess()) {
    	Zikula.showajaxerror(req.getMessage());
        return;
    }
    var data = req.getData();
    $('z-admincontainer').highlight({ startcolor: '#c0c0c0'});
    var element = $('module_' + data.response);
    if(data.newParentCat != element.parentNode.id) {}
    //add module to new category submenu 
    window['context_catcontext' + data.newParentCat].addItem({
        label: data.modulename,
        callback: function(){window.location = data.url;}
    });
    //remove from old category submenu
    var oldmenuitems = window['context_catcontext'+data.oldcid]['items'];
    for (var j in oldmenuitems) {
        if (oldmenuitems[j].label.indexOf(data.modulename) != -1) {
            window['context_catcontext'+data.oldcid]['items'].splice(j,1);
        	break;
        }
    }
    //remove moved module from page
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
Admin.Category.New = function(cat)
{
    var parent = cat.parentNode;
    old = parent.innerHTML;
    var innerhtml = $('ajaxNewCatHidden').innerHTML;
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
Admin.Category.Add = function(cat)
{
    var oldcat = $('ajaxCatImage');
    catname = $('ajaxNewCatForm').elements['catName'].value;
    if (catname == '') {
        Zikula.showajaxerror(Zikula.__('You must enter a name for the new category'));
        Admin.Category.Cancel(oldcat);
        return false;
    }
    var pars = {
        catname: catname
    }
    new Zikula.Ajax.Request("ajax.php?module=Admin&type=ajax&func=addCategory", {
        parameters: pars,
        onComplete : Admin.Category.addResponse
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
Admin.Category.Cancel = function()
{
    var parent = $('addcat');
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
Admin.Category.addResponse = function(req)
{

    if (!req.isSuccess()) {
    	Zikula.showajaxerror(req.getMessage());
    	Admin.Category.Cancel();
        return false;
    }
    var data = req.getData();
    newcat = $('addcat');
    newcat.innerHTML = '<a id="C' + data.response + '" href="'
        + data.url + '">' + catname + '</a><span id="catcontext' 
        + data.response + '" class="z-admindrop">&nbsp;</span>';
    newcat.setAttribute("class","admintab");
    newcat.setAttribute("id", "admintab_" + data.response);
    window['context_catcontext' + data.response] =  new Control.ContextMenu('catcontext' + data.response,{leftClick: true,animation: false });

    var newelement = document.createElement('li');
    newelement.innerHTML = old;
    newelement.setAttribute('id', 'addcat');
    $('admintabs').appendChild(newelement);
    Admin.Context.Add('C'+data.response);
    Admin.Editor.Add('C'+data.response);
    Droppables.add('C'+data.response, {
        accept: 'draggable',
        hoverclass: 'ajaxhover',
        onDrop: function(drag, drop) {Admin.Module.Move(drag.id, drop.id);}
    });  
    Sortable.destroy('admintabs');
    make_tabs_sortable();
    return false;
}
