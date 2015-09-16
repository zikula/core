<?php

namespace Zikula\RoutesModule\Routing;


/**
 * Class Util.
 */
class Util
{
    /**
     * Sanitizes the action / func parameter.
     *
     * @param string $action
     *
     * @return array ($action, $func)
     */
    public static function sanitizeAction($action)
    {
        if (substr($action, -6) !== 'Action') {
            $action .= 'Action';
        }

        $action = ucfirst($action);
        $func = lcfirst(substr($action, 0, -6));

        return array($action, $func);
    }

    /**
     * Sanitizes the controller / type parameter.
     *
     * @param string $controller
     *
     * @return array ($controller, $type)
     */
    public static function sanitizeController($controller)
    {
        if (substr($controller, -10) !== 'Controller') {
            $type = $controller;
            $controller .= 'Controller';
        } else {
            $type = substr($controller, 0, -10);
        }

        $type = strtolower($type);
        $controller = ucfirst($controller);

        return array($controller, $type);
    }
}
