{include file="modules_admin_menu.htm"}
<div class="z-admincontainer">
    <div class="z-adminpageicon">{img modname=core src=editdelete.gif set=icons/large __alt="Remove module"}</div>
    <h2>{gt text="Remove module"} - {modgetinfo modid=$id info=displayname}</h2>
    <p class="z-warningmsg">{gt text="Warning! Removing this module will also permanently remove all data associated with it, including all data held by other modules that are hooked to this module."}</p>

    <form class="z-form" action="{modurl modname="Modules" type="admin" func="remove"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Modules"}" />
            <input type="hidden" name="confirmation" value="1" />
            <input type="hidden" name="id" value="{$id|safetext}" />
            <input type="hidden" name="startnum" value="{$startnum}" />
            <input type="hidden" name="letter" value="{$letter}" />
            <input type="hidden" name="state" value="{$state}" />
            <fieldset>
                <legend>{gt text="Do you really want to delete this module?"}</legend>
                {if $hasBlocks gt 0}
                <div class="z-formrow">
                    <p class="z-warningmsg">{gt text="Warning! This module still has %s active block. Removing this module will also permanently remove this block." plural="Warning! This module still has %s active blocks. Removing this module will also permanently remove these blocks." count=$hasBlocks tag1=$hasBlocks}</p>
                </div>
                {/if}
                {if $dependents}
                <div class="z-formrow">
                    <p class="z-informationmsg">{gt text="Warning! Other modules present in your system are dependent on this module. If you remove this module then all modules that require it will also be removed."}</p>
                    <table class="z-admintable">
                        <thead>
                            <tr>
                                <th>{gt text="Module name"}</th>
                                <th>{gt text="Level"}</th>
                                <th>{gt text="Remove module"}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$dependents item=dependent}
                            <tr class="{cycle values="z-odd,z-even"}">
                                <td>{$dependent.displayname}</td>
                                <td>
                                    {if $dependent.status eq 1}
                                    {gt text="Required."}
                                    {elseif $dependent.status eq 2}
                                    {gt text="optional"}
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
                <div class="z-buttons z-formbuttons">
                    {button class="z-btgreen" src=button_ok.gif set=icons/extrasmall __alt="Delete" __title="Delete" __text="Delete"}
                    <a class="z-btred" href="{modurl modname=Modules type=admin func=view}">{img modname=core src=button_cancel.gif set=icons/extrasmall  __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
                </div>
            </fieldset>
        </div>
    </form>
</div>
