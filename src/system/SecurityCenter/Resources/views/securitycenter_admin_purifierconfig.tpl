{modulejavascript modname='SecurityCenter' script='securitycenter_admin_purifierconfig.js' modonly=true assign='configFormJS'}
{pageaddvar name='javascript' value=$configFormJS.scriptfile}
{adminheader}

<div class="z-admin-content-pagetitle">
    {icon type="options" size="small"}
    <h3>{gt text='HTMLPurifier Settings'}</h3>
</div>

<p class="z-informationmsg">{gt text='HTMLPurifier filtering occurs when a template string or variable is modified with the \'safehtml\' modifier, or when a module asks for similar processing from within its functions.'}</p>

<div class="z-warningmsg">
    <h4>{gt text="Warning"}</h4>
    <p>{gt text="Setting HTMLPurifier configuration directives incorrectly can render your system unstable and inacessible. No validity checking is performed on any user-supplied settings. Ensure you fully understand each directive and its effects on your system. Ensure that all prerequisites are met and that any additional software or libraries required by each directive are properly installed and available to HTMLPurifier."}</p>
    <p><a href="{modurl modname='SecurityCenter' type='admin' func='allowedhtml'}">{gt text="Allowed HTML settings"}</a> {gt text="will be applied after HTMLPurifier processing is completed."}</p>
</div>

<form class="z-form htmlpure" action="{modurl modname='SecurityCenter' type='admin' func='updatepurifierconfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
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
            <div class="z-formrow">
                <label for="purifierConfig_div_{$directive.key}">{$directiveName|safetext} <a href="http://htmlpurifier.org/live/configdoc/plain.html#{$directive.key|urlencode}">(?)</a></label>
                <div id="purifierConfig_div_{$directive.key}">
                    <input id="purifierConfig_Null_{$directive.key}" name="purifierConfig[Null_{$directive.key}]" type="checkbox" value="1"{if is_null($directive.value)} checked="checked"{/if} onclick="{if ($directive.type != $purifierTypes.bool)}toggleWriteability('{$idVal}', checked);{else}toggleWriteability('{$idVal}_Yes', checked); toggleWriteability('{$idVal}_No', checked);{/if}" />
                    <label for="purifierConfig_Null_{$directive.key}">{gt text='Use default value (if checked) or override value'}</label>
                </div>
            </div>
            <div class="z-formrow">
                <label for="{$idVal}">&nbsp;</label>
                {else}
                <div class="z-formrow">
                    <label for="{$idVal}">{$directiveName|safetext} <a href="http://htmlpurifier.org/live/configdoc/plain.html#{$directive.key|urlencode}">(?)</a></label>
                    {/if}

                    {if is_null($directive.value)}{assign var='disabledVal' value=' disabled="disabled"'}{else}{assign var='disabledVal' value=''}{/if}

                    {if isset($directive.allowedValues)}
                    <select id="{$idVal}" name="{$nameVal}"{$disabledVal} style="min-width: 5em;">
                        {foreach from=$directive.allowedValues item='allowedVal'}
                        <option value="{$allowedVal}"{if ($directive.value == $allowedVal)} selected="selected"{/if}>{$allowedVal|safetext}</option>
                        {/foreach}
                    </select>
                    {elseif (($directive.type == $purifierTypes.text) || ($directive.type == $purifierTypes.itext) || ($directive.type == $purifierTypes.list) || ($directive.type == $purifierTypes.hash) || ($directive.type == $purifierTypes.lookup))}
                    <textarea id="{$idVal}" name="{$nameVal}" cols="50" rows="8"{$disabledVal}>{$directive.value|safetext}</textarea>

                    {if (($directive.type == $purifierTypes.list) || ($directive.type == $purifierTypes.lookup))}
                    <em class="z-formnote z-sub">{gt text='(Place each value on a separate line.)'}</em>
                    {elseif ($directive.type == $purifierTypes.hash)}
                    <em class="z-formnote z-sub">{gt text='(Separate each key-value pair with a colon (e.g., key:value). Place each key-value pair on a separate line.)'}</em>
                    {/if}

                    {elseif (($directive.type == $purifierTypes.string) || ($directive.type == $purifierTypes.istring) || ($directive.type == $purifierTypes.int) || ($directive.type == $purifierTypes.float))}
                    <input id="{$idVal}" name="{$nameVal}" type="text" value="{$directive.value}"{$disabledVal} />
                    {elseif ($directive.type == $purifierTypes.bool)}
                    <div id="{$idVal}">
                        <input id="{$idVal}_Yes" name="{$nameVal}" type="radio" value="1"{if $directive.value === true} checked="checked"{/if}{$disabledVal} />
                        <label for="{$idVal}_Yes">{gt text='Yes'}</label>
                        <input id="{$idVal}_No" name="{$nameVal}" type="radio" value="0"{if $directive.value === false} checked="checked"{/if}{$disabledVal} />
                        <label for="{$idVal}_No">{gt text='No'}</label>
                    </div>
                    {else}
                    <em class="z-formnote z-sub">{gt text='(Modification not supported.)'} {gt text='Value:'} {$directive.value|serialize|safetext}</em>
                    {/if}
                </div>
                {/if}
                {/foreach}
            </fieldset>
            {/foreach}

            <div class="z-buttons z-formbuttons">
                {button src='button_ok.png' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
                <a href="{modurl modname='SecurityCenter' type='admin' func="main"}" title="{gt text="Cancel"}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text="Cancel"}</a>
                <a href="{modurl modname='SecurityCenter' type='admin' func='purifierconfig' reset='default'}" title="{gt text="Reset to Default Values"}">{img modname='core' src='reload.png' set='icons/extrasmall' __alt='Reset to Default Values' __title='Reset to Default Values'} {gt text="Reset to Default Values"}</a>
            </div>
        </div>
    </form>
    {adminfooter}