// Copyright Zikula Foundation 2014 - license GNU/LGPLv3 (or at your option, any later version).

(function($) {
    function displayErrors(data) {
        var errorMessages = $('#users_register_errormsgs');
        // hide error containers in case this is subsequent request
        errorMessages.addClass('hide');
        $('#users_register').find('.validation-error').addClass('hide');

        // display error messages
        if (data.errorMessages) {
            errorMessages.html(data.errorMessages.join('<br />')).removeClass('hide');
        }
        if (data.errorFields) {
            $.each(data.errorFields, function(key, value) {
                $('#users_register_' + key + '_error').html(value).removeClass('hide');
            })
        }
    }

    function validateEntries(event) {
        event.preventDefault();
        $('button.validate').bootstrapBtn('loading');
        $.ajax({
            url: Routing.generate('zikulausersmodule_ajax_getregistrationerrors'),
            type: 'POST',
            data: $('#users_register').serializeArray()
        }).always(function(response, status, xhr) {
            $('button.validate').bootstrapBtn('reset');
            if (response && response.data && response.data.errorFieldsCount > 0) {
                displayErrors(response.data);
            }
        })
    }

    $(document).ready(function() {
        /**
         * Force "User Name" and "Email Address" to lowercase.
         */
        $('.to-lower-case').blur(function() {
            $(this).val($(this).val().toLowerCase());
        });

        $('.z-module-zikulausersmodule input[data-match]').each(function() {
            var $this = $(this);
            var match = $this.data('match');

            /**
             * Check if fields match.
             */
            var e1 = $this[0];
            var e2 = document.getElementById(match.substr(1));
            var checkMatch = function() {
                if (e1.value != e2.value) {
                    e2.setCustomValidity($this.data('match-error-message'));
                } else {
                    e2.setCustomValidity('');
                }
            };

            e1.addEventListener('change', checkMatch, false);
            e2.addEventListener('keyup', checkMatch, false);
            e2.addEventListener('paste', checkMatch, false);
        });

        $('button.validate').on('submit', validateEntries);
    });
})(jQuery);
