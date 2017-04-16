<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Tests\Api\Fixtures;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

class FilterTestSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            HtmlFilterApiInterface::HTML_STRING_FILTER => ['test']
        ];
    }

    public function test(GenericEvent $event)
    {
        $string = $event->getData();
        $string = '***' . $string . '***';

        $event->setData($string);
    }
}
