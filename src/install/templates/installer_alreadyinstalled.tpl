<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{$lang}" xml:lang="{$lang}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>{gt text="Zikula is already installed!"}</title>
        <link rel="stylesheet" href="install/style/systemdialogs.css" type="text/css" />
    </head>
    <body>
        <div id="container">
            <div id="cell">
                <div id="content">
                    <h1>{gt text="Zikula Application Framework"}</h1>
                    <h2>{gt text='System is already installed!'}</h2>
                    <p>
                        {gt text='Zikula is already installed so the installer has been disabled.  If you need to run the installer a second time, you must reset config.php to its original state and clear the database tables before running the installer again. <a href="index.php">%s</a>.' tag1='Click here to visit your homepage'}
                        {gt text='Further information can be found in the <a href="http://community.zikula.org/Wiki-UserDocs.htm">%s</a>.' tag1='online documentation'}
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>
