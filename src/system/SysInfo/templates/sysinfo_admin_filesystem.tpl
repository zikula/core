{include file="sysinfo_admin_menu.tpl"}
{gt text="Zikula file system and 'ztemp' directory" assign=templatetitle}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=documentinfo.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <p class="z-informationmsg">{gt text="Notice: You are recommended to ensure that all writeable files and folders are secured from outside access. For best security, the 'ztemp' folder should not be located in a browseable directory of your site. <strong>Current path to 'ztemp':</strong>"} {$ztemp}</p>
    <table class="z-datatable">
        <thead>
            <tr>
                <th>{gt text="Type"}</th>
                <th>{gt text="Path"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$filelist item=file}
            {if $file.writable}
            <tr class="{cycle values="z-odd,z-even"}">
                <td>{$file.dir}</td>
                <td>{$file.path}{$file.name}</td>
            </tr>
            {/if}
            {/foreach}
        </tbody>
    </table>
</div>
