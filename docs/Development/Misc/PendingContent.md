---
currentMenu: dev-misc
---
# Pending Content

This document details the `Zikula\BlocksModule\Event\PendingContentEvent` which is used to collect information from extensions about
pending content items like news submissions, user verifications or similar.

Extensions needing to publish pending content information should create an event handler for `Zikula\BlocksModule\Event\PendingContentEvent` using
the DependencyInjection (DI) component of Symfony.

Create a class like this to handle the event:

```php
// file: FooModule/EventListener/PendingContentListener.php

namespace Acme\FooModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\BlocksModule\Collectible\PendingContentCollectible;
use Zikula\BlocksModule\Event\PendingContentEvent;
use Zikula\Bundle\CoreBundle\Collection\Container;

class PendingContentListener implements EventSubscriberInterface
{
    public function getPendingContent(PendingContentEvent $event)
    {
        $collection = new Container('AcmeFooModule');
        // PendingContentCollectible(<type>, <description>, <number>, <route>)
        $collection->add(new PendingContentCollectible('foo', $this->translator->trans('Pending foo'), 5, 'acmefoomodule_admin_viewfoo'));
        $collection->add(new PendingContentCollectible('bar', $this->translator->trans('Pending bar'), 7, 'acmefoomodule_admin_viewbar'));
        $event->addCollection($collection);
    }

    public static function getSubscribedEvents()
    {
        return [
            PendingContentEvent::class => [
                ['getPendingContent']
            ]
        ];
    }
}
```

## How it is used

If you are interested to see how the data is processed, you can look at the 
`Zikula\BlocksModule\Block\PendingContentBlock::display` method.
