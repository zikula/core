{route name='zikulaadminmodule_admin_help' assign='adminhelpurl'}
{checkpermissionblock component='ZikulaAdminModule::' instance='::' level=ACCESS_ADMIN}
{if $notices.security.magic_quotes_gpc or $notices.security.register_globals or $notices.security.config_php or !$notices.security.app_htaccess or !$notices.security.scactive}
<div id="z-securityanalyzer" class="alert alert-warning">
    <i class="close" data-dismiss="alert">&times;</i>
    <strong>{gt text='Security analyser warnings' domain='zikula'}</strong>
    <ul>
        {if $notices.security.config_php}
        <li>
            <a href="{$adminhelpurl|safetext}#admin_configphpwarning">{gt text="Configuration file 'config/config.php' is writeable, but should be read-only (please set to chmod 400, or 440 or last resort 444)." domain="zikula"}</a>
        </li>
        {/if}
        {if $notices.security.magic_quotes_gpc}
        <li>
            <a href="{$adminhelpurl|safetext}#admin_magic_quotes_warning">{gt text="PHP 'magic_quotes_gpc' setting is ON, but should be OFF." domain="zikula"}</a>
        </li>
        {/if}
        {if $notices.security.register_globals}
        <li>
            <a href="{$adminhelpurl|safetext}#admin_register_globals_warning">{gt text="PHP 'register_globals' setting is ON, but should be OFF." domain="zikula"}</a>
        </li>
        {/if}
        {if !$notices.security.app_htaccess}
        <li>
            <a href="{$adminhelpurl|safetext}#admin_app_htaccess_warning">{gt text="There is no '.htaccess' file in the application directory '/app', but one should be present." domain="zikula"}</a>
        </li>
        {/if}
        {if !$notices.security.scactive}
        <li>
            <a href="{$adminhelpurl|safetext}#admin_security_center_warning2">{gt text='Security center module is not installed, but preferably should be.' domain="zikula"}</a>
        </li>
        {/if}
        {if $notices.security.useids and $notices.security.idssoftblock}
        <li>
            <a href="{$adminhelpurl|safetext}#admin_idssoftblock_warning">{gt text='PHPIDS is activated, but requests are NOT blocked.' domain="zikula"}</a>
        </li>
        {/if}
    </ul>
</div>
{/if}
{/checkpermissionblock}
