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

namespace Zikula\ThemeBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * ExceptionListener catches exceptions and converts them to Response instances.
 */
class ExceptionListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly string $debug,
        private readonly Environment $twig
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => [
                ['onKernelException', 30] // lower priority than AccessDeniedExceptionListener
            ]
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->debug || $event->getRequest()->isXmlHttpRequest()) {
            return;
        }
        $exception = $event->getThrowable();
        $code = method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : -1;
        if ($event->getRequest()->hasSession()) {
            $event->getRequest()
                ->getSession()
                ->getFlashBag()
                ->add('danger', sprintf('Error Code: %d', $code));
            $event->getRequest()
                ->getSession()
                ->getFlashBag()
                ->add('danger', $exception->getMessage());
        }

        $parameters = [
            'status_code' => $code,
            'status_text' => $exception->getMessage(),
            'exception' => $exception
        ];
        $content = $this->twig->render($this->findTemplate($code), $parameters);
        $event->getRequest()->attributes->set('error', true); // sets the theme realm in Engine
        $event->setResponse(new Response($content));
    }

    private function findTemplate(int $statusCode): ?string
    {
        $template = sprintf('@ZikulaTheme/Exception/error%s.html.twig', $statusCode);
        if ($this->twig->getLoader()->exists($template)) {
            return $template;
        }

        $template = '@ZikulaTheme/Exception/error.html.twig';
        if ($this->twig->getLoader()->exists($template)) {
            return $template;
        }

        return null;
    }
}
