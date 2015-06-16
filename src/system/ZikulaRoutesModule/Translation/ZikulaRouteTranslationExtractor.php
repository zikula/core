<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\RoutesModule\Translation;

use JMS\I18nRoutingBundle\Router\I18nRouter;

use JMS\I18nRoutingBundle\Router\RouteExclusionStrategyInterface;

use Symfony\Component\Routing\RouterInterface;
use JMS\TranslationBundle\Model\Message;
use JMS\TranslationBundle\Model\MessageCatalogue;
use JMS\TranslationBundle\Translation\ExtractorInterface;

/**
 * This extractor duplicates \JMS\I18nRoutingBundle\Translation\RouteTranslationExtractor
 * adding only the Zikula module prefix as requested
 */
class ZikulaRouteTranslationExtractor implements ExtractorInterface
{
    private $router;
    private $routeExclusionStrategy;
    private $domain = 'routes';

    public function __construct(RouterInterface $router, RouteExclusionStrategyInterface $routeExclusionStrategy)
    {
        $this->router = $router;
        $this->routeExclusionStrategy = $routeExclusionStrategy;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    public function extract()
    {
        $catalogue = new MessageCatalogue();

        $collection = $this->router instanceof I18nRouter ? $this->router->getOriginalRouteCollection()
            : $this->router->getRouteCollection();

        foreach ($collection->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                continue;
            }

            ///////////////////////////////////////
            // Begin customizations

            $meaning = "Route Controller and method: " . $route->getDefault('_controller'); // set a default value

            // prefix with zikula module url if requested
            if ($route->hasDefault('_zkModule')) {
                $zkNoBundlePrefix = $route->getOption('zkNoBundlePrefix');
                if (!isset($zkNoBundlePrefix) || !$zkNoBundlePrefix) {
                    $meaning = "This is a route from the " . $route->getDefault('_zkModule') . "Bundle and will include a translated prefix.";
                }
            }

            // End customizations
            ///////////////////////////////////////

            $message = new Message($name, $this->domain);
            $message->setDesc($route->getPath());
            if (isset($meaning)) {
                $message->setMeaning($meaning);
            }
            $catalogue->add($message);
        }

        return $catalogue;
    }
}