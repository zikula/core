{ajaxheader ui=true imageviewer="true"}
{* For ajax modify and image uploading *}
{if $modvars.News.enableajaxedit}
    {if $modvars.News.picupload_enabled}
    {pageaddvar name='javascript' value='modules/News/javascript/multifile.js'}
    {/if}
{/if}

{if $modvars.News.enabledescriptionvar}
{setmetatag name='description' value=$info.hometext|notifyfilters:'news.hook.articlesfilter.ui.filter'|strip_tags|trim|truncate:$modvars.News.descriptionvarchars}
{/if}

{nocache}{include file='user/menu.tpl'}{/nocache}
{insert name='getstatusmsg'}



<div id="news_articlecontent">
    {include file='user/articlecontent.tpl'}
</div>
<div id="news_modify">&nbsp;</div>

{if $modvars.News.enablemorearticlesincat AND $morearticlesincat > 0}
<div id="news_morearticlesincat">
<h4>{gt text='More articles in category '}
{foreach name='categorynames' from=$preformat.categorynames item='categoryname'}
{$categoryname}{if $smarty.foreach.categorynames.last neq true}&nbsp;&amp;&nbsp;{/if}
{/foreach}</h4>
<ul>
    {foreach from=$morearticlesincat item='morearticle'}
    <li><a href="{modurl modname='News' type='user' func='display' sid=$morearticle.sid}">{$morearticle.title|safehtml}</a> ({gt text='by %1$s on %2$s' tag1=$morearticle.contributor tag2=$morearticle.from|dateformat:'datebrief'})</li>
    {/foreach}
</ul>
</div>
{/if}

{* the next code is to display any hooks (e.g. comments, ratings). All hooks are stored in $hooks and called individually. EZComments is not called when Commenting is not allowed *}
{notifydisplayhooks eventname='news.ui_hooks.articles.display_view' id=$info.sid assign='hooks'}
{foreach from=$hooks key='provider_area' item='hook'}
{if !(($provider_area eq 'provider.ezcomments.ui_hooks.comments') and ($info.allowcomments eq 0))}
{$hook}
{/if}
{/foreach}