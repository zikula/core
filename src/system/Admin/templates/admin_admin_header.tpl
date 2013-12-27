{admincategorymenu}
<div class="z-admin-content z-clearfix">
    <div class="z-admin-content-modtitle">
	{modgetinfo modname=$toplevelmodule info='displayname' assign='displayName'}
	<img src="{modgetimage|safetext}" alt="{$displayName|safetext}" />
	<h2>{$displayName}</h2>
    </div>
    {modulelinks modname=$toplevelmodule type='admin'}
