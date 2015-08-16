{* purpose of this template: show output of renew action in admin area *}
{include file='Admin/header.tpl'}
<div class="zikularoutesmodule-renew zikularoutesmodule-renew">
    {gt text='Renew' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
    <h3>
        <span class="fa fa-square"></span>
        {$templateTitle}
    </h3>

    <p>Please override this template by moving it from <em>/system/ZikulaRoutesModule/Resources/views/Admin/renew.tpl</em> to either your <em>/themes/YourTheme/templates/modules/ZikulaRoutesModule/Admin/renew.tpl</em> or <em>/config/templates/ZikulaRoutesModule/Admin/renew.tpl</em>.</p>
</div>
{include file='Admin/footer.tpl'}
