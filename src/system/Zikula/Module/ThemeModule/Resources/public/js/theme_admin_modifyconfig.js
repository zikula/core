// Copyright Zikula Foundation 2009 - license GNU/LGPLv3 (or at your option, any later version).

(function($) {
    $(function() { 
        if ($('#alt_theme_name').val() == "") {
            // Not set
            $('#alt_theme_domain').parent().hide();
        }

        $("#alt_theme_name").click(function() {
            if ($('#alt_theme_name').val() == "") {
                $('#alt_theme_domain').parent().fadeOut();
            } else {
                $('#alt_theme_domain').parent().fadeIn();
            }
        });
    });
})(jQuery);


document.observe('dom:loaded', theme_modifyconfig_init);

function theme_modifyconfig_init()
{
    $('enablecache').observe('change', theme_enablecache_onchange);
    $('cssjscombine').observe('change', combinecssjs_onchange);
    $('cssjsminify').observe('change', minifycssjs_onchange);
    $('render_cache').observe('change', render_lifetime_onchange);

    if (!$('render_cache').checked) {
        $('render_lifetime_container').hide();
    }
    if (!$('enablecache').checked) {
        $('theme_caching').hide();
    }
    if (!$('cssjscombine').checked) {
        $('theme_cssjscombine').hide();
    }
    if (!$('cssjsminify').checked) {
        $('theme_cssjsminify').hide();
    }
}

function theme_enablecache_onchange()
{
    Zikula.checkboxswitchdisplaystate('enablecache', 'theme_caching', true);
}

function combinecssjs_onchange()
{
    Zikula.checkboxswitchdisplaystate('cssjscombine', 'theme_cssjscombine', true);
}

function minifycssjs_onchange()
{
    Zikula.checkboxswitchdisplaystate('cssjsminify', 'theme_cssjsminify', true);
}

function render_lifetime_onchange()
{
    Zikula.checkboxswitchdisplaystate('render_cache', 'render_lifetime_container', true);
}
