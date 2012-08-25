<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{lang}">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset={charset}" />
        <title>{pagegetvar name='title'}</title>
        <meta name="description" content="{$metatags.description}" />
        <meta name="keywords" content="{$metatags.keywords}" />
        <meta name="robots" content="noindex,follow,noarchive" />
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
    	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.css" />
    	<script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
    	<script src="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.js"></script>
    </head>
    <body>            
            <div data-role="page">
            
            	<div data-role="header"">
            		<h1>{$modvars.ZConfig.sitename}</h1>
            		<a href="{homepage}" data-icon="home" data-theme="b">{gt text='Home'}</a>
            	</div><!-- /header -->
            
            	<div data-role="content">
            	
            		{$maincontent}  
            		
            		
            </div><!-- /page -->

</body>
</html>
