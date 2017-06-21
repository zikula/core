// Copyright Zikula Foundation, licensed MIT.

/**
 * Initialises a user field with auto completion.
 */
function initUserLiveSearch(fieldName)
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
            jQuery.getJSON(Routing.generate('zikulausersmodule_livesearch_getusers', { fragment: request.term }), function(data) {
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
