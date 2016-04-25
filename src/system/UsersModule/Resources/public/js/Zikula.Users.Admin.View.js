// Copyright Zikula Foundation, licensed MIT.

( function($) {

    var lastFragment = null;
    var ajax = null;
    var queryId = 0;
    var resultStore = {};

    $(document).ready(function() {
        $('#user-search-container').removeClass('hide');

        $('#user-search-discard').click(function(e) {
            $('#user-search').val('');
            $('#user-list').removeClass('hide');
            $('#user-search-list').addClass('hide');
            $('#user-search-min-char').addClass('hide');
            $('.pagination .hide-active').removeClass('hide-active').addClass('active');
        });

        $('#user-search').keyup(function(e) {
            queryId++;
            var currentQueryId = queryId;

            // skip if shift was pressed
            var key = e.keyCode || e.which;
            if (key == 16) {
                return;
            }

            var fragment = $(this).attr('value');

            if (fragment === '') {
                $('#user-list').removeClass('hide');
                $('#user-search-list').addClass('hide');
                $('#user-search-min-char').addClass('hide');
                $('.pagination .hide-active').removeClass('hide-active').addClass('active');
                return;
            }

            $('#user-list').addClass('hide');
            $('.pagination .active').removeClass('active').addClass('hide-active');

            if (fragment.length < 3) {
                $('#user-search-list tbody').empty();
                $('#user-search-min-char').removeClass('hide');
                return;
            }


            $('#user-search-list').removeClass('hide');
            $('#user-search-min-char').addClass('hide');


            // take the result from store if it is in the store
            if (resultStore[fragment] != undefined) {
                $('#user-search-list tbody').empty().append(resultStore[fragment]);
                lastFragment = fragment;

                return;
            }

            // search in the dom table - if the last search string is substring of the current one
            if (lastFragment == fragment.substring(0, fragment.length - 1)) {
                $('#user-search-list tbody tr').each(function() {

                    var $this = $(this);
                    var username = $this.children().first().text();
                    if (username.indexOf(fragment) === -1) {
                        $this.remove();
                    }
                });
                resultStore[fragment] = $('#user-search-list tbody').html();
                lastFragment = fragment;

                return;
            }

            // waiting icon
            $('#user-search-list tbody').empty().append('<tr><td colspan="7"><i class="fa fa-spinner fa-spin"></i></td></tr>');

            // get search result from database
            if (ajax !== null) {
                ajax.abort;
            }
            ajax = $.ajax({
                // TODO use a route for this url
                url: Zikula.Config.baseURL + 'index.php?module=users&type=ajax&func=getUsersAsTable',
                dataType: 'html',
                type: 'GET',
                data: {
                    'fragment': fragment
                },
                success: function(data) {
                    $('#user-search-list tbody').empty().append(data);
                    resultStore[fragment] = data;

                    if (currentQueryId != queryId) {
                        $('#user-search-list tbody tr').each(function() {
                            var $this = $(this);
                            var username = $this.children().first().text();
                            if (username.indexOf(lastFragment) === -1) {
                                $this.remove();
                            }
                            resultStore[lastFragment] = $('#user-search-list tbody').html();
                        });
                    }
                }
            });
            lastFragment = fragment;
        });
    });
})(jQuery);
