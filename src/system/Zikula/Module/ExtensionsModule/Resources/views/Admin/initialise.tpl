{adminheader}
<div class="z-admin-content-pagetitle">
    {img modname="core" src="folder_new.png" set=icons/small __alt="Install"}
    <h3>{gt text="Install"} - {modgetinfo modid=$id info=displayname}</h3>
</div>

<form class="z-form" action="{modurl modname="Extensions" type="admin" func="initialise"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="id" value="{$id|safetext}" />
        <div class="z-formrow">
            <p class="z-informationmsg">{gt text="Notice! This module either requires or recommends additional modules be installed. The report below details these requirements and/or recommendations."}</p>
        </div>
        {if $dependencies}
        <div class="z-formrow">
            <table class="z-datatable">
                <thead>
                    <tr>
                        <th>{gt text="Module name"}</th>
                        <th>{gt text="Level"}</th>
                        <th>{gt text="Install"}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$dependencies item=dependency}
                    <tr class="{cycle values="z-odd,z-even"}">
                        <td>{$dependency.modname}</td>
                        <td>
                            {if $dependency.insystem neq true and $dependency.status eq 1}
                            {gt text="Not present"}
                            {elseif $dependency.status eq 1}
                            {gt text="Required"}
                            {elseif $dependency.status eq 2}
                            {gt text="Recommended"}
                            {/if}
                        </td>
                        <td>
                            {if $dependency.insystem neq true and ($dependency.status eq 1 or $dependency.status eq 2)}
                            {gt text="Not present"}.
                            {elseif $dependency.status eq 1}{* required *}
                            <input type="hidden" name="dependencies[]" value="{$dependency.id}" />
                            <input type="checkbox" name="dummy[]" value="{$dependency.id}" disabled="disabled" />
                            {elseif $dependency.status eq 2}{* recommended *}
                            <input type="checkbox" name="dependencies[]" value="{$dependency.id}" />
                            {/if}
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        {/if}
        {if !$fataldependency}
        <p class="z-informationmsg">{gt text="Do you really want to install this module?"}</p>
        {else}
        <p class="z-errormsg">{gt text="Error! Required dependencies are not present. To install this module, please upload the dependencies."}</p>
        {/if}

        <div class="z-buttons z-formbuttons">
            {if !$fataldependency}
            {button src=button_ok.png set=icons/extrasmall __alt="Accept" __title="Accept" __text="Accept"}
            {/if}
            <a href="{modurl modname=Extensions type=admin func=view}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/extrasmall __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}