// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

var Form = {};

/*========================================================================================
  Context menu handling
========================================================================================*/
Form.contextMenu = {};

Form.contextMenu.visibleMenus = {};


Form.contextMenu.getCommandArgument = function(menuId)
{
  return $("contentMenuArgument"+menuId).value;
}

// Called when activating a menu
Form.contextMenu.showMenu = function(evt, menuId, commandArgument)
{
  Form.contextMenu.hideMenu();

  var contextMenu = $(menuId),
      element = Event.element(evt),
      cursorPos = element.cumulativeOffset();
  cursorPos.left += element.getWidth() + 10;

  $("contentMenuArgument"+menuId).value = commandArgument;
  Form.contextMenu.commandArgument = commandArgument;
  Event.observe(document, 'click', function() {Form.contextMenu.hideMenu(menuId);});
  Form.contextMenu.visibleMenus[menuId] = true;

  var offset;
  // ugly hack for IE, in which getOffsetParent doesn't work,
  if(Prototype.Browser.IE == true) {
    $(document.body).insert(contextMenu);
    offset = {left: 0, top: 0};
  } else {
    offset = contextMenu.getOffsetParent().positionedOffset();
  }

  contextMenu.style.display = 'block';
  contextMenu.style.position = 'absolute';
  contextMenu.style.left = (cursorPos.left - offset.left) + 'px';
  contextMenu.style.top = (cursorPos.top - offset.top) + 'px';
  Event.stop(evt);
}


// Called when deactivating a menu
Form.contextMenu.hideMenu = function()
{
  for (var vm in Form.contextMenu.visibleMenus)
  {
    contextMenu = $(vm);
    if (contextMenu != null)
      contextMenu.style.display = 'none';
  }

  Form.contextMenu.visibleMenus = {};
}


// Called when clicking on a menu item with "commandScript" set
Form.contextMenu.commandScript = function(commandArgumentId, script)
{
  var commandArgument = $(commandArgumentId).value;
  script(commandArgument);
}


// Called when clicking on a menu item with "commandRedirect" set
Form.contextMenu.redirect = function(commandArgumentId, url)
{
  var commandArgument = $(commandArgumentId).value;
  url = url.replace(/\{commandArgument\}/, commandArgument);
  window.location = url;
}