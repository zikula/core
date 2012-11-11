{include file="includes/header.tpl"}

<div data-role="header">
    <h1>{$modvars.ZConfig.sitename}</h1>
    <a href="{homepage}" data-icon="home" data-theme="b">{gt text='Home'}</a>
</div><!-- /header -->

<div data-role="content">
    {$maincontent}
</div><!-- /content -->

{include file="includes/footer.tpl"}

