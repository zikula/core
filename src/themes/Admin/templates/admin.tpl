<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" dir="{langdirection}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
    <title>{title}</title>
    <meta name="description" content="{slogan}" />
    <meta name="keywords" content="{keywords}" />
    <meta http-equiv="X-UA-Compatible" content="chrome=1" />
    
	<link rel="icon" type="image/png" href="{$themepath}/themes/Admin/images/favicon.png" />
    <link rel="icon" type="image/x-icon" href="{$themepath}/imags/favicon.ico" />
    <link rel="shortcut icon" type="image/ico" href="{$themepath}/themes/Admin/images/favicon.ico" />
    <link rel="alternate" href="{modurl modname='News' type='user' func='view' theme='rss'}" type="application/rss+xml" title="{sitename} {gt text='Main Feed'}" />
    {pageaddvar name="stylesheet" value="$stylepath/style.css"}
</head>
<body>
<!-- Main Header -->
<div class="header">
	<div class="wrapper">
		<div id="top">
            {include file="includes/adminnavtop.tpl"}
		</div>
		<!-- z-tabs -->
		<div class="navigation">
			<ul id="aui-tabs_eq">
                <li><a href="#eqone" class="active"><span>{gt text='Administration'}</span></a></li>
                {checkpermissionblock component='.*' instance='.*' level=ACCESS_ADMIN}
                <li><a href="#eqthree"><span>{gt text='Routines'}</span></a></li>
                <li><a href="#eqfour"><span>{gt text='Help'}</span></a></li>					
                <li><a href="{modurl modname="sysinfo" type="admin"}"><span>{gt text='Info'}</span></a></li>
                <li><a href="#eqsix"><span>{gt text='News'}</span></a></li>
                {/checkpermissionblock}		
			</ul>
		</div>
		<!-- end z-tabs -->
	</div>
</div>
<!-- End Main Header -->

<!-- Main Container -->
<div id="container">
	<div class="wrapper">		
		<div class="spacer">&nbsp;</div>
		<!-- Content -->
		<div id="content">
			<!-- dashboard-content -->
			<div class="dashboard-content">
				<div class="dashboard-content-head">
                    <h2 class="left">{gt text='Dashboard'}</h2>
					<div class="right link">
                        <strong>{gt text='Quick Navigation'}: </strong>
						<a href="{modurl modname="blocks" type="admin" func="view"}">{gt text='Blocks'}</a>                        
                        <a href="{modurl modname="themes" type="admin" func="view"}">{gt text='Themes'}</a>
                        <a href="{modurl modname="permissions" type="admin"}">{gt text='Permissions'}</a>
                        <a href="{modurl modname="securitycenter" type="admin"}">{gt text='Security'}</a>
                        <a href="{modurl modname="settings" type="admin"}">{gt text='Settings'}</a>
                        <a href="{modurl modname="users" type="admin"}">{gt text='Users'}</a>
					</div>
				</div>
                <div class="dashboard">
					<div id="dash">
                        <div id="eqone">{$maincontent}</div>
                        {checkpermissionblock component='.*' instance='.*' level=ACCESS_ADMIN}	                
                        <div id="eqthree">{include file="includes/routinetab.tpl"}</div>
                        <div id="eqfour">{include file="includes/helptab.tpl"}</div>
                        <div id="eqfive"></div>
                        <div id="eqsix">{include file="includes/newstab.tpl"}</div>
                        {/checkpermissionblock}
                    </div>
				</div>
				<div class="dashboard-footer">
                    <a href="#top"> &uarr; {gt text='Top'}</a>
                </div>
			</div>
			<!-- End dashboard-content -->
		</div>
		<!-- End Content -->
		<div class="spacer">&nbsp;</div>			
	</div>
</div>
<!-- End Container -->
<!-- Footer -->
<div class="footer">
	<div class="wrapper">
		<span class="left">&copy; {datetime format='%b %d - %I:%M'} - {sitename}</span>
		<span class="right">{gt text='Powered by'} <a href="http://community.zikula.org" target="_blank">Zikula</a> Version {version}</span>
	</div>
</div>
<!-- End Footer -->
<!-- UI Stuff -->
<script type="text/javascript" src="themes/Admin/js/cookie.js"></script>
<script type="text/javascript" src="themes/Admin/js/z-styleswitcher.js"></script>
<script type="text/javascript">
    function zswitcher() {
	    new ZikulaSwitcher('switcher', ['red', 'green']);	
                         }
            document.observe ('dom:loaded', zswitcher, false);
</script>
<script type="text/javascript" src="javascript/helpers/Zikula.UI.js"></script>
<script type="text/javascript">
    var eqtabs = new Zikula.UI.Tabs('aui-tabs_eq',{equal: true});
</script>
<script type="text/javascript">
    var defwindowmodal = new Zikula.UI.Window($('defwindowmodal-0'),{modal:true,minmax:true,resizable: true});
</script>
<script type="text/javascript">
    var defwindowmodal = new Zikula.UI.Window($('defwindowmodal-1'),{modal:true,minmax:true,resizable: true});
</script>
</body>
</html>