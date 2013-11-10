{pageaddvar name='javascript' value='system/Zikula/Module/BlocksModule/Resources/public/js/Zikula.Blocks.Admin.Common.js'}
{pageaddvar name='javascript' value='system/Zikula/Module/BlocksModule/Resources/public/js/Zikula.Blocks.Admin.Modifyposition.js'}
{pageaddvar name='javascript' value='jquery-ui'}
{adminheader}
<h3>
    <span class="fa fa-pencil"></span>
    {gt text="Edit block position"}
</h3>

<form id="blockpositionform" class="form-horizontal" role="form" action="{modurl modname="Blocks" type="admin" func="updateposition"}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text="Block position"}</legend>
        
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="position" name="position[pid]" value="{$pid|safetext}" />
        
        <div class="form-group">
            <label class="col-lg-3 control-label" for="blocks_positionname">{gt text="Name"}</label>
            <div class="col-lg-9">
                <input type="text" id="blocks_positionname" class="form-control" name="position[name]" value="{$name|safetext}" size="50" maxlength="255" />
                <em class="sub help-block">{gt text="Characters allowed: a-z, A-Z, 0-9, dash (-) and underscore (_)."}</em>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="blocks_positiondescription">{gt text="Description"}</label>
            <div class="col-lg-9">
                <textarea name="position[description]" id="blocks_positiondescription" class="form-control" rows="5" cols="30">{$description|safehtml}</textarea>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">
                    {gt text="Save"}
                </button>
                <a class="btn btn-danger" href="{modurl modname=Blocks type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </fieldset>

    <h3>{gt text="Block assignments"}</h3>
    <p class="alert alert-info">{gt text="Notice: Use drag and drop to arrange the blocks in this position into your desired order. The new block order will be saved automatically."}</p>

    <h4>{gt text="Blocks assigned to this position"}</h4>
    <table id="assignedblocklist" class="table table-bordered table-striped">
        <thead pid="">
            <tr id="assignedblocklistheader">
                <th width="20px"></th>
                <th>{gt text="Block ID"}</th>
                <th>{gt text="Title, Description"}</th>
                <th>{gt text="Module"}</span>
                <th>{gt text="Name"}</th>
                <th>{gt text="Language"}</th>
                <th>{gt text="State"}</th>
            </tr>
        </thead>
        <tbody>
            <tr {if !empty($assignedblocks)}style="display: none;"{/if}class="sortable-placeholder">
                <td class="warning" colspan="7">{gt text='No blocks assigned yet.'}</td>
            </tr>
            {foreach item=block from=$assignedblocks}
            <tr data-bid="{$block.bid}">
                <td><span class="fa fa-arrows"></span></td>
                <td>{$block.bid|safetext}</td>
                <td id="blockdrag_{$block.bid}">{$block.title|safehtml|default:"&nbsp;"}{if $block.title && $block.description},&nbsp;{/if}{$block.description|safehtml}</td>
                <td>{$block.modname|safetext}</td>
                <td>{$block.bkey|safetext}</td>
                <td>{$block.language|safetext|default:"&nbsp;"}</td>
                <td>
                    <a class="label label-success tooltips{if !$block.active} hide{/if}" href="#" title="{gt text="Click to deactivate this block"}" data-bid="{$block.bid}">{gt text="Active"}</a>
                    <a class="label label-danger tooltips{if $block.active} hide{/if}" href="#" title="{gt text="Click to deactivate this block"}" data-bid="{$block.bid}">{gt text="Inactive"}</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>

    <h4>{gt text="Blocks not assigned to this position"}</h4>
    <table id="unassignedblocklist" class="table table-bordered table-striped">
        <thead>
            <tr id="unassignedblocklistheader">
                <th width="20px"></th>
                <th>{gt text="Block ID"}</th>
                <th>{gt text="Title, Description"}</th>
                <th>{gt text="Module"}</th>
                <th>{gt text="Name"}</th>
                <th>{gt text="Language"}</th>
                <th>{gt text="State"}</th>
            </tr>
        </thead>
        <tbody>
            <tr {if !empty($unassignedblocks)}style="display: none;"{/if}class="sortable-placeholder">
                <td class="warning" colspan="7">{gt text='All blocks assigned.'}</td>
            </tr>
            {foreach item=block from=$unassignedblocks}
            <tr data-bid="{$block.bid}">
                <td><span class="fa fa-arrows"></span></td>
                <td>{$block.bid|safetext}</td>
                <td id="blockdrag_{$block.bid}">
                    {$block.title|safehtml|default:"&nbsp;"}{if $block.title && $block.description},&nbsp;{/if}{$block.description|safehtml}
                </td>
                <td>{$block.modname|safetext}</td>
                <td>{$block.bkey|safetext}</td>
                <td>{$block.language|safetext|default:"&nbsp;"}</td>
                <td>
                    <a class="label label-success tooltips{if !$block.active} hide{/if}" href="#" title="{gt text="Click to deactivate this block"}" data-bid="{$block.bid}">{gt text="Active"}</a>
                    <a class="label label-danger tooltips{if $block.active} hide{/if}" href="#" title="{gt text="Click to deactivate this block"}" data-bid="{$block.bid}">{gt text="Inactive"}</a>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
    
</form>
{adminfooter}