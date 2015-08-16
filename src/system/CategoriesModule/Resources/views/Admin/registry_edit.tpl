{adminheader}
<h3>
    <span class="fa fa-archive"></span>
    {gt text='Category registry'}
</h3>

{gt text='Choose category' assign='chooseCategory'}
{gt text='Choose module' assign='chooseModule'}
{gt text='Choose entity' assign='chooseEntity'}
<form class="form-horizontal" role="form" action="{route name='zikulacategoriesmodule_adminform_editregistry'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{gt text='Module'}</th>
                    <th>{gt text='Entity/Table'}</th>
                    <th>{gt text='Property name'}</th>
                    <th>{gt text='Category'}</th>
                    <th>{gt text='Actions'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach item='obj' from=$objectArray}
                <tr>
                {if $obj.id eq $id}
                    <input id="category_registry_id" name="category_registry[id]" value="{$obj.id}" type="hidden" />
                    <td>
                        <select name="category_registry[modname]" id="category_registry__id__" onchange="this.form.submit();">
                            {foreach from=$moduleOptions key='value' item='text' }<option value="{$value}"{if $value eq $obj.modname} selected="selected"{/if}>{$text}</option>{/foreach}
                        </select>
                    </td>
                    <td>
                        {if $obj.modname}{selector_module_tables modname=$obj.modname name='category_registry[entityname]' selectedValue=$obj.entityname defaultValue='' defaultText=$chooseEntity}
                        {else}----------
                        {/if}
                    </td>
                    <td>
                        <input id="category_registry_property" name="category_registry[property]" value="{$obj.property}" type="text" class="form-control" size="20" maxlength="32" />
                    </td>
                    <td>
                        {selector_category category=$root_id name='category_registry[category_id]' includeLeaf=0 selectedValue=$obj.category_id editLink=0}
                    </td>
                    <td>&nbsp;</td>
                {else}
                    {modgetinfo assign='dModname' info='displayname' modname=$obj.modname default=$obj.modname}
                    <td>{$dModname}</td>
                    <td>{if isset($obj.entityname)}{$obj.entityname}{/if}</td>
                    <td>{$obj.property}</td>
                    <td>{category_path id=$obj.category_id html=true}</td>
                    <td class="actions">
                        <a class="fa fa-pencil tooltips" href="{route name='zikulacategoriesmodule_admin_editregistry' id=$obj.id}" title="{gt text='Edit'}" ></a>
                        <a class="fa fa-trash-o tooltips" href="{route name='zikulacategoriesmodule_admin_deleteregistry' id=$obj.id}" title="{gt text='Delete'}"></a>
                    </td>
                {/if}
                </tr>
                {/foreach}

                {if $id eq 0}
                <tr>
                    <td>
                        <span class="required"></span>
                        <select name="category_registry[modname]" id="category_registry__id__" onchange="this.form.submit();">
                            <option value="0"{if empty($newobj.modname)} selected="selected"{/if}>{$chooseModule}</option>
                            {foreach from=$moduleOptions key='value' item='text' }<option value="{$value}"{if $value eq $newobj.modname|default:''} selected="selected"{/if}>{$text}</option>{/foreach}
                        </select>
                    </td>
                    <td>
                        {if !empty($newobj.modname)}
                        <span class="required"></span>{selector_module_tables modname=$newobj.modname name='category_registry[entityname]' displayField='name' selectedValue=$newobj.entityname defaultValue='' defaultText=$chooseEntity}
                        {else}----------
                        {/if}
                    </td>
                    <td>
                        <span class="required"></span><input id="category_registry_property" name="category_registry[property]" value="{$newobj.property|default:'Main'}" type="text" class="form-control" size="20" maxlength="32" />
                    </td>
                    <td>
                        <span class="required"></span>{selector_category category=$root_id name="category_registry[category_id]" includeLeaf=0 selectedValue=newobj.category_id defaultValue=0 defaultText=$chooseCategory editLink=0}
                    </td>
                    <td>&nbsp;</td>
                </tr>
                {/if}
            </tbody>
        </table>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button id="category_submit" name="category_submit" value="1" class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
                <a class="btn btn-danger" href="{route name='zikulacategoriesmodule_admin_editregistry'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
