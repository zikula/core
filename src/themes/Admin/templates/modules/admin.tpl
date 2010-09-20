<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{lang}" xml:lang="{lang}">
<head>
<title>{title}</title>
<meta http-equiv="Content-Type" content="text/html; charset={charset}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1" />
<meta name="Author" content="{sitename}" />
<meta name="description" content="{slogan}" />
<meta name="keywords" content="{keywords}" />
<meta name="Copyright" content="Copyright (c) {'Y'|date} by {sitename}" />
<meta name="Robots" content="index,follow" />

<link rel="icon" type="image/png" href="{$themepath}/img/favicon.png" />
<link rel="icon" type="image/x-icon" href="{$themepath}/img/favicon.ico" /><!--[* W3C *]-->
<link rel="shortcut icon" type="image/ico" href="{$themepath}/img/favicon.ico" /><!--[* IE *]-->
<link rel="alternate" href="{modurl modname='News' type='user' func='view' theme='rss'}" type="application/rss+xml" title="{sitename} {gt text='Main Feed'}" />

<script type="text/javascript" src="javascript/jquery/jquery.min.js"></script>
<script type="text/javascript" src="{$themepath}/js/jquery-ui-1.8.2.custom.min.js"></script>
<script type="text/javascript" src="{$themepath}/js/cookie.js"></script>
<script type="text/javascript" src="{$themepath}/js/admin.js"></script>
<script type="text/javascript" src="javascript/ajax/proto_scriptaculous.combined.min.js"></script>
<script type="text/javascript" src="javascript/helpers/Zikula.js"></script>
<script type="text/javascript" src="javascript/livepipe/livepipe.combined.min.js"></script>
<script type="text/javascript" src="javascript/helpers/Zikula.UI.js"></script>

<link type="text/css" href="{$themepath}/style/ui/ui.base.css" rel="stylesheet" media="all" />
<link type="text/css" href="{$themepath}/style/themes/gray/ui.css" rel="stylesheet" title="style" media="all" />

{browserhack condition="if lte IE 6"}
    <link href="{$themepath}/style/ie.css" rel="stylesheet" media="all" />
{/browserhack}
</head>
<body>
	<div id="page_wrapper">
		<div id="page-header">
			<div id="page-header-wrapper">
				<div id="top">
					<span class="logo" id="logo" style="padding-top:4px;">{gt text='Administration'}</span>
					<div class="welcome">
					{include file="includes/adminnavtop.tpl"}
			        </div>
				</div>
				<ul id="aui-tabs_eq">
                    <li class="z-tab"><a href="#eqone">{gt text='Main Administration'}</a></li>
                    {checkpermissionblock component='.*' instance='.*' level=ACCESS_ADMIN}
					<li class="z-tab"><a href="#eqtwo">{gt text='Dashboard'}</a></li>					
					<li class="z-tab"><a href="#eqthree">{gt text='Routines'}</a></li>
					<li class="z-tab"><a href="#eqfour">{gt text='Help'}</a></li>					
					<li class="z-tab"><a href="{modurl modname="sysinfo" type="admin"}">{gt text='Info'}</a></li>
					<li class="z-tab"><a href="#eqsix">{gt text='News'}</a></li>
					{/checkpermissionblock}				
                </ul>
			</div>
		</div>
 
		<div id="page-layout">
		<div id="page-content">
			<div id="page-content-wrapper">
				<div class="clear"></div>
				
				<div class="content-box">
                
                    <div id="eqone">{$maincontent}</div>
					{checkpermissionblock component='.*' instance='.*' level=ACCESS_ADMIN}
	                <div id="eqtwo">{include file="includes/dashboard.tpl"}</div>
					<div id="eqthree">{include file="includes/routinetab.tpl"}</div>
					<div id="eqfour">{include file="includes/helptab.tpl"}</div>
					<div id="eqfive"></div>
					<div id="eqsix">{include file="includes/newstab.tpl"}</div>
					{/checkpermissionblock}
					<script type="text/javascript">
                        var eqtabs = new Zikula.UI.Tabs('aui-tabs_eq');
                    </script>
		                <br />
		                <a class="fg-button btn ui-state-default full-link ui-corner-all" href="#logo" title="{gt text='Back to top'}" >
		                    <span class="ui-icon ui-icon-arrowthick-1-n" style="margin-top:3px;">&nbsp;</span>
		                    {gt text='Back to top'}
		                </a>
		        </div>
		{checkpermissionblock component='.*' instance='.*' level=ACCESS_ADMIN}
		{include file="includes/sidebar.tpl"}	
		{/checkpermissionblock}
		</div>
		<div class="clear"></div>
		</div>
		</div>		
	 
    </div>
</body>
</html>