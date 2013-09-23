{pageaddvar name='javascript' value='system/Zikula/Module/AdminModule/Resources/public/js/admin_admin_admintabs.js'}
{pageaddvar name='javascript' value='jquery-ui'}


<div class="admintabs-container" id="admintabs-container">

    <ul id="admintabs" class="nav nav-mouseover nav-tabs nav-tabs-admin">
        {foreach from=$menuoptions name='menuoption' item='menuoption'}
        <li class="dropdown droppable nowrap{if $currentcat eq $menuoption.cid} active{/if}" data-catid="{$menuoption.cid}">
            <a class="dropdown-toggle" href="#" data-toggle="dropdown"><span class="icon icon-move admintabs-lock"></span>
            <span>{$menuoption.title|safetext}</span>
            <span class="icon icon-caret-down"></span>
            </a>
                <ul class="dropdown-menu">
                    <li class="admintabs-lock admintabs-makedefault{if $currentcat eq $menuoption.cid} hide{/if}">
                        <a href="#"><span class="icon icon-asterisk icon-fixed-width" ></span> {gt text='Make default category'}</a>
                    </li>
                    <li class="admintabs-lock admintabs-edit" data-toggle="modal" data-target="#admintabs-rename-category-modal">
                        <a href="#"><span class="icon icon-pencil icon-fixed-width" ></span> {gt text='Edit category'}</a>
                    </li>
                    <li class="admintabs-lock admintabs-delete">
                        <a href="#"><span class="icon icon-trash icon-red icon-fixed-width" ></span> {gt text='Delete category'}</a>
                    </li>
                    <li class="divider admintabs-lock"></li>
                    <li>
                        <a href="{$menuoption.url|safetext}"><span class="icon icon-th-large icon-bluelight icon-fixed-width" ></span> {gt text="Overview"}</a>
                    </li>
                    {if count($menuoption.items) > 0}
                    <li class="divider"></li>
                    {foreach from=$menuoption.items item="item"}
                    {assign var="modname" value=$item.modname}
                    <li>
                        <a href="{$item.menutexturl}"><img src="{$item.icon}" width=15 heigh=15 style="margin-right:6px">{$item.menutext}</a>
                    </li>
                    {/foreach}
                    {/if}
                </ul>
        </li>
        {/foreach}
        <li id="admintabs-locker">
            <a href="#" title="{gt text='Lock/Unlock editing'}" data-placement="top" class="tooltips tooltips-bottom"><span class="icon icon-lock icon-fixed-width"></span></a>
        </li>
        <li class="admintabs-add admintabs-lock">
            <a href="{modurl modname=ZikulaAdminModule type=admin func=new}" title="{gt text='New module category'}" class="tooltips tooltips-bottom" data-placement="top"><span class="icon icon-plus"></span></a>
            <div id="admintabs-add-popover" class="hide">
                <div class="input-group">
                    <input type="text" class="form-control" name="name" id="admintabs-add-name" />
                    <span class="input-group-addon icon icon-remove icon-red pointer"></span>
                    <span class="input-group-addon icon icon-ok icon-green pointer"></span>
                </div>
            </div>

        </li>
    </ul>
</div>

<!-- Modal -->
<div class="modal fade" id="admintabs-rename-category-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">{gt text="Rename category"}</h4>
      </div>
      <div class="modal-body">
          <input />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" data-dismiss="modal">Save changes</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->