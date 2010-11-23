{ajaxheader ui=true}
{include file='theme_admin_menu.tpl'}
{gt text="Extension database" assign=extdbtitle}
{assign value="<strong><a href=\"http://community.zikula.org/module-Extensions.htm\">`$extdbtitle`</a></strong>" var=extdblink}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=windowlist.gif set=icons/large __alt="Available themes"}</div>
    <h2>{gt text="Themes list"}</h2>
    <p class="z-informationmsg">{gt text='Themes control the visual presentation of a site. Zikula ships with a small selection of themes, but many more are available from the %s.' tag1=$extdblink}</p>
    <div id="themes-alphafilter" style="padding:1em 0;"><strong>[{pagerabc posvar="startlet" forwardvars=''}]</strong></div>
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
            <tr class="{cycle values="z-odd,z-even}{if $theme.name|strtolower eq $currenttheme|strtolower} z-defaulttablerow{/if}">
                <td>
                    {if $theme.admin eq true}
                    <a href="{modurl modname=Admin type=admin func=adminpanel theme=$theme.name}" title="{$theme.displayname|safetext}"><span title="#title_{$theme.name}" class="tooltips marktooltip">{$theme.displayname|safetext}</span></a>&nbsp;
                    {else}
                    <a href="{entrypoint}?theme={$theme.name}" title="{$theme.displayname|safetext}"><span title="#title_{$theme.name}" class="tooltips marktooltip">{$theme.displayname|safetext}</span></a>&nbsp;
                    {/if}
                    {if $theme.name|strtolower eq $currenttheme|strtolower}<span title="{gt text="Default theme"}" class="tooltips"> (*) </span>{/if}
                    <div id="title_{$theme.name}" class="theme_preview z-center" style="display: none;">
                        <h4>{$theme.displayname}</h4>
                        {if $themeinfo.system neq 1}
                        <p>{previewimage name=$theme.name}</p>
                        {/if}
                    </div>
                </td>
                <td>{$theme.description|default:$theme.displayname}</td>
                <td class="z-right z-nowrap">
                    {if $theme.admin eq true}
                    <a href="{modurl modname=Admin type=admin func=adminpanel theme=$theme.name}" title="{$theme.displayname|safetext}">{img modname=core src=14_layer_visible.gif set=icons/extrasmall __alt="Preview" __title="Preview" class="tooltips"}</a>&nbsp;
                    {else}
                    <a href="{entrypoint}?theme={$theme.name}" title="{$theme.displayname|safetext}">{img modname=core src=14_layer_visible.gif set=icons/extrasmall __alt="Preview" __title="Preview" class="tooltips"}</a>&nbsp;
                    {/if}
                    <a href="{modurl modname="Theme" type="admin" func="modify" themename=$theme.name}">{img modname=core src=xedit.gif set=icons/extrasmall __alt="Edit" __title="Edit" class="tooltips"}</a>&nbsp;
                    {if $theme.name neq $currenttheme and $theme.state neq 2}
                    <a href="{modurl modname="Theme" type="admin" func="delete" themename=$theme.name}">{img modname=core src=14_layer_deletelayer.gif set=icons/extrasmall __alt="Delete" __title="Delete" class="tooltips"}</a>&nbsp;
                    {/if}
                    {if $theme.name neq $currenttheme and $theme.user and $theme.state neq 2}
                    <a href="{modurl modname="Theme" type="admin" func="setasdefault" themename=$theme.name}">{img modname=core src=ok.gif set=icons/extrasmall __alt="Set as default" __title="Set as default" class="tooltips"}</a>&nbsp;
                    {/if}
                    <a href="{modurl modname="Theme" type="admin" func="credits" themename=$theme.name}">{img modname=core src=info.gif set=icons/extrasmall __alt="Credits" __title="Credits" class="tooltips"}</a>
                </td>
            </tr>
            {foreachelse}
            <tr class="z-datatableempty"><td colspan="3">{gt text="No items found."}</td></tr>
            {/foreach}
        </tbody>
    </table>
    <em>(*) = {gt text="Default theme"}</em>
    {pager rowcount=$pager.numitems limit=$pager.itemsperpage posvar='startnum'}
</div>

<script type="text/javascript">
    Zikula.UI.Tooltips($$('.tooltips'));
</script>

