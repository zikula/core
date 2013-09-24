{* Do not allow editing of primaryadmingroup. For now it is read-only. *}
{adminheader}

<h3>
    <span class="icon icon-wrench"></span>
    {gt text="Settings"}
</h3>

<form class="form-horizontal" role="form" action="{modurl modname="Groups" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name="csrftoken"}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_itemsperpage">{gt text="Items per page"}</label>
                <div class="col-lg-9">
                <input id="groups_itemsperpage" type="text" class="form-control" name="itemsperpage" size="3" value="{$modvars.ZikulaGroupsModule.itemsperpage|safetext}" />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_defaultgroup">{gt text="Initial user group"}</label>
                <div class="col-lg-9">
                <select class="form-control" id="groups_defaultgroup" name="defaultgroup">
                    {html_options options=$groups selected=$modvars.ZikulaGroupsModule.defaultgroup}
                </select>
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_hideclosed">{gt text="Hide closed groups"}</label>
                <div class="col-lg-9">
                <input id="groups_hideclosed" name="hideclosed" type="checkbox"{if $modvars.ZikulaGroupsModule.hideclosed eq 1} checked="checked"{/if} />
            </div>
            </div>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="groups_mailwarning">{gt text="Receive e-mail alert when there are new applicants"}</label>
                <div class="col-lg-9">
                <input id="groups_mailwarning" name="mailwarning" type="checkbox"{if $modvars.ZikulaGroupsModule.mailwarning eq 1} checked="checked"{/if} />
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{modurl modname='ZikulaGroupsModule' type='admin' func='view'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
