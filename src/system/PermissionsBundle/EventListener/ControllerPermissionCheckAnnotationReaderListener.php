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

namespace Zikula\PermissionsBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\PermissionsBundle\Api\ApiInterface\PermissionApiInterface;

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

    private array $accessMap = [
        'ACCESS_ADMIN' => 'admin',
        'ACCESS_DELETE' => 'delete',
        'ACCESS_ADD' => 'add',
        'ACCESS_EDIT' => 'edit',
        'ACCESS_MODERATE' => 'moderate',
        'ACCESS_COMMENT' => 'comment',
        'ACCESS_READ' => 'read',
        'ACCESS_OVERVIEW' => 'overview',
    ];

    public function __construct(private readonly PermissionApiInterface $permissionApi)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['readControllerAnnotations'],
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
        $attribute = $this->getAttributeFromController($controller);
        if (!$attribute) {
            return;
        }

        [$component, $instance, $level] = $this->formatSchema($attribute, $event->getRequest());

        if (!$this->permissionApi->hasPermission($component, $instance, $level)) {
            throw new AccessDeniedException();
        }
    }

    private function getAttributeFromController(array $controller): ?PermissionCheck
    {
        [$controller, $method] = $controller;
        $controllerClassName = $controller::class;
        $reflectionClass = new \ReflectionClass($controllerClassName);
        if (!($controller instanceof AbstractController)) {
            return null;
        }
        $classLevelAttributes = $reflectionClass->getAttributes(PermissionCheck::class);
        $classPermissionAttribute = 0 < count($classLevelAttributes) ? $classLevelAttributes[0] : null;

        $reflectionMethod = $reflectionClass->getMethod($method);
        $methodLevelAttributes = $reflectionMethod->getAttributes(PermissionCheck::class);
        $methodPermissionAttribute = 0 < count($methodLevelAttributes) ? $methodLevelAttributes[0] : null;

        if (!$classPermissionAttribute && !$methodPermissionAttribute) {
            return null;
        }
        if ($classPermissionAttribute && $methodPermissionAttribute) {
            throw new \InvalidArgumentException('You cannot use #[PermissionCheck()] attribute at the method and class level at the same time.');
        }

        return ($classPermissionAttribute ?? $methodPermissionAttribute)->newInstance();
    }

    private function formatSchema(PermissionCheck $permAttribute, Request $request): array
    {
        if (is_string($permAttribute->value)) {
            return [$request->attributes->get('_zkModule') . '::', '::', $this->getConstant($permAttribute->value)];
        }
        if ($this->isValidSchema($permAttribute->value)) {
            return [
                $this->replaceRouteAttributes($permAttribute->value[0], $request),
                $this->replaceRouteAttributes($permAttribute->value[1], $request),
                $this->getConstant($permAttribute->value[2])
            ];
        }
        throw new \InvalidArgumentException('Invalid schema in #[PermissionCheck()] attribute. Value must be string or an array.');
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
            throw new AnnotationException('Invalid schema in #[PermissionCheck()] annotation. Could not match route attributes');
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
        throw new AnnotationException('Invalid schema in #[PermissionCheck()] annotation. Access level string invalid.');
    }
}
