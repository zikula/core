{include file='theme_admin_menu.tpl'}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="info" size="large"}</div>
    <h2>{gt text="Theme credits"}</h2>
    {if $themeinfo.system neq 1}
    <div style="text-align:center;">{previewimage size='large' name=$themeinfo.name id=theme_credits_preview}</div>
    {/if}
    <table class="z-datatable">
        <tbody>
            {foreach from=$themeinfo item=field key=key}
            {if $field neq ''}
            <tr class="{cycle values=z-odd,z-even}">
                {if $key eq 'id'}<th>{gt text="Internal ID"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'name'}<th>{gt text="Name"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'type'}<th>{gt text="Type"}</th><td>{$field|themetype}</td>{/if}
                {if $key eq 'displayname'}<th>{gt text="Display name"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'description'}<th>{gt text="Description"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'regid'}<th>{gt text="Registration ID"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'directory'}<th>{gt text="Directory"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'version'}<th>{gt text="Version"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'official'}<th>{gt text="Official"}</th><td>{$field|yesno|safetext}</td>{/if}
                {if $key eq 'author'}<th>{gt text="Author"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'contact'}<th>{gt text="Contact"}</th><td>{$field|safetext|activatelinks}</td>{/if}
                {if $key eq 'admin'}<th>{gt text="Admin panel theme"}</th><td>{$field|yesno|safetext}</td>{/if}
                {if $key eq 'user'}<th>{gt text="User theme"}</th><td>{$field|yesno|safetext}</td>{/if}
                {if $key eq 'system'}<th>{gt text="System theme"}</th><td>{$field|yesno|safetext}</td>{/if}
                {if $key eq 'state'}<th>{gt text="State"}</th><td>{$field|safetext|activeinactive}</td>{/if}
                {if $key eq 'credits'}<th>{gt text="Credits"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'changelog'}<th>{gt text="Change log"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'help'}<th>{gt text="Help"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'license'}<th>{gt text="License"}</th><td>{$field|safetext}</td>{/if}
                {if $key eq 'xhtml'}<th>{gt text="XHTML-capable"}</th><td>{$field|yesno|safetext}</td>{/if}
            </tr>
            {/if}
            {/foreach}
        </tbody>
    </table>
</div>
