// Copyright Zikula Foundation, licensed MIT.

'use strict';

var currentZikulaRoutesModuleEditor = null;
var currentZikulaRoutesModuleInput = null;

/**
 * Returns the attributes used for the popup window. 
 * @return {String}
 */
function getZikulaRoutesModulePopupAttributes()
{
    var pWidth, pHeight;

    pWidth = screen.width * 0.75;
    pHeight = screen.height * 0.66;

    return 'width=' + pWidth + ',height=' + pHeight + ',scrollbars,resizable';
}

/**
 * Open a popup window with the finder triggered by a Xinha button.
 */
function ZikulaRoutesModuleFinderXinha(editor, routesUrl)
{
    var popupAttributes;

    // Save editor for access in selector window
    currentZikulaRoutesModuleEditor = editor;

    popupAttributes = getZikulaRoutesModulePopupAttributes();
    window.open(routesUrl, '', popupAttributes);
}

/**
 * Open a popup window with the finder triggered by a CKEditor button.
 */
function ZikulaRoutesModuleFinderCKEditor(editor, routesUrl)
{
    // Save editor for access in selector window
    currentZikulaRoutesModuleEditor = editor;

    editor.popup(
        Routing.generate('zikularoutesmodule_external_finder', { editor: 'ckeditor' }),
        /*width*/ '80%', /*height*/ '70%',
        'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes'
    );
}


var zikulaRoutesModule = {};

zikulaRoutesModule.finder = {};

zikulaRoutesModule.finder.onLoad = function (baseId, selectedId)
{
    $('div.categoryselector select').change(zikulaRoutesModule.finder.onParamChanged);
    $('#zikulaRoutesModuleSort').change(zikulaRoutesModule.finder.onParamChanged);
    $('#zikulaRoutesModuleSortDir').change(zikulaRoutesModule.finder.onParamChanged);
    $('#zikulaRoutesModulePageSize').change(zikulaRoutesModule.finder.onParamChanged);
    $('#zikulaRoutesModuleSearchGo').click(zikulaRoutesModule.finder.onParamChanged);
    $('#zikulaRoutesModuleSearchGo').keypress(zikulaRoutesModule.finder.onParamChanged);
    $('#zikulaRoutesModuleSubmit').addClass('hidden');
    $('#zikulaRoutesModuleCancel').click(zikulaRoutesModule.finder.handleCancel);
};

zikulaRoutesModule.finder.onParamChanged = function ()
{
    $('#zikulaRoutesModuleSelectorForm').submit();
};

zikulaRoutesModule.finder.handleCancel = function ()
{
    var editor, w;

    editor = $('#editorName').val();
    if (editor === 'xinha') {
        w = parent.window;
        window.close();
        w.focus();
    } else if (editor === 'tinymce') {
        zikulaRoutesClosePopup();
    } else if (editor === 'ckeditor') {
        zikulaRoutesClosePopup();
    } else {
        alert('Close Editor: ' + editor);
    }
};


function zikulaRoutesGetPasteSnippet(mode, itemId)
{
    var quoteFinder, itemUrl, itemTitle, itemDescription, pasteMode;

    quoteFinder = new RegExp('"', 'g');
    itemUrl = $('#url' + itemId).val().replace(quoteFinder, '');
    itemTitle = $('#title' + itemId).val().replace(quoteFinder, '');
    itemDescription = $('#desc' + itemId).val().replace(quoteFinder, '');
    pasteMode = $('#zikulaRoutesModulePasteAs').val();

    if (pasteMode === '2' || pasteMode !== '1') {
        return itemId;
    }

    // return link to item
    if (mode === 'url') {
        // plugin mode
        return itemUrl;
    }

    // editor mode
    return '<a href="' + itemUrl + '" title="' + itemDescription + '">' + itemTitle + '</a>';
}


// User clicks on "select item" button
zikulaRoutesModule.finder.selectItem = function (itemId)
{
    var editor, html;

    editor = $('#editorName').val();
    if (editor === 'xinha') {
        if (window.opener.currentZikulaRoutesModuleEditor !== null) {
            html = zikulaRoutesGetPasteSnippet('html', itemId);

            window.opener.currentZikulaRoutesModuleEditor.focusEditor();
            window.opener.currentZikulaRoutesModuleEditor.insertHTML(html);
        } else {
            html = zikulaRoutesGetPasteSnippet('url', itemId);
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
        html = zikulaRoutesGetPasteSnippet('html', itemId);
        tinyMCE.activeEditor.execCommand('mceInsertContent', false, html);
        // other tinymce commands: mceImage, mceInsertLink, mceReplaceContent, see http://www.tinymce.com/wiki.php/Command_identifiers
    } else if (editor === 'ckeditor') {
        if (window.opener.currentZikulaRoutesModuleEditor !== null) {
            html = zikulaRoutesGetPasteSnippet('html', itemId);

            window.opener.currentZikulaRoutesModuleEditor.insertHtml(html);
        }
    } else {
        alert('Insert into Editor: ' + editor);
    }
    zikulaRoutesClosePopup();
};


function zikulaRoutesClosePopup()
{
    window.opener.focus();
    window.close();
}




//=============================================================================
// ZikulaRoutesModule item selector for Forms
//=============================================================================

zikulaRoutesModule.itemSelector = {};
zikulaRoutesModule.itemSelector.items = {};
zikulaRoutesModule.itemSelector.baseId = 0;
zikulaRoutesModule.itemSelector.selectedId = 0;

zikulaRoutesModule.itemSelector.onLoad = function (baseId, selectedId)
{
    zikulaRoutesModule.itemSelector.baseId = baseId;
    zikulaRoutesModule.itemSelector.selectedId = selectedId;

    // required as a changed object type requires a new instance of the item selector plugin
    $('#zikulaRoutesModuleObjectType').change(zikulaRoutesModule.itemSelector.onParamChanged);

    if ($('#' + baseId + '_catidMain').size() > 0) {
        $('#' + baseId + '_catidMain').change(zikulaRoutesModule.itemSelector.onParamChanged);
    } else if ($('#' + baseId + '_catidsMain').size() > 0) {
        $('#' + baseId + '_catidsMain').change(zikulaRoutesModule.itemSelector.onParamChanged);
    }
    $('#' + baseId + 'Id').change(zikulaRoutesModule.itemSelector.onItemChanged);
    $('#' + baseId + 'Sort').change(zikulaRoutesModule.itemSelector.onParamChanged);
    $('#' + baseId + 'SortDir').change(zikulaRoutesModule.itemSelector.onParamChanged);
    $('#zikulaRoutesModuleSearchGo').click(zikulaRoutesModule.itemSelector.onParamChanged);
    $('#zikulaRoutesModuleSearchGo').keypress(zikulaRoutesModule.itemSelector.onParamChanged);

    zikulaRoutesModule.itemSelector.getItemList();
};

zikulaRoutesModule.itemSelector.onParamChanged = function ()
{
    $('ajax_indicator').removeClass('hidden');

    zikulaRoutesModule.itemSelector.getItemList();
};

zikulaRoutesModule.itemSelector.getItemList = function ()
{
    var baseId, params;

    baseId = routes.itemSelector.baseId;
    params = 'ot=' + baseId + '&';
    if ($('#' + baseId + '_catidMain').size() > 0) {
        params += 'catidMain=' + $('#' + baseId + '_catidMain').val() + '&';
    } else if ($('#' + baseId + '_catidsMain').size() > 0) {
        params += 'catidsMain=' + $('#' + baseId + '_catidsMain').val() + '&';
    }
    params += 'sort=' + $('#' + baseId + 'Sort').val() + '&' +
              'sortdir=' + $('#' + baseId + 'SortDir').val() + '&' +
              'q=' + $('#' + baseId + 'SearchTerm').val();

    $.ajax({
        type: 'POST',
        url: Routing.generate('zikularoutesmodule_ajax_getitemlistfinder'),
        data: params
    }).done(function(res) {
        // get data returned by the ajax response
        var baseId;
        baseId = zikulaRoutesModule.itemSelector.baseId;
        zikulaRoutesModule.itemSelector.items[baseId] = res.data;
        $('#ajax_indicator').addClass('hidden');
        zikulaRoutesModule.itemSelector.updateItemDropdownEntries();
        zikulaRoutesModule.itemSelector.updatePreview();
    });
};

zikulaRoutesModule.itemSelector.updateItemDropdownEntries = function ()
{
    var baseId, itemSelector, items, i, item;

    baseId = zikulaRoutesModule.itemSelector.baseId;
    itemSelector = $('#' + baseId + 'Id');
    itemSelector.length = 0;

    items = zikulaRoutesModule.itemSelector.items[baseId];
    for (i = 0; i < items.length; ++i) {
        item = items[i];
        itemSelector.options[i] = new Option(item.title, item.id, false);
    }

    if (zikulaRoutesModule.itemSelector.selectedId > 0) {
        $('#' + baseId + 'Id').val(zikulaRoutesModule.itemSelector.selectedId);
    }
};

zikulaRoutesModule.itemSelector.updatePreview = function ()
{
    var baseId, items, selectedElement, i;

    baseId = zikulaRoutesModule.itemSelector.baseId;
    items = zikulaRoutesModule.itemSelector.items[baseId];

    $('#' + baseId + 'PreviewContainer').addClass('hidden');

    if (items.length === 0) {
        return;
    }

    selectedElement = items[0];
    if (zikulaRoutesModule.itemSelector.selectedId > 0) {
        for (var i = 0; i < items.length; ++i) {
            if (items[i].id === zikulaRoutesModule.itemSelector.selectedId) {
                selectedElement = items[i];
                break;
            }
        }
    }

    if (selectedElement !== null) {
        $('#' + baseId + 'PreviewContainer')
            .html(window.atob(selectedElement.previewInfo))
            .removeClass('hidden');
    }
};

zikulaRoutesModule.itemSelector.onItemChanged = function ()
{
    var baseId, itemSelector, preview;

    baseId = zikulaRoutesModule.itemSelector.baseId;
    itemSelector = $('#' + baseId + 'Id');
    preview = window.atob(zikulaRoutesModule.itemSelector.items[baseId][itemSelector.selectedIndex].previewInfo);

    $(baseId + 'PreviewContainer').html(preview);
    zikulaRoutesModule.itemSelector.selectedId = $('#' + baseId + 'Id').val();
};
