<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.5 (https://modulestudio.de).
 */

namespace Zikula\RoutesModule\Base;

/**
 * Events definition base class.
 */
abstract class AbstractRoutesEvents
{
    /**
     * The zikularoutesmodule.route_post_load event is thrown when routes
     * are loaded from the database.
     *
     * The event listener receives an
     * Zikula\RoutesModule\Event\FilterRouteEvent instance.
     *
     * @see Zikula\RoutesModule\Listener\EntityLifecycleListener::postLoad()
     * @var string
     */
    const ROUTE_POST_LOAD = 'zikularoutesmodule.route_post_load';
    
    /**
     * The zikularoutesmodule.route_pre_persist event is thrown before a new route
     * is created in the system.
     *
     * The event listener receives an
     * Zikula\RoutesModule\Event\FilterRouteEvent instance.
     *
     * @see Zikula\RoutesModule\Listener\EntityLifecycleListener::prePersist()
     * @var string
     */
    const ROUTE_PRE_PERSIST = 'zikularoutesmodule.route_pre_persist';
    
    /**
     * The zikularoutesmodule.route_post_persist event is thrown after a new route
     * has been created in the system.
     *
     * The event listener receives an
     * Zikula\RoutesModule\Event\FilterRouteEvent instance.
     *
     * @see Zikula\RoutesModule\Listener\EntityLifecycleListener::postPersist()
     * @var string
     */
    const ROUTE_POST_PERSIST = 'zikularoutesmodule.route_post_persist';
    
    /**
     * The zikularoutesmodule.route_pre_remove event is thrown before an existing route
     * is removed from the system.
     *
     * The event listener receives an
     * Zikula\RoutesModule\Event\FilterRouteEvent instance.
     *
     * @see Zikula\RoutesModule\Listener\EntityLifecycleListener::preRemove()
     * @var string
     */
    const ROUTE_PRE_REMOVE = 'zikularoutesmodule.route_pre_remove';
    
    /**
     * The zikularoutesmodule.route_post_remove event is thrown after an existing route
     * has been removed from the system.
     *
     * The event listener receives an
     * Zikula\RoutesModule\Event\FilterRouteEvent instance.
     *
     * @see Zikula\RoutesModule\Listener\EntityLifecycleListener::postRemove()
     * @var string
     */
    const ROUTE_POST_REMOVE = 'zikularoutesmodule.route_post_remove';
    
    /**
     * The zikularoutesmodule.route_pre_update event is thrown before an existing route
     * is updated in the system.
     *
     * The event listener receives an
     * Zikula\RoutesModule\Event\FilterRouteEvent instance.
     *
     * @see Zikula\RoutesModule\Listener\EntityLifecycleListener::preUpdate()
     * @var string
     */
    const ROUTE_PRE_UPDATE = 'zikularoutesmodule.route_pre_update';
    
    /**
     * The zikularoutesmodule.route_post_update event is thrown after an existing new route
     * has been updated in the system.
     *
     * The event listener receives an
     * Zikula\RoutesModule\Event\FilterRouteEvent instance.
     *
     * @see Zikula\RoutesModule\Listener\EntityLifecycleListener::postUpdate()
     * @var string
     */
    const ROUTE_POST_UPDATE = 'zikularoutesmodule.route_post_update';
    
}
