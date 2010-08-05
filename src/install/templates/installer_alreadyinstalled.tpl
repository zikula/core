<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{lang}" xml:lang="{lang}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{gt text='Zikula is already installed!'}</title>
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
                    <h1>{gt text='Zikula is already installed!'}</h1>
                    <p>{lang assign='lang'}
                        {gt text='You are seeing this message because Zikula is already installed so the installer has been disabled.  <a href="index.php">%s</a>.' tag1='Click here to visit your homepage'}
                        {gt text="If you are a system administrator you can proceed to the installer <a href='install.php?lang=$lang&action=login'>%s</a>." tag1='here'}
                        {gt text='Further information can be found in the <a href="http://community.zikula.org/Wiki-UserDocs.htm">%s</a>.' tag1='online documentation'}
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
