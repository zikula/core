{include file='includes/header.tpl'}

<div id="theme_navigation_bar">
    <ul class="z-clearfix">
        <li><a href="{homepage}">{gt text='Home'}</a></li>
        {checkpermission component='Settings::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='Settings' type='admin' func='main'}">{gt text="Settings"}</a></li>
        {/if}
        {checkpermission component='Extensions::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='Extensions' type='admin' func='main'}">{gt text="Extensions"}</a></li>
        {/if}
        {checkpermission component='Blocks::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='Blocks' type='admin' func='main'}">{gt text="Blocks"}</a></li>
        {/if}
        {checkpermission component='Users::' instance='::' level='ACCESS_MODERATE' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='Users' type='admin' func='main'}">{gt text="Users"}</a></li>
        {/if}
        {checkpermission component='Groups::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='Groups' type='admin' func='main'}">{gt text="Groups"}</a></li>
        {/if}
        {checkpermission component='Permissions::' instance='::' level='ACCESS_ADMIN' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='Permissions' type='admin' func='main'}">{gt text="Permission rules"}</a></li>
        {/if}
        {checkpermission component='Theme::' instance='::' level='ACCESS_EDIT' assign='okAccess'}
        {if $okAccess}
        <li><a href="{modurl modname='Theme' type='admin' func='main'}">{gt text="Themes"}</a></li>
        {/if}
    </ul>
</div>

{include file="body/$admin.tpl"}
{include file='includes/footer.tpl'}
