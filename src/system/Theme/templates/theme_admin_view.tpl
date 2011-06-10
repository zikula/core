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
{include file='theme_admin_menu.tpl'}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{icon type="view" size="large"}</div>
    <h3>{gt text="Themes list"}</h3>

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
                    <a href="{modurl modname=Admin type=admin func=adminpanel theme=$theme.name}" title="{$theme.displayname|safetext}">{icon type="preview" size="extrasmall" __alt="Preview" __title="Preview: `$theme.displayname`" class="tooltips"}</a>
                    {else}
                    <a href="{entrypoint}?theme={$theme.name}" title="{$theme.displayname|safetext}">{icon type="preview" size="extrasmall" __alt="Preview" __title="Preview: `$theme.displayname`" class="tooltips"}</a>
                    {/if}
                    <a href="{modurl modname="Theme" type="admin" func="modify" themename=$theme.name}">{icon type="edit" size="extrasmall" __alt="Edit" __title="Edit: `$theme.displayname` " class="tooltips"}</a>
                    {if $theme.name neq $currenttheme and $theme.state neq 2}
                    <a href="{modurl modname="Theme" type="admin" func="delete" themename=$theme.name}">{icon type="delete" size="extrasmall" __alt="Delete" __title="Delete: `$theme.displayname`" class="tooltips"}</a>
                    {/if}
                    {if $theme.name neq $currenttheme and $theme.user and $theme.state neq 2}
                    <a href="{modurl modname="Theme" type="admin" func="setasdefault" themename=$theme.name}">{icon type="ok" size="extrasmall" __alt="Set as default" __title="Set as default: `$theme.displayname`" class="tooltips"}</a>
                    {/if}
                    <a href="{modurl modname="Theme" type="admin" func="credits" themename=$theme.name}">{icon type="info" size="extrasmall" __alt="Credits" __title="Credits: `$theme.displayname`" class="tooltips"}</a>
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
