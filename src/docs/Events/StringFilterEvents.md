String Filter Events
====================

class: `\Zikula\SecurityCenterModule\Api\HtmlFilterApiInterface`

    /**
     * Occurs when a string is passed to HtmlFilterApi and filtered.
     * An instance of Zikula\Core\Event\GenericEvent, the data is the filterable string.
     */
    const HTML_STRING_FILTER = 'htmlfilter.outputfilter';
