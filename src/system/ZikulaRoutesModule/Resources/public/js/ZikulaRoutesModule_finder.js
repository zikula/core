'use strict';

var currentZikulaRoutesModuleEditor = null;
var currentZikulaRoutesModuleInput = null;

/**
 * Returns the attributes used for the popup window. 
 * @return {String}
 */
function getPopupAttributes()
{
    var pWidth, pHeight;

    pWidth = screen.width * 0.75;
    pHeight = screen.height * 0.66;

    return 'width=' + pWidth + ',height=' + pHeight + ',scrollbars,resizable';
}

/**
 * Open a popup window with the finder triggered by a Xinha button.
 */
function ZikulaRoutesModuleFinderXinha(editor, routesURL)
{
    var popupAttributes;

    // Save editor for access in selector window
    currentZikulaRoutesModuleEditor = editor;

    popupAttributes = getPopupAttributes();
    window.open(routesURL, '', popupAttributes);
}

/**
 * Open a popup window with the finder triggered by a CKEditor button.
 */
function ZikulaRoutesModuleFinderCKEditor(editor, routesURL)
{
    // Save editor for access in selector window
    currentZikulaRoutesModuleEditor = editor;

    editor.popup(
        Zikula.Config.baseURL + Zikula.Config.entrypoint + '?module=ZikulaRoutesModule&type=external&func=finder&editor=ckeditor',
        /*width*/ '80%', /*height*/ '70%',
        'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes'
    );
}



var routes = {};

routes.finder = {};

routes.finder.onLoad = function (baseId, selectedId)
{
    $$('div.categoryselector select').invoke('observe', 'change', routes.finder.onParamChanged);
    $('zikulaRoutesModuleSort').observe('change', routes.finder.onParamChanged);
    $('zikulaRoutesModuleSortDir').observe('change', routes.finder.onParamChanged);
    $('zikulaRoutesModulePageSize').observe('change', routes.finder.onParamChanged);
    $('zikulaRoutesModuleSearchGo').observe('click', routes.finder.onParamChanged);
    $('zikulaRoutesModuleSearchGo').observe('keypress', routes.finder.onParamChanged);
    $('zikulaRoutesModuleSubmit').addClassName('hidden');
    $('zikulaRoutesModuleCancel').observe('click', routes.finder.handleCancel);
};

routes.finder.onParamChanged = function ()
{
    $('zikulaRoutesModuleSelectorForm').submit();
};

routes.finder.handleCancel = function ()
{
    var editor, w;

    editor = $F('editorName');
    if (editor === 'xinha') {
        w = parent.window;
        window.close();
        w.focus();
    } else if (editor === 'tinymce') {
        routesClosePopup();
    } else if (editor === 'ckeditor') {
        routesClosePopup();
    } else {
        alert('Close Editor: ' + editor);
    }
};


function getPasteSnippet(mode, itemId)
{
    var itemUrl, itemTitle, itemDescription, pasteMode;

    itemUrl = $F('url' + itemId);
    itemTitle = $F('title' + itemId);
    itemDescription = $F('desc' + itemId);
    pasteMode = $F('zikulaRoutesModulePasteAs');

    if (pasteMode === '2' || pasteMode !== '1') {
        return itemId;
    }

    // return link to item
    if (mode === 'url') {
        // plugin mode
        return itemUrl;
    } else {
        // editor mode
        return '<a href="' + itemUrl + '" title="' + itemDescription + '">' + itemTitle + '</a>';
    }
}


// User clicks on "select item" button
routes.finder.selectItem = function (itemId)
{
    var editor, html;

    editor = $F('editorName');
    if (editor === 'xinha') {
        if (window.opener.currentZikulaRoutesModuleEditor !== null) {
            html = getPasteSnippet('html', itemId);

            window.opener.currentZikulaRoutesModuleEditor.focusEditor();
            window.opener.currentZikulaRoutesModuleEditor.insertHTML(html);
        } else {
            html = getPasteSnippet('url', itemId);
            var currentInput = window.opener.currentZikulaRoutesModuleInput;

            if (currentInput.tagName === 'INPUT') {
                // Simply overwrite value of input elements
                currentInput.value = html;
            } else if (currentInput.tagName === 'TEXTAREA') {
                // Try to paste into textarea - technique depends on environment
                if (typeof document.selection !== 'undefined') {
                    // IE: Move focus to textarea (which fortunately keeps its current selection) and overwrite selection
                    currentInput.focus();
                    window.opener.document.selection.createRange().text = html;
                } else if (typeof currentInput.selectionStart !== 'undefined') {
                    // Firefox: Get start and end points of selection and create new value based on old value
                    var startPos = currentInput.selectionStart;
                    var endPos = currentInput.selectionEnd;
                    currentInput.value = currentInput.value.substring(0, startPos)
                                        + html
                                        + currentInput.value.substring(endPos, currentInput.value.length);
                } else {
                    // Others: just append to the current value
                    currentInput.value += html;
                }
            }
        }
    } else if (editor === 'tinymce') {
        html = getPasteSnippet('html', itemId);
        window.opener.tinyMCE.activeEditor.execCommand('mceInsertContent', false, html);
        // other tinymce commands: mceImage, mceInsertLink, mceReplaceContent, see http://www.tinymce.com/wiki.php/Command_identifiers
    } else if (editor === 'ckeditor') {
        if (window.opener.currentZikulaRoutesModuleEditor !== null) {
            html = getPasteSnippet('html', itemId);

            window.opener.currentZikulaRoutesModuleEditor.insertHtml(html);
        }
    } else {
        alert('Insert into Editor: ' + editor);
    }
    routesClosePopup();
};


function routesClosePopup()
{
    window.opener.focus();
    window.close();
}




//=============================================================================
// ZikulaRoutesModule item selector for Forms
//=============================================================================

routes.itemSelector = {};
routes.itemSelector.items = {};
routes.itemSelector.baseId = 0;
routes.itemSelector.selectedId = 0;

routes.itemSelector.onLoad = function (baseId, selectedId)
{
    routes.itemSelector.baseId = baseId;
    routes.itemSelector.selectedId = selectedId;

    // required as a changed object type requires a new instance of the item selector plugin
    $('zikulaRoutesModuleObjectType').observe('change', routes.itemSelector.onParamChanged);

    if ($(baseId + '_catidMain') != undefined) {
        $(baseId + '_catidMain').observe('change', routes.itemSelector.onParamChanged);
    } else if ($(baseId + '_catidsMain') != undefined) {
        $(baseId + '_catidsMain').observe('change', routes.itemSelector.onParamChanged);
    }
    $(baseId + 'Id').observe('change', routes.itemSelector.onItemChanged);
    $(baseId + 'Sort').observe('change', routes.itemSelector.onParamChanged);
    $(baseId + 'SortDir').observe('change', routes.itemSelector.onParamChanged);
    $('zikulaRoutesModuleSearchGo').observe('click', routes.itemSelector.onParamChanged);
    $('zikulaRoutesModuleSearchGo').observe('keypress', routes.itemSelector.onParamChanged);

    routes.itemSelector.getItemList();
};

routes.itemSelector.onParamChanged = function ()
{
    $('ajax_indicator').removeClassName('hidden');

    routes.itemSelector.getItemList();
};

routes.itemSelector.getItemList = function ()
{
    var baseId, pars, request;

    baseId = routes.itemSelector.baseId;
    pars = 'ot=' + baseId + '&';
    if ($(baseId + '_catidMain') != undefined) {
        pars += 'catidMain=' + $F(baseId + '_catidMain') + '&';
    } else if ($(baseId + '_catidsMain') != undefined) {
        pars += 'catidsMain=' + $F(baseId + '_catidsMain') + '&';
    }
    pars += 'sort=' + $F(baseId + 'Sort') + '&' +
            'sortdir=' + $F(baseId + 'SortDir') + '&' +
            'searchterm=' + $F(baseId + 'SearchTerm');

    request = new Zikula.Ajax.Request(
        Zikula.Config.baseURL + 'index.php?module=ZikulaRoutesModule&type=ajax&func=getItemListFinder',
        {
            method: 'post',
            parameters: pars,
            onFailure: function(req) {
                Zikula.showajaxerror(req.getMessage());
            },
            onSuccess: function(req) {
                var baseId;
                baseId = routes.itemSelector.baseId;
                routes.itemSelector.items[baseId] = req.getData();
                $('ajax_indicator').addClassName('hidden');
                routes.itemSelector.updateItemDropdownEntries();
                routes.itemSelector.updatePreview();
            }
        }
    );
};

routes.itemSelector.updateItemDropdownEntries = function ()
{
    var baseId, itemSelector, items, i, item;

    baseId = routes.itemSelector.baseId;
    itemSelector = $(baseId + 'Id');
    itemSelector.length = 0;

    items = routes.itemSelector.items[baseId];
    for (i = 0; i < items.length; ++i) {
        item = items[i];
        itemSelector.options[i] = new Option(item.title, item.id, false);
    }

    if (routes.itemSelector.selectedId > 0) {
        $(baseId + 'Id').value = routes.itemSelector.selectedId;
    }
};

routes.itemSelector.updatePreview = function ()
{
    var baseId, items, selectedElement, i;

    baseId = routes.itemSelector.baseId;
    items = routes.itemSelector.items[baseId];

    $(baseId + 'PreviewContainer').addClassName('hidden');

    if (items.length === 0) {
        return;
    }

    selectedElement = items[0];
    if (routes.itemSelector.selectedId > 0) {
        for (var i = 0; i < items.length; ++i) {
            if (items[i].id === routes.itemSelector.selectedId) {
                selectedElement = items[i];
                break;
            }
        }
    }

    if (selectedElement !== null) {
        $(baseId + 'PreviewContainer').update(window.atob(selectedElement.previewInfo))
                                      .removeClassName('hidden');
    }
};

routes.itemSelector.onItemChanged = function ()
{
    var baseId, itemSelector, preview;

    baseId = routes.itemSelector.baseId;
    itemSelector = $(baseId + 'Id');
    preview = window.atob(routes.itemSelector.items[baseId][itemSelector.selectedIndex].previewInfo);

    $(baseId + 'PreviewContainer').update(preview);
    routes.itemSelector.selectedId = $F(baseId + 'Id');
};
