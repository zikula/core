// Copyright Zikula Foundation 2014 - license GNU/LGPLv3 (or at your option, any later version).

( function($) {

    $( document ).ready(function() {
        $('#liveusersearch').removeClass('hide');
        $('#modifyuser').click( function() {
            window.location.href = Zikula.Config.baseURL + "index.php?module=users&type=admin&func=modify&uname=" + $('#username').val();
        });
        $('#deleteuser').click( function() {
            window.location.href = Zikula.Config.baseURL + "index.php?module=users&type=admin&func=deleteusers&uname=" + $('#username').val();
        });

        $("#username").select2({
            placeholder: $('#username').attr('title'),
            minimumInputLength: 3,
            ajax: {
                dataType: "json",
                url: Zikula.Config.baseURL + "index.php?module=users&type=ajax&func=getUsers",
                data: function (term, page) { // page is the one-based page number tracked by Select2
                    return {
                        fragment: term, //search term
                        dataType: "json"
                    };
                },
                results: function (data) {
                    return {results: data};
                }
            },
            id: function(data){
                return data.uname;
            },
            formatResult: function(result, container, query) {
                var text = result.uname;
                return text.replace(query.term, '<b>'+query.term+'</b>');
            },
            formatSelection: function(result, container, query) {
                if (result.uid > 2) {
                    $('#modifyuser, #deleteuser').removeClass('hide');
                } else {
                    $('#modifyuser').removeClass('hide');
                    $('#deleteuser').addClass('hide');
                }
                return result.uname;
            }
        });
    })

})(jQuery);