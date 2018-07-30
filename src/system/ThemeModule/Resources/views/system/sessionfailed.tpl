<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xml:lang="<?php echo ZLanguage::getLanguageCode(); ?>" dir="<?php echo ZLanguage::getDirection(); ?>">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title><?php echo __('Unable to start session'); ?></title>
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
                background: url("/images/logo_with_title.gif") no-repeat scroll 50% 50% transparent;
                font-size: 24px;
                line-height: 100px;
                text-indent: -9000px;
            }
            h2 {
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
        </style>
    </head>
    <body>
        <div id="container">
            <div id="cell">
                <div id="content">
                    <h1><?php echo __('Zikula Application Framework'); ?></h1>
                    <h2><?php echo __('Unable to start session'); ?></h2>
                    <p>
                        <?php echo __('This website is experiencing temporary technical difficulties, refreshing the page may help.  If this problem persists please inform this website\'s owner about this problem.'); ?>
                    </p>
                    <p>
                        <?php echo __('Please <em>do not contact</em> the Zikula team about this error, as it is specific to this website because although this website is powered by the Zikula Application Framework, it is owned and managed independently by this website\'s administrator.'); ?>
                    </p>
                    <p>
                        <strong><?php echo __('If you are the website administrator:'); ?></strong><br/>
                        <?php echo __('Zikula is unable to correctly start sessions. This is because sessions cannot be written to the database which is usually caused by either your site being out of disk space or disk quota, or that the sessions table has become corrupted.  Running a repair on the sessions table may fix the problem. For further help please visit the Zikula website.'); ?>
                    </p>
                    <p>
                        <?php echo __('Zikula is free software released under the GPL license.  For more information, please visit'); ?> <a href="https://ziku.la/" title="<?php echo __('Zikula Homepage'); ?>">https://ziku.la</a>.
                    </p>
                    <p>
                        <a href="https://ziku.la"><img src="images/zk-power.png" alt="<?php echo __('Proudly powered by Zikula'); ?>" title="<?php echo __('Proudly powered by Zikula'); ?>"width="96" height="30" /></a>                        
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>


