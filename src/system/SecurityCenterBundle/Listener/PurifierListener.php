<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SecurityCenterBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\SecurityCenterBundle\Event\FilterHtmlEvent;
use Zikula\SecurityCenterBundle\Helper\PurifierHelper;

class PurifierListener implements EventSubscriberInterface
{
    private bool $installed;

    private bool $isUpgrading;

    public function __construct(
        string $installed,
        $isUpgrading, // cannot cast to bool because set with expression language
        private readonly VariableApiInterface $variableApi,
        private readonly PurifierHelper $purifierHelper
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->isUpgrading = $isUpgrading;
    }

    public static function getSubscribedEvents()
    {
        return [
            FilterHtmlEvent::class => ['purify']
        ];
    }

    public function purify(FilterHtmlEvent $event): void
    {
        if (!$this->installed || $this->isUpgrading) {
            return;
        }

        if (1 > $this->variableApi->getSystemVar('outputfilter')) {
            return;
        }

        static $safeCache;
        $string = $event->getHtmlContent();

        $md5 = md5($string);
        if (!isset($safeCache[$md5])) {
            $safeCache[$md5] = $this->purifierHelper->getPurifier()->purify($string);
        }

        $event->setHtmlContent($safeCache[$md5]);
    }
}
