{adminheader}
<h3>
    <span class="fa fa-plus"></span>
    {gt text='Install'} - {modgetinfo modid=$id info='displayname'}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulaextensionsmodule_admin_initialise'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="id" value="{$id|safetext}" />
        <p class="alert alert-info">{gt text='Notice! This module either requires or recommends additional modules be installed. The report below details these requirements and/or recommendations.'}</p>
        {if $dependencies}
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>{gt text='Module name'}</th>
                    <th>{gt text='Level'}</th>
                    <th>{gt text='Reason'}</th>
                    <th>{gt text='Install'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach item='dependency' from=$dependencies}
                <tr>
                    <td>{$dependency.displayname|default:$dependency.modname} ({$dependency.minversion|default:''})</td>
                    <td>
                        {if $dependency.insystem ne true && $dependency.status eq 1}
                        {gt text='Not present'}
                        {elseif $dependency.status eq 1}
                        {gt text='Required'}
                        {elseif $dependency.status eq 2}
                        {gt text='Recommended'}
                        {/if}
                    </td>
                    <td>{$dependency.reason}</td>
                    <td>
                        {if $dependency.insystem ne true and ($dependency.status eq 1 or $dependency.status eq 2)}
                        {gt text='Not present'}.
                        {elseif $dependency.status eq 1}{* required *}
                        <input type="hidden" name="dependencies[]" value="{$dependency.id}" />
                        <input type="checkbox" name="dummy[]" value="{$dependency.id}" disabled="disabled" checked="checked" />
                        {elseif $dependency.status eq 2}{* recommended *}
                        <input type="checkbox" name="dependencies[]" value="{$dependency.id}" />
                        {/if}
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        {/if}
        {if !$fataldependency}
        <p class="alert alert-info">{gt text='Do you really want to install this module?'}</p>
        {else}
        <p class="alert alert-danger">{gt text='Error! Required dependencies are not present. To install this module, please upload the dependencies.'}</p>
        {/if}

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
            {if !$fataldependency}
                {button class='btn btn-success' __alt='Accept' __title='Accept' __text='Accept'}
            {/if}
                <a class="btn btn-danger" href="{route name='zikulaextensionsmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
