<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Helper;

use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\DynamicConfigDumper;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\RoutesModule\Translation\ZikulaPatternGenerationStrategy;

class MultilingualRoutingHelper
{
    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var DynamicConfigDumper
     */
    private $configDumper;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    /**
     * @var bool
     */
    private $installed;

    public function __construct(
        VariableApiInterface $variableApi,
        DynamicConfigDumper $configDumper,
        CacheClearer $cacheClearer,
        string $installed
    ) {
        $this->variableApi = $variableApi;
        $this->configDumper = $configDumper;
        $this->cacheClearer = $cacheClearer;
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

        $this->configDumper->setConfiguration('jms_i18n_routing', [
            'strategy' => $strategy
        ]);

        $this->cacheClearer->clear('symfony');

        return true;
    }
}
