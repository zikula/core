// Copyright Zikula Foundation, licensed MIT.

(function($) {
    function displayErrors(data) {
        if (data.errorFields) {
            $.each(data.errorFields, function(key, value) {
                $('#users_register_'+key).callProp('setCustomValidity', [value]);
                
                $('#users_register_'+key).on('change keyup paste', function() {
                    $(this).callProp('setCustomValidity', ['']);   
                });
            });
        }
    }

    function validateEntries() {
        $.ajax({
            data: $('#users_register').serializeArray(),
            type: 'POST',
            url: Routing.generate('zikulausersmodule_ajax_getregistrationerrors')
        }).always(function(response, status, xhr) {                
            if ((response) && (response.data) && (response.data.errorFieldsCount > 0)) {
                displayErrors(response.data);
            }
        });
    }

    $(document).ready(function() {
        /**
         * Force "User Name" and "Email Address" to lowercase.
         */
        $('#users_register .to-lower-case').blur(function() {
            $(this).val($(this).val().toLowerCase());
        });

        $('#users_register input[data-match]').each(function() {
            var $this = $(this);
            var match = $this.data('match');

            /**
             * Check if fields match.
             */
            var e1 = $this[0];
            var e2 = document.getElementById(match.substr(1));
            var checkMatch = function() {
                if (e1.value != e2.value) {
                    $(e2).callProp('setCustomValidity', [$this.data('match-error-message')]);
                } else {
                    $(e2).callProp('setCustomValidity', ['']);
                }
            };

            e1.addEventListener('change', checkMatch, false);
            e2.addEventListener('keyup', checkMatch, false);
            e2.addEventListener('paste', checkMatch, false);
        });
        
        $('#users_register_uname, #users_register_email').on('blur', validateEntries);
    });
})(jQuery);
