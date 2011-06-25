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
