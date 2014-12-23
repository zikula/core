{* purpose of this template: module configuration *}
{include file='Admin/header.tpl'}
<div class="zikularoutesmodule-config">
    {gt text='Settings' assign='templateTitle'}
    {pagesetvar name='title' value=$templateTitle}
    <h3>
        <span class="fa fa-wrench"></span>
        {$templateTitle}
    </h3>

    {form cssClass='form-horizontal' role='form'}
        {* add validation summary and a <div> element for styling the form *}
        {zikularoutesmoduleFormFrame}
            {formsetinitialfocus inputId='moderationGroupForRoutes'}
            {gt text='Moderation' assign='tabTitle'}
            <fieldset>
                <legend>{$tabTitle}</legend>
            
                <p class="alert alert-info">{gt text='Here you can assign moderation groups for enhanced workflow actions.'|nl2br}</p>
            
                <div class="form-group">
                    {gt text='Used to determine moderator user accounts for sending email notifications.' assign='toolTip'}
                    {formlabel for='moderationGroupForRoutes' __text='Moderation group for routes' cssClass='zikularoutesmodule-form-tooltips  col-lg-3 control-label' title=$toolTip}
                    <div class="col-lg-9">
                        {formdropdownlist id='moderationGroupForRoutes' group='config' __title='Choose the moderation group for routes' cssClass='form-control'}
                    </div>
                </div>
            </fieldset>

            <div class="form-group form-buttons">
            <div class="col-lg-offset-3 col-lg-9">
                {formbutton commandName='save' __text='Update configuration' class='btn btn-success'}
                {formbutton commandName='cancel' __text='Cancel' class='btn btn-default'}
            </div>
            </div>
        {/zikularoutesmoduleFormFrame}
    {/form}
</div>
{include file='Admin/footer.tpl'}
<script type="text/javascript">
/* <![CDATA[ */
    ( function($) {
        $(document).ready(function() {
            $('.zikularoutesmodule-form-tooltips').tooltip();
        });
    })(jQuery);
/* ]]> */
</script>
