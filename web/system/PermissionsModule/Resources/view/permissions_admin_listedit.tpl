{adminheader}
{include file="permissions_admin_header.tpl"}

{if $action eq "add"}
<div class="z-admin-content-pagetitle">
    {icon type="new" size="small"}
    <h3>{$title|safetext}</h3>
</div>
{else}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{$title|safetext}</h3>
</div>
{/if}

{if $action eq "insert" or $action eq "modify" or $action eq "add"}
<form class="z-form" action="{$formurl|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="insseq" value="{$insseq|safetext}" />
        <input type="hidden" name="realm" value="0" />
        {/if}
        <table class="z-datatable">
            <thead>
                <tr>
                    <th>{gt text="Sequence"}</th>
                    <th>{$mlpermtype|safetext}</th>
                    <th><a href="javascript:showinstanceinformation()">{gt text="Component"}</a></th>
                    <th><a href="javascript:showinstanceinformation()">{gt text="Instance"}</a></th>
                    <th>{gt text="Permission level"}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {section name=permissions loop=$permissions}
                <tr class="{cycle values="z-odd,z-even"}">
                    {if ($insseq eq $permissions[permissions].sequence) and ($action eq "insert")}
                    <td>&nbsp;</td>
                    <td>
                        <select name="id">
                            {html_options options=$idvalues}
                        </select>
                    </td>
                    <td><textarea name="component"></textarea></td>
                    <td><textarea name="instance"></textarea></td>
                    <td>
                        <select name="level">
                            {html_options options=$permissionlevels}
                        </select>
                    </td>
                    <td>
                        <input name="submit" type="submit" value="{$submit}" />
                    </td>
                </tr>
                <tr class="{cycle values="z-odd,z-even"}">
                    <td>{$permissions[permissions].sequence|safetext}</td>
                    <td>{$permissions[permissions].group|safetext}</td>
                    <td>{$permissions[permissions].component|safetext}</td>
                    <td>{$permissions[permissions].instance|safetext}</td>
                    <td>{$permissions[permissions].accesslevel|safetext}</td>
                    <td>&nbsp;</td>
                    {elseif ($action eq "modify") and ($chgpid eq $permissions[permissions].pid)}
                    <td>
                        <input type="text" name="seq" size="3" value="{$permissions[permissions].sequence|safetext}" />
                        <input type="hidden" name="oldseq" value="{$permissions[permissions].sequence}" />
                        <input type="hidden" name="pid" value="{$permissions[permissions].pid}" />
                    </td>
                    <td>
                        <select name="id">
                            {html_options options=$idvalues selected=$selectedid}
                        </select>
                    </td>
                    <td><textarea name="component">{$permissions[permissions].component|safetext}</textarea></td>
                    <td><textarea name="instance">{$permissions[permissions].instance|safetext}</textarea></td>
                    <td>
                        <select name="level">
                            {html_options options=$permissionlevels selected=$permissions[permissions].level}
                        </select>
                    </td>
                    <td>
                        <input name="submit" type="submit" value="{$submit|safetext}" />
                    </td>
                    {else}
                    <td>{$permissions[permissions].sequence|safetext}</td>
                    <td>{$permissions[permissions].group|safetext}</td>
                    <td>{$permissions[permissions].component|safetext}</td>
                    <td>{$permissions[permissions].instance|safetext}</td>
                    <td>{$permissions[permissions].accesslevel|safetext}</td>
                    <td>&nbsp;</td>
                    {/if}
                </tr>
                {/section}
                {if $action eq "add"}
                <tr class="{cycle values="z-odd,z-even"}" style="vertical-align:top;">
                    <td>&nbsp;</td>
                    <td>
                        <select name="id">
                            {html_options options=$idvalues}
                        </select>
                    </td>
                    <td><textarea name="component" rows="2" cols="20">.*</textarea></td>
                    <td><textarea name="instance"  rows="2" cols="20">.*</textarea></td>
                    <td>
                        <select name="level">
                            {html_options options=$permissionlevels}
                        </select>
                    </td>
                    <td class="z-buttons z-right">
                        {button src=button_ok.png set=icons/extrasmall alt=$submit title=$submit text=$submit constants=false}
                    </td>
                </tr>
                {/if}
            </tbody>
        </table>
        {if $action eq "insert" or $action eq "modify" or $action eq "add"}
    </div>
</form>
{/if}
{adminfooter}
