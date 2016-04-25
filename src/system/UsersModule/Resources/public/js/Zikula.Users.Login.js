// Copyright Zikula Foundation, licensed MIT.

var ZikulaUsersLogin = {};

( function($) {

    ZikulaUsersLogin.init = function()
    {
        if ($('#users_login_select_authentication_form_users_uname').length > 0) {
            $('#users_login_select_authentication_form_users_uname').submit( function(event) {
                ZikulaUsersLogin.onSubmitSelectAuthenticationMethod(event, 'users_login_select_authentication_form_users_uname');
            });
        }
        if ($('#users_login_select_authentication_form_users_email').length > 0) {
            $('#users_login_select_authentication_form_users_email').submit( function(event) {
                ZikulaUsersLogin.onSubmitSelectAuthenticationMethod(event, 'users_login_select_authentication_form_users_email');
            });
        }
        if ($('#users_login_select_authentication_form_users_unameoremail').length > 0) {
            $('#users_login_select_authentication_form_users_unameoremail').submit( function(event) {
                ZikulaUsersLogin.onSubmitSelectAuthenticationMethod(event, 'users_login_select_authentication_form_users_unameoremail');
            });
        }

        if ($('#users_login_login_id').length > 0) {
            $('#users_login_login_id').focus();
        }
    };

    ZikulaUsersLogin.showAjaxInProgress = function()
    {
        // Hide login form
        var elementChangingClass = $('#users_login_login_form');
        if (!elementChangingClass.hasClass('hide')) {
            elementChangingClass.addClass('hide');
        }

        // Hide error notice
        elementChangingClass = $('#users_login_no_loginformfields');
        if (!elementChangingClass.hasClass('hide')) {
            elementChangingClass.addClass('hide');
        }

        // Unhide heading used when no authentication module is chosen
        elementChangingClass = $('#users_login_h5_no_authentication_method');
        if (elementChangingClass.hasClass('hide')) {
            elementChangingClass.removeClass('hide');
        }

        // Hide heading used when authentication module is chosen
        elementChangingClass = $('#users_login_h5_authentication_method');
        if (!elementChangingClass.hasClass('hide')) {
            elementChangingClass.addClass('hide');
        }

        // Remove selected indicator from selectors
        $('.authentication_select_method_selected').removeClass('authentication_select_method_selected');

        // Unhide the waiting indicator
        $('#users_login_waiting').removeClass('hide');
    };

    ZikulaUsersLogin.showAjaxComplete = function(isError)
    {
        // Unhide waiting indicator
        $('#users_login_waiting').addClass('hide');

        var elementChangingClass;
        if (isError) {
            // Hide login form
            elementChangingClass = $('#users_login_login_form');
            if (!elementChangingClass.hasClass('hide')) {
                elementChangingClass.addClass('hide');
            }

            // Unhide error notification
            elementChangingClass = $('#users_login_no_loginformfields');
            if (elementChangingClass.hasClass('hide')) {
                elementChangingClass.removeClass('hide');
            }

            // Unhide heading used when there is no authentication method selected
            elementChangingClass = $('#users_login_h5_no_authentication_method');
            if (elementChangingClass.hasClass('hide')) {
                elementChangingClass.removeClass('hide');
            }

            // Hide heading used when authentication method selected
            elementChangingClass = $('#users_login_h5_authentication_method');
            if (!elementChangingClass.hasClass('hide')) {
                elementChangingClass.addClass('hide');
            }
        } else {
            // No error

            // Unhide login form
            elementChangingClass = $('#users_login_login_form');
            if (elementChangingClass.hasClass('hide')) {
                elementChangingClass.removeClass('hide');
            }

            // Hide error notification
            elementChangingClass = $('#users_login_no_loginformfields');
            if (!elementChangingClass.hasClass('hide')) {
                elementChangingClass.addClass('hide');
            }

            // Hide heading used when there is no authentication method selected
            elementChangingClass = $('#users_login_h5_no_authentication_method');
            if (!elementChangingClass.hasClass('hide')) {
                elementChangingClass.addClass('hide');
            }

            // Unhide heading used when authentication method selected
            elementChangingClass = $('#users_login_h5_authentication_method');
            if (elementChangingClass.hasClass('hide')) {
                elementChangingClass.removeClass('hide');
            }
        }
    };

    ZikulaUsersLogin.onSubmitSelectAuthenticationMethod = function(event, formId)
    {
        ZikulaUsersLogin.showAjaxInProgress();

        var temp = $('#' + formId).serializeArray();
        var parameterObj = {};
        $.each(temp, function(index, value) {
            parameterObj[value.name] = value.value;
        });
        parameterObj.form_type = 'loginscreen';

        $.ajax({
            url: Routing.generate('zikulausersmodule_ajax_getloginformfields'),
            data: parameterObj
        }).success(function(result) {
            var data = result.data;

            $('#users_login_fields').html(data.content);
            $('#users_login_selected_authentication_module').attr('value', data.modname);
            $('#users_login_selected_authentication_method').attr('value', data.method);

            if (data.method !== false) {
                // Hide the chosen authentication method in the list
                $('#users_login_select_authentication_' + data.modname.toLowerCase() + '_' + data.method.toLowerCase() + '_submit').addClass('authentication_select_method_selected');
            }
            ZikulaUsersLogin.showAjaxComplete((data.content == false) || (data.content == ''));
        }).error(function(result) {
            ZikulaUsersLogin.showAjaxComplete(true);
            Zikula.showajaxerror(result.status + ': ' + result.statusText);
        });

        // Prevent form from submitting itself. We just did it here.
        event.preventDefault();
    };

    $(document).ready(function() {
        ZikulaUsersLogin.init();
    });
})(jQuery);
