// Copyright Zikula, licensed MIT.

( function($) {
    $(document).ready(function() {
        $('#zikulaSecurityCenterModuleIdsLogFilterForm select').change(function() {
            $('#zikulaSecurityCenterModuleIdsLogFilterForm').submit();
        });
    });
})(jQuery);
