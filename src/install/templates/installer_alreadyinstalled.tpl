<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Zikula is already installed!</title>
        {literal}
        <style type="text/css">
            html, body {
                height: 100%;
                margin:0;
                padding:0;
                font-family: Verdana, Arial, Helvetica, Sans-serif;
                font-size: 12px;
                background: #EEEEEE;
                line-height:1.6em;
            }
            a {
                color: #2147B3;
                font-weight: bold;
                border: none;
            }
            img {
                border: none;
            }
            .container {
                display: table;
                height: 100%;
                width: 100%;
            }
            .cell {
                display: table-cell;
                vertical-align: middle;
                /* For IE6/7 */
                position: relative;
                top:expression(this.parentNode.clientHeight/2 - this.firstChild.clientHeight/2 + " px");
            }
            .content {
                /* center horizontally */
                margin: 0 auto;
                width: 50%;
                padding: 1.5em;
                background: #FAFAFA;
                border: 1px solid #2147B3;
                -webkit-box-shadow: #999 4px 4px 10px;
                -moz-box-shadow: #999 4px 4px 10px;
                box-shadow: #999 4px 4px 10px;
            }
        </style>
        {/literal}
    </head>
    <body>
        <div class="container">
            <div class="cell">
                <div class="content">
                    <h1>Zikula is already installed!</h1>
                    <p>
                        You are seeing this message because Zikula is already installed so the installer has been disabled.  Click <a href="index.php">here to visit your homepage</a>.
                        If you are a system administrator you can proceed to the installer <a href="install.php?lang={lang}&action=login">here</a>.
                        Further information can be found in the <a href="http://community.zikula.org/Wiki-UserDocs.htm">online documentation</a>.
                    </p>
                    <p>
                        Zikula is free software released under the GNU/GPL.  For more information, please visit <a href="http://zikula.org" title="Zikula Homepage">
                        http://zikula.org</a>.
                    </p>
                    <p>
                        <a href="http://zikula.org"><img src="images/powered/small/cms_zikula.png" alt="Proudly powered by Zikula" width="80" height="15" /></a>
                        <a href="http://www.php.net"><img src="images/powered/small/php_powered.png" alt="PHP Language" width="80" height="15" /></a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
