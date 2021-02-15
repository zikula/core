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

namespace Zikula\RoutesModule\Form\Handler\Route;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouteCollection;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\RoutesModule\Form\Handler\Route\Base\AbstractEditHandler;
use Zikula\RoutesModule\Helper\PathBuilderHelper;
use Zikula\RoutesModule\Helper\RouteDumperHelper;
use Zikula\RoutesModule\Helper\SanitizeHelper;

/**
 * This handler class handles the page events of the Form called by the zikulaRoutesModule_route_edit() function.
 * It aims on the route object type.
 */
class EditHandler extends AbstractEditHandler
{
    /**
     * @var PathBuilderHelper
     */
    private $pathBuilderHelper;

    /**
     * @var RouteDumperHelper
     */
    private $routeDumperHelper;

    /**
     * @var SanitizeHelper
     */
    private $sanitizeHelper;

    /**
     * @var CacheClearer
     */
    private $cacheClearer;

    public function applyAction(array $args = []): bool
    {
        $this->sanitizeInput();
        if ($this->hasConflicts()) {
            return false;
        }

        $return = parent::applyAction($args);

        $this->cacheClearer->clear('symfony.routing');

        // reload **all** JS routes
        $this->routeDumperHelper->dumpJsRoutes();

        return $return;
    }

    protected function createForm(): ?FormInterface
    {
        $this->entityRef->setRouter($this->router);

        return parent::createForm();
    }

    /**
     * Ensures validity of input data.
     */
    private function sanitizeInput(): void
    {
        $entity = $this->entityRef;

        $entity
            ->setController($this->sanitizeHelper->sanitizeController($entity->getController()))
            ->setAction($this->sanitizeHelper->sanitizeAction($entity->getAction()))
            ->setSort(0)
        ;

        $this->entityRef = $entity;
    }

    /**
     * Checks for potential conflict.
     */
    private function hasConflicts(): bool
    {
        $newPath = $this->pathBuilderHelper->getPathWithBundlePrefix($this->entityRef);

        // when editing an existing route we expect that the path may exist once
        $amountOfRoutesWithSamePath = 0;
        $amountOfAllowedRoutesWithSamePath = 'create' === $this->templateParameters['mode'] ? 0 : 1;

        /** @var RouteCollection $routeCollection */
        $routeCollection = $this->router->getRouteCollection();

        $errors = [];
        foreach ($routeCollection->all() as $route) {
            $path = $route->getPath();
            if (in_array($path, ['/{url}', '/{path}'], true)) {
                continue;
            }

            if ($path === $newPath) {
                if (++$amountOfRoutesWithSamePath > $amountOfAllowedRoutesWithSamePath) {
                    $errors[] = [
                        'type' => 'SAME',
                        'path' => $path
                    ];
                }
                continue;
            }

            $pathRegExp = preg_quote(preg_replace('/{(.+)}/', '____DUMMY____', $path), '/');
            $pathRegExp = '#^' . str_replace('____DUMMY____', '(.+)', $pathRegExp) . '$#';

            $matches = [];
            preg_match($pathRegExp, $newPath, $matches);
            if (count($matches)) {
                $errors[] = [
                    'type' => 'SIMILAR',
                    'path' => $path
                ];
            }
        }

        $hasCriticalErrors = false;

        foreach ($errors as $error) {
            if ('SAME' === $error['type']) {
                $message = $this->trans('It looks like you created or updated a route with a path which already exists. This is an error in most cases.');
                $hasCriticalErrors = true;
                $flashType = 'error';
            } else {
                $message = $this->trans('The path of the route you created or updated looks similar to the following already existing path: %errorPath% Are you sure you haven\'t just introduced a conflict?', ['%errorPath%' => $error['path']]);
                $flashType = 'warning';
            }
            $request = $this->requestStack->getCurrentRequest();
            if ($request->hasSession() && ($session = $request->getSession())) {
                $session->getFlashBag()->add($flashType, $message);
            }
        }

        return $hasCriticalErrors;
    }

    /**
     * @required
     */
    public function setAdditionalDependencies(
        PathBuilderHelper $pathBuilderHelper,
        RouteDumperHelper $routeDumperHelper,
        SanitizeHelper $sanitizeHelper,
        CacheClearer $cacheClearer
    ): void {
        $this->pathBuilderHelper = $pathBuilderHelper;
        $this->routeDumperHelper = $routeDumperHelper;
        $this->sanitizeHelper = $sanitizeHelper;
        $this->cacheClearer = $cacheClearer;
    }
}
