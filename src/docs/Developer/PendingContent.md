# Pending Content

This document details the `get.pending_content` event which is used to collect information from modules about
pending content items like news submissions, user verifications or similar.

Modules needing to publish pending content information should create an event handler for `get.pending_content` using
the DependencyInjection (DI) component of Symfony.

Create a class like this to handle the event:

```php
// file: FooModule/EventListener/PendingContentListener.php

namespace Acme\FooModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\Collection\Container;
use Zikula\Bundle\CoreBundle\Collection\Collectible\PendingContentCollectible;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;

class PendingContentListener implements EventSubscriberInterface
{
    public function getPendingContent(GenericEvent $event)
    {
        $collection = new Container('AcmeFooModule');
        // PendingContentCollectible(<type>, <description>, <number>, <route>)
        $collection->add(new PendingContentCollectible('foo', $this->translator->trans('Pending foo'), 5, 'acmefoomodule_admin_viewfoo'));
        $collection->add(new PendingContentCollectible('bar', $this->translator->trans('Pending bar'), 7, 'acmefoomodule_admin_viewbar'));
        $event->getSubject()->add($collection);
    }

    public static function getSubscribedEvents()
    {
        return [
            'get.pending_content' => [
                ['getPendingContent']
            ]
        ];
    }
}
```

## How it is used

If you are interested to see how the data is processed, you can look at the 
`Zikula\BlocksModule\Block\PendingContentBlock::display` method.
