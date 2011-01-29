<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang}" dir="{$langdirection}">
<head>
    <title>{gt text="Zikula installer script"}</title>
    <meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
    <meta name="author" content="Zikula Development Team" />
    <meta name="generator" content="Zikula Installer -- http://www.zikula.org" />
    <link rel="stylesheet" href="install/style/style.css" type="text/css" />
    <link rel="stylesheet" href="styles/core.css" type="text/css" />
    {browserhack condition="if IE"}<link rel="stylesheet" type="text/css" href="styles/core_iehacks.css" media="print,projection,screen" />{/browserhack}
</head>
{if not $installbySQL}
{gt text="Select language" assign=selectlang}
{else}
{gt text="Welcome" assign=welcome}
{/if}
{gt text="System requirements" assign=sysrequir}
{gt text="Database information" assign=dbinfos}
{gt text="Select installation" assign=selinstalltype}
{gt text="Create administrator's account" assign=createaduser}
{gt text="Select start page" assign=selstartpage}
{gt text="Finish" assign=finish}
<body>
    <div id="container">
        <div id="wrapper" class="z-clearfix">
            <div id="header" class="z-clearfix">
                <div id="headertopleft"><img src="install/images/top1.jpg" alt="" /></div>
                    <div id="headertopright"><img src="install/images/top2.jpg" alt="" /></div>
                </div>
                <div class="menu">
                    <h3>{gt text="Installation steps"}</h3>
                    <ol>
                        <li{if $action eq lang} class="menu_selected"{/if}>{$selectlang}{$welcome}</li>
                        <li{if $action eq requirements} class="menu_selected"{/if}>{$sysrequir}</li>
                        <li{if $action eq dbinformation} class="menu_selected"{/if}>{$dbinfos}</li>
                        <li{if $action eq createadmin} class="menu_selected"{/if}>{$createaduser}</li>
                        <li{if $action eq finish} class="menu_selected"{/if}>{$finish}</li>
                    </ol>
                    <h3>{gt text="Useful resources"}</h3>
                    <ul>
                        <li><a href="docs/{$lang}/INSTALL" onclick="window.open('docs/{$lang}/INSTALL');return false;">{gt text="Installation guide"}</a></li>
                        <li><a href="{gt text="http://community.zikula.org/module-Wiki.htm"}">{gt text="Zikula documentation"}</a></li>
                        <li><a href="{gt text="http://community.zikula.org/module-Forum.htm"}">{gt text="Support forums"}</a></li>
                        {* custom links for each action go here*}
                        {if $action eq requirements}
                        <li class="highlight"><a href="{gt text="http://www.wikipedia.org/wiki/File_system_permissions"}">{gt text="File system permissions"}</a></li>
                        {/if}
                        {if $action eq selecttheme}
                        <li class="highlight"><a href="http://community.zikula.org/module-Extensions-view-comptype-2.htm">{gt text="Theme extensions database"}</a></li>
                        {/if}
                    </ul>
                    {if not $installbySQL}
                    <p id="notice">
                        {gt text="Note: Official Zikula distributions are only available from zikula.org. Please ensure that you are installing an official distribution."}
                    </p>
                    {/if}
                </div>
                <div id="content">
                    <h1>{gt text="Zikula installer script"}</h1>
                    {insert name="getstatusmsg"}
                    {php}
                        $lang = $this->_tpl_vars['lang'];
                        $this->assign('doclink', "docs/$lang/INSTALL");
                    {/php}
                    <p>{gt text="Please refer to the <a style=\"color: red\" href=\"%1\$s\" onclick=\"window.open('%2\$s');return false;\">Installation guide</a> during the process." html=1 tag1=$doclink tag2=$doclink}</p>
                    {$maincontent}
                </div>
            </div>
        </div>
    </body>
</html>
