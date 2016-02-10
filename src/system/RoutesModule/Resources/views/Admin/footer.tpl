{* purpose of this template: footer for admin area *}
{if !isset($smarty.get.theme) || $smarty.get.theme ne 'Printer'}
    {adminfooter}
{elseif isset($smarty.get.func) && $smarty.get.func eq 'edit'}
    {pageaddvar name='stylesheet' value='style/core.css'}
    {pageaddvar name='stylesheet' value='@ZikulaRoutesModule/Resources/public/css/style.css'}
    {pageaddvar name='stylesheet' value='style/form/style.css'}
    {pageaddvar name='stylesheet' value='@ZikulaAndreas08Theme/Resources/public/css/fluid960gs/reset.css'}
    {capture assign='pageStyles'}
    <style type="text/css">
        body {
            font-size: 70%;
        }
    </style>
    {/capture}
    {pageaddvar name='header' value=$pageStyles}
{/if}
