<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" dir="{langdirection}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
        <title>{title}</title>
        <meta name="description" content="{slogan}" />
        <meta name="keywords" content="{keywords}" />
        <meta http-equiv="X-UA-Compatible" content="chrome=1" />
        <link rel="stylesheet" type="text/css" href="{$stylepath}/style.css" media="print,projection,screen" />
        <link rel="stylesheet" type="text/css" href="{$stylepath}/print.css" media="print" />
    </head>
    <body>
        <div id="theme_page_container">
            <div id="theme_header">
                <h1>{sitename}</h1>
                <h2>{slogan}</h2>
            </div>
            <div id="theme_navigation_bar">
                <ul>
                    <li>
                        <a href="{homepage}">{gt text='Home'}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Settings type=admin}">{gt text="Settings"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Modules type=admin}">{gt text="Modules manager"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Blocks type=admin}">{gt text="Blocks manager"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Users type=admin}">{gt text="User administration"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Groups type=admin}">{gt text="Groups manager"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Permissions type=admin}">{gt text="Permission rules manager"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Theme type=admin}">{gt text="Themes manager"}</a>
                    </li>
                    <li>
                        <a href="{modurl modname=Categories type=admin}">{gt text="Categories"}</a>
                    </li>
                </ul>
            </div>
            <div id="theme_content" style="width:95%">{$maincontent}</div>
            <div id="theme_footer">
                <p>
                    {gt text="Powered by"} <a href="http://zikula.org">Zikula</a>
                </p>
                {nocache}{pagerendertime}{/nocache}
                {nocache}{sqldebug}{/nocache}
            </div>
        </div>
    </body>
</html>
