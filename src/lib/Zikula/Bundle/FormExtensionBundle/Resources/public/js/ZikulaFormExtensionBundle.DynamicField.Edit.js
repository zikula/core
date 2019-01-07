// Copyright Zikula Foundation, licensed MIT.
(function($) {
    $(document).ready(function() {
        var formType = $('#zikulaformextensionbundle_property_formType');
        formType.change(function() {
            $('#zikulaformextensionbundle_property_formOptions').html('<i class="fa fa-cog fa-spin fa-3x fa-fw" aria-hidden="true"></i>');
            var $form = $(this).closest('form');
            var data = {};
            data[formType.attr('name')] = formType.val();
            $.ajax({
                url : $form.attr('action'),
                type: $form.attr('method'),
                data : data,
                success: function(html) {
                    $('#zikulaformextensionbundle_property_formOptions').replaceWith(
                        $(html).find('#zikulaformextensionbundle_property_formOptions')
                    );
                }
            });
        });
    });
})(jQuery);
