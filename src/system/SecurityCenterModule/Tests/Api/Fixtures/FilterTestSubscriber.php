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
use Zikula\SecurityCenterModule\Event\FilterHtmlEvent;

class FilterTestSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FilterHtmlEvent::class => ['test']
        ];
    }

    public function test(FilterHtmlEvent $event): void
    {
        $string = $event->getHtmlContent();
        $string = '***' . $string . '***';

        $event->setHtmlContent($string);
    }
}
