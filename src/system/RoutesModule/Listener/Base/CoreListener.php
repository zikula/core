<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.0 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Listener\Base;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Zikula\Core\Event\GenericEvent;

/**
 * Event handler base class for core events.
 */
class CoreListener implements EventSubscriberInterface
{
    /**
     * Makes our handlers known to the event system.
     */
    public static function getSubscribedEvents()
    {
        return array(
            'api.method_not_found'        => array('apiMethodNotFound', 5),
            'core.preinit'                => array('preInit', 5),
            'core.init'                   => array('init', 5),
            'core.postinit'               => array('postInit', 5),
            'controller.method_not_found' => array('controllerMethodNotFound', 5)
        );
    }

    /**
     * Listener for the `api.method_not_found` event.
     *
     * Called in instances of Zikula_Api from __call().
     * Receives arguments from __call($method, argument) as $args.
     *     $event['method'] is the method which didn't exist in the main class.
     *     $event['args'] is the arguments that were passed.
     * The event subject is the class where the method was not found.
     * Must exit if $event['method'] does not match whatever the handler expects.
     * Modify $event->data and $event->stopPropagation().
     *
     * @param GenericEvent $event The event instance.
     */
    public function apiMethodNotFound(GenericEvent $event)
    {
    }

    /**
     * Listener for the `core.preinit` event.
     *
     * Occurs after the config.php is loaded.
     *
     * @param GenericEvent $event The event instance.
     */
    public function preInit(GenericEvent $event)
    {
    }

    /**
     * Listener for the `core.init` event.
     *
     * Occurs after each `System::init()` stage, `$event['stage']` contains the stage.
     * To check if the handler should execute, do `if($event['stage'] & System::CORE_STAGES_*)`.
     *
     * @param GenericEvent $event The event instance.
     */
    public function init(GenericEvent $event)
    {
    }

    /**
     * Listener for the `core.postinit` event.
     *
     * Occurs just before System::init() exits from normal execution.
     *
     * @param GenericEvent $event The event instance.
     */
    public function postInit(GenericEvent $event)
    {
    }

    /**
     * Listener for the `controller.method_not_found` event.
     *
     * Called in instances of `Zikula_Controller` from `__call()`.
     * Receives arguments from `__call($method, argument)` as `$args`.
     *    `$event['method']` is the method which didn't exist in the main class.
     *    `$event['args']` is the arguments that were passed.
     * The event subject is the class where the method was not found.
     * Must exit if `$event['method']` does not match whatever the handler expects.
     * Modify `$event->data` and `$event->stopPropagation()`.
     *
     * @param GenericEvent $event The event instance.
     */
    public function controllerMethodNotFound(GenericEvent $event)
    {
    }
}
