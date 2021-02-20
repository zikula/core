// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        var numberOfColumns = $("#connections-table > tbody > tr:first > td").length;
        $('.connectionAction').click(modifyConnection);
        $('#filter-text').on('input', filterColumns);

        function modifyConnection(event) {
            event.preventDefault();
            var a = $(this);
            var target = a.parent("td");
            target.css('background-color', '#ffdb99');

            $.ajax({
                url: Routing.generate('zikula_hook_connection_modify'),
                method: 'POST',
                data: {
                    id: target.attr('id'),
                    eventName: target.data('event-name'),
                    listenerName: target.data('listener-name'),
                    action: a.data('action')
                }
            }).done(function (data) {
                a.tooltip('hide');
                var html = $.parseHTML(data);
                target.replaceWith(html);
                $(html).find('.connectionAction').click(modifyConnection).tooltip('enable');
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            })
            .always(function (data) {
            });
        }

        function filterColumns(event) {
            event.preventDefault();
            $('#connections-table td,#connections-table th').show();
            var filterText = $(this).val().toLowerCase();
            if (!filterText) return;
            // find the column ids that do not contain the search text (removes first column which always matches)
            var columnsToHide = $("#connections-table thead th:not( [id*='" + filterText + "'] )").slice(1);
            if (columnsToHide.length === numberOfColumns) return;
            columnsToHide.each(function () {
                var columnIndex = $(this).parent().children().index($(this));
                columnIndex++; // convert from zero-based to one-based
                $('#connections-table td:nth-child(' + columnIndex + '),#connections-table th:nth-child( ' + columnIndex + ')').hide();
            });
        }
    });
})(jQuery);
