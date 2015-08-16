{if $format eq 1}
{pageaddvar name='javascript' value='system/Zikula/Module/ThemeModule/Resources/public/js/themeswitcher.js'}
<img src="{$currentthemepic}" id="preview" alt="{$currenttheme.displayname}" title="{$currenttheme.description|default:$currenttheme.displayname}" />
<form id="themeform" action="" method="get" enctype="application/x-www-form-urlencoded">
    <div>
        {foreach from=$themes item='theme'}
        {/foreach}
        <select id="newtheme" name="newtheme">
            {foreach from=$themes item=theme}
            <option id="theme_{$theme.directory}" title="{$theme.description}" value="{$theme.name}"{if $theme.name eq $currenttheme.name} selected="selected"{/if} data-previewimage="{$baseurl}{$theme.previewImage}">{$theme.displayname}</option>
            {/foreach}
        </select>
    </div>
    <input class="btn btn-success" type="submit" value="{gt text="Change theme" domain='zikula'}" />
</form>
{else}
<ul>
    {foreach from=$themes item='theme'}
    <li><a href="?newtheme={$theme.name}">{$theme.displayname}</a></li>
    {/foreach}
</ul>
{/if}
