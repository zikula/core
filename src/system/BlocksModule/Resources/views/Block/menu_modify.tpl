<h4>{gt text='CSS styling'}</h4>
<div class="form-group">
    <label class="col-sm-3 control-label" for="blocks_menu_stylesheet">{gt text='Style sheet' domain='zikula'}</label>
    <div class="col-sm-9">
        <input id="blocks_menu_stylesheet" class="form-control" type="text" name="stylesheet" size="20" value="{$stylesheet|safetext}" />
    </div>
</div>
<h4>{gt text='Visibility within block'}</h4>
<div class="form-group">
    <label class="col-sm-3 control-label" for="blocks_menu_modules">{gt text='Modules manager' domain='zikula'}</label>
    <div class="col-sm-9">
        <input id="blocks_menu_modules" type="checkbox" value="1" name="displaymodules"{if $displaymodules} checked="checked"{/if} />
    </div>
</div>
<h3>{gt text='Content'}</h3>
<p class="alert alert-info">{gt text='Module Urls should be notated like'}<code>&#123;MyModule&#125;</code> or <code>&#123;MyModule:type:func&#125;</code>.</p>
<table class="table table-bordered table-striped">
    <colgroup>
        <col id="cTitle" />
        <col id="cUrl" />
        <col id="cDescription" />
        <col id="cDelete" />
        <col id="cAdd" />
    </colgroup>
    <thead>
        <tr>
            <th id="hTitle" scope="col">{gt text='Title' domain='zikula'}</th>
            <th id="hUrl" scope="col">{gt text='URL' domain='zikula'}</th>
            <th id="hDescription" scope="col">{gt text='Description' domain='zikula'}&nbsp;<small>({gt text='optional' domain='zikula'})</small></th>
            <th id="hDelete" scope="col">{gt text='Delete' domain='zikula'}</th>
            <th id="hAdd" scope="col">{gt text='Insert blank after' domain='zikula'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach name='menuitems' item='menuitem' from=$menuitems}
        <tr>
            <td headers="hTitle" valign="top">
                <input class="form-control" type="text" name="linkname[{$smarty.foreach.menuitems.iteration}]" size="30" maxlength="255" value="{$menuitem.1|safetext}" />
            </td>
            <td headers="hUrl" valign="top">
                <input class="form-control" type="text" name="linkurl[{$smarty.foreach.menuitems.iteration}]" size="30" maxlength="255" value="{$menuitem.0|safetext}" />
            </td>
            <td headers="hDescription" valign="top">
                <input class="form-control" type="text" name="linkdesc[{$smarty.foreach.menuitems.iteration}]" size="30" maxlength="255" value="{$menuitem.2|safetext}" />
            </td>
            <td headers="hDelete" valign="top">
                <input type="checkbox" name="linkdelete[{$smarty.foreach.menuitems.iteration}]" value="1" />
            </td>
            <td headers="hAdd" valign="top">
                <input type="checkbox" name="linkinsert[{$smarty.foreach.menuitems.iteration}]" value="1" />
            </td>
        </tr>
        {/foreach}
        <tr>
            <td headers="hTitle"><input class="form-control" type="text" name="new_linkname" size="30" maxlength="255" /></td>
            <td headers="hUrl"><input class="form-control" type="text" name="new_linkurl" size="30" maxlength="255" /></td>
            <td headers="hDescription"><input class="form-control" type="text" name="new_linkdesc" size="30" maxlength="255" /></td>
            <td headers="hDelete">{gt text='New row'}</td>
            <td headers="hAdd"><input type="checkbox" name="new_linkinsert" value="1" /></td>
        </tr>
    </tbody>
</table>
