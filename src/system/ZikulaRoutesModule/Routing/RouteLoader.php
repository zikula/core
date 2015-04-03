<?php

namespace Zikula\RoutesModule\Routing;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Zikula\RoutesModule\Routing\Util as RoutingUtil;

/**
 * Class RouteLoader.
 *
 * Custom loader following http://symfony.com/doc/current/cookbook/routing/custom_route_loader.html
 */
class RouteLoader implements LoaderInterface
{
    private $loaded = false;

    private $em;

    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "zikularoutesmodule" loader twice');
        }
        unset($type);

        $routeCollection = new RouteCollection();

        try {
            // clear entity manager to ensure that we also fetch new routes which have been newly inserted during a module's installation
            $this->em->clear();

            // fetch all approved routes
            $routes = $this->em->getRepository('ZikulaRoutesModule:RouteEntity')->findBy(array('workflowState' => 'approved'), array('group' => 'ASC', 'sort' => 'ASC'));
        } catch (DBALException $e) {
            // It seems like the module is not yet installed. Fail silently.
            return $routeCollection;
        }

        if (!empty($routes)) {
            $helper = new RoutingUtil();
            /**
             * @var \Zikula\RoutesModule\Entity\RouteEntity $dbRoute
             */
            foreach ($routes as $dbRoute) {
                // Add modname, type and func to the route's default values.
                $defaults = $dbRoute->getDefaults();
                $defaults['_zkModule'] = $dbRoute->getBundle();
                list (, $type) = $helper->sanitizeController($dbRoute->getController());
                list (, $func) = $helper->sanitizeAction($dbRoute->getAction());
                $defaults['_zkType'] = $type;
                $defaults['_zkFunc'] = $func;

                $route = new Route(
                    $dbRoute->getPath(),
                    $defaults,
                    $dbRoute->getRequirements(),
                    $dbRoute->getOptions(),
                    $dbRoute->getHost(),
                    $dbRoute->getSchemes(),
                    $dbRoute->getMethods(),
                    $dbRoute->getCondition()
                );

                $routeCollection->add($dbRoute->getName(), $route);
            }
        }

        $this->loaded = true;
        // We would need a DatabaseResource or similar, which does not exist in Symfony (yet).
        // See https://github.com/symfony/symfony/issues/7176
        // $routeCollection->addResource(new FileResource())

        return $routeCollection;
    }

    public function supports($resource, $type = null)
    {
        return 'zikularoutesmodule' === $type;
    }

    public function getResolver()
    {
        // needed, but can be blank
    }

    public function setResolver(LoaderResolverInterface $resolver)
    {
        // same as above
    }
}
