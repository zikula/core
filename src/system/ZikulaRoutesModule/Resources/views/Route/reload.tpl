{include file="Admin/header.tpl"}
<div class="zikularoutesmodule-route zikularoutesmodule-delete">
    {gt text='Reload all routes' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
    <h3>
        <span class="fa fa-trash-o"></span>
        {$templateTitle}
    </h3>

    <p class="alert alert-warningmsg">{gt text='Do you really want to reload all routes? Note: Custom routes won\'t be removed.'}</p>

    <form class="form-horizontal" action="{modurl modname='ZikulaRoutesModule' type='route' func='reload' lct='admin'}" method="post" role="form">
        <div>
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <input type="hidden" id="confirmation" name="confirmation" value="1" />
            <fieldset>
                <legend>{gt text='Confirmation prompt'}</legend>
                <div class="form-group form-buttons">
                <div class="col-lg-offset-3 col-lg-9">
                    {gt text='Reload all routes' assign='deleteTitle'}
                    {button src='reload.png' set='icons/small' text=$deleteTitle title=$deleteTitle class='btn btn-danger'}
                    <a href="{modurl modname='ZikulaRoutesModule' type='route' func='view' lct='admin'}" class="btn btn-default" role="button"><span class="fa fa-times"></span> {gt text='Cancel'}</a>
                </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>
{include file="Admin/footer.tpl"}
