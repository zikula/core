// Copyright Zikula Foundation, licensed MIT.
(function($) {
    $(document).ready(function() {
        var formTypeField = $("select[id$='_fieldInfo_formType']");
        formTypeField.change(function() {
            var form;
            var data;

            $("[id$='_fieldInfo_formOptions']")
                .html('<i class="fa fa-cog fa-spin fa-3x fa-fw" aria-hidden="true"></i>');
            form = $(this).closest('form');
            data = {};
            data[formTypeField.attr('name')] = formTypeField.val();
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: data,
                success: function(html) {
                    $("[id$='_fieldInfo_formOptions']").replaceWith(
                        $(html).find("[id$='_fieldInfo_formOptions']")
                    );
                }
            });
        });
    });
})(jQuery);
