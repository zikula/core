{literal}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" lang="{lang}" dir="{langdirection}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
    <title>{title}</title>
    <meta name="description" content="{slogan}" />
    <meta name="keywords" content="{keywords}" />
    <link rel="stylesheet" type="text/css" href="{$stylepath}/style.css" />
    <!-- 2 column left menu template from bluerobot.com - http://bluerobot.com/web/layouts/layout1.html -->
</head>

<body>

<div id="Header"><a href="{getbaseurl}" title="{sitename}">{sitename}</a></div>

<div id="Content">
    {$maincontent}
</div>

<div id="Menu">
    {blockposition name=left}
</div>

</body>
</html>{/literal}