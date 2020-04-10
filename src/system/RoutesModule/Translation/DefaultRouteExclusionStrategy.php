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

namespace Zikula\RoutesModule\Translation;

use JMS\I18nRoutingBundle\Router\DefaultRouteExclusionStrategy as BaseDefaultRouteExclusionStrategy;
use Symfony\Component\Routing\Route;
use Zikula\RoutesModule\Helper\ExtractTranslationHelper;

class DefaultRouteExclusionStrategy extends BaseDefaultRouteExclusionStrategy
{
    /**
     * @var ExtractTranslationHelper
     */
    private $extractTranslationHelper;

    public function __construct(ExtractTranslationHelper $extractTranslationHelper)
    {
        $this->extractTranslationHelper = $extractTranslationHelper;
    }

    public function shouldExcludeRoute($routeName, Route $route)
    {
        $exclude = parent::shouldExcludeRoute($routeName, $route);
        if ($exclude) {
            return $exclude;
        }

        $module = $route->getDefault('_zkModule');
        if (!empty($this->extractTranslationHelper->getBundleName())) {
            return $module !== $this->extractTranslationHelper->getBundleName();
        }

        return $exclude;
    }
}
