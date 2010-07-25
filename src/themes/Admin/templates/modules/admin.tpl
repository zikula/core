<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>{title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={charset}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1" />
<meta name="Author" content="{sitename}" />
<meta name="description" content="{slogan}" />
<meta name="keywords" content="{keywords}" />
<meta name="Copyright" content="Copyright (c) {'Y'|date} by {sitename}" />
<meta name="Robots" content="index,follow" />

<link rel="icon" type="image/png" href="{$imagepath}/favicon.png" />
<link rel="icon" type="image/x-icon" href="{$imagepath}/favicon.ico" /><!--[* W3C *]-->
<link rel="shortcut icon" type="image/ico" href="{$imagepath}/favicon.ico" /><!--[* IE *]-->

	<script type="text/javascript" src="javascript/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="{$themepath}/js/jquery-ui-1.8.2.custom.min.js"></script>
	 <script type="text/javascript" src="{$themepath}/js/cookie.js"></script>
	 <script type="text/javascript" src="{$themepath}/js/superfish.js"></script>
	<script type="text/javascript" src="{$themepath}/js/admin.js"></script>
	
	<script type="text/javascript" src="javascript/ajax/proto_scriptaculous.combined.min.js"></script>
	<script type="text/javascript" src="javascript/helpers/Zikula.js"></script>
	<script type="text/javascript" src="javascript/livepipe/livepipe.combined.min.js"></script>
	<script type="text/javascript" src="javascript/helpers/Zikula.UI.js"></script>

	<link type="text/css" href="{$themepath}/style/ui/ui.base.css" rel="stylesheet" media="all" />
	<link type="text/css" href="{$themepath}/style/themes/black_rose/ui.css" rel="stylesheet" title="style" media="all" />

	{browserhack condition="if IE"}
	<link href="{$themepath}/style/ie6.css" rel="stylesheet" media="all" />
	
	<script src="{$themepath}/js/pngfix.js"></script>
	<script>
	  /* Fix IE6 Transparent PNG */
	  DD_belatedPNG.fix('.logo, ul#dashboard-buttons li a, .response-msg, #search-bar input');

	</script>
	{/browserhack}
</head>
<body>
	<div id="page_wrapper">
		<div id="page-header">
			<div id="page-header-wrapper">
				<div id="top">
					<p class="logo">Zikula Administration</p>
					<div class="welcome">
						<span class="note">{gt text='Welcome'}, <a href="#" title="{usergetvar name="uname" uid=$uid}">{usergetvar name="uname" uid=$uid}</a></span>
						<a class="btn ui-state-default ui-corner-all" href="#">
							<span class="ui-icon ui-icon-wrench">&nbsp;</span>
							{gt text='Settings'}
						</a>
						<a class="btn ui-state-default ui-corner-all" href="#">
							<span class="ui-icon ui-icon-person">&nbsp;</span>
							{gt text='My Account'}
						</a>
						<a class="btn ui-state-default ui-corner-all" href="#">
							<span class="ui-icon ui-icon-power">&nbsp;</span>
							{gt text='Logout'}
						</a>						
					</div>
				</div>
				{include file="includes/navitop.tpl"}
				<div id="search-bar">
					<form method="post" action="">
						<input type="text" name="q" value="Suche!" />
					</form>
				</div>
			</div>
		</div>
 
		<div id="page-layout"><div id="page-content">
			<div id="page-content-wrapper">
				<div class="clear"></div>
				
				<div class="content-box">
<ul id="tabs_example_eq">
                    <li class="tab"><a href="#eqone">{gt text='Main Administration'}</a></li>
                    {checkgroup gid="2"}
					<li class="tab"><a href="#eqtwo">{gt text='Dashboard'}</a></li>					
					<li class="tab"><a href="#eqthree">{gt text='Routines'}</a></li>
					<li class="tab"><a href="#eqfour">{gt text='Help'}</a></li>					
					<li class="tab"><a href="#eqfive">{gt text='Info'}</a></li>
					{/checkgroup}
                </ul>  
                    <div id="eqone">{$maincontent}</div>
					{checkgroup gid="2"}
	                <div id="eqtwo">{include file="includes/dashboard.tpl"}</div>
					<div id="eqthree">{include file="includes/routinetab.tpl"}</div>
					<div id="eqfour">{include file="includes/helptab.tpl"}</div>
					<div id="eqfive">{include file="includes/infotab.tpl"}</div>
					{/checkgroup}
					<script type="text/javascript">
                        var eqtabs = new Zikula.UI.Tabs('tabs_example_eq');
                    </script>
</div>
	
			{include file="includes/sidebar.tpl"}	
						
		<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
	<div id="footer">
		<a href="#navigation" title="{gt text='Back to top'}">{gt text='Back to top'}</a>
	</div>
	<div id="copyright">
		Powered by <a href="http://www.zikula.org" title="Zikula Version {version}">Zikula {version}</a>
	</div>
</div>
</body>
</html>