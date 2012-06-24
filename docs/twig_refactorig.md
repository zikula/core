Twig Refactoring
================

Documentation can be found at http://twig.sensiolabs.org/documentation

Markup
------

There are two kinds of markup, `{{ ... }}` and tags as `{% ... %}`.
Variables are specified as `{{ name }}` and within `{% ... %}`, they are as in the next example.
Command blocks, e.g. `{% if name == 'drak' %}Hello Drak{% endif %}`
Functions are called like this: `{{ strtolower(var) }}`

Escaping
--------

Please note that all output is escaped automatically but for reference it would be done
using `{{ foo|e }}`

To **not** escape, use `{{ foo|raw }}`

Template File Structure
-----------------------

Templates are resolved using `<modulename>:<controllertype>:<templatefile>`

e.g. `ZikulaSearchModule:User:form.html.twig`

Templates are stored according to `controller` and `action` as follows

Resources/views/`<Controller>`/`<action>`.`<type>`.`<extension>`
e.g. `Resources/views/User/view.html.twig`

Refactor as follows:

Before:

    templates/search_user_view.tpl
    templates/search_admin_edit.tpl

After:

    Resources/views/User/view.html.twig
    Resources/views/Admin/edit.html.twig

Controller code
---------------

Essentially a twig template can be rendered with

    return $this->get('templating')->renderResponse('VendorNameModule:Controllername:foo.html.twig', $vars);

The `$vars` is a keyed array of variables to pass to the template,
as was done with `$this->view->assign($name, $value);`

Global Variables
----------------

Global variables exists across all templates, so far the list is

  - `title` (string)

### Gettext

    {gt text="foo"}

becomes:

    {{ __('foo') }}

### Page vars

These will probably be deprecated since there are other way to do things but for now:

`{pageaddvar}` becomes  `{{ pageaddvar(name, value) }}`
`{pagesetvar}` becomes `{{ pagesetvar(name, value) }}`

### Includes

    {include 'name_foo_user.tpl'}

becomes

    {% include 'VendorNameModule:User:foo.html.twig' %}

## Conditionals

    <option value="oldest"{if $searchtype eq 'oldest'} selected="selected"{/if}>{gt text="Oldest first" domain='zikula'}</option>

becomes:

    <option value="oldest"{% if searchtype == 'oldest' %} selected="selected"{% endif %}>{{ __("Oldest first") }}</option>

## For loops

    {foreach from=$plugin_options key='plugin' item='plugin_option'}
        {$plugin_option}
    {/foreach}#}

becomes:

    {% for plugin, plugin_option in plugin_options %}
        {{ plugin_option }}
    {% endfor %}

