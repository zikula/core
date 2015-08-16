{adminheader}
<h3>
    <span class="fa fa-info-circle"></span>
    {gt text='Incompatible version with the core'} - {modgetinfo modid=$id info='displayname'}
</h3>

<div>{gt text='The version of this module is incompatible with the version of the core.'}</div>
{if $moduleInfo.core_min ne ''}
    <div>{gt text='The minimal version of the core that this module supports is %s' tag1=$moduleInfo.core_min}</div>
{/if}
{if $moduleInfo.core_max ne ''}
    <div>{gt text='The maximal version of the core that this module supports is %s' tag1=$moduleInfo.core_max}</div>
{/if}
<div class="form-group">
    <div class="col-sm-offset-3 col-sm-9">
        <a class="btn btn-danger" href="{route name='zikulaextensionsmodule_admin_view' startnum=$startnum letter=$letter state=$state}"><i class="fa fa-check"></i> {gt text='Ok'}</a>
    </div>
</div>
{adminfooter}
