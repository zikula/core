{modulejavascript modname='ZikulaSecurityCenterModule' script='securitycenter_admin_purifierconfig.js' modonly=true assign='configFormJS'}
{pageaddvar name='javascript' value=$configFormJS.scriptfile}
{adminheader}

<div class="z-admin-content-pagetitle">
    {icon type="options" size="small"}
    <h3>{gt text='HTMLPurifier Settings'}</h3>
</div>

<p class="alert alert-info">{gt text='HTMLPurifier filtering occurs when a template string or variable is modified with the \'safehtml\' modifier, or when a module asks for similar processing from within its functions.'}</p>

<div class="alert alert-warning">
    <h4>{gt text="Warning"}</h4>
    <p>{gt text="Setting HTMLPurifier configuration directives incorrectly can render your system unstable and inacessible. No validity checking is performed on any user-supplied settings. Ensure you fully understand each directive and its effects on your system. Ensure that all prerequisites are met and that any additional software or libraries required by each directive are properly installed and available to HTMLPurifier."}</p>
    <p><a href="{modurl modname='ZikulaSecurityCenterModule' type='admin' func='allowedhtml'}">{gt text="Allowed HTML settings"}</a> {gt text="will be applied after HTMLPurifier processing is completed."}</p>
</div>

<form class="form-horizontal" action="{modurl modname='ZikulaSecurityCenterModule' type='admin' func='updatepurifierconfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
    {foreach from=$purifierAllowed key='directiveNamespace' item='directives' name='directives'}
    <fieldset>
        {assign var='namespaceFrag' value=$directiveNamespace|urlencode}
        {assign var='namespaceLink' value='<a href="http://htmlpurifier.org/live/configdoc/plain.html#'|cat:$namespaceFrag|cat:'">'|cat:$directiveNamespace|cat:'</a>'}
        <legend>{gt text='HTMLPurifier \'%s\' configuration directives' tag1=$namespaceLink}</legend>
        {foreach from=$directives key='directiveName' item='directive'}
        {if ($directive.supported)}
        {assign var='idVal' value='purifierConfig_'|cat:$directive.key}
        {assign var='nameVal' value='purifierConfig['|cat:$directive.key|cat:']'}

        {if $directive.allowNull}
        <div class="form-group">
            <label class="col-lg-3 control-label" for="purifierConfig_div_{$directive.key}">
                {$directiveName|safetext} <a href="http://htmlpurifier.org/live/configdoc/plain.html#{$directive.key|urlencode}">(?)</a>
            </label>
            <div class="col-lg-9">
                <div id="purifierConfig_div_{$directive.key}">
                    <input id="purifierConfig_Null_{$directive.key}" name="purifierConfig[Null_{$directive.key}]" type="checkbox" value="1"{if is_null($directive.value)} checked="checked"{/if} onclick="{if ($directive.type != $purifierTypes.bool)}toggleWriteability('{$idVal}', checked);{else}toggleWriteability('{$idVal}_Yes', checked); toggleWriteability('{$idVal}_No', checked);{/if}" />
                    <label for="purifierConfig_Null_{$directive.key}">{gt text='Use default value (if checked) or override value'}</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-lg-3 control-label" for="{$idVal}">&nbsp;</label>
        {else}
        <div class="form-group">
            <label class="col-lg-3 control-label" for="{$idVal}">{$directiveName|safetext} <a href="http://htmlpurifier.org/live/configdoc/plain.html#{$directive.key|urlencode}">(?)</a></label>
        {/if}

            {if is_null($directive.value)}{assign var='disabledVal' value=' disabled="disabled"'}{else}{assign var='disabledVal' value=''}{/if}

            {if isset($directive.allowedValues)}
            <div class="col-lg-9">
                <select id="{$idVal}" class="form-control" name="{$nameVal}"{$disabledVal} style="min-width: 5em;">
                    {foreach from=$directive.allowedValues item='allowedVal'}
                    <option value="{$allowedVal}"{if ($directive.value == $allowedVal)} selected="selected"{/if}>{$allowedVal|safetext}</option>
                    {/foreach}
                </select>
            </div>
            {elseif (($directive.type == $purifierTypes.text) || ($directive.type == $purifierTypes.itext) || ($directive.type == $purifierTypes.list) || ($directive.type == $purifierTypes.hash) || ($directive.type == $purifierTypes.lookup))}
            <div class="col-lg-9">
                <textarea id="{$idVal}" class="form-control" name="{$nameVal}" cols="50" rows="8"{$disabledVal}>{$directive.value|safetext}</textarea>

                {if (($directive.type == $purifierTypes.list) || ($directive.type == $purifierTypes.lookup))}
                <em class="help-block sub">{gt text='(Place each value on a separate line.)'}</em>
                {elseif ($directive.type == $purifierTypes.hash)}
                <em class="help-block sub">{gt text='(Separate each key-value pair with a colon (e.g., key:value). Place each key-value pair on a separate line.)'}</em>
                {/if}
            </div>
            {elseif (($directive.type == $purifierTypes.string) || ($directive.type == $purifierTypes.istring) || ($directive.type == $purifierTypes.int) || ($directive.type == $purifierTypes.float))}
            <div class="col-lg-9">  
                <input id="{$idVal}" name="{$nameVal}" class="form-control" type="text" value="{$directive.value}"{$disabledVal} />
            </div>
            {elseif ($directive.type == $purifierTypes.bool)}
             <div id="{$idVal}" class="col-lg-9"> 
                <input id="{$idVal}_Yes" name="{$nameVal}" type="radio" value="1"{if $directive.value === true} checked="checked"{/if}{$disabledVal} />
                <label for="{$idVal}_Yes">{gt text='Yes'}</label>
                <input id="{$idVal}_No" name="{$nameVal}" type="radio" value="0"{if $directive.value === false} checked="checked"{/if}{$disabledVal} />
                <label for="{$idVal}_No">{gt text='No'}</label>
            </div>
            {else}
            <div class="col-lg-9">
                <em class="help-block sub">{gt text='(Modification not supported.)'} {gt text='Value:'} {$directive.value|serialize|safetext}</em>
            </div>
            {/if}
        {/if}
        </div>
    {/foreach}
    </fieldset>
    {/foreach}

    <div class="form-group">
        <div class="col-lg-offset-3 col-lg-9">
            <button class="btn btn-success" title="{gt text='Save'}">
                {gt text='Save'}
            </button>
            <a class="btn btn-danger" href="{modurl modname='ZikulaSecurityCenterModule' type='admin' func="main"}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            <a class="btn btn-danger" href="{modurl modname='ZikulaSecurityCenterModule' type='admin' func='purifierconfig' reset='default'}" title="{gt text="Reset to Default Values"}">{gt text="Reset to Default Values"}</a>
        </div>
    </div>
</form>
{adminfooter}