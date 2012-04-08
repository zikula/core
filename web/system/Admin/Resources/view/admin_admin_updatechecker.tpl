{checkpermissionblock component='::' instance='::' level=ACCESS_ADMIN}
    {if $notices.update.update_show}
        <div id="z-updatechecker">
            <strong>{gt text="Upgrade found!"}</strong>
            <p>
                {gt text="A new version of the Zikula core is available." domain="zikula"}<br />
                <a href="http://zikula.org/">{gt text="Please download the new Zikula core" domain="zikula"} {$notices.update.update_version}</a>
            </p>
        </div>
    {/if}
{/checkpermissionblock}