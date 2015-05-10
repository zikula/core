<?php

namespace Zikula\Bundle\CoreBundle\Twig\Extension;

use Zikula\Bundle\CoreBundle\Twig;
use Zikula\Core\Hook\DisplayHook;
use Zikula\Core\Hook\FilterHook;

class HookExtension extends \Twig_Extension
{
    private $container;

    public function __construct($container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'notifyDisplayHooks' => new \Twig_Function_Method($this, 'notifyDisplayHooks', array('is_safe' => array('html'))),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('notifyFilters', array($this, 'notifyFilters')),
        );
    }

    public function notifyDisplayHooks($eventName, $id = null, $urlObject = null)
    {
        if (!isset($eventName)) {
            return trigger_error(__f('Error! "%1$s" must be set in %2$s', array('eventname', 'notifydisplayhooks')));
        }
        if ($urlObject && !($urlObject instanceof \Zikula\Core\UrlInterface)) {
            return trigger_error(__f('Error! "%1$s" must be an instance of %2$s', array('urlobject', '\Zikula\Core\UrlInterface')));
        }

        // create event and notify
        $hook = new DisplayHook($id, $urlObject);
        $this->container->get('hook_dispatcher')->dispatch($eventName, $hook);
        $responses = $hook->getResponses();

        $output = '';
        foreach ($responses as $result) {
            $output .= "<div class=\"z-displayhook\">$result</div>\n";
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

        return $this->container->get('hook_dispatcher')->dispatch($filterEventName, $hook)->getData();
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
