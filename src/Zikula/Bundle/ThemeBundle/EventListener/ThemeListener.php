<?php

namespace Zikula\ThemeBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ThemeListener
{
    /**
     * @var EngineInterface
     */
    private $templating;
    private $activeTheme;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
        $this->activeTheme = 'BlueprintTheme';
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $response = $event->getResponse();
            $request = $event->getRequest();

            if (!$request->isXmlHttpRequest()
                && strpos($response->getContent(), '</body>') === false
                && !$response->isRedirection()
                && 'html' === $request->getRequestFormat()
                && (($response->headers->has('Content-Type') && false !== strpos($response->headers->get('Content-Type'), 'html')) || !$response->headers->has('Content-Type') )) {
                $content = $this->templating->render($this->activeTheme.'::base.html.twig', array('content' => $response->getContent()));
                $response->setContent($content);
            }
        }
    }
}
