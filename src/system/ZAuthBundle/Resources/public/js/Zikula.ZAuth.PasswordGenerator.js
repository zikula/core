(function($) {
    $(document).ready(function() {
        var passwordInput = $('#generatedPassword');
        var passwordModal = $('#passwordGeneratorModal');
        function createPassword() {
            // copied from https://stackoverflow.com/a/1497512/2600812 and modified
            var length = 16,
                charset = 'abcdefghjklmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789',
                retVal = '';
            for (var i = 0, n = charset.length; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * n));
            }
            return retVal;
        }

        passwordModal.on('show.bs.modal', function (e) {
            passwordInput.val(createPassword());
        });
        $('#regenerate-button').on('click', function() {
            passwordInput.val(createPassword());
        });
        $('#copy-button').on('click', function() {
            passwordInput.focus();
            passwordInput.select();
            document.execCommand('copy');
            passwordModal.modal('hide')
        });
    });
})(jQuery);
