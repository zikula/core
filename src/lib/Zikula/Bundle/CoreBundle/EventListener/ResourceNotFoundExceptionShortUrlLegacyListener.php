<?php
/**
 * Copyright Zikula Foundation 2015 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Zikula\Core\Response\PlainResponse;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * ExceptionListener catches exceptions and converts them to Response instances.
 */
class ResourceNotFoundExceptionShortUrlLegacyListener implements EventSubscriberInterface
{
    /**
     * @var VariableApi
     */
    private $variableApi;

    public function __construct(VariableApi $variableApi)
    {
        $this->variableApi = $variableApi;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array(
                array('onKernelException', 32), /** must trigger before the Core's ExceptionListener */
            )
        );
    }

    /**
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        /**
         * @todo maybe https://github.com/symfony/symfony/issues/5320#issuecomment-56401080 is a better way
         */
        if (!$event->getRequest()->isXmlHttpRequest()) {
            $exception = $event->getException();
            do {
                if ($exception instanceof ResourceNotFoundException) {
                    $this->handleResourceNotFoundException($event);
                }
            } while (null !== $exception = $exception->getPrevious());
        }
    }

    /**
     * Handles handleResourceNotFoundException for only legacy shortUrls.
     *
     * @param GetResponseForExceptionEvent $event
     */
    private function handleResourceNotFoundException(GetResponseForExceptionEvent $event)
    {
        if ($this->variableApi->get(VariableApi::CONFIG, 'shorturls') && !$event->getRequest()->query->has('module')) {

            $request = $event->getRequest();
            \System::resolveLegacyShortUrl($request);

            $module = $request->attributes->get('_zkModule');
            $type = $request->attributes->get('_zkType', 'user');
            $func = $request->attributes->get('_zkFunc', 'index');
            $arguments = $request->attributes->get('_zkArgs');

            // get module information
            $modinfo = \ModUtil::getInfoFromName($module);
            if (!$modinfo) {
                throw new NotFoundHttpException(__('Page not found.'));
            }

            $return = \ModUtil::func($modinfo['name'], $type, $func, $arguments);

            if (false === $return) {
                // hack for BC since modules currently use ModUtil::func without expecting exceptions - drak.
                $event->setException(new NotFoundHttpException(__('Page not found.')));
            } else {
                if (false === $return instanceof Response) {
                    $response = new Response($return);
                } else {
                    $response = $return;
                }
                $response->legacy = true;
                $event->setResponse($response);
                $event->stopPropagation();
            }
        }
    }
}
