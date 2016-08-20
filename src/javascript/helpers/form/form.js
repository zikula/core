// Copyright Zikula Foundation, licensed MIT.

var FormContextMenu = {};

( function($) {
    FormContextMenu.visibleMenus = {};

    FormContextMenu.getCommandArgument = function(menuId)
    {
        return $('#contentMenuArgument' + menuId).val();
    };

    // Called when activating a menu
    FormContextMenu.showMenu = function(event, menuId, commandArgument)
    {
        event.preventDefault();

        FormContextMenu.hideMenu();

        var contextMenu = $('#' + menuId),
            element = $(event.target);

        var cursorPos = element.offset();
        cursorPos.left += element.width() + 10;

        $('#contentMenuArgument' + menuId).val(commandArgument);
        FormContextMenu.commandArgument = commandArgument;

        var documentClickHandler = function(event) {
            if ($(event.target).is(element)) {
                return;
            }

            // remove event handler
            $(document).off('click', documentClickHandler);

            FormContextMenu.hideMenu(menuId);
        }
        $(document).on('click', documentClickHandler);

        FormContextMenu.visibleMenus[menuId] = true;

        var offset = contextMenu.offsetParent().position();

        contextMenu.css({
            display: 'block',
            position: 'absolute',
            left: (cursorPos.left - offset.left) + 'px',
            top: (cursorPos.top - offset.top) + 'px'
        });
    };

    // Called when deactivating a menu
    FormContextMenu.hideMenu = function()
    {
        for (var vm in FormContextMenu.visibleMenus) {
            contextMenu = $('#' + vm);
            if (contextMenu != null) {
                contextMenu.css('display', 'none');
            }
        }

        FormContextMenu.visibleMenus = {};
    }

    // Called when clicking on a menu item with "commandScript" set
    FormContextMenu.commandScript = function(commandArgumentId, script)
    {
        var commandArgument = $('#' + commandArgumentId).val();
        script(commandArgument);
    }

    // Called when clicking on a menu item with "commandRedirect" set
    FormContextMenu.redirect = function(commandArgumentId, url)
    {
        var commandArgument = $('#' + commandArgumentId).val();
        url = url.replace(/\{commandArgument\}/, commandArgument);
        url = url.replace(/%7BcommandArgument%7D/, commandArgument);
        window.location = url;
    }
})(jQuery);
