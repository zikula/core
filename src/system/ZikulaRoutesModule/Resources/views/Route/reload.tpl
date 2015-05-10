{include file="Admin/header.tpl"}
<div class="zikularoutesmodule-route zikularoutesmodule-delete">
    {gt text='Reload routes' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
    <h3>
        <span class="fa fa-trash-o"></span>
        {$templateTitle}
    </h3>

    {insert name="getstatusmsg"}
    <p class="alert alert-warning">{gt text='Do you really want to reload routes? Note: Custom routes won\'t be removed.'}</p>
    <form class="form-horizontal" action="{modurl modname='ZikulaRoutesModule' type='route' func='reload' lct='admin' stage=1}" method="post" role="form">
        <div>
            <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
            <fieldset>
                <legend>{gt text='Confirmation prompt'}</legend>
                <div class="form-group">
                    <div class="col-sm-3">
                        <label for="reload-module">{gt text='Choose module'}</label>
                    </div>
                    <div class="col-sm-9">
                        <select class="form-control" id="reload-module" name="reload-module" size="1">
                            {foreach from=$options item='option'}
                                <option value="{$option.value|safetext}">{$option.text|safetext}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="form-group form-buttons">
                    <div class="col-sm-offset-3 col-sm-9">
                        <button type="submit" class="btn btn-danger"><i class="fa fa-refresh"></i> {gt text='Reload routes'}</button>
                        <a href="{modurl modname='ZikulaRoutesModule' type='route' func='view' lct='admin'}" class="btn btn-default" role="button"><span class="fa fa-times"></span> {gt text='Cancel'}</a>
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>
{include file="Admin/footer.tpl"}
