{checkpermissionblock component='ZikulaAdminModule::' instance='::' level=ACCESS_ADMIN}
    {if $notices.update.update_show}
        <div id="z-updatechecker" class="alert alert-success">
            <i class="close" data-dismiss="alert">&times;</i>
            <strong>{gt text='Upgrade found!' domain='zikula'}</strong>
            <ul>
                <li>
                    <a href="http://zikula.org/">
                        {gt text='A new version of the Zikula core is available. Please download the new Zikula core' domain='zikula'} {$notices.update.update_version}.
                    </a>
                </li>
            </ul>
        </div>
    {/if}
{/checkpermissionblock}
