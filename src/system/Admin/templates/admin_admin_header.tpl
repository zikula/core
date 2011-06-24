{admincategorymenu}
<div class="z-adminarea z-clearfix">
    <div class="z-admin-moduleheader">
        {img modname=$toplevelmodule src='admin.png' height='36'}
        <h2>{modgetinfo modname=$toplevelmodule info='displayname'}</h2>
    </div>
    {modulelinks modname=$toplevelmodule type='admin'}
