{if $action eq "subscribe"}
    {gt text="Membership application" assign="templatetitle"}
{elseif $action eq "unsubscribe"}
    {gt text="Membership resignation" assign="templatetitle"}
{elseif $action eq "cancel"}
    {gt text="Membership application cancellation" assign="templatetitle"}
{/if}

{include file="User/menu.tpl"}

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="user" func="userupdate" action=$action}" method="post" enctype="application/x-www-form-urlencoded">
    
    <div>
        <input type="hidden" id="csrftoken" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="gid" value="{$gid|safetext}" />
        <input type="hidden" name="action" value="{$action|safetext}" />
        <input type="hidden" name="gtype" value="{$gtype|safetext}" />
        <input type="hidden" name="tag" value="1" />
        
        <fieldset>
            <legend>{$templatetitle}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Group name"}</label>
                <div class="col-lg-9">
                <span>{$gname}</span>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text="Description"}</label>
                <div class="col-lg-9">
                <span>{if $description}{$description}{else}<em>{gt text="Not available"}</em>{/if}</span>
            </div>
            {if $action eq "subscribe" && $gtype eq 2}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_applytext">{gt text="Comment to attach to your application"}</label>
                <div class="col-lg-9">
                <textarea class="form-control" id="groups_applytext" name="applytext" cols="50" rows="8"></textarea>
            </div>
            {/if}
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                {button class="btn btn-success" value="Apply" __alt="Apply" __title="Apply" __text="Apply"}
                <a class="btn btn-danger" href="{modurl modname='ZikulaGroupsModule' type='user' func='view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
