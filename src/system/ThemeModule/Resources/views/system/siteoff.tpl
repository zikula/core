<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="<?php echo ZLanguage::getLanguageCode(); ?>" dir="<?php echo ZLanguage::getDirection(); ?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo ZLanguage::getEncoding(); ?>" />
        <title><?php echo __('The site is currently off-line.'); ?></title>
        <base href="<?php echo System::getBaseURL(); ?>" />
        <style type="text/css">
            html {
                background: #eaeaea; /*non-CSS3 browsers*/
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#FAFAFA', endColorstr='#eaeaea'); /*IE*/
                background: -webkit-gradient(linear, left top, left bottom, from(#FAFAFA), to(#eaeaea) ) fixed; /*webkit*/
                background: -moz-linear-gradient(center top , #FAFAFA, #eaeaea) no-repeat scroll 0 0 #eaeaea; /*gecko*/
                background: linear-gradient(center top , #FAFAFA, #eaeaea) no-repeat scroll 0 0 #eaeaea; /*CSS3*/
                height: 100%;
            }
            body {
                font-family: Verdana, Arial, Helvetica, Sans-serif;
                font-size: 14px;
                line-height: 1.6em;
                color: #444;
                margin: 0;
                padding: 0;
                height: 100%;
            }
            a {
                color: #2147B3;
                border: none;
            }
            a:hover {
                text-decoration: none;
            }
            img {
                border: none;
            }
            h1 {
                color: #E5352C;
                font-size: 22px;
                line-height: 26px;
                text-shadow: 1px 1px 1px rgba(128, 128, 128, 0.5);
                text-transform: uppercase;
            }
            #container {
                display: table;
                height: 100%;
                width: 100%;
            }
            #cell {
                display: table-cell;
                vertical-align: middle;
                /* For IE6/7 */
                position: relative;
                top:expression(this.parentNode.clientHeight/2 - this.firstChild.clientHeight/2 + " px");
            }
            #content {
                /* center horizontally */
                margin: 0 auto;
                width: 50%;
                padding: 1.5em;
                background: #fafafa;
                border: 1px solid #42403E\9; /* IE6/7/8 */
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                -webkit-box-shadow: 4px 4px 20px rgba(0, 0, 0, .5);
                -moz-box-shadow: 4px 4px 20px rgba(0, 0, 0, .5);
                box-shadow: 4px 4px 20px rgba(0, 0, 0, .5);
                text-align: center;
                color: #444;
            }
            .login-button {
                display: inline-block;
                padding: 7px 10px;
                color: #fff;
                font-size: 14px;
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
            .login-button:hover {
                text-decoration: none;
                font-weight:bold;
                background: #3764DF;
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#8FBAFF', endColorstr='#3764DF');
                background: -webkit-gradient(linear, 0% 0, 0% 100%, from(#8FBAFF), to(#3764DF) );
                background: -moz-linear-gradient(-90deg, #8FBAFF, #3764DF);
                background: linear-gradient(-90deg, #8FBAFF, #3764DF);
            }
            .showloginbutton {
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
            a.showloginbutton:hover,
            a.showloginbutton:active {
                text-decoration: none;
                background: #3764DF;
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#8FBAFF', endColorstr='#3764DF');
                background: -webkit-gradient(linear, 0% 0, 0% 100%, from(#8FBAFF), to(#3764DF) );
                background: -moz-linear-gradient(-90deg, #8FBAFF, #3764DF);
                background: linear-gradient(-90deg, #8FBAFF, #3764DF);
            }
            .showloginbutton strong {
                display: block;
                color: #fff;
                font-size: 18px;
            }
            #login {
                text-align:left;
                visibility:hidden;
                border: 1px #ddd solid;
                margin: 0 0 1em 0;
                padding: 0.5em 1em;
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                border-radius: 5px;
                background: #fafafa; /*non-CSS3 browsers*/
                filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fafafa', endColorstr='#f2f2f2'); /*IE*/
                background: -webkit-gradient(linear,left top, left bottom,from(#fafafa),to(#f2f2f2)); /*webkit*/
                background: -moz-linear-gradient(top,#fafafa,#f2f2f2); /*gecko*/
                background: linear-gradient(#fafafa, #f2f2f2); /*CSS3*/
            }
            #login .loginrow {
                clear:both;
                min-height:2em;
                padding:0.5em 0;
                font-size:14px;
                line-height:16px;
                font-weight:normal;
            }
            #login .loginrow label {
                float:left;
                margin:5px 0;
                padding:2px;
                text-align:right;
                width:40%;
            }
            #login .loginrow input {
                border:1px solid #DDDDDD;
                margin:5px 0 5px 1%;
                padding:2px;
                width:47%;
            }
            #login .logincheck {
                clear:both;
                margin:5px 0 5px 41%;
                padding:2px;
            }
            #login .loginbutton {
                margin:5px 0 5px 41%;
                padding:2px;
            }
        </style>

        <script type="text/javascript">
            <!--
            function toggleLoginBox()
            {
                document.getElementById('login').style.visibility = (document.getElementById('login').style.visibility == 'visible') ? 'hidden' : 'visible';
            }
            // -->
        </script>

    </head>
    <body>

        <div id="container">
            <div id="cell">
                <div id="content">
                    <h1><?php echo __('The site is currently off-line.'); ?></h1>
                    <h2><?php echo nl2br(\DataUtil::formatForDisplay((Zikula_Core::VERSION_NUM != System::getVar('Version_Num')) ? __('This site needs to be upgraded, please contact the system administrator.') : System::getVar('siteoffreason')));?></h2>
                    <?php if (Zikula_Core::VERSION_NUM == System::getVar('Version_Num')) { ?>
                    <p>
                        <a href="#" class="showloginbutton" onclick="toggleLoginBox(); return false;" title="<?php echo __('Administrator log-in'); ?>">
                            <strong><?php echo __('Administrator log-in'); ?></strong>
                        </a>
                    </p>
                    <form id="login" action="<?php System::getVar('entrypoint', 'index.php'); ?>?module=Users&amp;type=user&amp;func=siteOffLogin" method="post">
                        <div>
                        <p><strong><?php echo __('An administrator log-in is required.'); ?></strong></p>
                            <div class="loginrow">
                                <label for="user"><?php echo __('User name'); ?></label>
                                <input id="user" type="text" name="user" size="14" maxlength="64" />
                            </div>
                            <div class="loginrow">
                                <label for="pass"><?php echo __('Password'); ?></label>
                                <input id="pass" type="password" name="pass" size="14" maxlength="20" />
                            </div>
                            <div class="logincheck">
                                <input id="rememberme" type="checkbox" value="1" name="rememberme" />
                                <label for="rememberme"><?php echo __('Remember me'); ?></label>
                            </div>
                            <div class="loginbutton">
                                <input class="login-button" type="submit" value="<?php echo __('Log in'); ?>" />
                            </div>
                        </div>
                    </form>
                    <?php } ?>
                    <p>
                        <a href="http://zikula.org"><img src="images/zk-power.png" alt="<?php echo __('Proudly powered by Zikula'); ?>" title="<?php echo __('Proudly powered by Zikula'); ?>" width="96" height="30" /></a>
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
