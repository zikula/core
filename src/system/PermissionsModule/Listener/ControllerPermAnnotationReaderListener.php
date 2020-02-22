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

namespace Zikula\PermissionsModule\Listener;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Zikula\PermissionsModule\Annotation\PermRequired as PermAnnotation;

class ControllerPermAnnotationReaderListener implements EventSubscriberInterface
{
    /**
     * flag of route attribute parameter
     */
    const ROUTE_ATTRIBUTE_FLAG = '$';

    /**
     * @var array
     */
    private $accessMap = [
        'ACCESS_ADMIN' => 'admin',
        'ACCESS_DELETE' => 'delete',
        'ACCESS_ADD' => 'add',
        'ACCESS_EDIT' => 'edit',
        'ACCESS_MODERATE' => 'moderate',
        'ACCESS_COMMENT' => 'comment',
        'ACCESS_READ' => 'read',
        'ACCESS_OVERVIEW' => 'overview',
    ];

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var Reader
     */
    private $annotationReader;

    public function __construct(
        PermissionApiInterface $permissionApi,
        Reader $annotationReader
    ) {
        $this->permissionApi = $permissionApi;
        $this->annotationReader = $annotationReader;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => [
                ['readControllerAnnotations']
            ]
        ];
    }

    /**
     * Read the controller annotations and check user access permissions
     */
    public function readControllerAnnotations(ControllerEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            // prevents calling this for controller usage within a template or elsewhere
            return;
        }
        $controller = $event->getController();
        if (!is_array($controller)) {
            return;
        }
        [$controller, $method] = $controller;
        $controllerClassName = get_class($controller);
        $reflectionClass = new \ReflectionClass($controllerClassName);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $permAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, PermAnnotation::class);
        if (!$permAnnotation) {
            return;
        }
        [$component, $instance, $level] = $this->formatSchema($permAnnotation, $event->getRequest());

        if (!$this->permissionApi->hasPermission($component, $instance, $level)) {
            throw new AccessDeniedException();
        }
    }

    private function formatSchema($permAnnotation, Request $request): array
    {
        if (!is_array($permAnnotation->value) && false !== $constant = array_search($permAnnotation->value, $this->accessMap)) {
            return [$request->attributes->get('_zkModule') . '::', '::', constant($constant)];
        }

        if ($this->isValidSchema($permAnnotation->value)) {
            return [
                $this->replaceRouteAttributes($permAnnotation->value[0], $request),
                $this->replaceRouteAttributes($permAnnotation->value[1], $request),
                constant($permAnnotation->value[2])
            ];
        }

        throw new AnnotationException('Invalid schema in @Annotation: @PermRequired(' . $permAnnotation->value . ')');
    }

    private function isValidSchema(array $schema): bool
    {
        if (3 !== count($schema)) {
            return false;
        }
        if (array_sum(array_map('is_string', $schema)) !== count($schema)) {
            return false;
        }
        if (((false !== mb_strpos($schema[0], ':')) && (2 !== mb_substr_count($schema[0], ':')))
            || ((false !== mb_strpos($schema[1], ':')) && (2 !== mb_substr_count($schema[1], ':')))) {
                return false;
        }
        if (false === array_key_exists($schema[2], $this->accessMap)) {
            return false;
        }

        return true;
    }

    private function replaceRouteAttributes(string $segment, Request $request): string
    {
        if (!$this->hasRouteAttribute($segment)) {
            return $segment;
        }
        if (1 !== preg_match( '/(\$[^:\n]+)/' , $segment, $matches)) {
            throw new AnnotationException('Invalid schema in @Annotation: @PermRequired(). Could not match route attributes');
        }
        unset ($matches[0]); // first key is unneeded full match

        foreach ($matches as $name) {
            if ($request->attributes->has(mb_substr($name, 1))) {
                $value = $request->attributes->get(mb_substr($name, 1));
            }
            $segment = str_replace($name, $value, $segment);
        }

        return $segment;
    }

    private function hasRouteAttribute(string $value): bool
    {
        return false !== mb_strpos($value, self::ROUTE_ATTRIBUTE_FLAG);
    }
}
