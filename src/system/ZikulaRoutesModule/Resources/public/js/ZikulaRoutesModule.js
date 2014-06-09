'use strict';

var routesContextMenu;

routesContextMenu = Class.create(Zikula.UI.ContextMenu, {
    selectMenuItem: function ($super, event, item, item_container) {
        // open in new tab / window when right-clicked
        if (event.isRightClick()) {
            item.callback(this.clicked, true);
            event.stop(); // close the menu
            return;
        }
        // open in current window when left-clicked
        return $super(event, item, item_container);
    }
});

/**
 * Initialises the context menu for item actions.
 */
function routesInitItemActions(objectType, func, containerId)
{
    var triggerId, contextMenu, icon;

    triggerId = containerId + 'Trigger';

    // attach context menu
    contextMenu = new routesContextMenu(triggerId, { leftClick: true, animation: false });

    // process normal links
    $$('#' + containerId + ' a').each(function (elem) {
        // save css class before hiding (#428)
        var elemClass = elem.readAttribute('class');
        // hide it
        elem.addClassName('hidden');
        // determine the link text
        var linkText = '';
        if (func === 'display') {
            linkText = elem.innerHTML;
        } else if (func === 'view') {
            linkText = elem.readAttribute('data-linktext');
        }

        // determine the icon
        icon = '';
        if (elem.hasClassName('fa')) {
            icon = '<span class="' + elemClass + '"></span>';
        }

        contextMenu.addItem({
            label: icon + linkText,
            callback: function (selectedMenuItem, isRightClick) {
                var url;

                url = elem.readAttribute('href');
                if (isRightClick) {
                    window.open(url);
                } else {
                    window.location = url;
                }
            }
        });
    });
    $(triggerId).removeClassName('hidden');
}

function routesCapitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * Submits a quick navigation form.
 */
function routesSubmitQuickNavForm(objectType)
{
    $('zikularoutesmodule' + routesCapitaliseFirstLetter(objectType) + 'QuickNavForm').submit();
}

/**
 * Initialise the quick navigation panel in list views.
 */
function routesInitQuickNavigation(objectType)
{
    if ($('zikularoutesmodule' + routesCapitaliseFirstLetter(objectType) + 'QuickNavForm') == undefined) {
        return;
    }

    if ($('catid') != undefined) {
        $('catid').observe('change', function () { routesSubmitQuickNavForm(objectType); });
    }
    if ($('sortby') != undefined) {
        $('sortby').observe('change', function () { routesSubmitQuickNavForm(objectType); });
    }
    if ($('sortdir') != undefined) {
        $('sortdir').observe('change', function () { routesSubmitQuickNavForm(objectType); });
    }
    if ($('num') != undefined) {
        $('num').observe('change', function () { routesSubmitQuickNavForm(objectType); });
    }

    switch (objectType) {
    case 'route':
        if ($('workflowState') != undefined) {
            $('workflowState').observe('change', function () { routesSubmitQuickNavForm(objectType); });
        }
        if ($('userRoute') != undefined) {
            $('userRoute').observe('change', function () { routesSubmitQuickNavForm(objectType); });
        }
        break;
    default:
        break;
    }
}
