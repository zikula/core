{pageaddvar name='javascript' value='jquery'}
{ajaxheader modname='ZikulaSettingsModule' filename='ZikulaSettingsModule.Admin.Phpinfo.js' noscriptaculous=true effects=false}

{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="info" size="small"}
    <h3>{gt text="PHP configuration"}</h3>
</div>

<div id="phpinfo">
    {$phpinfo}
</div>

{adminfooter}
