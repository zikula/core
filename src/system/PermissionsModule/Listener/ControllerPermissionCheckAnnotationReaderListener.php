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

namespace Zikula\PermissionsModule\Listener;

use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ControllerPermissionCheckAnnotationReaderListener implements EventSubscriberInterface
{
    /**
     * flag of route attribute parameter variable
     */
    private const ROUTE_ATTRIBUTE_FLAG = '$';

    /**
     * regex pattern to search for embedded attribute parameters
     */
    private const REGEX_PATTERN = '/(^|:)\\' . self::ROUTE_ATTRIBUTE_FLAG . '(\w+)/m';

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
        $annotationValue = $this->getAnnotationValueFromController($controller);
        if (!$annotationValue) {
            return;
        }

        [$component, $instance, $level] = $this->formatSchema($annotationValue, $event->getRequest());

        if (!$this->permissionApi->hasPermission($component, $instance, $level)) {
            throw new AccessDeniedException();
        }
    }

    private function getAnnotationValueFromController(array $controller): ?object
    {
        [$controller, $method] = $controller;
        $controllerClassName = get_class($controller);
        $reflectionClass = new \ReflectionClass($controllerClassName);
        if (!($controller instanceof AbstractController)) {
            return null;
        }
        $classPermissionAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, PermissionCheck::class);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $methodPermissionAnnotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, PermissionCheck::class);
        if (!$classPermissionAnnotation && !$methodPermissionAnnotation) {
            return null;
        } elseif ($classPermissionAnnotation && $methodPermissionAnnotation) {
            throw AnnotationException::semanticalError('You cannot use @PermissionCheck() annotation at the method and class level at the same time.');
        }

        return $classPermissionAnnotation ?? $methodPermissionAnnotation;
    }

    private function formatSchema($permAnnotation, Request $request): array
    {
        if (is_string($permAnnotation->value)) {
            return [$request->attributes->get('_zkModule') . '::', '::', $this->getConstant($permAnnotation->value)];
        }
        if ($this->isValidSchema($permAnnotation->value)) {
            return [
                $this->replaceRouteAttributes($permAnnotation->value[0], $request),
                $this->replaceRouteAttributes($permAnnotation->value[1], $request),
                $this->getConstant($permAnnotation->value[2])
            ];
        }
        throw new AnnotationException('Invalid schema in @PermissionCheck() annotation. Value must be string or an array.');
    }

    private function isValidSchema(array $schema): bool
    {
        if (3 !== count($schema)) {
            return false;
        }
        if (array_sum(array_map('is_string', $schema)) !== count($schema)) {
            return false;
        }
        if (
            (false !== mb_strpos($schema[0], ':') && 2 !== mb_substr_count($schema[0], ':'))
            || (false !== mb_strpos($schema[1], ':') && 2 !== mb_substr_count($schema[1], ':'))
        ) {
            return false;
        }

        return true;
    }

    private function replaceRouteAttributes(string $segment, Request $request): string
    {
        if (!$this->hasFlag($segment)) {
            return $segment;
        }
        if (false === preg_match_all(self::REGEX_PATTERN, $segment, $matches)) {
            throw new AnnotationException('Invalid schema in @PermissionCheck() annotation. Could not match route attributes');
        }
        foreach ($matches[2] as $name) {
            if ($request->attributes->has($name)) {
                $value = $request->attributes->get($name);
                $pattern = str_replace('(\w+)', $name, self::REGEX_PATTERN);
                $segment = preg_replace($pattern, '${1}' . $value, $segment);
            }
        }

        return $segment;
    }

    private function hasFlag(string $value): bool
    {
        return false !== mb_strpos($value, self::ROUTE_ATTRIBUTE_FLAG);
    }

    private function getConstant(string $string): int
    {
        if (false !== array_key_exists($string, $this->accessMap)) {
            return constant($string);
        }
        if (false !== $key = array_search($string, $this->accessMap)) {
            return constant($key);
        }
        throw new AnnotationException('Invalid schema in @PermissionCheck() annotation. Access level string invalid.');
    }
}
