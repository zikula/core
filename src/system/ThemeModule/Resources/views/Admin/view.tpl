{pageaddvar name='javascript' value='system/ThemeModule/Resources/public/js/ZikulaThemeModule.Admin.View.js'}
{gt text="Extension database" assign='extdbtitle'}
{assign value="<strong><a href=\"https://github.com/zikula-modules\">`$extdbtitle`</a></strong>" var='extdblink'}

{adminheader}
<h3>
    <span class="fa fa-list"></span>
    {gt text="Themes list"}
</h3>

<p class="alert alert-info">{gt text='Themes control the visual presentation of a site. Zikula ships with a small selection of themes, but many more are available from the %s.' tag1=$extdblink}</p>

{pagerabc posvar='startlet' forwardvars='' printempty=true route='zikulathememodule_admin_view'}

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Description"}</th>
            <th style="width: 8em;">{gt text="Version"}</th>
            <th class="text-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {homepage assign='homepageurl'}
        {foreach from=$themes item=theme}
        {if $homepageurl|strstr:"?"}
            {assign var='themeurl' value=$homepageurl|cat:"&theme="|cat:$theme.name}
        {else}
            {assign var='themeurl' value=$homepageurl|cat:"?theme="|cat:$theme.name}
        {/if}
        <tr {if $theme.name|strtolower eq $currenttheme|strtolower}class="success"{/if}>
            <td>
                {if $theme.isTwigBased|default:false}{assign var='themeType' value='twig'}{else}{assign var='themeType' value='smarty'}{/if}
                <img src="system/ThemeModule/Resources/public/images/{$themeType}-logo.jpg" title="{gt text='Theme is %s based' tag1=$themeType}" class="tooltips" />
                {if !$theme.structure}<strike>{/if}
                {previewimage name=$theme.name assign='img'}
                <a href="{$themeurl|safetext}" title="{$theme.displayname|safetext}{if $theme.name|strtolower eq $currenttheme|strtolower} ({gt text='Default theme'}){/if}" class="marktooltip" data-trigger="hover" data-html="true" data-content='{$img|safehtml}' target="_blank">
                   {$theme.displayname|safetext}
                </a>
                {if !$theme.structure}</strike>{/if}
                {if $theme.name|strtolower eq $currenttheme|strtolower}<span class="required"></span>{/if}
            </td>
            <td>
                {if !$theme.structure}<strike>{/if}
                {$theme.description|default:$theme.displayname}
                {if !$theme.structure}</strike>{/if}
            </td>
            <td>
                {$theme.version}
            </td>
            <td class="actions">
                {gt text='Preview: %s' tag1=$theme.displayname assign=strPreviewTheme}
                {gt text='Edit: %s' tag1=$theme.displayname assign=strEditTheme}
                {gt text='Delete: %s' tag1=$theme.displayname assign=strDeleteTheme}
                {gt text='Set as default: %s' tag1=$theme.displayname assign=strSetDefaultTheme}
                {gt text='Credits: %s' tag1=$theme.displayname assign=strCreditsTheme}
                {if $theme.name neq $currenttheme and $theme.user and $theme.state neq 2 and $theme.structure}
                <a href="{route name='zikulathememodule_admin_setasdefault' themename=$theme.name}"><span class="fa fa-check tooltips" title="{$strSetDefaultTheme}"></span></a>
                {/if}
                {if $theme.structure}
                <a href="{$themeurl|safetext}" title="{$theme.displayname|safetext}" target="_blank"><span class="fa fa-eye tooltips" title="{$strPreviewTheme}"></span></a>
                {if $theme.isTwigBased|default:false}
                    <a href="{route name='zikulathememodule_var_var' themeName=$theme.name}"><span class="fa fa-pencil tooltips" title="{$strEditTheme}"></span></a>
                {else}
                    <a href="{route name='zikulathememodule_admin_modify' themename=$theme.name}"><span class="fa fa-pencil tooltips" title="{$strEditTheme}"></span></a>
                {/if}
                {/if}
                {if $theme.name neq $currenttheme and $theme.state neq 2}
                <a href="{route name='zikulathememodule_admin_delete' themename=$theme.name}"><span class="fa fa-trash-o tooltips" title="{$strDeleteTheme}"></span></a>
                {/if}
                <a href="{route name='zikulathememodule_admin_credits' themename=$theme.name}"><span class="fa fa-info-circle tooltips" title="{$strCreditsTheme}"></span></a>
            </td>
        </tr>
        {foreachelse}
        <tr class="table table-borderedempty"><td colspan="3">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>

<em><span class="required"></span> = {gt text="Default theme"}</em>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum' route='zikulathememodule_admin_view'}
{adminfooter}
