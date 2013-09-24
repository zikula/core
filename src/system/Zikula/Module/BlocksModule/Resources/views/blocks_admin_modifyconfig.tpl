{adminheader}
<h3>
    <span class="icon icon-wrench"></span>
    {gt text="Settings"}
</h3>

<form class="form-horizontal" role="form" action="{modurl modname="Blocks" type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="General settings"}</legend>
            <div class="form-group">
                <label class="col-lg-3 control-label" for="blocks_collapseable">{gt text="Enable menu collapse icons"}</label>
                <div class="col-lg-9">
                {if $collapseable eq 1}
                <input id="blocks_collapseable" name="collapseable" type="checkbox" value="1" checked="checked" />
                {else}
                <input id="blocks_collapseable" name="collapseable" type="checkbox" value="1" />
                {/if}
            </div>
        </div>
        </fieldset>

        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text="Save"}">{gt text="Save"}</button>
                <a class="btn btn-danger" href="{modurl modname=Blocks type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}