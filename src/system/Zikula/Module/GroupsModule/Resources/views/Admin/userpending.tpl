{gt text='Membership application' assign='templatetitle'}
{adminheader}
<h3>
    <span class="fa fa-plus"></span>
    {$templatetitle}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulagroupsmodule_admin_userupdate'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <input type="hidden" name="gid" value="{$gid|safetext}" />
        <input type="hidden" name="userid" value="{$userid|safetext}" />
        <input type="hidden" name="action" value="{$action|safetext}" />
        <input type="hidden" name="tag" value="1" />
        
        <fieldset>
            <legend>{$templatetitle}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text='User name'}</label>
                <div class="col-lg-9">
                    <span>{usergetvar name="uname" uid=$userid|safetext}</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text='Membership application'}</label>
                <div class="col-lg-9">
                    <span>{$application|safehtml}</span>
                </div>
            </div>
            {if $action eq 'deny'}
                <div class="form-group">
                    <label class="col-lg-3 control-label" for="groups_reason">{gt text='Reason'}</label>
                    <div class="col-lg-9">
                        <textarea class="form-control" id="groups_reason" name="reason" cols="50" rows="8">{gt text='Sorry! This is a message to inform you with regret that your application for membership of the aforementioned private group has been rejected.'}</textarea>
                    </div>
                </div>
            {/if}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_sendtag">{gt text='Notification type'}</label>
                <div class="col-lg-9">
                    <select class="form-control" id="groups_sendtag" name="sendtag">
                        {html_options options=$sendoptions}
                    </select>
                </div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
            {if $action eq 'deny'}
            <button type="submit">{img modname='core' src='14_layer_deletelayer.png' set='icons/extrasmall' __alt='Deny' __title='Deny'} {gt text='Deny'}</button>
            {else}
            <button type="submit" class="btn btn-success" title="{gt text='Accept'}">{gt text="Accept"}</button>
            {/if}
            </div>
        </div>
    </div>
</form>
{adminfooter}
