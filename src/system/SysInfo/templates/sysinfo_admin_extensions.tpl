{include file="sysinfo_admin_menu.tpl"}
{gt text="Zikula extensions" assign=templatetitle}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=info.gif set=icons/large alt=$templatetitle}</div>
    <h2>{$templatetitle}</h2>
    <p class="z-informationmsg">{gt text="Notice: This is a list of all the modules present within the file system of your site, with the versions of the modules. It includes both modules that are installed and modules that are not installed."}</p>
    <table class="z-admintable">
        <thead>
            <tr>
                <th>{gt text="Module"}</th>
                <th>{gt text="Display name"}</th>
                <th>{gt text="Version"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$mods item=mod}
            <tr class="{cycle name=mods values="z-odd,z-even"}">
                <td>{$mod.name}</td>
                <td>{$mod.displayname}</td>
                <td>{$mod.version}</td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td colspan="3">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
    <p class="z-informationmsg">{gt text="Notice: This is a list of all the themes installed on your site, with the versions of the themes."}</p>
    <table class="z-admintable">
        <thead>
            <tr>
                <th>{gt text="Themes manager"}</th>
                <th>{gt text="Display name"}</th>
                <th>{gt text="Version"}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$themes item=theme}
            <tr class="{cycle name=themes values="z-odd,z-even"}">
                <td>{$theme.name}</td>
                <td>{$theme.displayname}</td>
                <td>{$theme.version}</td>
            </tr>
            {foreachelse}
            <tr class="z-admintableempty"><td colspan="3">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
</div>
