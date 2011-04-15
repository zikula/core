<div class="dashboard-content-head" style="margin-left:15px;margin-right:15px;float:center;">
    <h2 class="left">{gt text='News'}</h2>
</div>   
<div class="z-form" style="margin-top:12px">
    <div class="rss-item" style="padding:18px;">
        <fieldset>
            <legend> &raquo; {gt text='Extensions'}</legend>
            <p>{gt text='Updates for your Modules:'}</p>
            <br />
            {php}
                $src = 'http://community.zikula.org/index.php?module=Extensions&func=view&ot=component&comptype=0&sort=lu_date&sdir=desc&tpl=rss&raw=1&catms=0&catma=0&catmf=0&cattt=0&catrt=0&catcl=0';
                $chan = 'n';
                $num = 5;
                $desc = 0;
                $html = 'y';
                $tz = 'feed';
                $utf = 'y';
                $date = 'n';
                $targ = 'y';
                include 'themes/Admin/templates/rss/feed2php.inc';
            {/php}
        </fieldset>
    </div>
    <div class="rss-item" style="padding:18px;">
        <fieldset>
            <legend> &raquo; {gt text='News'}</legend>
            <p>{gt text='Latest Messages from the Community:'}</p>
            <br />
            {php}
                $src = 'http://community.zikula.org/index.php?module=News&theme=RSS';
                $chan = 'n';
                $num = 5;
                $desc = 0;
                $html = 'n';
                $tz = 'feed';
                $utf = 'y';
                $date = 'n';
                $targ = 'y';
                include 'themes/Admin/templates/rss/feed2php.inc';
            {/php}
        </fieldset>
    </div>
    <div class="rss-item" style="padding:18px;">
        <fieldset>
            <legend> &raquo; {gt text='Blog'}</legend>
            <p>{gt text='Latest Blog articles:'}</p>
            <br />
            {php}
                $src = 'http://blog.zikula.org/index.php?module=CMS&tid=1&template=rss&rss=true';
                $chan = 'n';
                $num = 5;
                $desc = 0;
                $html = 'y';
                $tz = 'feed';
                $utf = 'y';
                $date = 'n';
                $targ = 'y';
                include 'themes/Admin/templates/rss/feed2php.inc';
            {/php}
        </fieldset>
    </div>
    <div class="rss-item" style="padding:18px;">
        <fieldset>
           <legend> &raquo; {gt text='Wiki'}</legend>
            <p>{gt text='Latest International Wiki Changes:'}</p>
            <br />
            {php}
                $src = 'http://community.zikula.org/module-Wiki-recentchangesxml-theme-rss.htm';
                $chan = 'n';
                $num = 5;
                $desc = 0;
                $html = 'y';
                $tz = 'feed';
                $utf = 'y';
                $date = 'n';
                $targ = 'y';
                include 'themes/Admin/templates/rss/feed2php.inc';
            {/php}
        </fieldset>  
    </div>
</div>
