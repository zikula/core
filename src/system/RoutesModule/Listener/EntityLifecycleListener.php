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

namespace Zikula\RoutesModule\Listener;

use Zikula\RoutesModule\Listener\Base\EntityLifecycleListener as BaseEntityLifecycleListener;

/**
 * Event subscriber implementation class for entity lifecycle events.
 */
class EntityLifecycleListener extends BaseEntityLifecycleListener
{
    /**
     * {@inheritdoc}
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$this->isEntityManagedByThisBundle($entity)) {
            return;
        }

        if (php_sapi_name() == 'cli') {
            return;
        }

        $serviceManager = \ServiceUtil::getManager();
        $requestStack = $serviceManager->get('request_stack');
        if (null === $requestStack->getCurrentRequest()) {
            return;
        }

        return parent::postLoad($args);
    }
}
