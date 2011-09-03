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
    
    public function __construct(EngineInterface $templating) {
        $this->templating = $templating;
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
                $template = new \Symfony\Component\Templating\TemplateReference(__DIR__ . '/../../../../themes/Blueprint/Resources/views/base.html.twig');
                $content = $this->templating->render($template, array('content' => $response->getContent()));
                $response->setContent($content);
            }
        }
    }
}
