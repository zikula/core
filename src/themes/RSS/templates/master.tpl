<?xml version="1.0" encoding="{charset}"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<atom:link href="{getcurrenturl}" rel="self" type="application/rss+xml" />
<title>{pagegetvar name='title'}</title>
<link>{getbaseurl}</link>
<description>{$metatags.description}</description>
<language>{lang}</language>
{* here you can add your image if you want to *}
{* <image>
 <title>{configgetvar name="sitename"}</title>
 <url>{getbaseurl}images/logo.gif</url>
 <link>{getbaseurl}</link>
</image> *}
<managingEditor>{gt text="Administrator"} {configgetvar name="adminmail"} ({configgetvar name="sitename"})</managingEditor>
<webMaster>{gt text="Administrator"} {configgetvar name="adminmail"} ({configgetvar name="sitename"})</webMaster>
{$maincontent}
</channel>
</rss>
{nocache}{xmlHeader}{/nocache}