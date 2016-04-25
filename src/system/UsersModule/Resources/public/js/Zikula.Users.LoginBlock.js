// Copyright Zikula Foundation, licensed MIT.

(function($) {
    $(function() {
        $('[id^=zikulausersmodule-authentication-select-method-loginblock-form]').each(function() {
            $(this).submit(function(event) {
                onSubmitSelectAuthenticationMethod(event);
            });
        });

        function onSubmitSelectAuthenticationMethod(event) {
            showAjaxInProgress();

            var temp = $(event.target).serializeArray();
            var parameterObj = {};
            $.each(temp, function(index, value) {
                parameterObj[value.name] = value.value;
            });
            parameterObj.form_type = 'loginblock';

            $.ajax({
                url: Routing.generate('zikulausersmodule_ajax_getloginformfields'),
                data: parameterObj
            }).success(function(result) {
                var data = result.data;

                $('#users_loginblock_fields').html(data.content);
                $('#users_loginblock_selected_authentication_module').attr('value', data.modname);
                $('#users_loginblock_selected_authentication_method').attr('value', data.method);

                if (data.method !== false) {
                    // Hide the chosen authentication method in the list
                    $('#zikulausersmodule-authentication-select-method-loginblock-form_' + data.modname.toLowerCase() + '_' + data.method.toLowerCase()).addClass('hide');
                }
                showAjaxComplete(false);
            }).error(function(result) {
                showAjaxComplete(true);
                Zikula.showajaxerror(result.status + ': ' + result.statusText);
            });

            // Prevent form from submitting itself. We just did it here.
            event.preventDefault();
        }

        function showAjaxInProgress() {
            // Hide login form
            $('#users_loginblock_login_form').addClass('hide');

            // Hide error notice
            $('#users_loginblock_no_loginformfields').addClass('hide');

            // Unhide heading used when no authentication module is chosen
            $('#users_loginblock_h5_no_authentication_method').removeClass('hide');

            // Hide heading used when authentication module is chosen
            $('#users_loginblock_h5_authentication_method').addClass('hide');

            // Unhide all authentication method selectors
            $('form.authentication_select_method').removeClass('hide');

            // Unhide the waiting indicator
            $('#users_loginblock_waiting').removeClass('hide');
        }

        function showAjaxComplete(isError)
        {
            // Unhide waiting indicator
            $('#users_loginblock_waiting').addClass('hide');

            if (isError) {
                // Hide login form
                $('#users_loginblock_login_form').addClass('hide');

                // Unhide error notification
                $('#users_loginblock_no_loginformfields').removeClass('hide');

                // Unhide heading used when there is no authentication method selected
                $('#users_loginblock_h5_no_authentication_method').removeClass('hide');

                // Hide heading used when authentication method selected
                $('#users_loginblock_h5_authentication_method').addClass('hide');
            } else {
                // No error

                // Unhide login form
                $('#users_loginblock_login_form').removeClass('hide');

                // Hide error notification
                $('#users_loginblock_no_loginformfields').addClass('hide');

                // Hide heading used when there is no authentication method selected
                $('#users_loginblock_h5_no_authentication_method').addClass('hide');

                // Unhide heading used when authentication method selected
                $('#users_loginblock_h5_authentication_method').removeClass('hide');
            }
        }
    });
})(jQuery);
