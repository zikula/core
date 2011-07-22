<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$lang}" xml:lang="{$lang}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{gt text="Zikula is not installed!"}</title>
        <link rel="stylesheet" href="install/style/systemdialogs.css" type="text/css" />
    </head>
    <body>
        <div id="container">
            <div id="cell">
                <div id="content">
                    <h1>{gt text="Zikula Application Framework"}</h1>
                    <h2>{gt text="System is not installed!"}</h2>
                    <p>
                        {gt text='You are seeing this message because Zikula is not yet installed.  You can install Zikula by clicking on the install button, but before doing so please read the <a href="docs/en/INSTALL">installation instructions</a>. Further information can be found in the <a href="http://community.zikula.org/Wiki-UserDocs.htm">online documentation</a>.'}
                    </p>
                    <p>
                        <a href="install.php?lang={$lang}" class="button-install">
                            <strong>{gt text="Install Zikula!"}</strong>
                            {gt text="Zikula is free software released under the GPL license!"}
                        </a>
                    </p>
                    <p>
                        {gt text='For more information, please visit <a href="http://zikula.org/" title="Zikula Homepage">http://zikula.org</a>.'}
                    </p>
                    <p>
                        <a href="http://zikula.org/"><img src="images/powered/small/cms_zikula.png" alt="Proudly powered by Zikula" width="80" height="15" /></a>
                        <a href="http://www.php.net/"><img src="images/powered/small/php_powered.png" alt="PHP Language" width="80" height="15" /></a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
