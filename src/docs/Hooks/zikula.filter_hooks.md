# Hook types

## 'filter_hooks' category

See `\Zikula\Bundle\HookBundle\Category\FilterHooksCategory`.

```php
public const NAME = 'filter_hooks';

/**
 * Dispatches FilterHook instances.
 */
public const TYPE_FILTER = 'filter';
```

A filter is simply a something that alters data in some way.
A filter might be used to sanitize HTML content, format text in some way, or otherwise transform content.

## Area Type

There is only one kind of hook type in a filter bundle:

    filter   - This is a filter to be applied in a given area.  Filters should probably
               Have their own separate area(s) as it would give a user more control over
               what filters are applied and where.

## Subscriber implementation

Usage in a Twig template:

```twig
{{ var|notifyfilters:'news.filter_hooks.articles.filter'|safeHtml }}
```

This generates a `Zikula\Bundle\HookBundle\Hook\FilterHook` event object that has the event name and
the data to be filtered.

## Provider implementation

See [zikula.ui_hooks.ProviderImplementation.md](zikula.ui_hooks.ProviderImplementation.md).
