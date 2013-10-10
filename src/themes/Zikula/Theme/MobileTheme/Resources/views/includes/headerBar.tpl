<div data-role="header" data-theme="b">
    {if $enablePanel == 'left'}
        <a href="#leftPanel" data-theme="c" title="Navigation"><i class="icon-th-list"></i></a>
    {/if}
    <a href="{homepage}" data-icon="home" data-theme="c">{gt text='Home'}</a>
    {if $enablePanel == 'right'}
        <a href="#rightPanel" data-theme="c" title="Navigation"><i class="icon-th-list"></i></a>
    {/if}
    <h1>{$modvars.ZConfig.sitename}</h1>
</div><!-- /header -->