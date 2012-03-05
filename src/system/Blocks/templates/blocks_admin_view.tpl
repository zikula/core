{ajaxheader modname=Blocks filename=blocks.js ui=true}
{pageaddvarblock}
<script type="text/javascript">
    var msgBlockStatusActive = '{{gt text="Active"}}';
    var msgBlockStatusInactive = '{{gt text="Inactive"}}';
    document.observe("dom:loaded", function() {
        initactivationbuttons();
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}
{gt text="Click to activate this block" assign=activate}
{gt text="Click to deactivate this block" assign=deactivate}

{adminheader}

<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Blocks list"}</h3>
</div>

<p class="z-informationmsg">{gt text="This is the list of blocks present in your system, you can use the filter to display only certain blocks. The order in which blocks are listed here is not necessarily the order in which they are displayed in site pages. To manage the display order within site pages, scroll down (or <a href=\"#blockpositions\">click here</a>), then edit a block position. You will be able to arrange the order of display for blocks assigned to that block position."}</p>

<form class="z-form" action="{modurl modname="Blocks" type="admin" func="view"}" method="post" enctype="application/x-www-form-urlencoded">
    {gt text="All" assign="lblAll"}
    {gt text="Filter" assign="lblFilter"}
    <fieldset>
        <legend>{$lblFilter}</legend>
        <span class="z-nowrap">
            <label for="filter_blockposition_id">{gt text="Block Position"}</label>
            <select id="filter_blockposition_id" name="filter[blockposition_id]">
                <option value="0">{$lblAll}</option>
                {foreach from=$positions item='position'}
                    <option value="{$position.pid}" {if $filter.blockposition_id eq $position.pid}selected="selected"{/if}>{$position.name|safetext}</option>
                {/foreach}
            </select>
        </span>
        <span class="z-nowrap">
            <label for="filter_module_id_">{gt text="Module"}</label>
            {selector_module name="filter[module_id]" field="id" allText=$lblAll allValue=0 selectedValue=$filter.module_id|default:0}
        </span>
        <span class="z-nowrap">
            <label for="filter_language">{gt text="Language"}</label>
            {html_select_languages id="filter_language" name="filter[language]" installed=1 all=1 selected=$filter.language|default:''}
        </span>
        <span class="z-nowrap">
            <label for="filter_status">{gt text="Active Status"}</label>
            <select id="filter_status" name="filter[active_status]">
                <option value="0" {if (isset($filter.active_status) && $filter.active_status == 0)}selected="selected"{/if}>{gt text="All"}</option>
                <option value="1" {if (isset($filter.active_status) && $filter.active_status == 1)}selected="selected"{/if}>{gt text="Active"}</option>
                <option value="2" {if (isset($filter.active_status) && $filter.active_status == 2)}selected="selected"{/if}>{gt text="Inactive"}</option>
            </select>
        </span>
        <span class="z-nowrap z-buttons">
            <input class="z-bt-filter z-bt-small" name="submit" type="submit" value="{gt text='Filter'}" />
            <input class="z-bt-cancel z-bt-small" name="clear" type="submit" value="{gt text='Clear'}" />
        </span>
    </fieldset>
</form>

<table class="z-datatable">
    <thead>
        <tr>
            <th>
                {sortlink __linktext='Block ID' sort='bid' currentsort=$sort sortdir=$sortdir modname='Blocks' type='admin' func='view' filter=$filter}
            </th>
            <th>
                {sortlink __linktext='Title' sort='title' currentsort=$sort sortdir=$sortdir modname='Blocks' type='admin' func='view' filter=$filter}
            </th>
            <th>
                {sortlink __linktext='Description' sort='description' currentsort=$sort sortdir=$sortdir modname='Blocks' type='admin' func='view' filter=$filter}
            </th>
            <th>
                {gt text="Module"}
            </th>
            <th>
                {sortlink __linktext='Name' sort='bkey' currentsort=$sort sortdir=$sortdir modname='Blocks' type='admin' func='view' filter=$filter}
            </th>
            <th>{gt text="Position(s)"}</th>
            <th>
                {sortlink __linktext='Language' sort='language' currentsort=$sort sortdir=$sortdir modname='Blocks' type='admin' func='view' filter=$filter}
            </th>
            <th>
                {sortlink __linktext='State' sort='active' currentsort=$sort sortdir=$sortdir modname='Blocks' type='admin' func='view' filter=$filter}
            </th>
            <th class="z-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach item=block from=$blocks}
        {assign var='lbl_block' value=$block.title|strip_tags|safetext}
        {gt text='Deactivate %s' tag1=$lbl_block assign='lbl_deactivate_block'}
        {gt text='Activate %s' tag1=$lbl_block assign='lbl_activate_block'}
        {gt text='Edit %s' tag1=$lbl_block assign='lbl_edit_block'}
        {gt text='Delete %s' tag1=$lbl_block assign='lbl_delete_block'}
        {checkpermission component="`$module`::" instance="`$block.bkey`:`$block.title`:`$block.bid`" level="ACCESS_EDIT" assign="access_edit"}
        {checkpermission component="`$module`::" instance="`$block.bkey`:`$block.title`:`$block.bid`" level="ACCESS_DELETE" assign="access_delete"}
        <tr class="{cycle values="z-odd,z-even" name=blocks}">
            <td>{$block.bid|safetext}</td>
            <td>{$block.title|safehtml}</td>
            <td>{$block.description|safehtml}</td>
            <td>{$block.modname|safetext}</td>
            <td>{$block.bkey|safetext}</td>
            <td>{$block.positions|safetext}</td>
            <td>{$block.language|safetext}</td>
            <td>
                {if $block.active}
                <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})">{img src="greenled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$deactivate alt=$deactivate id="active_`$block.bid`"}{img src="redled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$activate alt=$activate style="display: none;" id="inactive_`$block.bid`"}</a>
                <noscript><div><a href="{modurl modname=$module type='admin' func='deactivate' bid=$block.bid|safetext csrftoken=$csrftoken}">{img src='folder_green.png' modname='core' set='icons/extrasmall' title=$lbl_deactivate_block alt=$lbl_deactivate_block}</a></div></noscript>
                &nbsp;<span id="activity_{$block.bid}">{gt text="Active"}</span>
                {else}
                <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})">{img src="greenled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$deactivate alt=$deactivate style="display: none;" id="active_`$block.bid`"}{img src="redled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$deactivate alt=$deactivate id="inactive_`$block.bid`"}</a>
                <noscript><div><a href="{modurl modname=$module type='admin' func='activate' bid=$block.bid|safetext csrftoken=$csrftoken}">{img src='folder_grey.png' modname='core' set='icons/extrasmall' title=$lbl_activate_block alt=$lbl_activate_block}</a></div></noscript>
                &nbsp;<span id="activity_{$block.bid}">{gt text="Inactive"}</span>
                {/if}
            </td>
            <td class="z-right">
                {if $access_edit}
                <a href="{modurl modname=$module type='admin' func='modify' bid=$block.bid|safetext}">{img modname='core' src='xedit.png' set='icons/extrasmall' title=$lbl_edit_block alt=$lbl_edit_block class='tooltips'}</a>
                {/if}
                {if $access_delete}
                <a href="{modurl modname=$module type='admin' func='delete' bid=$block.bid|safetext}">{img modname='core' src='14_layer_deletelayer.png' set='icons/extrasmall' title=$lbl_delete_block alt=$lbl_delete_block class='tooltips'}</a>
                {/if}
            </td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty"><td colspan="9">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>

<h3 id="blockpositions">{gt text="Block positions list"}</h3>

<p class="z-informationmsg">{gt text="This is the list of block positions currently existing for your site's pages. You can create a new block position by clicking 'Create block position' in the menu. To edit the settings for a block position, or to reorder the blocks within a block position, click on the 'Edit' icon beside that particular position. To delete a block position, click on the 'Delete' icon and confirm the action in the confirmation prompt that will display."}</p>
<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Description"}</th>
            <th>{gt text="Theme tag"}</th>
            <th class="z-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$positions item=position}
        {gt text='Edit blockposition %s' tag1=$position.name|safetext assign='lbl_edit_blockposition'}
        {gt text='Delete blockposition %s' tag1=$position.name|safetext assign='lbl_delete_blockposition'}
        {checkpermission component="`$module`::position" instance="`$position.name`:`$position.pid`" level="ACCESS_EDIT" assign="access_edit"}
        {checkpermission component="`$module`::position" instance="`$position.name`:`$position.pid`" level="ACCESS_DELETE" assign="access_delete"}
        <tr class="{cycle values="z-odd,z-even" name=blockpositions}">
            <td>{$position.name|safehtml}</td>
            <td>{$position.description|truncate:25|safehtml}</td>
            <td><pre style="margin:0;padding:0;">&#123;blockposition name='{$position.name|safehtml}'&#125;</pre></td>
            <td class="z-right">
                {if $access_edit}
                <a href="{modurl modname=$module type='admin' func='modifyposition' pid=$position.pid|safetext}">{img modname='core' src='xedit.png' set='icons/extrasmall' title=$lbl_edit_blockposition alt=$lbl_edit_blockposition class='tooltips'}</a>
                {/if}
                {if $access_delete}
                <a href="{modurl modname=$module type='admin' func='deleteposition' pid=$position.pid|safetext}">{img modname='core' src='14_layer_deletelayer.png' set='icons/extrasmall' title=$lbl_delete_blockposition alt=$lbl_delete_blockposition class='tooltips'}</a>
                {/if}
            </td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty"><td colspan="4">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>
{adminfooter}
