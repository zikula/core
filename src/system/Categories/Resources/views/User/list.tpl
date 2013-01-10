{assign var="dr" value=`$rootCat.id`}

<table class="z-datatable">
    <thead>
        <tr>
            <th style="text-align:left">{gt text="Internal ID"}</th>
            <th style="text-align:left">{gt text="Name"}</th>
            <th style="text-align:left">{gt text="Value"}</th>
            <th style="text-align:center">{gt text="Active"}</th>
            <th style="text-align:right">{gt text="Sort value"}</th>
            {if (isset($rootCat.__ATTRIBUTES__) && $rootCat.__ATTRIBUTES__)}
            <th style="text-align:right">{gt text="Attributes"}</th>
            {/if}
            <th style="text-align:center">{gt text="Down"}</th>
            <th style="text-align:center">{gt text="Up"}</th>
            <th style="text-align:center">{gt text="Edit"}</th>
            <th style="text-align:center">{gt text="Delete"}</th>
        </tr>
    </thead>
    {if ($allCats)}
    {array_size assign="max" array=$allCats}
    {foreach from=$allCats item=cat name=loop}
    {counter print=false assign="cnt"}
    {array_field assign='displayname' array=$cat.display_name field=$userlanguage}
    <tbody>
        <tr class="{cycle values="z-odd,z-even"}">
            <td style="text-align:left">{$cat.id|safetext}</td>
            <td style="text-align:left">{$displayname|default:$cat.name}</td>
            <td style="text-align:left">{$cat.value|safetext}</td>
            <td style="text-align:center">{$cat.status|safetext}</td>
            <td style="text-align:right">{$cat.sort_value|safetext}</td>
            {if (isset($rootCat.__ATTRIBUTES__) && $rootCat.__ATTRIBUTES__)}
            <td style="text-align:right">{$cat__ATTRIBUTES__|@count}</td>
            {/if}
            <td style="text-align:center">
                {if ($cnt != $max)}
                <a href="{modurl modname="Categories" type="userform" func="moveField" dr=$dr cid=$cat.id direction="down" append="#top"}">
                    {img modname=core src=1downarrow.png set=icons/extrasmall __alt="Down" __title="Down"}
                </a>
                {else}
                &nbsp;
                {/if}
            </td>
            <td style="text-align:center">
                {if ($cnt != 1)}
                <a href="{modurl modname="Categories" type="userform" func="moveField" dr=$dr cid=$cat.id direction="up" append="#top"}">
                    {img modname=core src=1uparrow.png set=icons/extrasmall __alt="Up" __title="Up"}
                </a>
                {else}
                &nbsp;
                {/if}
            </td>
            <td style="text-align:center">
                {if ($cat.is_locked)}
                {img modname=core src=locked.png set=icons/extrasmall __alt="Category is locked" __title="Category is locked"}
                {else}
                <a href="{modurl modname="Categories" type="user" func="edit" dr=$dr cid=$cat.id}">
                    {img modname=core src=xedit.png set=icons/extrasmall __alt="Edit" __title="Edit"}
                </a>
                {/if}
            </td>
            <td style="text-align:center">
                {gt text="Do you really want to delete the category '%s'?" tag1=$cat.name|safetext  assign="delPrompt"}
                <a href="{modurl modname="Categories" type="userform" func="delete" dr=$dr cid=$cat.id}" onclick="return confirm('{$delPrompt}');" >
                    {img modname=core src=14_layer_deletelayer.png set=icons/extrasmall __alt="Delete" __title="Delete"}
                </a>
            </td>
        </tr>
    </tbody>
    {/foreach}
    {/if}
</table>
