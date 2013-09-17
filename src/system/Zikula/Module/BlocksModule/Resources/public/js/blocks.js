// Copyright Zikula Foundation 2013 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {$(document).ready(function() {


/*******************************************************************************
 * Toggle block
*******************************************************************************/

$('.label').click( function(e) {
    e.preventDefault();
    var a = $(this)
    $.ajax({
        url: 'index.php?module=Blocks&type=ajax&func=toggleblock',
        data: {
            bid: a.data('bid')
        },
        success: function(response) {
            // toggle label
            a.parent().find('a').toggleClass('hide');

        }
    });
});


/*******************************************************************************
 * Sort blocks in a block position
*******************************************************************************/
    
// Return a helper with preserved width of cells
var fixHelper = function(e, ui) {
    ui.children().each(function() {
        $(this).width($(this).width());
    });
    return ui;
};

$("#assignedblocklist tbody").sortable({
    connectWith: "#unassignedblocklist tbody",
    helper: fixHelper,
    update: function(event, ui) {
        var blockorder = new Array();
        $('#assignedblocklist > tbody > tr').each( function() {
            var bid = $(this).data('bid');
            console.log(bid);
            if (bid !== undefined) {
                blockorder.push(bid);
            }
        });
        console.log(blockorder);
        $.ajax({
            url: 'index.php?module=Blocks&type=ajax&func=changeblockorder',
            data: {
                position: $('#position').val(),
                blockorder: blockorder

            },
            success: function(response) {
                console.log('jo')
            }
        });
    },
}).disableSelection();

$("#unassignedblocklist tbody").sortable({
    connectWith: "#assignedblocklist tbody",
    helper: fixHelper
}).disableSelection();



});})(jQuery);