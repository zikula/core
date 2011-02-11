{include file="sysinfo_admin_menu.tpl"}
{gt text="System summary" assign=templatetitle}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=documentinfo.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <p>{gt text="This system summary and the other pages of the 'System info panel' provide information about your system that can be used to help diagnose problems with your Zikula installation."}</p>

    <h3>{gt text="General information"}</h3>
    <strong>{gt text="Zikula version:"}</strong> {$pnversionid} {$pnversionnum} ({$pnversionsub})<br />
    <strong>{gt text="Server information:"}</strong> {$serversig|strip_tags}<br />
    <strong>{gt text="PHP version:"}</strong> {$phpversion}<br />
    <strong>{gt text="Database version"}:</strong> {$dbinfo}<br />
    <strong>{gt text='Recommended security settings:'}</strong><br />
    <ul>
        {if ($php_register_globals==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="register_globals"}</li>
        {/if}
        {if ($php_allow_url_include==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="allow_url_include"}</li>
        {/if}
        {if ($php_allow_url_fopen==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="allow_url_fopen"}</li>
        {/if}
        {if ($php_magic_quotes_gpc==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="magic_quotes_gpc"}</li>
        {/if}
        {if ($php_display_errors==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="display_errors"}</li>
        {/if}
        {if ($php_display_startup_errors==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="display_startup_errors"}</li>
        {/if}
        {if ($php_expose_php==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="expose_php"}</li>
        {/if}
        {if ($php_magic_quotes_runtime==true)}
        <li>{gt text="PHP: %s=on - Should be off." tag1="magic_quotes_runtime"}</li>
        {/if}
        <li>{gt text="Check disable_functions for: show_source, system, shell_exec, passthru, exec, popen, proc_open."}<br />{gt text="Current disable_functions:"} {$php_disable_functions}</li>
        {if ($mod_security==false)}
        <li>{gt text='Server: Apache 2 and <a href="http://www.modsecurity.org">modsecurity</a> recommended.'}</li>
        {/if}
        <li>{gt text='Other recommendations:<br />Check <a href="http://www.php.net/manual/en/ini.list.php">\'List of php.ini directives\'</a> and <a href="http://php.net/manual/en/configuration.changes.php">\'How to change configuration settings\'</a> in the PHP documentation for more information about PHP configuration settings.<br />Subscribe to the <a href="http://groups.google.com/group/zikula-announce/">Zikula announcement mailing list</a>.<br />Monitor reports from security trackers: <a href="http://secunia.com/search?search=zikula&amp;x=0&amp;y=0">Secunia</a>, <a href="http://securitytracker.com/search/search2.html?cx=007223271850322448217%3Akrkjeopp4tm&amp;cof=FORID%3A9&amp;ie=UTF-8&amp;q=zikula&amp;sa=Search#204">SecurityTracker.com</a>, <a href="http://cve.mitre.org/cgi-bin/cvekey.cgi?keyword=zikula">CVE (Common Vulnerabilities and Exposures)</a>.'}</li>
    </ul>

    <h3>{gt text="Required PHP extensions"}</h3>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text="Name"}</th>
                <th>{gt text="Reason"}</th>
                <th>{gt text="Status"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach name=reqexts from=$extensions item=ext}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$ext.name}</td>
                <td>{$ext.reason}</td>
                <td>{img src=$ext.loaded modname=core set=icons/extrasmall alt=$ext.status title=$ext.status}</td>
            </tr>
            {foreachelse}
            <tr class="z-datatableempty"><td colspan="3">{gt text="No required extensions listed."}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <h3>{gt text="Optional PHP extensions"}</h3>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text="Name"}</th>
                <th>{gt text="Reason"}</th>
                <th>{gt text="Status"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach name=optexts from=$opt_extensions item=ext}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$ext.name}</td>
                <td>{$ext.reason}</td>
                <td>{img src=$ext.loaded modname=core set=icons/extrasmall alt=$ext.status title=$ext.status}</td>
            </tr>
            {foreachelse}
            <tr class="z-datatableempty"><td colspan="3">{gt text="No optional extensions listed."}</td></tr>
            {/foreach}
        </tbody>
    </table>

    <h3>{gt text="Optional PHP patches"}</h3>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text="Name"}</th>
                <th>{gt text="Reason"}</th>
                <th>{gt text="Status"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach name=optpatches from=$opt_patches item=ext}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{gt text=$ext.text}</td>
                <td>{$ext.reason}</td>
                <td>{img src=$ext.loaded modname=core set=icons/extrasmall alt=$ext.status title=$ext.status}</td>
            </tr>
            {foreachelse}
            <tr class="z-datatableempty"><td colspan="3">{gt text="No optional patches listed."}</td></tr>
            {/foreach}
        </tbody>
    </table>
</div>
