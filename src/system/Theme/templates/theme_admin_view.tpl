{ajaxheader ui=true}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        Zikula.UI.Tooltips($$('.tooltips'));
    });
</script>
{/pageaddvarblock}

{gt text="Extension database" assign=extdbtitle}
{assign value="<strong><a href=\"http://community.zikula.org/module-Extensions.htm\">`$extdbtitle`</a></strong>" var=extdblink}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{gt text="Themes list"}</h3>
</div>

<p class="z-informationmsg">{gt text='Themes control the visual presentation of a site. Zikula ships with a small selection of themes, but many more are available from the %s.' tag1=$extdblink}</p>

<div id="themes-alphafilter" style="padding:0 0 1em;"><strong>[{pagerabc posvar="startlet" forwardvars=''}]</strong></div>

<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="Name"}</th>
            <th>{gt text="Description"}</th>
            <th class="z-right">{gt text="Actions"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$themes item=theme}
        {homepage assign='homepageurl'}
        {if $modvars.ZConfig.shorturls eq 1 && $modvars.ZConfig.shorturlsstripentrypoint neq 1}
        {assign var='themeurl' value="`$homepageurl`/`$theme.name`"}
        {elseif $modvars.ZConfig.shorturls eq 1 && $modvars.ZConfig.shorturlsstripentrypoint eq 1}
        {assign var='themeurl' value="`$homepageurl``$theme.name`"}
        {else}
        {if $homepageurl|strstr:"?"}
        {assign var='themeurl' value="`$homepageurl`&theme=`$theme.name`"}
        {else}
        {assign var='themeurl' value="`$homepageurl`?theme=`$theme.name`"}
        {/if}
        {/if}
        <tr class="{cycle values="z-odd,z-even}{if $theme.name|strtolower eq $currenttheme|strtolower} z-defaulttablerow{/if}">
            <td>
                <a href="{$themeurl|safetext}" title="{$theme.displayname|safetext}">
                    {if !$theme.structure}<strike>{/if}
                    <span title="#title_{$theme.name}" class="tooltips marktooltip">{$theme.displayname|safetext}</span>
                    {if !$theme.structure}</strike>{/if}
                </a>
                {if $theme.name|strtolower eq $currenttheme|strtolower}<span title="{gt text="Default theme"}" class="tooltips z-form-mandatory-flag">*</span>{/if}
                <div id="title_{$theme.name}" class="theme_preview z-center" style="display: none;">
                    <h4>{$theme.displayname}</h4>
                    {if $themeinfo.system neq 1}
                    <p>{previewimage name=$theme.name}</p>
                    {/if}
                </div>
            </td>
            <td>
                {if !$theme.structure}<strike>{/if}
                {$theme.description|default:$theme.displayname}</td>
                {if !$theme.structure}</strike>{/if}
            <td class="z-right z-nowrap">
                {gt text='Preview: %s' tag1=$theme.displayname assign=strPreviewTheme}
                {gt text='Edit: %s' tag1=$theme.displayname assign=strEditTheme}
                {gt text='Delete: %s' tag1=$theme.displayname assign=strDeleteTheme}
                {gt text='Set as default: %s' tag1=$theme.displayname assign=strSetDefaultTheme}
                {gt text='Credits: %s' tag1=$theme.displayname assign=strCreditsTheme}
                {if $theme.structure}
                <a href="{$themeurl|safetext}" title="{$theme.displayname|safetext}">{icon type="preview" size="extrasmall" __alt="Preview" title=$strPreviewTheme class="tooltips"}</a>
                <a href="{modurl modname="Theme" type="admin" func="modify" themename=$theme.name}">{icon type="edit" size="extrasmall" __alt="Edit" title=$strEditTheme class="tooltips"}</a>
                {/if}
                {if $theme.name neq $currenttheme and $theme.state neq 2}
                <a href="{modurl modname="Theme" type="admin" func="delete" themename=$theme.name}">{icon type="delete" size="extrasmall" __alt="Delete" title=$strDeleteTheme class="tooltips"}</a>
                {/if}
                {if $theme.name neq $currenttheme and $theme.user and $theme.state neq 2 and $theme.structure}
                <a href="{modurl modname="Theme" type="admin" func="setasdefault" themename=$theme.name}">{icon type="ok" size="extrasmall" __alt="Set as default" title=$strSetDefaultTheme class="tooltips"}</a>
                {/if}
                <a href="{modurl modname="Theme" type="admin" func="credits" themename=$theme.name}">{icon type="info" size="extrasmall" __alt="Credits" title=$strCreditsTheme class="tooltips"}</a>
            </td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty"><td colspan="3">{gt text="No items found."}</td></tr>
        {/foreach}
    </tbody>
</table>

<em><span class="z-form-mandatory-flag">*</span> = {gt text="Default theme"}</em>
{pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
{adminfooter}