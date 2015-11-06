{* purpose of this template: build the Form to edit an instance of route *}
{assign var='lct' value='user'}
{if isset($smarty.get.lct) && $smarty.get.lct eq 'admin'}
    {assign var='lct' value='admin'}
{/if}
{assign var='lctUc' value=$lct|ucfirst}
{include file="`$lctUc`/header.tpl"}
{pageaddvar name='javascript' value='@ZikulaRoutesModule/Resources/public/js/ZikulaRoutesModule.EditFunctions.js'}
{pageaddvar name='javascript' value='@ZikulaRoutesModule/Resources/public/js/ZikulaRoutesModule.Validation.js'}

{if $mode eq 'edit'}
    {gt text='Edit route' assign='templateTitle'}
    {if $lct eq 'admin'}
        {assign var='adminPageIcon' value='pencil-square-o'}
    {/if}
{elseif $mode eq 'create'}
    {gt text='Create route' assign='templateTitle'}
    {if $lct eq 'admin'}
        {assign var='adminPageIcon' value='plus'}
    {/if}
{else}
    {gt text='Edit route' assign='templateTitle'}
    {if $lct eq 'admin'}
        {assign var='adminPageIcon' value='pencil-square-o'}
    {/if}
{/if}
<div class="zikularoutesmodule-route zikularoutesmodule-edit">
    {pagesetvar name='title' value=$templateTitle}
    {if $lct eq 'admin'}
        <h3>
            <span class="fa fa-{$adminPageIcon}"></span>
            {$templateTitle}
        </h3>
    {else}
        <h2>{$templateTitle}</h2>
    {/if}
{form cssClass='form-horizontal' role='form'}
    {* add validation summary and a <div> element for styling the form *}
    {zikularoutesmoduleFormFrame}
    {formsetinitialfocus inputId='routeType' doSelect=true}

    <fieldset>
        <legend>{gt text='Content'}</legend>
        
        <div class="form-group">
            {formlabel for='routeType' __text='Route type' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formdropdownlist group='route' id='routeType' mandatory=true __title='Choose the route type' selectionMode='single' cssClass='form-control'}
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='replacedRouteName' __text='Replaced route name' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='replacedRouteName' mandatory=false readOnly=false __title='Enter the replaced route name of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='bundle' __text='Bundle' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='bundle' mandatory=true readOnly=false __title='Enter the bundle of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='controller' __text='Controller' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='controller' mandatory=true readOnly=false __title='Enter the controller of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='action' __text='Action' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='action' mandatory=true readOnly=false __title='Enter the action of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='path' __text='Path' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='path' mandatory=true readOnly=false __title='Enter the path of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='host' __text='Host' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='host' mandatory=false readOnly=false __title='Enter the host of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='schemes' __text='Schemes' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formdropdownlist group='route' id='schemes' mandatory=true __title='Choose the schemes' selectionMode='multiple' cssClass='form-control'}
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='methods' __text='Methods' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formdropdownlist group='route' id='methods' mandatory=true __title='Choose the methods' selectionMode='multiple' cssClass='form-control'}
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='prependBundlePrefix' __text='Prepend bundle prefix' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formcheckbox group='route' id='prependBundlePrefix' readOnly=false __title='prepend bundle prefix ?' cssClass='required' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='translatable' __text='Translatable' mandatorysym='1' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formcheckbox group='route' id='translatable' readOnly=false __title='translatable ?' cssClass='required' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='translationPrefix' __text='Translation prefix' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='translationPrefix' mandatory=false readOnly=false __title='Enter the translation prefix of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='condition' __text='Condition' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='condition' mandatory=false readOnly=false __title='Enter the condition of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='description' __text='Description' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='description' mandatory=false readOnly=false __title='Enter the description of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='sort' __text='Sort' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formintinput group='route' id='sort' mandatory=false __title='Enter the sort of the route' maxLength=11 cssClass='form-control  validate-digits' }
            </div>
        </div>
        
        <div class="form-group">
            {formlabel for='group' __text='Group' cssClass=' col-sm-3 control-label'}
            <div class="col-sm-9">
            {formtextinput group='route' id='group' mandatory=false readOnly=false __title='Enter the group of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
            </div>
        </div>
    </fieldset>
    
    {if $mode ne 'create'}
        {include file='Helper/include_standardfields_edit.tpl' obj=$route}
    {/if}
    
    {* include display hooks *}
    {if $mode ne 'create'}
        {assign var='hookId' value=$route.id}
        {notifydisplayhooks eventname='zikularoutesmodule.ui_hooks.routes.form_edit' id=$hookId assign='hooks'}
    {else}
        {notifydisplayhooks eventname='zikularoutesmodule.ui_hooks.routes.form_edit' id=null assign='hooks'}
    {/if}
    {if is_array($hooks) && count($hooks)}
        {foreach name='hookLoop' key='providerArea' item='hook' from=$hooks}
            {if $providerArea ne 'provider.scribite.ui_hooks.editor'}{* fix for #664 *}
                <fieldset>
                    {$hook}
                </fieldset>
            {/if}
        {/foreach}
    {/if}
    
    
    {* include return control *}
    {if $mode eq 'create'}
        <fieldset>
            <legend>{gt text='Return control'}</legend>
            <div class="form-group">
                {formlabel for='repeatCreation' __text='Create another item after save' cssClass='col-sm-3 control-label'}
            <div class="col-sm-9">
                    {formcheckbox group='route' id='repeatCreation' readOnly=false}
            </div>
            </div>
        </fieldset>
    {/if}
    
    {* include possible submit actions *}
    <div class="form-group form-buttons">
    <div class="col-sm-offset-3 col-sm-9">
        {foreach item='action' from=$actions}
            {assign var='actionIdCapital' value=$action.id|@ucfirst}
            {gt text=$action.title assign='actionTitle'}
            {*gt text=$action.description assign='actionDescription'*}{* TODO: formbutton could support title attributes *}
            {if $action.id eq 'delete'}
                {gt text='Really delete this route?' assign='deleteConfirmMsg'}
                {formbutton id="btn`$actionIdCapital`" commandName=$action.id text=$actionTitle class=$action.buttonClass confirmMessage=$deleteConfirmMsg}
            {else}
                {formbutton id="btn`$actionIdCapital`" commandName=$action.id text=$actionTitle class=$action.buttonClass}
            {/if}
        {/foreach}
        {formbutton id='btnCancel' commandName='cancel' __text='Cancel' class='btn btn-default' formnovalidate='formnovalidate'}
    </div>
    </div>
    {/zikularoutesmoduleFormFrame}
{/form}
</div>
{include file="`$lctUc`/footer.tpl"}

{assign var='editImage' value='<span class="fa fa-pencil-square-o"></span>'}
{assign var='deleteImage' value='<span class="fa fa-trash-o"></span>'}


<script type="text/javascript">
/* <![CDATA[ */
    
            var formButtons;
    
            function executeCustomValidationConstraints()
            {
                zikulaRoutesPerformCustomValidationRules('route', '{{if $mode ne 'create'}}{{$route.id}}{{/if}}');
            }
    
            function triggerFormValidation()
            {
                executeCustomValidationConstraints();
                if (!document.getElementById('{{$__formid}}').checkValidity()) {
                    // This does not really submit the form,
                    // but causes the browser to display the error message
                    jQuery('#{{$__formid}}').find(':submit').not(jQuery('#btnDelete')).click();
                }
            }
    
            function handleFormSubmit (event) {
                triggerFormValidation();
                if (!document.getElementById('{{$__formid}}').checkValidity()) {
                    event.preventDefault();
                    return false;
                }
    
                // hide form buttons to prevent double submits by accident
                formButtons.each(function (index) {
                    jQuery(this).addClass('hidden');
                });
    
                return true;
            }
    
            ( function($) {
                $(document).ready(function() {
    
                    var allFormFields = $('#{{$__formid}} input, #{{$__formid}} select, #{{$__formid}} textarea');
                    allFormFields.change(executeCustomValidationConstraints);
    
                    formButtons = $('#{{$__formid}} .form-buttons input');
                    $('#{{$__formid}}').submit(handleFormSubmit);
    
                    {{if $mode ne 'create'}}
                        triggerFormValidation();
                    {{/if}}
    
                    $('#{{$__formid}} label').tooltip();
                });
            })(jQuery);
/* ]]> */
</script>
