---
currentMenu: security-center
---
# String filter events

The `\Zikula\SecurityCenterModule\Api\HtmlFilterApiInterface` class provides this event:

```php
/**
 * Occurs when a string is passed to HtmlFilterApi and filtered.
 * An instance of Zikula\Bundle\CoreBundle\Event\GenericEvent, the data is the filterable string.
 */
public const HTML_STRING_FILTER = 'htmlfilter.outputfilter';
```
