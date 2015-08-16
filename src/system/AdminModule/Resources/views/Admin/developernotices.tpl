{checkpermissionblock component='ZikulaAdminModule::' instance='::' level=ACCESS_ADMIN}
{modurl modname='Theme' type='admin' func='modifyconfig' assign='themeurl'}
{if $notices.developer.devmode}
    <div id="z-developernotices" class="alert alert-info">
        <i class="close" data-dismiss="alert">&times;</i>
        <span class="fa fa-caret-right fa-fw"></span>
        <strong>{gt text='Developer notices (development mode on)' domain='zikula'}</strong>
        <ul class="hide">
            {if isset($notices.developer.render)}
                <li>
                    <a href="{$themeurl|safetext}">{gt text='Enabled Template settings' domain='zikula'}:</a>
                    {foreach name='item' item='item' from=$notices.developer.render}
                    {$item.title}{if !$smarty.foreach.item.last}, {/if}
                    {/foreach}
                </li>
            {/if}
            {if isset($notices.developer.theme)}
                <li>
                    <a href="{$themeurl|safetext}">{gt text='Enabled Theme settings' domain='zikula'}:</a>
                    {foreach name='item' item='item' from=$notices.developer.theme}
                    {$item.title}{if !$smarty.foreach.item.last}, {/if}
                    {/foreach}
                </li>
            {/if}
            {if isset($notices.developer.cssjscombine) && $notices.developer.cssjscombine}
                <li>{gt text='CSS/JS combination is enabled' domain='zikula'}</li>
            {/if}
            <li><a href="{modurl modname='Theme' type='admin' func='clearallcompiledcaches'}">{gt text='Clear all cache and compile directories'}</a></li>
        </ul>
    </div>
{/if}
{/checkpermissionblock}
