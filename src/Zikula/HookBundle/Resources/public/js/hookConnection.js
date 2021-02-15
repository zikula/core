// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        $('.connectionAction').click(function (event) {
            event.preventDefault();
            var a = $(this);
            var id = a.data('id');
            var action = a.data('action')

            $.ajax({
                url: Routing.generate('zikula_hook_connection_modify'),
                method: 'POST',
                data: {
                    id: id,
                    action: action
                }
            }).done(function (data) {
                console.log(data)
            }).fail(function (jqXHR, textStatus) {
                alert('Request failed: ' + textStatus);
            })
            .always(function () {
                console.log('id: ' + id, 'action: ' + action)
            });
        });
    });
})(jQuery);
