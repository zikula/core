<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent;
use Zikula\ThemeModule\ThemeEvents;

class TemplateNameExposeListener implements EventSubscriberInterface
{
    private $env;

    public function __construct($env)
    {
        $this->env = $env;
    }

    /**
     * This listener decorates the rendered output to include the template name in the html source as an html comment.
     * @param TwigPostRenderEvent $event
     */
    public function exposeTemplateNames(TwigPostRenderEvent $event)
    {
        if ($this->env == 'dev') {
            $name = $event->getTemplateName();
            if (false !== strpos($name, '.js.')) {
                $content = '/* ' . $name . ' */' . $event->getContent() . '/* end ' . $name . ' */';
            } else {
                $content = '<!-- ' . $name . ' -->' . $event->getContent() . '<!-- /' . $name . ' -->';
            }
            $event->setContent($content);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            ThemeEvents::POST_RENDER => [
                ['exposeTemplateNames']
            ]
        ];
    }
}
