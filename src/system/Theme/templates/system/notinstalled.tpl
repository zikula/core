<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Zikula is not installed!</title>
        <style type="text/css">
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                font-family: Verdana, Arial, Helvetica, Sans-serif;
                font-size: 14px;
                background: #F2F2F2; /*non-CSS3 browsers*/
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FAFAFA', endColorstr='#eaeaea'); /*IE*/
                background: -webkit-gradient(linear, left top, left bottom, from(#FAFAFA), to(#eaeaea) ); /*webkit*/
                background: -moz-linear-gradient(center top , #FAFAFA, #eaeaea) repeat scroll 0 0 transparent; /*gecko*/
                background: linear-gradient(center top , #FAFAFA, #eaeaea) repeat scroll 0 0 transparent; /*CSS3*/
                line-height: 1.6em;
            }
            a {
                color: #2147B3;
                border: none;
            }
            img {
                border: none;
            }
            h1 {
                font-size:24px;
                line-height:28px;
            }
            h2 {
                color:#770000;
                font-size:22px;
                line-height:26px;
                text-transform:uppercase;
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
                background: #fafafa;
                border: 1px solid #2147B3;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                -webkit-box-shadow: 4px 4px 10px rgba(0, 0, 0, .3);
                -moz-box-shadow: 4px 4px 10px rgba(0, 0, 0, .3);
                box-shadow: 4px 4px 10px rgba(0, 0, 0, .3);
                text-align: center;
            }
            .button-install {
                display: inline-block;
                padding: 15px 20px;
                color: #bed7e1;
                font-size: 12px;
                text-decoration: none;
                margin: 1em 0;
                border: 1px solid #A4C3EF;
                text-shadow: -1px -1px 0 rgba(0, 0, 0, 0.3);
                background: #2147B3;
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#7DA3DF', endColorstr='#2147B3');
                background: -webkit-gradient(linear, left top, left bottom, from(#7DA3DF), to(#2147B3) );
                background: -moz-linear-gradient(top, #7DA3DF, #2147B3);
                background: linear-gradient(top, #7DA3DF, #2147B3);
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                -webkit-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
                -moz-box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
            }
            a.button-install:hover,
            a.button-install:active {
                text-decoration: none;
                background: #3764DF;
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#8FBAFF', endColorstr='#3764DF');
                background: -webkit-gradient(linear, 0% 0, 0% 100%, from(#8FBAFF), to(#3764DF) );
                background: -moz-linear-gradient(-90deg, #8FBAFF, #3764DF);
                background: linear-gradient(-90deg, #8FBAFF, #3764DF);
            }
            .button-install strong {
                display: block;
                color: #fff;
                font-size: 18px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="cell">
                <div class="content">
                    <h1>Zikula Application Framework</h1>
                    <h2>System is not installed!</h2>
                    <p>
                        You are seeing this message because Zikula is not yet installed.  You can install Zikula by clicking on the install button, but before doing so please read the <a href="docs/en/README">installation instructions</a>.
                        Further information can be found in the <a href="http://community.zikula.org/Wiki-UserDocs.htm">online documentation</a>.
                    </p>
                    <p>
                        <a href="install.php?lang=en" class="button-install">
                            <strong>Install Zikula!</strong>
                            Zikula is free software released under the GPL license!
                        </a>
                    </p>
                    <p>
                        For more information, please visit <a href="http://zikula.org/" title="Zikula Homepage">http://zikula.org</a>.
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
