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

namespace Zikula\Bundle\CoreBundle\Translation\Extractor\Visitor\Php\Routes;

use JMS\I18nRoutingBundle\Router\I18nRouter;
use JMS\I18nRoutingBundle\Router\RouteExclusionStrategyInterface;
use PhpParser\Node;
use PhpParser\NodeVisitor;
use Symfony\Component\Routing\RouterInterface;
use Translation\Extractor\Model\SourceLocation;
use Translation\Extractor\Visitor\Php\BasePHPVisitor;

/**
 * This extractor ensures routes are externalised as part of the translation:extract command.
 */
class ZikulaRouteTranslationExtractor extends BasePHPVisitor implements NodeVisitor
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var RouteExclusionStrategyInterface
     */
    private $routeExclusionStrategy;

    /**
     * @var string
     */
    private $domain = 'routes';

    /**
     * @var bool
     */
    private $routesExported = false;

    public function __construct(
        RouterInterface $router,
        RouteExclusionStrategyInterface $routeExclusionStrategy
    ) {
        $this->router = $router;
        $this->routeExclusionStrategy = $routeExclusionStrategy;
    }

    public function beforeTraverse(array $nodes): ?Node
    {
        return null;
    }

    public function enterNode(Node $node): ?Node
    {
        if (true === $this->routesExported) {
            return null;
        }

        $collection = $this->router instanceof I18nRouter
            ? $this->router->getOriginalRouteCollection()
            : $this->router->getRouteCollection()
        ;

        foreach ($collection->all() as $name => $route) {
            if ($this->routeExclusionStrategy->shouldExcludeRoute($name, $route)) {
                continue;
            }

            $location = new SourceLocation($name, '', 0, [
                'domain' => $this->domain,
                'desc' => $route->getPath()
            ]);
            $this->collection->addLocation($location);
        }

        $this->routesExported = true;

        return null;
    }

    public function leaveNode(Node $node): ?Node
    {
        return null;
    }

    public function afterTraverse(array $nodes): ?Node
    {
        return null;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }
}
