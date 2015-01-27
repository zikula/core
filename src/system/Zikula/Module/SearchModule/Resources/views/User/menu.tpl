{insert name='getstatusmsg'}
{gt text='Site search' assign='title' domain='zikula'}
<h2>{$title|safetext}</h2>
{pagesetvar name='title' value=$title}
{modulelinks modname='ZikulaSearchModule' type='user'}
