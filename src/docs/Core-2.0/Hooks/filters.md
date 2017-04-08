Filters
=======

NOTICE: This is a Core-1.x-era document that is still basically correct, but has some
errors. It will be updated as soon as possible.

A filter is simply a something that alters data in some way.  A filter
might be used to sanitize HTML content, format text in some way, or
otherwise transform content.


Category
--------

Filters are implemented in their own category 'filters'.


Area Type
---------

There is only one kind of hook type in a filter bundle:

    filter   - This is a filter to be applied in a given area.  Filters should probably
               Have their own separate area(s) as it would give a user more control over
               what filters are applied and where.


Subscriber Implementation
-------------------------

Usage in a template:

    {$var|notifyfilters:'news.filter_hooks.articles.filter'}

This generates a Zikula_FilterHook event object that has the event name and
the data to be filtered.


Provider Implementation
-----------------------

    class Handler
    {
        public static function filter(Zikula_FilterHook $hook)
        {
            $data = $hook->getData();
            $data = str_replace('foo', 'bar', $data);
            $hook->setData($data);
        }
    }




