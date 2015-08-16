{adminheader}

<h3>
    {if $action eq 'add'}
        <span class="fa fa-plus"></span>
    {else}
        <span class="fa fa-pencil"></span>
    {/if}
    {$title|safetext}
</h3>

{if $action eq 'insert' || $action eq 'modify' || $action eq 'add'}
<form class="form-horizontal" role="form" action="{$formurl|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="insseq" value="{$insseq|safetext}" />
        <input type="hidden" name="realm" value="0" />
        {/if}
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{gt text='Sequence'}</th>
                    <th>{$mlpermtype|safetext}</th>
                    <th><a href="javascript:showinstanceinformation()">{gt text='Component'}</a></th>
                    <th><a href="javascript:showinstanceinformation()">{gt text='Instance'}</a></th>
                    <th>{gt text='Permission level'}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {section name='permissions' loop=$permissions}
                <tr>
                    {if $insseq eq $permissions[permissions].sequence && $action eq 'insert'}
                    <td>&nbsp;</td>
                    <td>
                        <select class="form-control" name="id">
                            {html_options options=$idvalues}
                        </select>
                    </td>
                    <td><textarea class="form-control" name="component"></textarea></td>
                    <td><textarea class="form-control" name="instance"></textarea></td>
                    <td>
                        <select class="form-control" name="level">
                            {html_options options=$permissionlevels}
                        </select>
                    </td>
                    <td>
                        <input name="submit" type="submit" value="{$submit}" />
                    </td>
                </tr>
                <tr>
                    <td>{$permissions[permissions].sequence|safetext}</td>
                    <td>{$permissions[permissions].group|safetext}</td>
                    <td>{$permissions[permissions].component|safetext}</td>
                    <td>{$permissions[permissions].instance|safetext}</td>
                    <td>{$permissions[permissions].accesslevel|safetext}</td>
                    <td>&nbsp;</td>
                    {elseif $action eq 'modify' && $chgpid eq $permissions[permissions].pid}
                    <td>
                        <input type="text" class="form-control" name="seq" size="3" value="{$permissions[permissions].sequence|safetext}" />
                        <input type="hidden" name="oldseq" value="{$permissions[permissions].sequence}" />
                        <input type="hidden" name="pid" value="{$permissions[permissions].pid}" />
                    </td>
                    <td>
                        <select class="form-control" name="id">
                            {html_options options=$idvalues selected=$selectedid}
                        </select>
                    </td>
                    <td><textarea class="form-control" name="component">{$permissions[permissions].component|safetext}</textarea></td>
                    <td><textarea class="form-control" name="instance">{$permissions[permissions].instance|safetext}</textarea></td>
                    <td>
                        <select class="form-control" name="level">
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
                {if $action eq 'add'}
                <tr style="vertical-align: top">
                    <td>&nbsp;</td>
                    <td>
                        <select class="form-control" name="id">
                            {html_options options=$idvalues}
                        </select>
                    </td>
                    <td><textarea class="form-control no-editor" name="component" rows="2" cols="20">.*</textarea></td>
                    <td><textarea class="form-control no-editor" name="instance"  rows="2" cols="20">.*</textarea></td>
                    <td>
                        <select class="form-control" name="level">
                            {html_options options=$permissionlevels}
                        </select>
                    </td>
                    <td class="z-buttons text-right">
                        {button class="btn btn-success" alt=$submit title=$submit text=$submit constants=false}
                    </td>
                </tr>
                {/if}
            </tbody>
        </table>
        {if $action eq 'insert' || $action eq 'modify' || $action eq 'add'}
    </div>
</form>
{/if}
{adminfooter}
