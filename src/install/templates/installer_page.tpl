<!DOCTYPE html>
<html lang="{$lang}" xml:lang="{$lang}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset={$charset}" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Zikula Aplication Framework">
        <meta name="author" content="Zikula Development Team">
        <meta name="generator" content="Zikula Installer -- http://www.zikula.org" />
        <meta http-equiv="X-UA-Compatible" content="chrome=1" />        
        <title>{gt text="Zikula installer script"}</title>
        <link rel="stylesheet" href="web/bootstrap/css/bootstrap.css" type="text/css" />	
        <link rel="stylesheet" href="install/style/installer.css" type="text/css" />
        {browserhack condition="if IE"}<link rel="stylesheet" type="text/css" href="style/core_iehacks.css" media="print,projection,screen" />{/browserhack}
        <script type="text/javascript" src="web/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="install/javascript/install.js"></script>
    </head>
    <body>
        <div class="container">
            <div id="content">
                <div id="header">
                    <h1>{gt text="Zikula Application Framework"}</h1>
                    <h2>{gt text="Installer script"}</h2>
                    {php}
                    $lang = $this->_tpl_vars['lang'];
                    $this->assign('doclink', "docs/$lang/INSTALL");
                    {/php}
                    <ol class="bs clearfix">
                        <li{if $action eq lang} class="menu_selected"{/if}>
                            <span class="{if $step > 0}icon-ok{else}icon-nok{/if}"><a href="install.php?lang=">{gt text="Select language"}</a></span>
                        </li>
                        <li{if $action eq requirements} class="menu_selected"{/if}>
                            <span class="{if $step > 1}icon-ok{else}icon-nok{/if}">{gt text="Check requirements"}</span>
                        </li>
                        <li{if $action eq dbinformation} class="menu_selected"{/if}>
                            <span class="{if $step > 2}icon-ok{else}icon-nok{/if}">{gt text="Database information"}</span>
                        </li>
                        <li class="last {if $action eq createadmin}menu_selected{/if}">
                            <span class="{if $step > 3}icon-ok{else}icon-nok{/if}">{gt text="Create administrator's account"}</span>
                        </li>
                    </ol>
                    <p class="installguide"><em>{gt text="Please refer to the <a style=\"color: red\" href=\"%1\$s\" onclick=\"window.open('%2\$s');return false;\">Installation guide</a> during the process." html=1 tag1=$doclink tag2=$doclink}</em></p>
                </div>
                <div id="maincontent">{$maincontent}</div>
            </div>
            <div id="footer" class="footer">
                {if not $installbySQL}
                <br />
                <div class="alert alert-warning center">{gt text="NOTICE: Official copies of Zikula are only from zikula.org"}</div>
                {/if}
                <ul>
                    <li><strong>{gt text="Useful resources"}:</strong></li>
                    <li><a href="docs/{$lang}/INSTALL.md" onclick="window.open('docs/{$lang}/INSTALL.md');return false;">{gt text="Installation guide"}</a></li>
                    <li><a href="{gt text="http://community.zikula.org/module-Wiki.htm"}">{gt text="Zikula documentation"}</a></li>
                    <li><a href="{gt text="http://community.zikula.org/module-Forum.htm"}">{gt text="Support forums"}</a></li>
                    {* custom links for each action go here*}
                    {if $action eq requirements}
                    <li class="highlight"><a href="{gt text="http://www.wikipedia.org/wiki/File_system_permissions"}">{gt text="File system permissions"}</a></li>
                    {/if}
                    {if $action eq selecttheme}
                    <li class="highlight"><a href="https://github.com/zikula/themes">{gt text="Theme extensions database"}</a></li>
                    {/if}
                </ul>
            </div>
        </div>
        <div id="ZikulaOverlay" style="display:none;"></div>
    </body>
</html>
