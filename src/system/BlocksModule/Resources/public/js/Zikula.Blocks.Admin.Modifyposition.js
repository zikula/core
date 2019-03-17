// Copyright Zikula Foundation, licensed MIT.

/*******************************************************************************
 * Sort blocks in a block position
 *******************************************************************************/
( function($) {
    $(document).ready(function() {

        // Return a helper with preserved width of cells
        var fixHelper = function(e, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });
            return ui;
        };

        $('#assignedblocklist tbody').sortable({
            connectWith: '#unassignedblocklist tbody',
            helper: fixHelper,
            placeholder: 'warning',
            update: function(event, ui) {
                // Make sure to always show a dropzone.
                if ($('#assignedblocklist tbody tr').length <= 1) {
                    $('#assignedblocklist .sortable-placeholder').fadeIn();
                } else {
                    $('#assignedblocklist .sortable-placeholder').fadeOut();
                }

                var blockOrder = [];
                $('#assignedblocklist > tbody > tr').each( function() {
                    var bid = $(this).data('bid');
                    if (bid !== undefined) {
                        blockOrder.push(bid);
                    }
                });

                $.ajax({
                    url: Routing.generate('zikulablocksmodule_placement_changeblockorder'),
                    data: {
                        position: $('#position').data('position'),
                        blockorder: blockOrder
                    }
                })
                .done(function(data) {
                    $('#feedback').fadeIn(200).fadeOut(3500);
                    //var descriptionDiv = $('#zikulablocksmodule_block_description').parents('.form-group');
                    //descriptionDiv.after(data);
                })
                .fail(function(jqXHR, textStatus) {
                    alert('Error: ' + textStatus);
                })
            }
        }).disableSelection();

        $('#unassignedblocklist tbody').sortable({
            connectWith: '#assignedblocklist tbody',
            helper: fixHelper,
            placeholder: 'warning',
            update: function(event, ui) {
                // Make sure to always show a dropzone.
                if ($('#unassignedblocklist tbody tr').length <= 1) {
                    $('#unassignedblocklist .sortable-placeholder').fadeIn();
                } else {
                    $('#unassignedblocklist .sortable-placeholder').fadeOut();
                }
            }
        }).disableSelection();
    });
})(jQuery);
