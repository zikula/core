{menu from=$menuitems item='item' name='extmenu' class='pull-left'}
{% if item.name != '' && item.url != '' %}
<li{% if item.url|replace:baseurl:'' == currenturi|urldecode %} class="selected"{% endif %}>
    <a href="{$item.url|safetext}" title="{{ item.title }}">
        {% if item.image != '' %}
        <img src="{{ item.image }}" alt="{{ item.title }}" />
        {% endif %}
        {{ item.name }}
    </a>
</li>
{% endif %}
{/menu}
