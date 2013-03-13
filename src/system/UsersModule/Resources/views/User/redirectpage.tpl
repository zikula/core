<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" dir="{langdirection}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset={charset}" />
<meta name="description" content="{configgetvar name=sitename}" />
<meta name="copyright" content="Copyright (c) 2009 by {configgetvar name=sitename}" />
<meta name="generator" content="Zikula - http://zikula.org" />
<meta http-equiv="refresh" content="2;url={$url|safetext}" />
<title>{configgetvar name=sitename}</title>
<link rel="stylesheet" href="{$stylesheet}" type="text/css" />
<style type="text/css">
<!--
div {
    background-color: #E4DFF4;
    color: #000000;
    border: 1px solid #6483DE;
    text-align: center;
}

h1 {
    margin: 6px;
    font-size: 12px;
    color: #000000;
    font-family: Verdana, Arial;
    font-weight: normal;
}

h2 {
    margin: 6px;
    font-size: 11px;
    color: #000000;
    font-family: Verdana, Arial;
    font-weight: normal;
}
a:visited {
    color: #000000; text-decoration: none
}

a:hover {
    color: red; text-decoration: none
}

a:link {
    color: #000000; text-decoration: none
}
-->
</style>
</head>
<body>
    <div>
        <h1>{$message}</h1>
        <h2><a href="{$url|safetext}">{$redirectmessage}</a></h2>
    </div>
</body>
</html>
