{ajaxheader modname=Blocks filename=blocks.js}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", blocksmodifyinit);
</script>
{/pageaddvarblock}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Edit block position"}</h3>
</div>

<form id="blockpositionform" class="z-form" action="{modurl modname="Blocks" type="admin" func="updateposition"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="position" name="position[pid]" value="{$pid|safetext}" />
        <fieldset>
            <legend>{gt text="Block position"}</legend>
            <div class="z-formrow">
                <label for="blocks_positionname">{gt text="Name"}</label>
                <input type="text" id="blocks_positionname" name="position[name]" value="{$name|safetext}" size="50" maxlength="255" />
                <em class="z-sub z-formnote">{gt text="Characters allowed: a-z, A-Z, 0-9, dash (-) and underscore (_)."}</em>
            </div>
            <div class="z-formrow">
                <label for="blocks_positiondescription">{gt text="Description"}</label>
                <textarea name="position[description]" id="blocks_positiondescription" rows="5" cols="30">{$description|safehtml}</textarea>
            </div>
            <div class="z-formbuttons z-buttons">
                {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
                <a href="{modurl modname=Blocks type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
            </div>
        </fieldset>

        <h3>{gt text="Block assignments"}</h3>
        <p class="z-informationmsg">{gt text="Notice: Use drag and drop to arrange the blocks in this position into your desired order. The new block order will be saved automatically."}</p>

        <h4>{gt text="Blocks assigned to this position"}</h4>
        <ol id="assignedblocklist" class="z-itemlist">
            <li id="assignedblocklistheader" class="z-itemheader z-itemsortheader z-clearfix">
                <span class="z-itemcell z-w10">{gt text="Block ID"}</span>
                <span class="z-itemcell z-w30">{gt text="Title, Description"}</span>
                <span class="z-itemcell z-w15">{gt text="Module"}</span>
                <span class="z-itemcell z-w15">{gt text="Name"}</span>
                <span class="z-itemcell z-w15">{gt text="Language"}</span>
                <span class="z-itemcell z-w15">{gt text="State"}</span>
            </li>
            {foreach item=block from=$assignedblocks}
            <li id="block_{$block.bid}" class="{cycle name=assignedblocklist values="z-odd,z-even"} z-sortable z-clearfix">
                <span class="z-itemcell z-w10">{$block.bid|safetext}</span>
                <span id="blockdrag_{$block.bid}" class="z-itemcell z-w30">{$block.title|safehtml|default:"&nbsp;"}{if $block.title && $block.description},&nbsp;{/if}{$block.description|safehtml}</span>
                <span class="z-itemcell z-w15">{$block.modname|safetext}</span>
                <span class="z-itemcell z-w15">{$block.bkey|safetext}</span>
                <span class="z-itemcell z-w15">{$block.language|safetext|default:"&nbsp;"}</span>
                <span class="z-itemcell z-w15">
                    {if $block.active}
                    <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})" title="{gt text="Click to deactivate this block"}">{img src=greenled.png modname=core set=icons/extrasmall __alt="Active" id="active_`$block.bid`"}{img src=redled.png modname=core set=icons/extrasmall __alt="Inactive" style="display: none;" id="inactive_`$block.bid`"}</a>
                    <noscript><div>{img src=greenled.png modname=core set=icons/extrasmall __alt="Active"}</div></noscript>
                    &nbsp;{gt text="Active"}

                    {else}
                    <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})" title="{gt text="Click to activate this block"}">{img src=greenled.png modname=core set=icons/extrasmall __alt="Active" style="display: none;" id="active_`$block.bid`"}{img src=redled.png modname=core set=icons/extrasmall __alt="Inactive" id="inactive_`$block.bid`"}</a>
                    <noscript><div>{img src=redled.png modname=core set=icons/extrasmall __alt="Inactive"}</div></noscript>
                    &nbsp;{gt text="Inactive"}
                    {/if}
                </span>
            </li>
            {/foreach}
        </ol>

        <h4>{gt text="Blocks not assigned to this position"}</h4>
        <ol id="unassignedblocklist" class="z-itemlist">
            <li id="unassignedblocklistheader" class="z-itemheader z-itemsortheader z-clearfix">
                <span class="z-itemcell z-w10">{gt text="Block ID"}</span>
                <span class="z-itemcell z-w30">{gt text="Title, Description"}</span>
                <span class="z-itemcell z-w15">{gt text="Module"}</span>
                <span class="z-itemcell z-w15">{gt text="Name"}</span>
                <span class="z-itemcell z-w15">{gt text="Language"}</span>
                <span class="z-itemcell z-w15">{gt text="State"}</span>
            </li>
            {foreach item=block from=$unassignedblocks}
            <li id="block_{$block.bid}" class="{cycle name=unassignedblocklist values="z-odd,z-even"} z-sortable z-clearfix">
                <span class="z-itemcell z-w10">{$block.bid|safetext}</span>
                <span id="blockdrag_{$block.bid}" class="z-itemcell z-w30">{$block.title|safehtml|default:"&nbsp;"}{if $block.title && $block.description},&nbsp;{/if}{$block.description|safehtml}</span>
                <span class="z-itemcell z-w15">{$block.modname|safetext}</span>
                <span class="z-itemcell z-w15">{$block.bkey|safetext}</span>
                <span class="z-itemcell z-w15">{$block.language|safetext|default:"&nbsp;"}</span>
                <span class="z-itemcell z-w15">
                    {if $block.active}
                    <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})" title="{gt text="Click to deactivate this block"}">{img src=greenled.png modname=core set=icons/extrasmall __alt="Active" id="active_`$block.bid`"}{img src=redled.png modname=core set=icons/extrasmall __alt="Inactive" style="display: none;" id="inactive_`$block.bid`"}</a>
                    <noscript><div>{img src=greenled.png modname=core set=icons/extrasmall __alt="Active"}</div></noscript>
                    &nbsp;{gt text="Active"}
                    {else}
                    <a class="activationbutton" href="javascript:void(0);" onclick="toggleblock({$block.bid})" title="{gt text="Click to activate this block"}">{img src=greenled.png modname=core set=icons/extrasmall __alt="Active" style="display: none;" id="active_`$block.bid`"}{img src=redled.png modname=core set=icons/extrasmall __alt="Inactive" id="inactive_`$block.bid`"}</a>
                    <noscript><div>{img src=redled.png modname=core set=icons/extrasmall __alt="Inactive"}</div></noscript>
                    &nbsp;{gt text="Inactive"}
                    {/if}
                </span>
            </li>
            {/foreach}
        </ol>
    </div>
</form>
{adminfooter}