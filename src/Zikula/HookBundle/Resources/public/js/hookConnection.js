// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('.connectionAction').click(modifyConnection);

        function modifyConnection(event) {
            event.preventDefault();
            var a = $(this);
            var target = a.parent("td");

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
                // console.log($(data).find('.connectionAction'))
                // $('.connectionAction').off('click').click(modifyConnection).tooltip('enable');
            });
        }
    });
})(jQuery);
