<!DOCTYPE html>
<html lang="{lang}" xml:lang="{lang}">
<head>
    <meta http-equiv="refresh" content="{$delay|default:4};{$url|default:"/"}">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Zikula redirect page">
    <meta name="author" content="Zikula Development Team">
    <title>{gt text="Redirecting..."}</title>
    <meta name="generator" content="Zikula -- http://www.zikula.org" />
    <meta http-equiv="X-UA-Compatible" content="chrome=1" />
    <link rel="stylesheet" href="{$baseurl}/web/bootstrap-font-awesome.css" type="text/css" />
</head>
<body>
<div class="site-wrapper">
    <div class="container">
        <div class="jumbotron text-center" style="margin-top: 4em;">
            <h1>{gt text='Doing something very important!'}</h1>
            {gt text='Please be patient. You will be redirected in a moment.' assign='defaultmessage'}
            <p>{$message|default:$defaultmessage}</p>
            <p><i class="fa fa-4x fa-spinner fa-spin"></i></p>
            <p style="margin-top: 4em"><a class="btn btn-primary btn-lg" href="{$url}" role="button">{gt text="Proceed now"}</a></p>
        </div>
    </div>
</div>
</body>
</html>
