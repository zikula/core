<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Twig\Extension;

use Zikula\Bundle\HookBundle\Dispatcher\HookDispatcherInterface;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\FilterHook;
use Zikula\Core\UrlInterface;

class HookExtension extends \Twig_Extension
{
    /**
     * @var HookDispatcherInterface
     */
    private $hookDispatcher;

    public function __construct(HookDispatcherInterface $hookDispatcher)
    {
        $this->hookDispatcher = $hookDispatcher;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('notifyDisplayHooks', [$this, 'notifyDisplayHooks'], ['is_safe' => ['html']])
        ];
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('notifyFilters', [$this, 'notifyFilters'])
        ];
    }

    public function notifyDisplayHooks($eventName, $id = null, $urlObject = null)
    {
        if (!isset($eventName)) {
            return trigger_error(__f('Error! "%1$s" must be set in %2$s', ['eventname', 'notifydisplayhooks']));
        }
        if ($urlObject && !($urlObject instanceof UrlInterface)) {
            return trigger_error(__f('Error! "%1$s" must be an instance of %2$s', ['urlobject', '\Zikula\Core\UrlInterface']));
        }

        // create event and notify
        $hook = new DisplayHook($id, $urlObject);
        $this->hookDispatcher->dispatch($eventName, $hook);
        $responses = $hook->getResponses();

        $output = '';
        foreach ($responses as $result) {
            if (!empty($result)) {
                $output .= '<div class="z-displayhook">' . $result . '</div>' . "\n";
            }
        }

        return $output;
    }

    /**
     * @param $content
     * @param $filterEventName
     * @return mixed
     */
    public function notifyFilters($content, $filterEventName)
    {
        $hook = new FilterHook($content);

        return $this->hookDispatcher->dispatch($filterEventName, $hook)->getData();
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulacore.hook';
    }
}
