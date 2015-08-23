{adminheader}
<h3>
    <span class="fa fa-wrench"></span>
    {gt text='Settings'}
</h3>

<form class="form-horizontal" role="form" action="{route name='zikulablocksmodule_admin_updateconfig'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text='General settings'}</legend>
            <div class="form-group">
                <label class="col-sm-3 control-label" for="blocks_collapseable">{gt text='Enable menu collapse icons'}</label>
                <div class="col-sm-9">
                    <input id="blocks_collapseable" name="collapseable" type="checkbox" value="1"{if $collapseable eq 1} checked="checked"{/if} />
                </div>
            </div>
        </fieldset>

        <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9">
                <button class="btn btn-success" title="{gt text='Save'}">{gt text='Save'}</button>
                <a class="btn btn-danger" href="{route name='zikulablocksmodule_admin_view'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
            </div>
        </div>
    </div>
</form>
{adminfooter}
