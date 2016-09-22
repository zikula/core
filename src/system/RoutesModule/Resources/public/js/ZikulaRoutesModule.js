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

    var fieldPrefix = 'zikularoutesmodule_' + objectType.toLowerCase() + 'quicknav_';
    if (jQuery('#' + fieldPrefix + 'catid').length > 0) {
        jQuery('#' + fieldPrefix + 'catid').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if (jQuery('#' + fieldPrefix + 'sortBy').length > 0) {
        jQuery('#' + fieldPrefix + 'sortBy').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if (jQuery('#' + fieldPrefix + 'sortDir').length > 0) {
        jQuery('#' + fieldPrefix + 'sortDir').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if (jQuery('#' + fieldPrefix + 'num').length > 0) {
        jQuery('#' + fieldPrefix + 'num').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }

    switch (objectType) {
    case 'route':
        if (jQuery('#' + fieldPrefix + 'workflowState').length > 0) {
            jQuery('#' + fieldPrefix + 'workflowState').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#' + fieldPrefix + 'routeType').length > 0) {
            jQuery('#' + fieldPrefix + 'routeType').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#' + fieldPrefix + 'schemes').length > 0) {
            jQuery('#' + fieldPrefix + 'schemes').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#' + fieldPrefix + 'methods').length > 0) {
            jQuery('#' + fieldPrefix + 'methods').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#' + fieldPrefix + 'prependBundlePrefix').length > 0) {
            jQuery('#' + fieldPrefix + 'prependBundlePrefix').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if (jQuery('#' + fieldPrefix + 'translatable').length > 0) {
            jQuery('#' + fieldPrefix + 'translatable').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
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
