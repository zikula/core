{nocache}{php}header("Content-type: application/atom+xml");{/php}{/nocache}
<?xml version="1.0" encoding="{charset}"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <link rel="alternate" type="text/html" href="{$baseurl}" />
    <link rel="self" type="application/atom+xml" href="{getcurrenturl}" />
    <title>{pagegetvar name='title'}</title>
    <subtitle>{$modvars.ZConfig.slogan}</subtitle>
    <id>{id}</id>
    <updated>{updated}</updated>
    <author>
        <name>{$modvars.ZConfig.adminmail}</name>
    </author>
    <generator>{$modvars.ZConfig.Version_ID}</generator>
    <rights>Copyright {$modvars.ZConfig.sitename}</rights>
    {$maincontent}
</feed>
