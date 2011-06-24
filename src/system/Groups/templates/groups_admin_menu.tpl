{ajaxheader modname=Groups filename=groups.js}
{pageaddvarblock}
<script type="text/javascript">
    document.observe("dom:loaded", function() {
        Element.remove('groups_new');
    });
</script>
{/pageaddvarblock}
{admincategorymenu}
<div class="z-adminbox">
    <div class="z-admin-moduleheader">
        {img modname='Groups' src='admin.png' height='36'}
        <h2>{gt text="Groups manager"}</h2>
    </div>
    {modulelinks modname='Groups' type='admin'}
</div>
