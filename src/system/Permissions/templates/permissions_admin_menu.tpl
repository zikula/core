{ajaxheader modname=Permissions filename=permissions.js ui=true}
{admincategorymenu}
<div class="z-adminbox">
    {img modname='Permissions' src='admin.png'}
    <h1>{gt text="Permission rules manager"}</h1>
    {modulelinks modname='Permissions' type='admin'}
</div>

<script type="text/javascript">
    Element.addClassName('permissions_new', 'z-hide');
</script>

<script type="text/javascript">
    $$('.showinstanceinformation').each(function(element) {
        new Zikula.UI.Window(element,{width: 600, iframe: true, modal:true, resizable: true});
    })
</script>