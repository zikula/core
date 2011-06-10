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
{include file="blocks_admin_menu.tpl"}

<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="view" size="large"}</div>
    <h3>{gt text="Blocks list"}</h3>

    <p class="z-informationmsg">{gt text="This is the list of blocks present in your system, you can use the filter to display only certain blocks. The order in which blocks are listed here is not necessarily the order in which they are displayed in site pages. To manage the display order within site pages, scroll down (or <a href=\"#blockpositions\">click here</a>), then edit a block position. You will be able to arrange the order of display for blocks assigned to that block position."}</p>
    <form class="z-form" action="{modurl modname="Blocks" type="admin" func="view"}" method="post" enctype="application/x-www-form-urlencoded">
        {gt text="All" assign="lblAll"}
        {gt text="Filter" assign="lblFilter"}
        <fieldset>
            <legend>{$lblFilter}</legend>
            <span class="z-nowrap">
                <label for="filter_blockposition_id_">{gt text="Block Position"}</label>
                {selector_field_array name="filter[blockposition_id]" modname="Blocks" table="block_positions" field="name" assocKey="pid" sort="z_name" allText=$lblAll allValue=0 selectedValue=$filter.blockposition_id|default:0}
            </span>
            <span class="z-nowrap">
                <label for="filter_modid_">{gt text="Module"}</label>
                {selector_module name="filter[modid]" field="id" allText=$lblAll allValue=0 selectedValue=$filter.modid|default:0}
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
                <input class="z-bt-filter" name="submit" type="submit" value="{gt text='Filter'}" />
                <input class="z-bt-cancel" name="clear" type="submit" value="{gt text='Clear'}" />
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
                    {sortlink __linktext='Module' sort='module_name' currentsort=$sort sortdir=$sortdir modname='Blocks' type='admin' func='view' filter=$filter}
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
            <tr class="{cycle values="z-odd,z-even" name=blocks}">
                <td>{$block.bid|safetext}</td>
                <td>{$block.title|safetext}</td>
                <td>{$block.description|safetext}</td>
                <td>{$block.modname|safetext}</td>
                <td>{$block.bkey|safetext}</td>
                <td>{$block.positions|safetext}</td>
                <td>{$block.language|safetext}</td>
                <td>
                    {if $block.active}
                    <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})">{img src="greenled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$deactivate alt=$deactivate id="active_`$block.bid`"}{img src="redled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$activate alt=$activate style="display: none;" id="inactive_`$block.bid`"}</a>
                    <noscript><div>{img src=greenled.png modname=core set=icons/extrasmall __title="Active" __alt="Active" }</div></noscript>
                    &nbsp;<span id="activity_{$block.bid}">{gt text="Active"}</span>
                    {else}
                    <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})">{img src="greenled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$deactivate alt=$deactivate style="display: none;" id="active_`$block.bid`"}{img src="redled.png" modname="core" set="icons/extrasmall" class="tooltips" title=$deactivate alt=$deactivate id="inactive_`$block.bid`"}</a>
                    <noscript><div>{img src=redled.png modname=core set=icons/extrasmall __title="Inactive" __alt="Inactive" }</div></noscript>
                    &nbsp;<span id="activity_{$block.bid}">{gt text="Inactive"}</span>
                    {/if}
                </td>
                <td class="z-right">
                    {foreach item=option from=$block.options}
                    {if $option.noscript eq true}<noscript><div>{/if}
                        <a href="{$option.url|safetext}">{img modname=core src=$option.image set=icons/extrasmall title=$option.title alt=$option.title class='tooltips'}</a>
                    {if $option.noscript eq true}</div></noscript>{/if}
                    {/foreach}
                </td>
            </tr>
            {foreachelse}
            <tr class="z-datatableempty"><td colspan="9">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <h2 id="blockpositions">{gt text="Block positions list"}</h2>
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
            <tr class="{cycle values="z-odd,z-even" name=blockpositions}">
                <td>{$position.name|safehtml}</td>
                <td>{$position.description|truncate:25|safehtml}</td>
                <td><pre style="margin:0;padding:0;">&#123blockposition name={$position.name|safehtml}&#125</pre></td>
                <td class="z-right">
                    {foreach item=option from=$position.options}
                    <a href="{$option.url|safetext}">{img modname=core src=$option.image set=icons/extrasmall title=$option.title alt=$option.title class='tooltips'}</a>
                    {/foreach}
                </td>
            </tr>
            {foreachelse}
            <tr class="z-datatableempty"><td colspan="4">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
</div>
