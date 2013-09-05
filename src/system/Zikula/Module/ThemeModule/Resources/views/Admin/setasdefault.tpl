{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{gt text="Theme confirmation prompt"}</h3>
</div>

<p class="alert alert-warning">{gt text="Do you really want to set '%s' as the active theme for all site users?" tag1=$themename|safetext}</p>
<form class="form-horizontal" role="form" action="{modurl modname="Theme" type="admin" func="setasdefault" themename=$themename|safetext}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            {if $theme_change}
            <div class="form-group">
                <label class="col-lg-3 control-label" for="themeswitcher_theme_change">{gt text="Override users' theme settings"}</label>
                <div class="col-lg-9">
                <input id="themeswitcher_theme_change" name="resetuserselected" type="checkbox" value="1"  />
            </div>
            {/if}
            <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                    {button class="z-btgreen" class="btn btn-success" __alt="Accept" __title="Accept" __text="Accept"}
                    <a class="btn btn-danger" class="z-btred" href="{modurl modname=Theme type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
                </div>
        </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}