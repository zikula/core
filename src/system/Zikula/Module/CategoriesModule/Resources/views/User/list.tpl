{assign var='dr' value=$rootCat.id}
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th class="text-left">{gt text='Internal ID'}</th>
            <th class="text-left">{gt text='Name'}</th>
            <th class="text-left">{gt text='Value'}</th>
            <th class="text-center">{gt text='Active'}</th>
            <th class="text-right">{gt text='Sort value'}</th>
            {if (isset($rootCat.__ATTRIBUTES__) && $rootCat.__ATTRIBUTES__)}
            <th class="text-right">{gt text='Attributes'}</th>
            {/if}
            <th class="text-center">{gt text='Down'}</th>
            <th class="text-center">{gt text='Up'}</th>
            <th class="text-center">{gt text='Edit'}</th>
            <th class="text-center">{gt text='Delete'}</th>
        </tr>
    </thead>
    {if ($allCats)}
    {array_size assign='max' array=$allCats}
    {foreach name='loop' item='cat' from=$allCats}
    {counter print=false assign='cnt'}
    {array_field assign='displayname' array=$cat.display_name field=$userlanguage}
    <tbody>
        <tr>
            <td class="text-left">{$cat.id|safetext}</td>
            <td class="text-left">{$displayname|default:$cat.name}</td>
            <td class="text-left">{$cat.value|safetext}</td>
            <td class="text-center">{$cat.status|safetext}</td>
            <td class="text-right">{$cat.sort_value|safetext}</td>
            {if (isset($rootCat.__ATTRIBUTES__) && $rootCat.__ATTRIBUTES__)}
            <td class="text-right">{$cat__ATTRIBUTES__|@count}</td>
            {/if}
            <td class="text-center">
                {if $cnt ne $max}
                <a href="{route name='zikulacategoriesmodule_userform_movefield' dr=$dr cid=$cat.id direction='down' append='#top'}">{img modname='core' src='1downarrow.png' set='icons/extrasmall' __alt='Down' __title='Down'}</a>
                {else}
                &nbsp;
                {/if}
            </td>
            <td class="text-center">
                {if $cnt ne 1}
                <a href="{route name='zikulacategoriesmodule_userform_movefield' dr=$dr cid=$cat.id direction='up' append='#top'}">{img modname='core' src='1uparrow.png' set='icons/extrasmall' __alt='Up' __title='Up'}</a>
                {else}
                &nbsp;
                {/if}
            </td>
            <td class="text-center">
                {if $cat.is_locked}
                {img modname='core' src='locked.png' set=icons/extrasmall __alt="Category is locked" __title="Category is locked"}
                {else}
                <a href="{route name='zikulacategoriesmodule_user_edit' dr=$dr cid=$cat.id}">{img modname='core' src='xedit.png' set='icons/extrasmall' __alt='Edit' __title='Edit'}</a>
                {/if}
            </td>
            <td class="text-center">
                {gt text="Do you really want to delete the category '%s'?" tag1=$cat.name|safetext  assign='delPrompt'}
                <a href="{route name='zikulacategoriesmodule_userform_delete' dr=$dr cid=$cat.id}" onclick="return confirm('{$delPrompt}');" >
                    {img modname='core' src='14_layer_deletelayer.png' set='icons/extrasmall' __alt='Delete' __title='Delete'}
                </a>
            </td>
        </tr>
    </tbody>
    {/foreach}
    {/if}
</table>
