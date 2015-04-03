{adminheader}

<h3>
    <span class="fa fa-trash-o"></span>
    {gt text='Purge IDS Log'}
</h3>

<ul class="navbar navbar-default navbar-modulelinks">
    <li><a href="{route name='zikulasecuritycentermodule_admin_exportidslog'}" title="{gt text='Download the entire log to a csv file'}" class="fa fa-arrow-circle-o-down"> {gt text='Export IDS Log'}</a></li>
    <li><span class="fa fa-trash-o"> {gt text='Purge IDS Log'}</span></li>
</ul>

<p class="alert alert-warning">{gt text='Do you really want to delete the entire IDS log?'}</p>

<form class="form-horizontal" role="form" action="{route name='zikulasecuritycentermodule_admin_purgeidslog'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" name="confirmation" value="1" />
        <fieldset>
            <legend>{gt text='Confirmation prompt'}</legend>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    {button class='btn btn-success' __alt='Delete' __title='Delete' __text='Delete'}
                    <a class="btn btn-danger" href="{route name='zikulasecuritycentermodule_admin_viewidslog'}" title="{gt text='Cancel'}">{gt text='Cancel'}</a>
                </div>
            </div>
        </div>
        </fieldset>
    </div>
</form>
{adminfooter}
