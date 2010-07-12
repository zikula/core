<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{sitename}</title>
	<meta name="description" content="" />
    <meta name="keywords" content="{keywords}" />
    <meta name="robots" content="index,follow" />
	<meta http-equiv="X-UA-Compatible" content="chrome=1" />
   
    <link rel="stylesheet" type="text/css" media="screen, projection" href="{$stylepath}/style.css" />	
	<link rel="alternate stylesheet" href="{$stylepath}/ui/yellow.css" type="text/css" title="Yellow" media="screen, projection" />
	<link rel="alternate stylesheet" href="{$stylepath}/ui/blue.css" type="text/css" title="Blue" media="screen, projection" />
	<link rel="alternate stylesheet" href="{$stylepath}/ui/grey.css" type="text/css" title="Grey" media="screen, projection" />

	{browserhack condition="if lte IE 7"}<link rel="stylesheet" href="{$stylepath}/ie.css" type="text/css" media="screen, projection" />{/browserhack}
	{browserhack condition="if lte IE 6"}<link rel="stylesheet" href="{$stylepath}/ie6.css" type="text/css" media="screen, projection" />{/browserhack}
    {browserhack condition="if IE"}<style type="text/css">.clearfix {zoom: 1;display: block;}</style>{/browserhack}
	
    <script type="text/javascript" src="{$themepath}/js/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="{$themepath}/js/jquery-ui-1.8.2.custom.min.js"></script>
	<script type="text/javascript" src="{$themepath}/js/switch.js"></script>
	<script type="text/javascript" src="{$themepath}/js/flyoutMenu.js"></script>
	<script type="text/javascript" src="{$themepath}/js/menubar.js"></script>
	<script type="text/javascript" src="{$themepath}/js/admin.js"></script>
</head>
<body>
<div class="zk-container" id="zk-container">
    <div  id="zk-header">
	<div style="position: absolute; top: 50px; margin-left: 290px;">
	<strong>{datetime}</strong>
</div>
	<div class="box">
	<h3>{gt text='Welcome'} {usergetvar name="uname" uid=$userid|safetext} !</h3> 
	{modavailable modname=Avatar assign=avatar}
    {if $avatar}
	 <span style="float:left;margin-right:6px;"><img src="images/avatar/{usergetvar name="avatar" uid=$userid|safetext}" style="width:80px;height:80px;" /></span>	
	<img src="themes/Admin/img/switch/reload_page.gif" style="vertical-align:middle;" />
	{/if}
	<a href="switch.tpl" id="toggler"><strong>{gt text='switch Style'}</strong></a>			
	<br /> 
	<a href="index.php?module=users&amp;func=logout" class="fg-button ui-state-default ui-state-active ui-priority-primary ui-corner-left" style="margin-right:-4px;">{gt text='Logout'}</a>
	<a href="#" class="fg-button ui-state-default ui-priority-primary ui-corner-right">{gt text='My Profile'}</a>
    	
	</div>
		<div>
		    <img src="images/logo.gif" style="margin-top:6px;margin-bottom:4px;" />
		</div>
		
    </div><!-- end header -->
	    <div id="zk-content" >
	    <!-- menu -->
		<!--[include file="includes/navitop.tpl"]-->		
		<!-- /menu -->
		
		<div id="zk-content_main" class="clearfix">
		
			<div class="section">
		
				<div id="tabs" style="padding:12px;">
		
				<ul>
					<li><a href="#tabs-1"><strong>{gt text='Main Administration'}</strong></a></li>
					{checkgroup gid="2"}
					<li><a href="#tabs-2">{gt text='Dashboard'}</a></li>					
					<li><a href="#tabs-3">{gt text='Routines'}</a></li>
					<li><a href="#tabs-4">{gt text='Help'}</a></li>					
					<li><a href="#tabs-5">{gt text='Info'}</a></li>
					{/checkgroup}
				</ul>
					<div id="tabs-1">{$maincontent}</div>
						{checkgroup gid="2"}
							<div id="tabs-2">				
								<!--[include file="includes/dashboard.tpl"]-->	
							</div>
							<div id="tabs-3">
								<!--[include file="includes/routinetab.tpl"]-->		
							</div>
							<div id="tabs-4">	
								<!--[include file="includes/helptab.tpl"]-->
							</div>
							<div id="tabs-5">	
								<!--[include file="includes/infotab.tpl"]-->			  
							</div>
						{/checkgroup}
				</div>
			</div>
		</div>
			 </div>   
<!--[include file="includes/footer.tpl"]-->	
</div> 
</body>
</html>