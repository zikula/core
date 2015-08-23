{pageaddvar name='javascript' value='system/AdminModule/Resources/public/js/admin_admin_admintabs.js'}
{pageaddvar name='javascript' value='web/bootstrap-jqueryui/bootstrap-jqueryui.min.js'}

<div class="admintabs-container" id="admintabs-container">

    <ul id="admintabs" class="nav nav-mouseover nav-tabs nav-tabs-admin">
        {foreach name='menuoption' item='menuoption' from=$menuoptions}
        <li class="dropdown droppable nowrap{if $currentcat eq $menuoption.cid} active{/if}" data-catid="{$menuoption.cid}">
            <a class="dropdown-toggle" href="#" data-toggle="dropdown"><span class="fa fa-arrows admintabs-lock"></span>
            <span>{$menuoption.title|safetext}</span>
            <span class="fa fa-caret-down"></span>
            </a>
                <ul class="dropdown-menu">
                    <li class="admintabs-lock admintabs-makedefault{if $currentcat eq $menuoption.cid} hide{/if}">
                        <a href="#"><span class="fa fa-asterisk fa-fw" ></span> {gt text='Make default category'}</a>
                    </li>
                    <li class="admintabs-lock admintabs-edit" data-toggle="modal" data-target="#admintabs-rename-category-modal">
                        <a href="#"><span class="fa fa-pencil fa-fw" ></span> {gt text='Edit category'}</a>
                    </li>
                    <li class="admintabs-lock admintabs-delete">
                        <a href="#"><span class="fa fa-trash-o fa-red fa-fw" ></span> {gt text='Delete category'}</a>
                    </li>
                    <li class="divider admintabs-lock"></li>
                    <li>
                        <a href="{$menuoption.url|safetext}"><span class="fa fa-th-large fa-bluelight fa-fw" ></span> {gt text="Overview"}</a>
                    </li>
                    {if count($menuoption.items) > 0}
                    <li class="divider"></li>
                    {foreach from=$menuoption.items item="item"}
                    {assign var="modname" value=$item.modname}
                    <li>
                        <a href="{$item.menutexturl|safetext}"><img src="{$item.icon}" width=15 height=15 style="margin-right:6px" alt="{$item.menutext|safetext}">{$item.menutext|safetext}</a>
                    </li>
                    {/foreach}
                    {/if}
                </ul>
        </li>
        {/foreach}
        <li id="admintabs-locker">
            <a href="#" title="{gt text='Lock/Unlock editing'}" data-placement="top" class="tooltips tooltips-bottom"><span class="fa fa-lock fa-fw"></span></a>
        </li>
        <li class="admintabs-add admintabs-lock">
            <a href="{route name='zikulaadminmodule_admin_newcat'}" title="{gt text='New module category'}" class="tooltips tooltips-bottom" data-placement="top"><span class="fa fa-plus"></span></a>
            <div id="admintabs-add-popover" class="hide">
                <div class="input-group">
                    <input type="text" class="form-control" name="name" id="admintabs-add-name" />
                    <span class="input-group-addon fa fa-times fa-red pointer"></span>
                    <span class="input-group-addon fa fa-check fa-green pointer"></span>
                </div>
            </div>
        </li>
    </ul>
</div>

<div class="modal fade" id="admintabs-rename-category-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">{gt text='Rename category'}</h4>
            </div>
            <div class="modal-body">
                <input />
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">{gt text='Save changes'}</button>
            </div>
        </div>
    </div>
</div>
