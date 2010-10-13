<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}">
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
