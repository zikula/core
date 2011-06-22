<?xml version="1.0" encoding="{charset}"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="{getcurrenturl}" rel="self" type="application/rss+xml" />
<title>{pagegetvar name='title'}</title>
<link>{$baseurl}</link>
<description>{$metatags.description}</description>
<language>{lang}</language>
{* here you can add your image if you want to *}
{* <image>
 <title>{$modvars.ZConfig.sitename}</title>
 <url>{$baseurl}images/logo.gif</url>
 <link>{$baseurl}</link>
</image> *}
<managingEditor>{gt text="Administrator"} {$modvars.ZConfig.adminmail} ({$modvars.ZConfig.sitename})</managingEditor>
<webMaster>{gt text="Administrator"} {$modvars.ZConfig.adminmail} ({$modvars.ZConfig.sitename})</webMaster>
{$maincontent}
</channel>
</rss>
{nocache}{xmlHeader}{/nocache}