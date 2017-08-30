'use strict';

function zikulaRoutesCapitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.substring(1);
}

/**
 * Initialise the quick navigation form in list views.
 */
function zikulaRoutesInitQuickNavigation()
{
    var quickNavForm;
    var objectType;

    if (jQuery('.zikularoutesmodule-quicknav').length < 1) {
        return;
    }

    quickNavForm = jQuery('.zikularoutesmodule-quicknav').first();
    objectType = quickNavForm.attr('id').replace('zikulaRoutesModule', '').replace('QuickNavForm', '');

    quickNavForm.find('select').change(function (event) {
        quickNavForm.submit();
    });

    var fieldPrefix = 'zikularoutesmodule_' + objectType.toLowerCase() + 'quicknav_';
    // we can hide the submit button if we have no visible quick search field
    if (jQuery('#' + fieldPrefix + 'q').length < 1 || jQuery('#' + fieldPrefix + 'q').parent().parent().hasClass('hidden')) {
        jQuery('#' + fieldPrefix + 'updateview').addClass('hidden');
    }
}

/**
 * Simulates a simple alert using bootstrap.
 */
function zikulaRoutesSimpleAlert(anchorElement, title, content, alertId, cssClass)
{
    var alertBox;

    alertBox = ' \
        <div id="' + alertId + '" class="alert alert-' + cssClass + ' fade"> \
          <button type="button" class="close" data-dismiss="alert">&times;</button> \
          <h4>' + title + '</h4> \
          <p>' + content + '</p> \
        </div>';

    // insert alert before the given anchor element
    anchorElement.before(alertBox);

    jQuery('#' + alertId).delay(200).addClass('in').fadeOut(4000, function () {
        jQuery(this).remove();
    });
}

/**
 * Initialises the mass toggle functionality for admin view pages.
 */
function zikulaRoutesInitMassToggle()
{
    if (jQuery('.zikularoutes-mass-toggle').length > 0) {
        jQuery('.zikularoutes-mass-toggle').unbind('click').click(function (event) {
            if (jQuery('.table.fixed-columns').length > 0) {
                jQuery('.zikularoutes-toggle-checkbox').prop('checked', false);
                jQuery('.table.fixed-columns .zikularoutes-toggle-checkbox').prop('checked', jQuery(this).prop('checked'));
            } else {
                jQuery('.zikularoutes-toggle-checkbox').prop('checked', jQuery(this).prop('checked'));
            }
        });
    }
}

/**
 * Initialises fixed table columns.
 */
function zikulaRoutesInitFixedColumns()
{
    jQuery('.table.fixed-columns').remove();
    jQuery('.table').each(function() {
        var originalTable, fixedColumnsTable, fixedTableWidth;

        originalTable = jQuery(this);
        fixedTableWidth = 0;
        if (originalTable.find('.fixed-column').length > 0) {
            fixedColumnsTable = originalTable.clone().insertBefore(originalTable).addClass('fixed-columns').removeAttr('id');
            originalTable.find('.dropdown').addClass('hidden');
            fixedColumnsTable.find('.dropdown').removeClass('hidden');
            fixedColumnsTable.css('left', originalTable.parent().position().left);

            fixedColumnsTable.find('th, td').not('.fixed-column').remove();
            fixedColumnsTable.find('th').each(function (i, elem) {
                jQuery(this).css('width', originalTable.find('th').eq(i).css('width'));
                fixedTableWidth += originalTable.find('th').eq(i).width();
            });
            fixedColumnsTable.css('width', fixedTableWidth + 'px');

            fixedColumnsTable.find('tr').each(function (i, elem) {
                jQuery(this).height(originalTable.find('tr:eq(' + i + ')').height());
            });
        }
    });
    zikulaRoutesInitMassToggle();
}

/**
 * Creates a dropdown menu for the item actions.
 */
function zikulaRoutesInitItemActions(context)
{
    var containerSelector;
    var containers;
    var listClasses;

    containerSelector = '';
    if (context == 'view') {
        containerSelector = '.zikularoutesmodule-view';
        listClasses = 'list-unstyled dropdown-menu';
    } else if (context == 'display') {
        containerSelector = 'h2, h3';
        listClasses = 'list-unstyled dropdown-menu';
    }

    if (containerSelector == '') {
        return;
    }

    containers = jQuery(containerSelector);
    if (containers.length < 1) {
        return;
    }

    containers.find('.dropdown > ul').removeClass('list-inline').addClass(listClasses);
    containers.find('.dropdown > ul a').each(function (index) {
        var title;

        title = jQuery(this).find('i').first().attr('title');
        if (title == '') {
            title = jQuery(this).find('i').first().data('original-title');
        }
        jQuery(this).html(jQuery(this).html() + title);
    });
    containers.find('.dropdown > ul a i').addClass('fa-fw');
    containers.find('.dropdown-toggle').removeClass('hidden').dropdown();
}

/**
 * Initialises reordering view entries using drag n drop.
 */
function zikulaRoutesInitSortable()
{
    if (jQuery('#sortableTable').length < 1) {
        return;
    }

    jQuery('#sortableTable > tbody').sortable({
        cursor: 'move',
        handle: '.sort-handle',
        items: '.sort-item',
        placeholder: 'ui-state-highlight',
        tolerance: 'pointer',
        sort: function(event, ui) {
            ui.item.addClass('active-item-shadow');
        },
        stop: function(event, ui) {
            ui.item.removeClass('active-item-shadow');
            zikulaRoutesInitFixedColumns();
        },
        update: function(event, ui) {
            jQuery.ajax({
                method: 'POST',
                url: Routing.generate('zikularoutesmodule_ajax_updatesortpositions'),
                data: {
                    ot: jQuery('#sortableTable').data('object-type'),
                    identifiers: jQuery(this).sortable('toArray', { attribute: 'data-item-id' }),
                    min: jQuery('#sortableTable').data('min'),
                    max: jQuery('#sortableTable').data('max')
                }/*,
                success: function(data) {
                    if (data.message) {
                        zikulaRoutesSimpleAlert(jQuery('#sortableTable'), Translator.__('Success'), data.message, 'sortingDoneAlert', 'success');
                    }
            	}*/
            });
        }
    });
    jQuery('#sortableTable').disableSelection();
}

jQuery(document).ready(function() {
    var isViewPage;
    var isDisplayPage;

    isViewPage = jQuery('.zikularoutesmodule-view').length > 0;
    isDisplayPage = jQuery('.zikularoutesmodule-display').length > 0;

    if (isViewPage) {
        zikulaRoutesInitQuickNavigation();
        zikulaRoutesInitMassToggle();
        jQuery(window).resize(zikulaRoutesInitFixedColumns);
        zikulaRoutesInitFixedColumns();
        window.setTimeout(zikulaRoutesInitFixedColumns, 1000);
        zikulaRoutesInitItemActions('view');
        zikulaRoutesInitSortable();
    } else if (isDisplayPage) {
        zikulaRoutesInitItemActions('display');
    }
});
