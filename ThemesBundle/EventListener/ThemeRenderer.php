<?php

namespace Zikula\ThemesBundle\EventListener;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 *
 */
class ThemeRenderer {
    /**
     * @var EngineInterface 
     */
    private $templating;
    
    private $activeTheme;
    
    public function __construct(EngineInterface $templating) {
        $this->templating = $templating;
        $this->activeTheme = 'BlueprintTheme';
    }
    
    public function onKernelResponse(\Symfony\Component\HttpKernel\Event\FilterResponseEvent $event) {
        if($event->getRequestType() == \Symfony\Component\HttpKernel\HttpKernelInterface::MASTER_REQUEST) {
            $response = $event->getResponse();
            $request = $event->getRequest();
            
            if(!$request->isXmlHttpRequest()
                    && strpos($response->getContent(), '</body>') === false
                    && !$response->isRedirection()
                    && 'html' === $request->getRequestFormat()
                    && (($response->headers->has('Content-Type') && false !== strpos($response->headers->get('Content-Type'), 'html'))  || !$response->headers->has('Content-Type') )) {
                $content = $this->templating->render($this->activeTheme . '::base.html.twig', array('content' => $response->getContent()));
                $response->setContent($content);
            }
        }
    }
}
