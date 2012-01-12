{gt text="Recent searches" assign=templatetitle domain='zikula'}
{include file='search_user_menu.tpl'}

<h3>{$templatetitle}</h3>
<table class="z-datatable">
    <thead>
        <tr>
            <th>{gt text="Search keywords" domain='zikula'}</th>
            <th>{gt text="Number of searches" domain='zikula'}</th>
            <th>{gt text="Date of last search" domain='zikula'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$recentsearches item=recentsearch}
        <tr class="{cycle values="z-odd,z-even"}">
            <td><a href="{modurl modname='Search' type='user' func='search' q=$recentsearch.search|urlencode}">{$recentsearch.search|replace:' ':', '|safetext}</a></td>
            <td>{$recentsearch.count|safetext}</td>
            <td>{$recentsearch.date|date_format}</td>
        </tr>
        {foreachelse}
        <tr class="z-datatableempty"><td colspan="3">{gt text="No items found." domain='zikula'}</td></tr>
        {/foreach}
    </tbody>
</table>
