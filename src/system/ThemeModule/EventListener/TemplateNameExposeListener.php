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

namespace Zikula\ThemeModule\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ThemeModule\Bridge\Event\TwigPostRenderEvent;
use Zikula\ThemeModule\ThemeEvents;

class TemplateNameExposeListener implements EventSubscriberInterface
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public static function getSubscribedEvents()
    {
        return [
            ThemeEvents::POST_RENDER => [
                ['exposeTemplateNames']
            ]
        ];
    }

    /**
     * Decorate the rendered output to include the template name in the html source as an html comment.
     */
    public function exposeTemplateNames(TwigPostRenderEvent $event): void
    {
        if ('dev' !== $this->kernel->getEnvironment()) {
            return;
        }

        $name = $event->getTemplateName();
        if (false !== mb_strpos($name, '.js.')) {
            $content = '/* ' . $name . ' */' . $event->getContent() . '/* end ' . $name . ' */';
        } else {
            $content = '<!-- ' . $name . ' -->' . $event->getContent() . '<!-- /' . $name . ' -->';
        }
        $event->setContent($content);
    }
}
