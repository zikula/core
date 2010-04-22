function moveModule(id, cid) {
    var element = document.getElementById(id);
    element.parentNode.removeChild(element);
    var id = id.substr(1);
    var cid = cid.substr(1);
    var pars = "module=Admin&type=ajax&func=changeModuleCategory&modid=" + id + "&cat=" + cid;
    var myAjax = new Ajax.Request(
            "ajax.php",
            {
                method: 'get',
                parameters: pars,
                onComplete:changeModuleCategoryResponse
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
    if (json.response !== '1')
    {
        alert("Oops something went wrong!");
    }
    return;    
}

function newCategory(cat)
{
    var parent = cat.parentNode;
    old = parent.innerHTML;
    var innerhtml = document.getElementById('ajaxNewCatHidden').innerHTML;
    parent.innerHTML = innerhtml;
    parent.setAttribute("class","newCat");
    parent.setAttribute("className","newCat");
    return false;
}

function addCategory(cat)
{
    oldcat = cat;
    catname = document.getElementById('ajaxNewCatForm').elements['catName'].value;
    if (catname == '') {
        pnshowajaxerror('You must enter a name for the new category');
        cancelCategory(oldcat);
        return false;
    }
    var pars = "module=Admin&type=ajax&func=addCategory&catname=" + catname;
    var myAjax = new Ajax.Request(
            "ajax.php",
            {
                method: 'get',
                parameters: pars,
                onComplete:addCategoryResponse
            });
    return false;
}

function cancelCategory(cat)
{
    var parent = cat.parentNode.parentNode;
    parent.innerHTML = old;
    parent.setAttribute("class","");
    parent.setAttribute("className","");
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
        Droppables.add('C'+json.response, { 
            accept: 'draggable',
            hoverclass: 'ajaxhover',
            onDrop: function(drag, drop) {moveModule(drag.id, drop.id);}
        });
    }
    return false;    
}

/*
function addDropDown(id) {
    document.getElementById(id).addEventListener(
            "mouseover",
            function() {
                return showMenu(id);
            },
            false
        );
}

function showMenu(id) {
    var scope = Effect.Queues.get(id);
    scope.each(function(effect){effect.cancel()});
    var menu = document.getElementById('D'+id);
    if (!menu) return false;
    if (!menu.hasClassName('open')) menu.addClassName('open');
    if (menu.style.display == 'none') {    
        Effect.BlindDown('D'+id, {duration: 0.2, queue: {position:'end',scope:id}});
        menu.style.display = "block";
    }
    return;
}

function hideMenu(id) {
    var scope = Effect.Queues.get(id);
    scope.each(function(effect){effect.cancel()});
    var menu = document.getElementById('D'+id);
    if (!menu) return false;
    if (menu.style.display !== 'none') {
        Effect.BlindUp('D'+id, {duration: 0.2, queue: {position:'end',scope:id}});
        menu.style.display = "none";
    }
}
*/