<!DOCTYPE html>
<html lang="{lang}" dir="{langdirection}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
        <title>{pagegetvar name='title'}</title>
        <meta name="description" content="{$metatags.description}" />
        <meta name="keywords" content="{$metatags.keywords}" />
        {pageaddvar name="stylesheet" value="web/bootstrap/css/bootstrap-theme.min.css"}
        {pageaddvar name="stylesheet" value="$stylepath/fluid960gs/reset.css"}
        {pageaddvar name="stylesheet" value="$stylepath/fluid960gs/$layout.css"}
        {pageaddvar name="stylesheet" value="$stylepath/style.css"}
        {browserhack condition="if IE 7" assign="ieconditional"}<link rel="stylesheet" type="text/css" href="{$stylepath}/fluid960gs/ie.css" media="screen" />{/browserhack}
        {pageaddvar name='header' value=$ieconditional}
    </head>
    <body>

        <div id="theme_page_container" class="container_16">
            <div id="theme_header">
                <h1 class="title"><a href="{homepage}">{$modvars.ZConfig.sitename}</a></h1>
                <h2 class="slogan">{$modvars.ZConfig.slogan}</h2>
            </div>
