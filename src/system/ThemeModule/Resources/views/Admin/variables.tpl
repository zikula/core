{adminheader}
{include file="Admin/modifymenu.tpl"}

<h4>{gt text="Variables"}{if $filename} &raquo; {$filename}{/if}</h4>

<div class="alert alert-info">{gt text='You can handle plain variables <var>name &rarr; value</var>, but also setup arrays in the format <var>name[key] &rarr; value</var>.'}</div>

<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_updatevariables'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="themename" value="{$themename|safetext}" />
        <input type="hidden" name="filename" value="{$filename|safetext}" />
        <table class="table table-bordered table-striped">
            <colgroup>
                <col id="cName" />
                <col id="cValue" />
            </colgroup>
            <thead>
                <tr>
                    <th id="hName" scope="col">{gt text='Name'}</th>
                    <th id="hValue" scope="col">{gt text='Value'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach key='name' item='value' from=$variables.variables}
                <tr>
                    <td headers="hName">
                        {if isset($variables.$name.editable)}
                        <input type="text" class="form-control" name="variablesnames[{$name|safetext}]" value="{$name|safetext}" />
                        {else}
                        <input type="hidden" name="variablesnames[{$name|safetext}]" value="{$name|safetext}" />
                        {if isset($variables.$name.language)}
                        {$variables.$name.language}
                        {else}
                        {$name|safetext}
                        {/if}
                        {/if}
                    </td>
                    <td headers="hValue">
                        {if $variables.$name.type|default:'' eq 'yesno'}
                        <input type="radio" name="variablesvalues[{$name|safetext}]" value="1"{if $value eq 1} checked="checked"{/if} />&nbsp;{gt text="Yes"}&nbsp;
                        <input type="radio" name="variablesvalues[{$name|safetext}]" value="0"{if $value eq 0} checked="checked"{/if} />&nbsp;{gt text="No"}
                        {elseif $variables.$name.type|default:'' eq 'readonly'}
                        <input type="text" class="form-control" name="variablesvalues[{$name|safetext}]" value="{$value|safetext}" readonly />
                        {elseif $variables.$name.type|default:'' eq 'select'}
                        {html_options class="form-control" name=variablesvalues[$name] values=$variables.$name.values output=$variables.$name.output selected=$value}
                        {else}
                        <input type="text" class="form-control" name="variablesvalues[{$name|safetext}]" value="{$value|safetext}" />
                        {/if}
                    </td>
                </tr>
                {foreachelse}
                <tr class="table table-borderedempty"><td colspan="2">{gt text='No items found.'}</td></tr>
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
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_newvariablename">{gt text="Name"}</label>
                <div class="col-sm-9">
                    <input id="theme_newvariablename" type="text" class="form-control" name="newvariablename" size="30" />
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="theme_newvariablevalue">{gt text="Value"}</label>
                <div class="col-sm-9">
                    <input id="theme_newvariablevalue" type="text" class="form-control" name="newvariablevalue" size="30" />
                </div>
            </div>
        </fieldset>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{route name='zikulathememodule_admin_pageconfigurations' themename=$themename}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
