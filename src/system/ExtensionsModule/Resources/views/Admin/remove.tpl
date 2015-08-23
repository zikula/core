{adminheader}
<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Uninstall module'} - {modgetinfo modid=$id info=displayname}
</h3>

<p class="alert alert-danger">{gt text='Warning! Uninstalling this module will also permanently remove all data associated with it, including all data held by other modules that are hooked to this module.'}</p>

<form id="uninstall-module" class="form-horizontal" role="form" action="{route name='zikulaextensionsmodule_admin_remove'}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text='Do you really want to uninstall this module?'}</legend>

        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="id" value="{$id|safetext}" />
        <input type="hidden" name="startnum" value="{$startnum}" />
        <input type="hidden" name="letter" value="{$letter}" />
        <input type="hidden" name="state" value="{$state}" />

        {if $hasBlocks gt 0}
        <div class="">
            <p class="alert alert-danger">{gt text="Warning! This module still has %s active block. Removing this module will also permanently remove this block." plural="Warning! This module still has %s active blocks. Removing this module will also permanently remove these blocks." count=$hasBlocks tag1=$hasBlocks}</p>
        </div>
        {/if}
        {if $dependents}
        <div class="">
            <p class="alert alert-danger">{gt text='Warning! Other modules present in your system are dependent on this module. If you uninstall this module then all modules that require it will also be uninstalled.'}</p>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>{gt text='Module name'}</th>
                        <th>{gt text='Level'}</th>
                        <th>{gt text='Uninstall module'}</th>
                    </tr>
                </thead>
                <tbody>
                {foreach item='dependent' from=$dependents}
                    <tr>
                        <td>{$dependent.displayname}</td>
                        <td>
                            {if $dependent.status eq 1}
                            {gt text='Required'}
                            {elseif $dependent.status eq 2}
                            {gt text='Optional'}
                            {/if}
                        </td>
                        <td>
                            {if $dependent.status eq 1}
                            <input type="hidden" name="dependents[]" value="{$dependent.id}" />
                            <input type="checkbox" name="dummy[]" value="{$dependent.id}" checked="checked" disabled="disabled" />
                            {elseif $dependent.status eq 2}
                            <input type="checkbox" name="dependents[]" value="{$dependent.id}" />
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
        {/if}

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-danger" title="{gt text='Uninstall'}">{gt text='Uninstall'}</button>
                <a class="btn btn-default" href="{route name='zikulaextensionsmodule_admin_view'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </fieldset>
</form>
{adminfooter}
