( function($) {$(document).ready(function() {
        
/*******************************************************************************
 * Delete groupe
*******************************************************************************/

$('.fa fa-trash-o').click( function(e) {
    e.preventDefault();
    var a = $(this);
    if (confirm(a.data('confirm'))) {
        $.ajax({
            url: 'index.php?module=Groups&type=ajax&func=deletegroup',
            data: {
                gid: a.data('gid')
            },
            success: function(response) {
                a.parent().parent().remove();

            },
            error: function (response) {
                alert($.parseJSON(response.responseText).core.statusmsg);
            }
        });
    }
});    
        
        
});})(jQuery);