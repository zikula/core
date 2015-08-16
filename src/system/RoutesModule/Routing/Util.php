<?php

namespace Zikula\RoutesModule\Routing;


/**
 * Class Util.
 */
class Util
{
    /**
     * Extracts modname, type, function and numeric suffix from a route's name.
     *
     * @param string $name The route name, e.g. acmeexamplemodule_user_index_1
     *
     * @return array ($modname, $type, $func, $numericSuffix)
     */
    public static function getParametersFromRouteName($name)
    {
        $name = explode('_', $name);
        $count = count($name);
        if (is_numeric($name[$count - 1])) {
            // e.g. acmeexamplemodule_user_index_1
            $type = $name[$count - 3];
            $func = $name[$count - 2];
            $numericSuffix = "_" . $name[$count - 1];
        } else {
            // e.g. acmeexamplemodule_user_index
            $type = $name[$count - 2];
            $func = $name[$count - 1];
            $numericSuffix = "";
        }

        return array($name[0], $type, $func, $numericSuffix);
    }

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
