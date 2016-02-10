{* @deprecated at Core-2.0 - do not convert to twig *}
<div class='alert alert-warning help-block'>
    {gt text="Notice: The template file will be sought on all the template paths of the specified module." domain='zikula'}
    {foreach from=$warnings item='warning'}
        <hr />{$warning}
    {/foreach}
</div>
<div class="form-group">
    <label class="col-sm-3 control-label" for="rmodule">{gt text="Module" domain='zikula'}</label>
    <div class="col-sm-9">
    {html_select_modules id='rmodule' name='rmodule' capability='user' selected=$module|default:''}
</div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label" for="rtemplate">{gt text="Template file" domain='zikula'}</label>
    <div class="col-sm-9">
    <input id="rtemplate" value="{$template|default:''|safetext}" maxlength="100" size="40" name="rtemplate" type="text" />
</div>
</div>
<div class="form-group">
    <label class="col-sm-3 control-label" for="rparameters">{gt text="Parameters" domain='zikula'}</label>
    <div class="col-sm-9">
    <input id="rtemplate" value="{$parameters|default:''|safetext}" maxlength="300" size="40" name="rparameters" type="text" />
    <span class='help-block sub'>{gt text="Format: parameter1=value1;parameter2=value2..." domain='zikula'}</span>
</div>
