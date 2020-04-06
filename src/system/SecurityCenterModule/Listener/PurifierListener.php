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

namespace Zikula\SecurityCenterModule\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\SecurityCenterModule\Event\FilterHtmlEvent;
use Zikula\SecurityCenterModule\Helper\PurifierHelper;

class PurifierListener implements EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $installed;

    /**
     * @var bool
     */
    private $isUpgrading;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var PurifierHelper
     */
    private $purifierHelper;

    public function __construct(
        string $installed,
        $isUpgrading, // cannot cast to bool because set with expression language
        VariableApiInterface $variableApi,
        PurifierHelper $purifierHelper
    ) {
        $this->installed = '0.0.0' !== $installed;
        $this->isUpgrading = $isUpgrading;
        $this->variableApi = $variableApi;
        $this->purifierHelper = $purifierHelper;
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
