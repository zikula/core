<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Controller;

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
     * Constructor.
     *
     * @param AbstractBundle $bundle An AbstractBundle instance
     * @param PermissionApiInterface $permissionApi
     * @param VariableApiInterface $variableApi
     * @param TranslatorInterface $translator
     *
     * @throws \InvalidArgumentException
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
     * boot the controller
     *
     * @param AbstractBundle $bundle
     */
    protected function boot(AbstractBundle $bundle)
    {
        // load optional bootstrap
        $bootstrap = $bundle->getPath() . '/bootstrap.php';
        if (file_exists($bootstrap)) {
            include_once $bootstrap;
        }
    }

    /**
     * Returns a rendered view.
     *
     * @param string $view
     *            The view name
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @return string The rendered view
     */
    public function renderView($view, array $parameters = []): string
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::renderView($view, $parameters);
    }

    /**
     * Renders a view.
     *
     * @param string $view
     *            The view name
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @param Response $response
     *            A response instance
     * @return Response A Response instance
     */
    public function render($view, array $parameters = [], Response $response = null): Response
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::render($view, $parameters, $response);
    }

    /**
     * Streams a view.
     *
     * @param string $view
     *            The view name
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @param StreamedResponse $response
     *            A response instance
     * @return StreamedResponse A StreamedResponse instance
     */
    public function stream($view, array $parameters = [], StreamedResponse $response = null): StreamedResponse
    {
        $parameters = $this->decorateTranslator($parameters);

        return parent::stream($view, $parameters, $response);
    }

    /**
     * Decorate translator.
     *
     * @param array $parameters
     *            An array of parameters to pass to the view
     * @return array An array including translator parameters to pass to the view
     */
    protected function decorateTranslator(array $parameters)
    {
        $parameters['domain'] = $this->translator->getDomain();

        return $parameters;
    }

    /**
     * Returns a NotFoundHttpException.
     * This will result in a 404 response code. Usage example:
     * throw $this->createNotFoundException();
     *
     * @param string $message
     *            A message
     * @param \Exception $previous
     *            The previous exception
     * @return NotFoundHttpException
     */
    public function createNotFoundException($message = null, \Exception $previous = null): NotFoundHttpException
    {
        $message = null === $message ? $this->__('Page not found') : $message;

        return new NotFoundHttpException($message, $previous);
    }

    /**
     * Returns a AccessDeniedException.
     * This will result in a 403 response code. Usage example:
     * throw $this->createAccessDeniedException();
     *
     * @param string $message
     *            A message
     * @param \Exception $previous
     *            The previous exception
     * @return AccessDeniedException
     */
    public function createAccessDeniedException($message = null, \Exception $previous = null): AccessDeniedException
    {
        //Do not translate Access denied. to ensure that the ExceptionListener is able to catch the message also in other languages.
        $message = null === $message ? 'Access denied.' : $message;

        return new AccessDeniedException($message, $previous);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Convenience shortcut to check if user has requested permissions.
     *
     * @param string $component
     * @param string $instance
     * @param int $level
     * @param int $user
     *
     * @return bool
     */
    protected function hasPermission($component = null, $instance = null, $level = null, $user = null)
    {
        return $this->permissionApi->hasPermission($component, $instance, $level, $user);
    }

    /**
     * Forwards the request to another controller.
     * Overrides parent::forward() to add request parameters.
     *
     * @param string $controller The controller name (a string like BlogBundle:Post:index)
     * @param array  $path       An array of path parameters
     * @param array  $query      An array of query parameters
     * @param array  $request    An array of request parameters
     *
     * @return Response A Response instance
     */
    protected function forward(string $controller, array $path = [], array $query = [], array $request = []): Response
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $path['_controller'] = $controller;
        $subRequest = $request->duplicate($query, $request, $path);

        return $this->container->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    }
}
