function initFontAwesomeIconPicker() {
    jQuery('.zikula-icon-picker').iconpicker({
        hideOnSelect: true,
        inputSearch: true,
        component: '.input-group-append .input-group-text,.iconpicker-component'
    });
}

jQuery(document).ready(initFontAwesomeIconPicker);
