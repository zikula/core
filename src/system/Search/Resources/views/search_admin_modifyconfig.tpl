{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{gt text="Settings"}</h3>
</div>

<form class="z-form" action="{modurl modname="Search" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="z-formrow">
                <label for="search_itemsperpage">{gt text="Items per page"}</label>
                <input id="search_itemsperpage" type="text" name="itemsperpage" size="3" value="{$itemsperpage|safetext}" />
            </div>
            <div class="z-formrow">
                <label for="search_limitsummary">{gt text="Number of characters to display in item summaries"}</label>
                <input id="search_limitsummary" type="text" name="limitsummary" size="5" value="{$limitsummary|safetext}" />
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Disable search plug-ins"}</legend>
            {foreach from=$plugins item=plugin}
            {if isset($plugin.title)}
            <div class="z-formrow">
                <label for="search_disable{$plugin.title|safetext}">{modgetinfo info=displayname modname=$plugin.title}</label>
                <input id="search_disable{$plugin.title|safetext}" type="checkbox" name="disable[{$plugin.title|safetext}]" value="1"
                {if $plugin.disabled} checked="checked"{/if} />
            </div>
            {/if}
            {/foreach}
        </fieldset>

        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Admin type=admin func=adminpanel}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}