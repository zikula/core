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

namespace Zikula\RoutesModule\Helper;

use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Configurator;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\RoutesModule\Translation\ZikulaPatternGenerationStrategy;

class MultilingualRoutingHelper
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var string
     */
    private $projectDir;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        string $projectDir,
        string $installed
    ) {
        $this->variableApi = $variableApi;
        $this->cacheClearer = $cacheClearer;
        $this->projectDir = $projectDir;
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
