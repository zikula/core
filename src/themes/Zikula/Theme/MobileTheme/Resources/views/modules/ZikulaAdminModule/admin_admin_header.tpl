{admincategorymenu}

{modulelinks modname=$toplevelmodule type='admin' menuid='mainModuleLinks'}

<script type="text/javascript">
    jQuery('#mainModuleLinks').prepend('<li data-role="list-divider"><a href="#">{{modgetinfo modname=$toplevelmodule info='displayname'}}</a></li>');
</script>