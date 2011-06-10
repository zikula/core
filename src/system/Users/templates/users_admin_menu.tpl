{ajaxheader modname=$modinfo.name filename='users.js' ui=true}

{admincategorymenu}
<div class="z-adminbox">
    {img modname=$modinfo.name src='admin.png' height='36'}
    <h2>{gt text="Users manager"}</h2>
    {modulelinks modname=$modinfo.name type='admin'}
    {if !empty($modvars.ZConfig.profilemodule)}
    {modulelinks menuid='profileadminlinks' menuclass='z-hide z-menulinks' modname=$modvars.ZConfig.profilemodule type='admin'}
    {/if}
</div>
