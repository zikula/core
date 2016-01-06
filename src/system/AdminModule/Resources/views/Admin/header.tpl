{admincategorymenu}
<div class="z-admin-content clearfix">
    {modgetinfo modname=$toplevelmodule info='displayname' assign='displayName'}
    {modgetimage modname=$toplevelmodule assign='image'}
    {moduleheader modname=$toplevelmodule type='admin' title=$displayName putimage=true image=$image}
