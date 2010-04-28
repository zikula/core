/**
 * Onload function adds droppable locations to all the tabs as well as context
 * menus.
 */
window.onload = function() {
	context_menu = Array();
	droppables = Array();
	var list = document.getElementById('minitabs');
	if (list.hasChildNodes) {
		var nodes = list.getElementsByTagName("a");
		for ( var i = 0; i < nodes.length; i++) {
			var nid = nodes[i].getAttribute('id');
			if (nid != null) {
				addContext(nid);
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

function addContext(nid)
{
	context_menu.push(new Control.ContextMenu(nid));
	context_menu[context_menu.length - 1].addItem( {
		label : 'Edit',
		callback : function(nid) {
			var match = /acid=([0-9]*){1,}/.exec(nid);
			if (match instanceof Array) {
				if (match.length == 2) {
					cid = match[match.length - 1];
				}
			}
			alert(cid);
			return;
		},
	    enabled : false
	});
	context_menu[context_menu.length - 1].addItem( {
		label : 'Delete',
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

function deleteTab(id) {
	var pars = "module=Admin&type=ajax&func=deleteCategory&cid=" + id;
	var myAjax = new Ajax.Request("ajax.php", {
		method : 'get',
		parameters : pars,
		onComplete : deleteTabResponse
	});
}

function deleteTabResponse(req) {
	if (req.status != 200) {
		pnshowajaxerror(req.responseText);
		return false;
	}
	var json = pndejsonize(req.responseText);
	if (json.alerttext !== '' || json.response == '-1') {
		alert("Oops something went wrong! " + json.alerttext + "response: "
				+ json.respnse);
	} else {
		var element = document.getElementById("C" + json.response);
		element.parentNode.removeChild(element);
	}
	return false;
}

function moveModule(id, cid) {
	var id = id.substr(1);
	var cid = cid.substr(1);
	var pars = "module=Admin&type=ajax&func=changeModuleCategory&modid=" + id
			+ "&cat=" + cid;
	var myAjax = new Ajax.Request("ajax.php", {
		method : 'get',
		parameters : pars,
		onComplete : changeModuleCategoryResponse
	});
}

function changeModuleCategoryResponse(req) {
	if (req.status != 200) {
		pnshowajaxerror(req.responseText);
		return;
	}
	var json = pndejsonize(req.responseText);
	if (json.alerttext !== '') {
		alert(json.alerttext);
	}
	if (json.response == '-1') {
		alert("Oops something went wrong!");
		return;
	}
	var element = document.getElementById('A' + json.response);
	element.parentNode.removeChild(element);
	return;
}

function newCategory(cat) {
	var parent = cat.parentNode;
	old = parent.innerHTML;
	var innerhtml = document.getElementById('ajaxNewCatHidden').innerHTML;
	parent.innerHTML = innerhtml;
	parent.setAttribute("class", "newCat");
	parent.setAttribute("className", "newCat");
	return false;
}

function addCategory(cat) {
	oldcat = cat;
	catname = document.getElementById('ajaxNewCatForm').elements['catName'].value;
	if (catname == '') {
		pnshowajaxerror('You must enter a name for the new category');
		cancelCategory(oldcat);
		return false;
	}
	var pars = "module=Admin&type=ajax&func=addCategory&catname=" + catname;
	var myAjax = new Ajax.Request("ajax.php", {
		method : 'get',
		parameters : pars,
		onComplete : addCategoryResponse
	});
	return false;
}

// cancel the addition of a new category, puts widget back to normal.
function cancelCategory(cat) {
	var parent = cat.parentNode.parentNode;
	parent.innerHTML = old;
	parent.setAttribute("class", "");
	parent.setAttribute("className", "");
	return false;
}

function addCategoryResponse(req) {
    var oldcat = document.getElementById('ajaxCatImage');
    if (req.status != 200) {
        cancelCategory(oldcat);
        pnshowajaxerror(req.responseText);
        return false;
    }
    var json = pndejsonize(req.responseText);
    if (json.alerttext !== '' || json.response == '0') {
        alert("Oops something went wrong! " + json.alerttext);
        cancelCategory(oldcat);
    } else {
        newcat = oldcat.parentNode.parentNode;
        newcat.innerHTML = '<a id="C'+json.response+'" href="'+json.url+'">'+catname+'</a>';
        newcat.setAttribute("class","");
        newcat.setAttribute("className","");
        newcat.setAttribute("id", "");
    
        var newelement = document.createElement('li');
        newelement.innerHTML = old;
        newelement.setAttribute('id', 'addcat');
        document.getElementById('minitabs').appendChild(newelement);
        addContext('C'+json.response);
        Droppables.add('C'+json.response, { 
            accept: 'draggable',
            hoverclass: 'ajaxhover',
            onDrop: function(drag, drop) {moveModule(drag.id, drop.id);}
        });
    }
    return false;    
}

function editmode() {
	if (typeof editors == 'undefined') {
		editors = new Array();
	}
	if (editors.length != 0) {
		while (editors.length > 0) {
			editors.pop().dispose();
		}
		document.getElementById('editcat').setAttribute("class", "");
		document.getElementById('editcat').setAttribute("className", "");
		return false;
	}
	var list = document.getElementById('minitabs');
	if (list.hasChildNodes) {
		var nodes = list.getElementsByTagName("a");
		for ( var i = 0; i < nodes.length; i++) {
			var nid = nodes[i].getAttribute('id');
			if (nid != null) {
				if (nid == 'editcat') {
					nodes[i].setAttribute("class", "editmode");
					nodes[i].setAttribute("className", "editmode");
					continue;
				}
				editors.push(new Ajax.InPlaceEditor(nid, 'ajaxedit', {
					submitOnBlur : true,
					okButton : false,
					cols : 10,
					formClassName : "tabedit tabeditHeight",
					cancelControl : false
				}));
				continue;
			}
		}
	}
	return false;
}