// Copyright Zikula, licensed MIT.

(function($) {
    $(document).ready(function() {
        var $moduleName = $('#zikulacategoriesbundle_category_registry_modname');
        $moduleName.change(function () {
            $('#entity-loading').removeClass('d-none');
            var $form = $(this).closest('form');
            var data = {};
            data[$moduleName.attr('name')] = $moduleName.val();
            $.ajax({
                url: $form.attr('action'),
                type: $form.attr('method'),
                data: data,
                success: function (html) {
                    $('#zikulacategoriesbundle_category_registry_entityname').replaceWith(
                        $(html).find('#zikulacategoriesbundle_category_registry_entityname')
                    );
                    $('#entity-loading').addClass('d-none');
                }
            });
        });
    });
})(jQuery);
