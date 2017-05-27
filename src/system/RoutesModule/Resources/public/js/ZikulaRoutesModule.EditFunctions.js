'use strict';

/**
 * Initialises a user field with auto completion.
 */
function zikulaRoutesInitUserField(fieldName, getterName)
{
    jQuery('#' + fieldName + 'ResetVal').click( function (event) {
        event.preventDefault();
        jQuery('#' + fieldName).val('');
        jQuery('#' + fieldName + 'Selector').val('');
    }).removeClass('hidden');

    if (jQuery('#' + fieldName + 'LiveSearch').length < 1) {
        return;
    }
    jQuery('#' + fieldName + 'LiveSearch').removeClass('hidden');

    jQuery('#' + fieldName + 'Selector').autocomplete({
        minLength: 1,
        source: function (request, response) {
            jQuery.getJSON(Routing.generate('zikularoutesmodule_ajax_' + getterName.toLowerCase(), { fragment: request.term }), function(data) {
                response(data);
            });
        },
        response: function(event, ui) {
            if (ui.content.length === 0) {
                jQuery('#' + fieldName + 'LiveSearch').append('<div class="empty-message">' + Translator.__('No results found!') + '</div>');
            } else {
                jQuery('#' + fieldName + 'LiveSearch .empty-message').remove();
            }
        },
        focus: function(event, ui) {
            jQuery('#' + fieldName + 'Selector').val(ui.item.uname);

            return false;
        },
        select: function(event, ui) {
            jQuery('#' + fieldName).val(ui.item.uid);
            jQuery('#' + fieldName + 'Avatar').html(ui.item.avatar);

            return false;
        }
    })
    .autocomplete('instance')._renderItem = function(ul, item) {
        return jQuery('<div class="suggestion">')
            .append('<div class="media"><div class="media-left"><a href="javascript:void(0)">' + item.avatar + '</a></div><div class="media-body"><p class="media-heading">' + item.uname + '</p></div></div>')
            .appendTo(ul);
    };
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

