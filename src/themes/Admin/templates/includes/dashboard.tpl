<div class="dashboard">
<div style="width:49%;height:auto;float:left;" class="dash-div draggable">
<span style="float:right;"><a href="#" title="{gt text='open/close'}" onclick="Effect.toggle('hint-1','BLIND'); return false;">{gt text='open/close'}</a></span>
<h2>{gt text='Pending Content'}</h2>
<div class="dash-content-div" id="hint-1" style="display:none">
<ul>
<li>2 pending News</li>
<li>3 new Newsletter Useres waiting for approval</li>
<li>4 pending Mediashare Items</li>
</ul>
</div>
</div>
<div style="width:49%;height:auto;float:right;" class="dash-div draggable">
<span style="float:right;"><a href="#" title="{gt text='open/close'}" onclick="Effect.toggle('hint-2','BLIND'); return false;">{gt text='open/close'}</a></span>
<h2>{gt text='Counters &amp; Stats'}</h2>
<div class="dash-content-div" id="hint-2" style="display:none">
<ul>
<li>400 Newsarticles</li>
<li>45000 Postings</li>
<li>4000 Comments</li>
<li>39 PageMaster Publications</li>
</ul>
</div>
</div>
<div style="clear:both;"></div>
<div style="width:49%;height:auto;float:left;" class="dash-div draggable">
<span style="float:right;"><a href="#" title="{gt text='open/close'}" onclick="Effect.toggle('hint-3','BLIND'); return false;">{gt text='open/close'}</a></span>
<h2>{gt text='Extension Updates'}</h2>
<div class="dash-content-div" id="hint-3" style="display:none">
{php}
	$src = 'http://community.zikula.org/index.php?module=Extensions&func=view&ot=component&comptype=0&sort=lu_date&sdir=desc&tpl=rss&raw=1&catms=0&catma=0&catmf=0&cattt=0&catrt=0&catcl=0';
	$chan = 'y';
	$num = 4;
	$desc = 0;
	$html = 'y';
	$tz = 'feed';
	$utf = 'y';
	$date = 'n';
	$targ = 'y';
	include 'themes/Admin/templates/rss/feed2php.inc';
{/php}
</div>
</div>
<div style="width:49%;height:auto;float:right;" class="dash-div draggable">
<span style="float:right;"><a href="#" title="{gt text='open/close'}" onclick="Effect.toggle('hint-4','BLIND'); return false;">{gt text='open/close'}</a></span>
<h2>{gt text='Community News'}</h2>
<div class="dash-content-div" id="hint-4" style="display:none">
{php}
    // adding Feed2Js variables
	$src = 'http://community.zikula.org/index.php?module=News&theme=RSS';
	$chan = 'y';
	$num = 4;
	$desc = 200;
	$html = 'y';
	$tz = 'feed';
	$utf = 'y';
	$date = 'y';
	$targ = 'y';
	include 'themes/Admin/templates/rss/feed2php.inc';
{/php}
</div>
</div>

</div>