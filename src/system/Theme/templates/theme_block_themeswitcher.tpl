{if $format eq 1}
{ajaxheader modname='Theme' noscriptaculous=true}
{pageaddvar name="javascript" value="system/Theme/javascript/themeswitcher.js"}
<img src="{$currentthemepic}" id="preview" alt="{$currenttheme.displayname}" title="{$currenttheme.description|default:$currenttheme.displayname}" />
<form id="themeform" action="" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        {foreach from=$themes item=theme}
        <input type="hidden" id="previmg_{$theme.directory}" name="previmg_{$theme.directory}" value="{$baseurl}{$theme.previewImage}" />
        {/foreach}
        <select id="newtheme" name="newtheme" onchange="showthemeimage()">
            {foreach from=$themes item=theme}
            <option id="theme_{$theme.directory}" title="{$theme.description}" value="{$theme.directory}"{if $theme.name eq $currenttheme.name} selected="selected"{/if}>{$theme.displayname}</option>
            {/foreach}
        </select>
    </div>
    <div class="z-buttons">
        <input class="z-bt-ok z-bt-small" type="submit" value="{gt text="Change theme" domain='zikula'}" />
    </div>
</form>
{else}
<ul>
    {foreach from=$themes item=theme}
    <li><a href="?newtheme={$theme.name}">{$theme.displayname}</a></li>
    {/foreach}
</ul>
{/if}
