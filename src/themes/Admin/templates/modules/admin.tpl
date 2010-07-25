<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<!--[lang]-->" lang="<!--[lang]-->" dir="<!--[langdirection]-->">
<head>
{include file="includes/head.tpl"}
</head>
<body>
<div id="zk-container">

{include file='includes/header.tpl'}


<div id="zk-content-container" style="width:100%">
<div style="width:99%;">
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
</div>
{include file="includes/footer.tpl"}
</div>
</body>
</html>
