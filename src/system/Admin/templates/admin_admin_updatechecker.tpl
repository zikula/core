{checkpermissionblock component='::' instance='::' level=ACCESS_ADMIN}
    {if $notices.update.update_show}
        <div id="z-updatechecker">
            {gt text="Upgrade found! A new version of the Zikula core is available." domain="zikula"} {gt text="Please download the new Zikula core" domain="zikula"} <a href="http://zikula.org/">{$notices.update.update_version}</a>
        </div>
    {/if}
{/checkpermissionblock}