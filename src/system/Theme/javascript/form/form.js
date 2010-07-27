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

  var contextMenu = $(menuId);
  var cursorPos = { x: Event.pointerX(evt), y: Event.pointerY(evt) };

  $("contentMenuArgument"+menuId).value = commandArgument;
  Form.contextMenu.commandArgument = commandArgument;
  Event.observe(document, 'click', function() {Form.contextMenu.hideMenu(menuId);});
  Form.contextMenu.visibleMenus[menuId] = true;

  contextMenu.style.display = 'block';
  contextMenu.style.position = 'absolute';
  contextMenu.style.left = cursorPos.x + 'px';
  contextMenu.style.top = cursorPos.y + 'px';

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