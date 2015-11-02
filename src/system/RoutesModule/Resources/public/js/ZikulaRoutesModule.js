'use strict';

function zikulaRoutesCapitaliseFirstLetter(string)
{
    return string.charAt(0).toUpperCase() + string.substring(1);
}

/**
 * Submits a quick navigation form.
 */
function zikulaRoutesSubmitQuickNavForm(objectType)
{
    jQuery('#zikularoutesmodule' + zikulaRoutesCapitaliseFirstLetter(objectType) + 'QuickNavForm').submit();
}

/**
 * Initialise the quick navigation panel in list views.
 */
function zikulaRoutesInitQuickNavigation(objectType)
{
    if (jQuery('#zikularoutesmodule' + zikulaRoutesCapitaliseFirstLetter(objectType) + 'QuickNavForm').length < 1) {
        return;
    }

    if (jQuery('#catid').length > 0) {
        jQuery('#catid').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if (jQuery('#sortBy').length > 0) {
        jQuery('#sortBy').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if (jQuery('#sortDir').length > 0) {
        jQuery('#sortDir').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if (jQuery('#num').length > 0) {
        jQuery('#num').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }

    switch (objectType) {
    case 'route':
        if (jQuery('#workflowState').length > 0) {
            jQuery('#workflowState').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#routeType').length > 0) {
            jQuery('#routeType').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#schemes').length > 0) {
            jQuery('#schemes').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#methods').length > 0) {
            jQuery('#methods').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#prependBundlePrefix').length > 0) {
            jQuery('#prependBundlePrefix').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#translatable').length > 0) {
            jQuery('#translatable').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        break;
    default:
        break;
    }
}

/**
 * Simulates a simple alert using bootstrap.
 */
function zikulaRoutesSimpleAlert(beforeElem, title, content, alertId, cssClass)
{
    var alertBox;

    alertBox = ' \
        <div id="' + alertId + '" class="alert alert-' + cssClass + ' fade"> \
          <button type="button" class="close" data-dismiss="alert">&times;</button> \
          <h4>' + title + '</h4> \
          <p>' + content + '</p> \
        </div>';

    // insert alert before the given element
    beforeElem.before(alertBox);

    jQuery('#' + alertId).delay(200).addClass('in').fadeOut(4000, function () {
        jQuery(this).remove();
    });
}
