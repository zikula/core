// Copyright Zikula, licensed MIT.

(function($) {
    function showAvatar() {
        $('.avatar-preview').remove();
        $('.avatar-selector').each(function (index) {
            var avatarUrl, avatarPreview;

            avatarUrl = '';
            if ('blank.jpg' !== $(this).val()) {
                if ('' === $(this).val() || 'gravatar.jpg' === $(this).val()) {
                    avatarUrl = $('#gravatarUrl').val();
                } else {
                    avatarUrl = Zikula.Config.baseURL + Zikula.Config.baseURI + '/' + $('#avatarPath').val() + '/' + $(this).val();
                }
            }

            avatarPreview = '' !== avatarUrl ? '<img src="' + avatarUrl + '" alt="' + Translator.trans('Avatar') + '" class="img-fluid img-thumbnail" />' : '';

            $(this).parent().append('<p class="avatar-preview" style="margin-top: 20px">' + avatarPreview + '</p>');
        });
    }

    $(document).ready(function() {
        if ($('.avatar-selector').length > 0) {
            $('.avatar-selector').change(showAvatar);
            showAvatar();
        }
    });
})(jQuery);
