<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesBundle\Helper;

use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\RoutesBundle\Translation\ZikulaPatternGenerationStrategy;

class MultilingualRoutingHelper
{
    private bool $installed;

    public function __construct(
        private readonly VariableApiInterface $variableApi,
        private readonly CacheClearer $cacheClearer,
        private readonly string $projectDir,
        string $installed
    ) {
        $this->installed = '0.0.0' !== $installed;
    }

    /**
     * Reloads the multilingual routing settings by reading system variables
     * and checking installed languages.
     */
    public function reloadMultilingualRoutingSettings(): bool
    {
        $isRequiredLangParameter = $this->installed
            ? $this->variableApi->getSystemVar('languageurl', 0)
            : 0
        ;

        $strategy = $isRequiredLangParameter
            ? ZikulaPatternGenerationStrategy::STRATEGY_PREFIX
            : ZikulaPatternGenerationStrategy::STRATEGY_PREFIX_EXCEPT_DEFAULT
        ;

        $configurator = new Configurator($this->projectDir);
        $configurator->loadPackages('zikula_routes');
        $configurator->set('zikula_routes', 'jms_i18n_routing_strategy', $strategy);
        $configurator->write();
        $this->cacheClearer->clear('symfony.config');

        return true;
    }
}
