{adminheader}
<div class="z-admin-content-pagetitle">{icon type="info" size="small"}</div>
<h3>{gt text="Incompatible version with the core"} - {modgetinfo modid=$id info=displayname}</h3>

<div>{gt text="The version of this module is incompatible with the version of the core."}</div>
{if $moduleInfo.core_min neq ''}
<div>{gt text="The minimal version of the core that this module supports is %s" tag1=$moduleInfo.core_min}</div>
{/if}
{if $moduleInfo.core_max neq ''}
<div>{gt text="The maximal version of the core that this module supports is %s" tag1=$moduleInfo.core_max}</div>
{/if}
<div class="z-buttons z-formbuttons">
    <a href="{modurl modname=Extensions type=admin func=view startnum=$startnum letter=$letter state=$state}">{img modname=core src=button_ok.png set=icons/small  __alt="Ok" __title="Ok"} {gt text="Ok"}</a>
</div>
{adminfooter}