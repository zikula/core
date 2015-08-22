{* purpose of this template: show output of reload routes action in admin area *}
{include file='Admin/header.tpl'}
<div class="zikularoutesmodule-reloadroutes zikularoutesmodule-reloadroutes">
    {gt text='Reload routes' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
    <h3>
        <span class="fa fa-square"></span>
        {$templateTitle}
    </h3>

    <p>Please override this template by moving it from <em>/system/RoutesModule/Resources/views/Admin/reloadRoutes.tpl</em> to either your <em>/themes/YourTheme/templates/modules/ZikulaRoutesModule/Admin/reloadRoutes.tpl</em> or <em>/config/templates/ZikulaRoutesModule/Admin/reloadRoutes.tpl</em>.</p>
</div>
{include file='Admin/footer.tpl'}
