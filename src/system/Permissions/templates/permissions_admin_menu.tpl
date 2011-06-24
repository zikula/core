{ajaxheader modname=Permissions filename=permissions.js ui=true}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        Element.addClassName('permissions_new', 'z-hide');

        $$('.showinstanceinformation').each(function(element) {
            new Zikula.UI.Window(element,{width: 600, iframe: true, resizable: true});
        });
    });
</script>
{/pageaddvarblock}
{admincategorymenu}
<div class="z-adminbox">
    <div class="z-admin-moduleheader">
        {img modname='Permissions' src='admin.png' height='36'}
        <h2>{gt text="Permission rules manager"}</h2>
    </div>
    {modulelinks modname='Permissions' type='admin'}
</div>
