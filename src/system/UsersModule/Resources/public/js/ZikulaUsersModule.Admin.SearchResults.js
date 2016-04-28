// Copyright Zikula Foundation, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#select-all').click( function(event) {
            event.preventDefault();
            $('.user-checkboxes').prop('checked', true);
        });
        $('#deselect-all').click( function(event) {
            event.preventDefault();
            $('.user-checkboxes').prop('checked', false);
        });
        $('#zikulausersmodule_mail_send').on('click', function(event) {
            event.preventDefault();
            var idValues = [];
            // collect values of selected checkboxes
            $('.user-checkboxes:checked').each(function() {
                idValues.push($(this).val());
            });
            if (idValues.length === 0) {
                alert('No users checked! Please select at least one user.');
            } else {
                // set selected values into mailForm and submit the form
                $('#zikulausersmodule_mail_userIds').val(idValues);
                $("form[name='zikulausersmodule_mail']").submit();
            }
        });
    });
})(jQuery);
