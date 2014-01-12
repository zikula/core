<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Bundle\CoreBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

/**
 * Class RoutesDumper.
 */
class RoutesDumper
{
    /**
     * Dumps a set of routes to a PHP class.
     *
     * @param RouteCollection $routeCollection
     *
     * @return string A PHP class.
     */
    public function dump(RouteCollection $routeCollection)
    {
        return <<<EOF
<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * ZikulaRoutes
 *
 * This class has been auto-generated
 * by the Zikula Routing Component.
 */
class ZikulaRoutes
{
    private static \$declaredRoutes = {$this->generateDeclaredRoutes($routeCollection)};

{$this->generateGenerateMethod()}
}

EOF;
    }

    /**
     * Generates PHP code representing an array of defined routes
     * together with the routes properties (e.g. requirements).
     *
     * @return string PHP code
     */
    private function generateDeclaredRoutes(RouteCollection $routeCollection)
    {
        $routes = "array(\n";
        foreach ($routeCollection->all() as $name => $route) {
            $routes .= sprintf("        '%s' => \"%s\",\n", $name, addslashes(serialize($route)));
        }
        $routes .= '    )';

        return $routes;
    }

    /**
     * Generates PHP code representing the `generate` method that implements the UrlGeneratorInterface.
     *
     * @return string PHP code
     */
    private function generateGenerateMethod()
    {
        return <<<EOF
    public function getRoute(\$name)
    {
        if (!isset(self::\$declaredRoutes[\$name])) {
            throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', \$name));
        }

        return unserialize(self::\$declaredRoutes[\$name]);
    }

    public function getRoutes()
    {
        \$collection = new RouteCollection();
        \$routes = self::\$declaredRoutes;
        foreach (\$routes as \$name => \$route) {
            \$collection->add(\$name, unserialize(\$route));
        }

        return \$collection;
    }
EOF;
    }
}