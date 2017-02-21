'use strict';

/**
 * Initialises a user field with auto completion.
 */
function zikulaRoutesInitUserField(fieldName, getterName)
{
    if (jQuery('#' + fieldName + 'LiveSearch').length < 1) {
        return;
    }
    jQuery('#' + fieldName + 'LiveSearch').removeClass('hidden');

    jQuery('#' + fieldName + 'Selector').typeahead({
        highlight: true,
        hint: true,
        minLength: 2
    }, {
        limit: 15,
        // The data source to query against. Receives the query value in the input field and the process callbacks.
        source: function (query, syncResults, asyncResults) {
            // Retrieve data from server using "query" parameter as it contains the search string entered by the user
            jQuery('#' + fieldName + 'Indicator').removeClass('hidden');
            jQuery.getJSON(Routing.generate('zikularoutesmodule_ajax_' + getterName.toLowerCase(), { fragment: query }), function( data ) {
                jQuery('#' + fieldName + 'Indicator').addClass('hidden');
                asyncResults(data);
            });
        },
        templates: {
            empty: '<div class="empty-message">' + jQuery('#' + fieldName + 'NoResultsHint').text() + '</div>',
            suggestion: function(user) {
                var html;

                html = '<div class="typeahead">';
                html += '<div class="media"><a class="pull-left" href="javascript:void(0)">' + user.avatar + '</a>';
                html += '<div class="media-body">';
                html += '<p class="media-heading">' + user.uname + '</p>';
                html += '</div>';
                html += '</div>';

                return html;
            }
        }
    }).bind('typeahead:select', function(ev, user) {
        // Called after the user selects an item. Here we can do something with the selection.
        jQuery('#' + fieldName).val(user.uid);
        jQuery(this).typeahead('val', user.uname);
    });
}


var editedObjectType;
var editedEntityId;
var editForm;
var formButtons;
var triggerValidation = true;

function zikulaRoutesTriggerFormValidation()
{
    zikulaRoutesExecuteCustomValidationConstraints(editedObjectType, editedEntityId);

    if (!editForm.get(0).checkValidity()) {
        // This does not really submit the form,
        // but causes the browser to display the error message
        editForm.find(':submit').first().click();
    }
}

function zikulaRoutesHandleFormSubmit (event) {
    if (triggerValidation) {
        zikulaRoutesTriggerFormValidation();
        if (!editForm.get(0).checkValidity()) {
            event.preventDefault();
            return false;
        }
    }

    // hide form buttons to prevent double submits by accident
    formButtons.each(function (index) {
        jQuery(this).addClass('hidden');
    });

    return true;
}

/**
 * Initialises an entity edit form.
 */
function zikulaRoutesInitEditForm(mode, entityId)
{
    if (jQuery('.zikularoutes-edit-form').length < 1) {
        return;
    }

    editForm = jQuery('.zikularoutes-edit-form').first();
    editedObjectType = editForm.attr('id').replace('EditForm', '');
    editedEntityId = entityId;

    if (jQuery('#moderationFieldsSection').length > 0) {
        jQuery('#moderationFieldsContent').addClass('hidden');
        jQuery('#moderationFieldsSection legend').addClass('pointer').click(function (event) {
            if (jQuery('#moderationFieldsContent').hasClass('hidden')) {
                jQuery('#moderationFieldsContent').removeClass('hidden');
                jQuery(this).find('i').removeClass('fa-expand').addClass('fa-compress');
            } else {
                jQuery('#moderationFieldsContent').addClass('hidden');
                jQuery(this).find('i').removeClass('fa-compress').addClass('fa-expand');
            }
        });
    }

    var allFormFields = editForm.find('input, select, textarea');
    allFormFields.change(function (event) {
        zikulaRoutesExecuteCustomValidationConstraints(editedObjectType, editedEntityId);
    });

    formButtons = editForm.find('.form-buttons input');
    editForm.find('.btn-danger').first().bind('click keypress', function (event) {
        if (!window.confirm(Translator.__('Do you really want to delete this entry?'))) {
            event.preventDefault();
        }
    });
    editForm.find('button[type=submit]').bind('click keypress', function (event) {
        triggerValidation = !jQuery(this).attr('formnovalidate');
    });
    editForm.submit(zikulaRoutesHandleFormSubmit);

    if (mode != 'create') {
        zikulaRoutesTriggerFormValidation();
    }
}

