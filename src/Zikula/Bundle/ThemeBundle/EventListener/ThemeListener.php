<?php

namespace Zikula\Bundle\ThemeBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThemeListener implements EventSubscriberInterface
{
    /**
     * @var EngineInterface
     */
    private $templating;
    private $activeTheme;

    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
        $this->activeTheme = 'Andreas08Theme';
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($event->getRequestType() == HttpKernelInterface::MASTER_REQUEST) {
            $response = $event->getResponse();
            $request = $event->getRequest();

            if ($request->isXmlHttpRequest()) {
                return;
            }

//            if (!$request->isXmlHttpRequest()
//                && strpos($response->getContent(), '</body>') === false
//                && !$response->isRedirection()
//                && 'html' === $request->getRequestFormat()
//                && (($response->headers->has('Content-Type') && false !== strpos($response->headers->get('Content-Type'), 'html')) || !$response->headers->has('Content-Type') )) {
//                $content = $this->templating->render($this->activeTheme.'::master.html.twig', array('maincontent' => $response->getContent()));
//                $response->setContent('ddd'.$content);
//            }

            $content = $this->templating->render($this->activeTheme.'::master.html.twig', array('maincontent' => $response->getContent()));
            $response->setContent($content);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::RESPONSE => array('onKernelResponse', 5));
    }
}
