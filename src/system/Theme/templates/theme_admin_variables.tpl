{adminheader}
{include file="theme_admin_modifymenu.tpl"}

<h4>{gt text="Variables"}{if $filename} &raquo; {$filename}{/if}</h4>

<div class="z-informationmsg">{gt text='You can handle plain variables <var>name &rarr; value</var>, but also setup arrays in the format <var>name[key] &rarr; value</var>.'}</div>

<form class="z-form" action="{modurl modname="Theme" type="admin" func="updatevariables"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <input type="hidden" name="filename" value="{$filename|safetext}" />
        <table class="z-datatable">
            <thead>
                <tr>
                    <th>{gt text="Name"}</th>
                    <th>{gt text="Value"}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$variables.variables key=name item=value}
                <tr class="{cycle values=z-odd,z-even}">
                    <td>
                        {if isset($variables.$name.editable)}
                        <input type="text" name="variablesnames[{$name|safetext}]" value="{$name|safetext}" />
                        {else}
                        <input type="hidden" name="variablesnames[{$name|safetext}]" value="{$name|safetext}" />
                        {if isset($variables.$name.language)}
                        {$variables.$name.language}
                        {else}
                        {$name|safetext}
                        {/if}
                        {/if}
                    </td>
                    <td>
                        {if $variables.$name.type eq 'yesno'}
                        <input type="radio" name="variablesvalues[{$name|safetext}]" value="1"{if $value eq 1} checked="checked"{/if} />&nbsp;{gt text="Yes"}&nbsp;
                        <input type="radio" name="variablesvalues[{$name|safetext}]" value="0"{if $value eq 0} checked="checked"{/if} />&nbsp;{gt text="No"}
                        {elseif $variables.$name.type eq 'select'}
                        {html_options name=variablesvalues[$name] values=$variables.$name.values output=$variables.$name.output selected=$value}
                        {else}
                        <input type="text" name="variablesvalues[{$name|safetext}]" value="{$value|safetext}" />
                        {/if}
                    </td>
                </tr>
                {foreachelse}
                <tr class="z-datatableempty"><td colspan="2">{gt text="No items found."}</td></tr>
                {/foreach}
            </tbody>
        </table>
        <fieldset>
            <legend>
                {if !$filename}
                {gt text="Add new theme variable"}
                {else}
                {gt text="Add new page variable"}
                {/if}
            </legend>
            <div class="z-formrow">
                <label for="theme_newvariablename">{gt text="Name"}</label>
                <input id="theme_newvariablename" type="text" name="newvariablename" size="30" />
            </div>
            <div class="z-formrow">
                <label for="theme_newvariablevalue">{gt text="Value"}</label>
                <input id="theme_newvariablevalue" type="text" name="newvariablevalue" size="30" />
            </div>
        </fieldset>
        <div class="z-buttons z-formbuttons">
            {button src=button_ok.png set=icons/extrasmall __alt="Save" __title="Save" __text="Save"}
            <a href="{modurl modname=Theme type=admin func=pageconfigurations themename=$themename}" title="{gt text="Cancel"}">{img modname=core src=button_cancel.png set=icons/small __alt="Cancel" __title="Cancel"} {gt text="Cancel"}</a>
        </div>
    </div>
</form>
{adminfooter}