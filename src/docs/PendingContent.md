Pending Content
===============

This document details the `get.pending_content` event which is used to collect information from modules about
pending content items like news submissions, user verifications or similar.

Modules needing to publish pending content information should create an event handler for 'get.pending_content' using
the DependencyInjection (DI) component of Symfony.

First create a class to handle the event:

    // file: FooModule/EventListener/PendingContentListener.php

    namespace Acme\FooModule\EventListener;
    
    use Symfony\Component\EventDispatcher\EventSubscriberInterface;
    use Zikula\Common\Collection\Container;
    use Zikula\Common\Collection\Collectible\PendingContentCollectible;
    use Zikula\Core\Event\GenericEvent;
    
    class PendingContentListener implements EventSubscriberInterface
    {
        public function getPendingContent(GenericEvent $event)
        {
            $collection = new Container('AcmeFooModule');
            // PendingContentCollectible(<type>, <description>, <number>, <route>)
            $collection->add(new PendingContentCollectible('foo', __('Pending foo'), 5, 'acmefoomodule_admin_viewfoo'));
            $collection->add(new PendingContentCollectible('bar', __('Pending bar'), 7, 'acmefoomodule_admin_viewbar'));
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

Second, register the class as an event listener in the module's service definitions:

    <!-- file: FooModule/Resources/config/services.xml -->

    <service id="acmefoomodule.event_listener.pending_content" class="Acme\FooModule\EventListener\PendingContentListener">
        <tag name="kernel.event_subscriber" />
    </service>

Note that the module must have the DependencyInjection component set up in the bundle to function.


How it is used
--------------

If you are interested to see how the data is processed, you can look at the 
`Zikula\BlocksModule\Block\PendingContentBlock::display` method.


Upgrading from Core-1.3 style Persistent Handlers
-------------------------------------------------

Be sure to remove the old persistent handler in the module's upgrade routine:

    EventUtil::unregisterPersistentModuleHandlers('FooModule');

This will clear the database of all persistent handlers for the module.
