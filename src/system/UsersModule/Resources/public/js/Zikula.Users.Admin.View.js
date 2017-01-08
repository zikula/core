// Copyright Zikula Foundation, licensed MIT.

( function($) {

    var lastFragment = null;
    var ajax = null;
    var queryId = 0;
    var resultStore = {};

    $(document).ready(function() {
        var userListTable = $('#user-list');
        var userSearchListTable = $('#user-search-list');
        var userSearchMinChar = $('#user-search-min-char');

        $('#user-search-container').removeClass('hide');

        $('#user-search-discard').click(function(e) {
            $('#user-search').val('');
            userListTable.removeClass('hide');
            userSearchListTable.addClass('hide');
            userSearchMinChar.addClass('hide');
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

            if (fragment === "") {
                userListTable.removeClass('hide');
                userSearchListTable.addClass('hide');
                userSearchMinChar.addClass('hide');
                $('.pagination .hide-active').removeClass('hide-active').addClass('active');
                return;
            }

            userListTable.addClass('hide');
            $('.pagination .active').removeClass('active').addClass('hide-active');

            if (fragment.length < 3) {
                userSearchListTable.find('tbody').empty();
                userSearchMinChar.removeClass('hide');
                return;
            }


            userSearchListTable.removeClass('hide');
            userSearchMinChar.addClass('hide');


            // take the result from store if it is in the store
            if (resultStore[fragment] != undefined) {
                userSearchListTable.find('tbody').empty().append(resultStore[fragment]);
                lastFragment = fragment;

                return;
            }

            // search in the dom table - if the last search string is substring of the current one
            if (lastFragment == fragment.substring(0, fragment.length - 1)) {
                userSearchListTable.find('tbody tr').each(function() {

                    var $this = $(this);
                    var username = $this.children().first().text();
                    if (username.indexOf(fragment) === -1) {
                        $this.remove();
                    }
                });
                resultStore[fragment] = userSearchListTable.find('tbody').html();
                lastFragment = fragment;

                return;
            }

            // waiting icon
            userSearchListTable.find('tbody').empty().append('<tr><td colspan="7"><i class="fa fa-spinner fa-spin"></i></td></tr>');

            // get search result from database
            // route must be defined as a data-attribute of the text field e.g. data-route="my_special_route"
            // route-params *may* also be defined if desired and they will be included e.g. data-route-params='{"gid":"{{ group.gid }}"}'
            ajax = $.ajax({
                url: Routing.generate($(this).data('route'), $(this).data('route-params')),
                dataType: 'html',
                type: 'POST',
                data: {
                    'fragment': fragment
                },
                success: function(data) {
                    userSearchListTable.find('tbody').empty().append(data);
                    resultStore[fragment] = data;

                    if (currentQueryId != queryId) {
                        userSearchListTable.find('tbody tr').each(function() {
                            var $this = $(this);
                            var username = $this.children().first().text();
                            if (username.indexOf(lastFragment) === -1) {
                                $this.remove();
                            }
                            resultStore[lastFragment] = userSearchListTable.find('tbody').html();
                        });
                    }
                }
            });
            lastFragment = fragment;
        });
    });
})(jQuery);
