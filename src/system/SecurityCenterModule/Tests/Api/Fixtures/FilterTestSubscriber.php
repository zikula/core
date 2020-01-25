<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterModule\Tests\Api\Fixtures;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\Event\GenericEvent;
use Zikula\SecurityCenterModule\Api\ApiInterface\HtmlFilterApiInterface;

class FilterTestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            HtmlFilterApiInterface::HTML_STRING_FILTER => ['test']
        ];
    }

    public function test(GenericEvent $event): void
    {
        $string = $event->getData();
        $string = '***' . $string . '***';

        $event->setData($string);
    }
}
