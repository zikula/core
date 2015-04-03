{adminheader}
<h3>
    <span class="fa fa-pencil"></span>
    {gt text="Theme confirmation prompt"}
</h3>

<p class="alert alert-warning">
    {gt text="Do you really want to set '%s' as the active theme for all site users?" tag1=$themename|safetext}
</p>
<form class="form-horizontal" role="form" action="{route name='zikulathememodule_admin_setasdefault' themename=$themename|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset>
        <legend>{gt text="Confirmation prompt"}</legend>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <div class="form-group">
            <label class="col-sm-3 control-label" for="themeswitcher_theme_change">
                {gt text="Override users' theme settings"}
            </label>
            <div class="col-sm-9">
                <input id="themeswitcher_theme_change" name="resetuserselected" type="checkbox" value="1" />
                {if $theme_change}
                    <em class="help-block">{gt text='Users are allowed to change their own theme.'}</em>
                {else}
                    <em class="help-block">{gt text='Users are not currently allowed to change their own theme, but may have previously changed them.'}</em>
                {/if}
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Accept'}">
                    {gt text="Accept"}
                </button>
                <a class="btn btn-danger" href="{route name='zikulathememodule_admin_view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </fieldset>
</form>
{adminfooter}