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

namespace Zikula\Core\Controller;

use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\AbstractBundle;
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
        AbstractBundle $bundle,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator
    ) {
        $this->name = $bundle->getName();
        $this->permissionApi = $permissionApi;
        $this->extensionName = $this->name; // for ExtensionVariablesTrait
        $this->variableApi = $variableApi; // for ExtensionVariablesTrait
        $this->setTranslator($translator);
        $this->translator->setDomain($bundle->getTranslationDomain());
        $this->boot($bundle);
    }

    /**
     * Boot the controller.
     */
    protected function boot(AbstractBundle $bundle): void
    {
        // load optional bootstrap
        $bootstrap = $bundle->getPath() . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
    }

    public function renderView(string $view, array $parameters = []): string
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::renderView($view, $parameters);
    }

    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::render($view, $parameters, $response);
    }

    public function stream(string $view, array $parameters = [], StreamedResponse $response = null): StreamedResponse
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::stream($view, $parameters, $response);
    }

    /**
     * Decorate translator.
     */
    protected function decorateTranslator(array $parameters): array
    {
        $parameters['domain'] = $this->translator->getDomain();

        return $parameters;
    }

    /**
     * Returns a NotFoundHttpException; this will result in a 404 response code.
     * Usage example: throw $this->createNotFoundException();
     */
    public function createNotFoundException(string $message = null, Exception $previous = null): NotFoundHttpException
    {
        $message = $message ?? $this->__('Page not found');

        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Returns a AccessDeniedException; this will result in a 403 response code.
     * Usage example: throw $this->createAccessDeniedException();
     */
    public function createAccessDeniedException(string $message = null, Exception $previous = null): AccessDeniedException
    {
        //Do not translate Access denied. to ensure that the ExceptionListener is able to catch the message also in other languages.
        $message = $message ?? 'Access denied.';

        return new AccessDeniedException($message, $previous);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
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
