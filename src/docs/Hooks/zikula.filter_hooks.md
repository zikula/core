Hook Types
==========

### 'filter_hooks' category

    see \Zikula\Bundle\HookBundle\Category\FilterHooksCategory

    filter          - Filter's content in a template.


A filter is simply a something that alters data in some way.  A filter might be used to sanitize HTML content, format
text in some way, or otherwise transform content.


Area Type
---------

There is only one kind of hook type in a filter bundle:

    filter   - This is a filter to be applied in a given area.  Filters should probably
               Have their own separate area(s) as it would give a user more control over
               what filters are applied and where.


Subscriber Implementation
-------------------------

Usage in a twig template:

    {{ var|notifyfilters:'news.filter_hooks.articles.filter'|safeHtml }}

This generates a `Zikula\Bundle\HookBundle\Hook\FilterHook` event object that has the event name and
the data to be filtered.


Provider Implementation
-----------------------

see `/src/docs/Hooks/zikula.ui_hooks.ProviderImplementation.md`
