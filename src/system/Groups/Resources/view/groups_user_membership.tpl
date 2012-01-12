{if $action eq "subscribe"}
{gt text="Membership application" assign="templatetitle"}
{elseif $action eq "unsubscribe"}
{gt text="Membership resignation" assign="templatetitle"}
{elseif $action eq "cancel"}
{gt text="Membership application cancellation" assign="templatetitle"}
{/if}
{include file="groups_user_menu.tpl"}

{if $action != "subscribe" and $action != "unsubscribe" and $action != "cancel"}
<p>{gt text="Error! Could not load data."}</p>
{else}
<form class="z-form" action="{modurl modname="Groups" type="user" func="userupdate" action=$action}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" id="csrftoken" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="gid" value="{$gid|safetext}" />
        <input type="hidden" name="gtype" value="{$gtype|safetext}" />
        <input type="hidden" name="action" value="{$action|safetext}" />
        <input type="hidden" name="tag" value="1" />
        <fieldset>
            <legend>{$templatetitle}</legend>
            <div class="z-formrow">
                <label>{gt text="Group name"}</label>
                <span>{$gname}</span>
            </div>
            <div class="z-formrow">
                <label>{gt text="Description"}</label>
                <span>{if $description}{$description}{else}<em>{gt text="Not available"}</em>{/if}</span>
            </div>
            {if $action eq "subscribe" && $gtype eq 2}
            <div class="z-formrow">
                <label for="groups_applytext">{gt text="Comment to attach to your application"}</label>
                <textarea id="groups_applytext" name="applytext" cols="50" rows="8"></textarea>
            </div>
            {/if}
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall value="Submit" __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Groups type=user func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{/if}
