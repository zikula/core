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
    $('#zikularoutesmodule' + zikulaRoutesCapitaliseFirstLetter(objectType) + 'QuickNavForm').submit();
}

/**
 * Initialise the quick navigation panel in list views.
 */
function zikulaRoutesInitQuickNavigation(objectType)
{
    if ($('#zikularoutesmodule' + zikulaRoutesCapitaliseFirstLetter(objectType) + 'QuickNavForm').size() < 1) {
        return;
    }

    if ($('#catid').size() > 0) {
        $('#catid').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if ($('#sortby').size() > 0) {
        $('#sortby').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if ($('#sortdir').size() > 0) {
        $('#sortdir').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }
    if ($('#num').size() > 0) {
        $('#num').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
    }

    switch (objectType) {
    case 'route':
        if ($('#workflowState').size() > 0) {
            $('#workflowState').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
        }
        if ($('#userRoute').size() > 0) {
            $('#userRoute').change(function () { zikulaRoutesSubmitQuickNavForm(objectType); });
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

    $('#' + alertId).delay(200).addClass('in').fadeOut(4000, function () {
        $(this).remove();
    });
}
