/**
 * Copyright Zikula Foundation 2014 - license GNU/LGPLv3 (or at your option, any later version).
 */
(function($) {
    function displayErrors(data) {
        var errorMessages = $('#users_register_errormsgs');
        
        /**
         * Hide error containers, in case this is a subsequent request.
         */
        errorMessages.addClass('hide');
        $('#users_register').find('.validation-error').addClass('hide');

        /**
         * Display error messages.
         */
        if (data.errorMessages) {
            errorMessages.html(data.errorMessages.join('<br />')).removeClass('hide').fadeIn('fast');
        }

        if (data.errorFields) {
            $.each(data.errorFields, function(key, value) {
                $('#users_register_'+key).callProp('setCustomValidity', [value]);
                
                $('#users_register_'+key).on('change keyup paste', function() {
                    $('#users_register_errormsgs').fadeOut('fast', function() {
                        $(this).addClass('hide');
                    });

                    $(this).callProp('setCustomValidity', ['']);   
                });
            });
            
            /**
             * Simulate the form submission, so that the first invalid message appears.
             */
            $('#users_register input[type="submit"]').trigger('click', function(event) {
                event.preventDefault();
            });
            
            /**
             * Focus on the first invalid field.
             */
            $('#users_register').find(':invalid:first').focus();
        }
    }

    function validateEntries(event) {
        event.preventDefault();

        $('#users_register .help-block').fadeOut('fast', function() {
            $.ajax({
                data: $('#users_register').serializeArray(),
                type: 'POST',
                url: Routing.generate('zikulausersmodule_ajax_getregistrationerrors')
            }).always(function(response, status, xhr) {                
                if ((response) && (response.data) && (response.data.errorFieldsCount > 0)) {
                    displayErrors(response.data);
                } else {
                    $('#users_register').off('submit').submit();
                }
            });
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

        $('#users_register').on('submit', validateEntries);
    });
})(jQuery);
