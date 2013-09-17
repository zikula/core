{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="delete" size="small"}
    <h3>{$blockname|safetext}</h3>
</div>

<p class="alert alert-warning">{gt text="Do you really want to delete block '%s'?" tag1=$block.title|safetext}</p>

<form class="form-horizontal" role="form" action="{modurl modname="Blocks" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
    <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" name="confirmation" value="1" />
    <input type="hidden" name="bid" value="{$block.bid|safetext}" />
    <fieldset>
        <legend>{gt text="Confirmation prompt"}</legend>
        <div class="form-group">
            <div class="col-lg-offset-3 col-lg-9">
                <button class="btn btn-success" title="{gt text='Delete'}">
                    {gt text='Delete'}
                </button>
                <a class="btn btn-danger" href="{modurl modname=Blocks type=admin func=view}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
            </div>
        </div>
    </fieldset>
</form>
{adminfooter}