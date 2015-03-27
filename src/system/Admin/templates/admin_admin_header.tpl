{admincategorymenu}
<div class="z-admin-content z-clearfix">
    {modgetinfo modname=$toplevelmodule info='displayname' assign='displayName'}
    {modgetimage assign='image'}
    {moduleheader modname=$toplevelmodule type='admin' title=$displayName putimage=true image=$image}
