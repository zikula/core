{include file='includes/header.tpl'}

<div id="theme_navigation_bar">
    <ul class="clearfix">
        <li><a href="{homepage}">{gt text='Home'}</a></li>
        {checkpermission component='ZikulaSettingsModule::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='ZikulaSettingsModule' type='admin' func='index'}">{gt text="Settings"}</a></li>
        {/if}
        {checkpermission component='ZikulaExtensionsModule::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='ZikulaExtensionsModule' type='admin' func='index'}">{gt text="Extensions"}</a></li>
        {/if}
        {checkpermission component='ZikulaBlocksModule::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='ZikulaBlocksModule' type='admin' func='index'}">{gt text="Blocks"}</a></li>
        {/if}
        {checkpermission component='ZikulaUsersModule::' instance='::' level='ACCESS_MODERATE' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='ZikulaUsersModule' type='admin' func='index'}">{gt text="Users"}</a></li>
        {/if}
        {checkpermission component='ZikulaGroupsModule::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='ZikulaGroupsModule' type='admin' func='index'}">{gt text="Groups"}</a></li>
        {/if}
        {checkpermission component='ZikulaPermissionsModule::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='ZikulaPermissionsModule' type='admin' func='index'}">{gt text="Permission rules"}</a></li>
        {/if}
        {checkpermission component='ZikulaThemeModule::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='ZikulaThemeModule' type='admin' func='index'}">{gt text="Themes"}</a></li>
        {/if}
    </ul>
</div>

{include file="body/$admin.tpl"}
{include file='includes/footer.tpl'}
