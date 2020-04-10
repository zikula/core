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

namespace Zikula\Bundle\CoreBundle\Controller;

use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Zikula\Bundle\CoreBundle\Translation\TranslatorTrait;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\ExtensionVariablesTrait;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

abstract class AbstractController extends BaseController
{
    use TranslatorTrait;
    use ExtensionVariablesTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var PermissionApiInterface
     */
    protected $permissionApi;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator
    ) {
        $this->name = $extension->getName();
        $this->permissionApi = $permissionApi;
        $this->extensionName = $this->name; // for ExtensionVariablesTrait
        $this->variableApi = $variableApi; // for ExtensionVariablesTrait
        $this->setTranslator($translator);
        $this->boot($extension);
    }

    /**
     * Boot the controller.
     */
    protected function boot(AbstractExtension $extension): void
    {
        // load optional bootstrap
        $bootstrap = $extension->getPath() . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
    }

    /**
     * Returns a NotFoundHttpException; this will result in a 404 response code.
     * Usage example: throw $this->createNotFoundException();
     */
    public function createNotFoundException(string $message = 'Not Found.', Throwable $previous = null): NotFoundHttpException
    {
        $message = $message ?? $this->trans('Page not found');

        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Returns a AccessDeniedException; this will result in a 403 response code.
     * Usage example: throw $this->createAccessDeniedException();
     */
    public function createAccessDeniedException(string $message = 'Access Denied.', Throwable $previous = null): AccessDeniedException
    {
        // Do not translate "Access denied." to ensure the ExceptionListener is able
        // to catch the message also in other languages.
        $message = $message ?? 'Access denied.';

        return new AccessDeniedException($message, $previous);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Convenience shortcut to check if user has requested permissions.
     */
    protected function hasPermission(string $component = null, string $instance = null, int $level = null, int $user = null): bool
    {
        return $this->permissionApi->hasPermission($component, $instance, $level, $user);
    }

    /**
     * Forwards the request to another controller.
     * Overrides parent::forward() to add request parameters.
     */
    protected function forward(string $controller, array $path = [], array $query = [], array $requestParameters = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, $requestParameters, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
