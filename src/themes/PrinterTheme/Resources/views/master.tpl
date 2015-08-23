<!DOCTYPE html>
<html lang="{lang}" dir="{langdirection}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
        <title>{pagegetvar name='title'}</title>
        <meta name="description" content="{$metatags.description}" />
        <meta name="keywords" content="{$metatags.keywords}" />
        <meta name="robots" content="noindex,follow,noarchive" />
        <link rel="stylesheet" href="{$stylepath}/style.css" type="text/css" media="print" />
    </head>
    <body>
        {$maincontent|footnotes}
        {footnotes assign="fn"}
        {if $fn}
        <div>
            <strong>{gt text="Links"}</strong>
        </div>
        {$fn}
        {/if}
    </body>
</html>
