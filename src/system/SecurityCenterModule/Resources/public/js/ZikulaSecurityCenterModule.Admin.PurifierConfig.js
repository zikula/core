// Copyright Zikula Foundation, licensed MIT.

function toggleWriteability(node, checked) {
    document.getElementById(node).disabled = checked;
}

(function($) {
    $(document).ready(function() {
        $('a.external').attr('target', '_blank');
    });
})(jQuery);
