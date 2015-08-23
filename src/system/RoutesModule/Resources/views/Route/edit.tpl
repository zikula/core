{* purpose of this template: build the Form to edit an instance of route *}
{pageaddvar name='javascript' value='jquery'}
{pageaddvarblock}
    <script type="text/javascript">
        (function($) {
            $(function(){
                function updatePathPrefix() {
                    var i18n = $('#i18n').prop('checked');
                    var bundlePrefix = $('#bundlePrefix').prop('checked');
                    var baseUrl = {{$baseurl|rtrim:'/'|json_encode}};
                    var moduleUrlNames = {{$moduleUrlNames|@json_encode}};

                    if (bundlePrefix) {
                        var bundle = $('#bundle').val();
                        bundlePrefix = "/" + moduleUrlNames[bundle];
                    } else {
                        bundlePrefix = "";
                    }
                    if (i18n) {
                        i18n = "/{" + {{gt text='lang' assign='gt'}}{{$gt|json_encode}} + "}";
                    } else {
                        i18n = "";
                    }

                    $('#pathPrefix').text(baseUrl + i18n + bundlePrefix);
                }

                updatePathPrefix();
                $('#i18n').click(updatePathPrefix);
                $('#bundlePrefix').click(updatePathPrefix);
                $('#bundle').change(updatePathPrefix);
            });
        })(jQuery)
    </script>
{/pageaddvarblock}

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
            <span class="icon icon-{$adminPageIcon}"></span>
            {$templateTitle}
        </h3>
    {else}
        <h2>{$templateTitle}</h2>
    {/if}
{form cssClass='form-horizontal' role='form'}
    {* add validation summary and a <div> element for styling the form *}
    {zikularoutesmoduleFormFrame}
    {formsetinitialfocus inputId='bundle'}

    <fieldset>
        <legend>{gt text='Content'}</legend>

        <div>
            {formtextinput group='route' id='name' mandatory=false readOnly=false textMode='hidden' maxLength=255}
        </div>

        {*<div class="form-group">
            {formlabel for='name' __text='Name' mandatorysym='1' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
                {formtextinput group='route' id='name' mandatory=true readOnly=false __title='Enter the name of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
            </div>
        </div>*}

        <div class="form-group">
            {formlabel for='bundle' __text='Bundle' mandatorysym='1' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
                {*formtextinput group='route' id='bundle' mandatory=true readOnly=false __title='Enter the bundle of the route' textMode='singleline' maxLength=255 cssClass='form-control required' *}
                {formdropdownlist items=$modules group='route' id='bundle' mandatory=true readOnly=false __title='Enter the bundle of the route' cssClass='form-control required' }
            </div>
        </div>

        <div class="form-group">
            {formlabel for='controller' __text='Controller' mandatorysym='1' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
                {formtextinput group='route' id='controller' mandatory=true readOnly=false __title='Enter the controller of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
                <em class="z-sub">{gt text='Insert the name of the controller, which was called "type" in earlier versions of Zikula. Example: "UserController"'}</em>
            </div>
        </div>

        <div class="form-group">
            {formlabel for='action' __text='Action' mandatorysym='1' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
                {formtextinput group='route' id='action' mandatory=true readOnly=false __title='Enter the action of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
                <em class="z-sub">{gt text='Insert the name of the action, which was called "func" in earlier versions of Zikula. Example: "EditAction"'}</em>
            </div>
        </div>

        <div class="form-group">
            {formlabel for='i18n' __text='Translatable' cssClass=' col-lg-3 control-label' mandatorysym=false}
            <div class="col-lg-9">
                {formcheckbox group='route' id='i18n' checked=true mandatory=false readOnly=false __title='Decide whether or not the route is translatable'}
            </div>
        </div>
        <div class="form-group">
            {formlabel for='bundlePrefix' __text='Prepend bundle prefix' cssClass=' col-lg-3 control-label' mandatorysym=false}
            <div class="col-lg-9">
                {formcheckbox group='route' id='bundlePrefix' checked=true mandatory=false readOnly=false __title='Decide whether or not to prepend the bundle prefix to the path'}
            </div>
        </div>

        <div class="form-group">
            {formlabel for='path' __text='Path' cssClass=' col-lg-3 control-label' mandatorysym=true}
            <div class="col-lg-9">
                <div class="input-group">
                    <span class="input-group-addon" id="pathPrefix"></span>
                    {formtextinput group='route' id='path' mandatory=true readOnly=false __title='Enter the path of the route' textMode='singleline' maxLength=255 cssClass='form-control required' }
                </div>
                <em class="z-sub">{gt text='The path must start with a "/" and can be a regular expression. Example: "/login"'}</em>
            </div>
        </div>

        <div class="form-group">
            {formlabel for='host' __text='Host' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
            {formtextinput group='route' id='host' mandatory=false readOnly=false __title='Enter the host of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
                <em class="z-sub">{gt text='Advanced setting, see %s' tag1='<a href="http://symfony.com/doc/current/components/routing/hostname_pattern.html">http://symfony.com/doc/current/components/routing/hostname_pattern.html</a>'}</em>
            </div>
        </div>

        <div class="form-group">
            {formlabel for='condition' __text='Condition' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
            {formtextinput group='route' id='condition' mandatory=false readOnly=false __title='Enter the condition of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
                <em class="z-sub">{gt text='Advanced setting, see %s' tag1='<a href="http://symfony.com/doc/current/book/routing.html#completely-customized-route-matching-with-conditions">http://symfony.com/doc/current/book/routing.html#completely-customized-route-matching-with-conditions</a>'}</em>
            </div>
        </div>

        <div class="form-group">
            {formlabel for='description' __text='Description' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
            {formtextinput group='route' id='description' mandatory=false readOnly=false __title='Enter the description of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
                <em class="z-sub">{gt text='Insert a brief description of the route, to explain why you created it. It is only shown in the admin interface.'}</em>
            </div>
        </div>

        {*<div class="form-group">
            {formlabel for='userRoute' __text='User route' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
                {formcheckbox group='route' id='userRoute' readOnly=false __title='user route ?' cssClass='' }
            </div>
        </div>

        <div class="form-group">
            {formlabel for='sort' __text='Sort' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
            {formintinput group='route' id='sort' mandatory=false __title='Enter the sort of the route' maxLength=11 cssClass='form-control  validate-digits' }
            </div>
        </div>

        <div class="form-group">
            {formlabel for='group' __text='Group' cssClass=' col-lg-3 control-label'}
            <div class="col-lg-9">
            {formtextinput group='route' id='group' mandatory=false readOnly=false __title='Enter the group of the route' textMode='singleline' maxLength=255 cssClass='form-control ' }
            </div>
        </div>*}
    </fieldset>

    {if $mode ne 'create'}
        {include file='Helper/include_standardfields_edit.tpl' obj=$route}
    {/if}

    {* include display hooks *}
    {if $mode ne 'create'}
        {assign var='hookId' value=$route.id}
        {notifydisplayhooks eventname='routes.ui_hooks.routes.form_edit' id=$hookId assign='hooks'}
    {else}
        {notifydisplayhooks eventname='routes.ui_hooks.routes.form_edit' id=null assign='hooks'}
    {/if}
    {if is_array($hooks) && count($hooks)}
        {foreach name='hookLoop' key='providerArea' item='hook' from=$hooks}
            <fieldset>
                {$hook}
            </fieldset>
        {/foreach}
    {/if}

    {* include return control *}
    {if $mode eq 'create'}
        <fieldset>
            <legend>{gt text='Return control'}</legend>
            <div class="form-group">
                {formlabel for='repeatCreation' __text='Create another item after save' cssClass='col-lg-3 control-label'}
            <div class="col-lg-9">
                    {formcheckbox group='route' id='repeatCreation' readOnly=false}
            </div>
            </div>
        </fieldset>
    {/if}

    {* include possible submit actions *}
    <div class="form-group form-buttons">
    <div class="col-lg-offset-3 col-lg-9">
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
        {formbutton id='btnCancel' commandName='cancel' __text='Cancel' class='btn btn-default'}
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

            function handleFormButton (event) {
                zikulaRoutesPerformCustomValidationRules('route', '{{if $mode ne 'create'}}{{$route.id}}{{/if}}');
                var result = document.getElementById('{{$__formid}}').checkValidity();
                if (!result) {
                    // validation error, abort form submit
                    event.stopPropagation();
                } else {
                    // hide form buttons to prevent double submits by accident
                    formButtons.each(function (btn) {
                        btn.addClass('hidden');
                    });
                }

                return result;
            }

            ( function($) {
                $(document).ready(function() {

                    {{* observe validation on button events instead of form submit to exclude the cancel command *}}
                    {{if $mode ne 'create'}}
                        if (!document.getElementById('{{$__formid}}').checkValidity()) {
                            document.getElementById('{{$__formid}}').submit();
                        }
                    {{/if}}

                    formButtons = $('#{{$__formid}}').find('div.form-buttons input');

                    formButtons.each(function (elem) {
                        if (elem.attr('id') != 'btnCancel') {
                            elem.click(handleFormButton);
                        }
                    });

                    $('.zikularoutesmodule-form-tooltips').tooltip();
                });
            })(jQuery);
/* ]]> */
</script>
