<h4>{gt text='CSS styling'}</h4>
<div class="form-group">
    <label class="col-lg-3 control-label" for="blocks_menu_stylesheet">{gt text='Style sheet' domain='zikula'}</label>
    <div class="col-lg-9">
        <input id="blocks_menu_stylesheet" class="form-control" type="text" name="stylesheet" size="20" value="{$stylesheet|safetext}" />
    </div>
</div>
<h4>{gt text='Visibility within block'}</h4>
<div class="form-group">
    <label class="col-lg-3 control-label" for="blocks_menu_modules">{gt text='Modules manager' domain='zikula'}</label>
    <div class="col-lg-9">
        <input id="blocks_menu_modules" type="checkbox" value="1" name="displaymodules"{if $displaymodules} checked="checked"{/if} />
    </div>
</div>
<h3>{gt text='Content'}</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text='Title' domain='zikula'}</th>
            <th>{gt text='URL' domain='zikula'}</th>
            <th>{gt text='Description' domain='zikula'}&nbsp;<small>({gt text='optional' domain='zikula'})</small></th>
            <th>{gt text='Delete' domain='zikula'}</th>
            <th>{gt text='Insert blank after' domain='zikula'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach name='menuitems' item='menuitem' from=$menuitems}
        <tr>
            <td valign="top"><input class="form-control" type="text" name="linkname[{$smarty.foreach.menuitems.iteration}]" size="30" maxlength="255" value="{$menuitem.1|safetext}" /></td>
            <td valign="top"><input class="form-control" type="text" name="linkurl[{$smarty.foreach.menuitems.iteration}]" size="30" maxlength="255" value="{$menuitem.0|safetext}" /></td>
            <td valign="top"><input class="form-control" type="text" name="linkdesc[{$smarty.foreach.menuitems.iteration}]" size="30" maxlength="255" value="{$menuitem.2|safetext}" /></td>
            <td valign="top"><input type="checkbox" name="linkdelete[{$smarty.foreach.menuitems.iteration}]" value="1" /></td>
            <td valign="top"><input type="checkbox" name="linkinsert[{$smarty.foreach.menuitems.iteration}]" value="1" /></td>
        </tr>
        {/foreach}
        <tr>
            <td><input class="form-control" type="text" name="new_linkname" size="30" maxlength="255" /></td>
            <td><input class="form-control" type="text" name="new_linkurl" size="30" maxlength="255" /></td>
            <td><input class="form-control" type="text" name="new_linkdesc" size="30" maxlength="255" /></td>
            <td>{gt text='New row'}</td>
            <td><input type="checkbox" name="new_linkinsert" value="1" /></td>
        </tr>
    </tbody>
</table>
