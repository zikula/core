<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}" dir="{langdirection}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
    <title>{pagegetvar name='title'}</title>
    <meta name="description" content="{$modvars.ZConfig.slogan}" />
    <meta name="keywords" content="{$metatags.keywords}" />
    <link rel="stylesheet" type="text/css" href="style/core.css" media="print,projection,screen" />
    {browserhack condition="if IE"}<link rel="stylesheet" type="text/css" href="style/core_iehacks.css" media="print,projection,screen" />{/browserhack}
    <link rel="stylesheet" type="text/css" href="{$stylepath}/style.css" media="print,projection,screen" />
    <link rel="stylesheet" type="text/css" href="{$stylepath}/print.css" media="print" />
    <style type="text/css">
        body { color: #303030; font: 76%/1.4em Verdana,Tahoma,Arial,sans-serif; }
    </style>
</head>
<body>
    <h1 class="z-center" style="font-size: 1em; margin: 0 0 0.7em;">{gt text="Permission rule information"}</h1>
    <table class="z-datatable">
        <thead>
        <tr>
            <th>{gt text="Registered component"}</th>
            <th>{gt text="Instance template"}</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$schemas key=component item=instance name=schemas}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{$component|safetext}</td>
            <td>{$instance|safetext}</td>
        </tr>
        {/foreach}
        </tbody>
    </table>
</body>
</html>