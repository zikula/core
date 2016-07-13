{* purpose of this template: header for admin area *}
{pageaddvar name='javascript' value='jquery'}
{pageaddvar name='javascript' value='jquery-ui'}
{pageaddvar name='stylesheet' value='web/jquery-ui/themes/base/jquery-ui.min.css'}
{pageaddvar name='javascript' value='web/bootstrap/js/bootstrap.min.js'}
{pageaddvar name='javascript' value='zikula'}{* still required for Gettext *}
{pageaddvar name='stylesheet' value='web/bootstrap-jqueryui/bootstrap-jqueryui.min.css'}
{pageaddvar name='javascript' value='web/bootstrap-jqueryui/bootstrap-jqueryui.min.js'}
{if isset($smarty.get.func) && $smarty.get.func eq 'edit'}
    {pageaddvar name='javascript' value='polyfill' features='forms'}
{/if}
{pageaddvar name='javascript' value='@ZikulaRoutesModule/Resources/public/js/ZikulaRoutesModule.js'}

{* initialise additional gettext domain for translations within javascript *}
{pageaddvar name='jsgettext' value='zikularoutesmodule_js:ZikulaRoutesModule'}

{if !isset($smarty.get.theme) || $smarty.get.theme ne 'Printer'}
    {adminheader}
{/if}
