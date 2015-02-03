{* purpose of this template: header for admin area *}
{pageaddvar name='stylesheet' value='web/bootstrap/css/bootstrap.min.css'}
{pageaddvar name='stylesheet' value='web/bootstrap/css/bootstrap-theme.min.css'}
{pageaddvar name='javascript' value='jquery'}
{pageaddvar name='javascript' value='web/bootstrap/js/bootstrap.min.js'}
{pageaddvar name='javascript' value='zikula'}{* still required for Gettext *}
{pageaddvar name='stylesheet' value='web/bootstrap-jqueryui/bootstrap-jqueryui.min.css'}
{pageaddvar name='javascript' value='web/bootstrap-jqueryui/bootstrap-jqueryui.min.js'}
{pageaddvar name='javascript' value='@ZikulaRoutesModule/Resources/public/js/ZikulaRoutesModule.js'}

{* initialise additional gettext domain for translations within javascript *}
{pageaddvar name='jsgettext' value='module_zikularoutesmodule_js:ZikulaRoutesModule'}

{if !isset($smarty.get.theme) || $smarty.get.theme ne 'Printer'}
    {adminheader}
{/if}
