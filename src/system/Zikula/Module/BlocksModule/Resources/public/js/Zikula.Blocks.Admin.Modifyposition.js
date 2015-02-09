// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

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

                var blockorder = new Array();
                $('#assignedblocklist > tbody > tr').each( function() {
                    var bid = $(this).data('bid');
                    if (bid !== undefined) {
                        blockorder.push(bid);
                    }
                });

                $.ajax({
                    url: Routing.generate('zikulablocksmodule_ajax_changeblockorder'),
                    data: {
                        position: $('#position').val(),
                        blockorder: blockorder
                    }
                });
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
