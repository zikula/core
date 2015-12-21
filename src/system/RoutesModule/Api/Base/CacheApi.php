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

namespace Zikula\RoutesModule\Api\Base;

use ModUtil;
use UserUtil;
use Zikula_AbstractBase;
use Zikula_View;
use Zikula_View_Theme;

/**
 * Cache api base class.
 */
class CacheApi extends Zikula_AbstractBase
{
    /**
     * Clear cache for given item. Can be called from other modules to clear an item cache.
     *
     * @param $args['ot']   the treated object type
     * @param $args['item'] the actual object
     */
    public function clearItemCache(array $args = array())
    {
        if (!isset($args['ot']) || !isset($args['item'])) {
            return;
        }

        $objectType = $args['ot'];
        $item = $args['item'];

        $controllerHelper = $this->get('zikularoutesmodule.controller_helper');
        $utilArgs = array('api' => 'cache', 'action' => 'clearItemCache');
        if (!in_array($objectType, $controllerHelper->getObjectTypes('controllerAction', $utilArgs))) {
            return;
        }

        if ($item && !is_array($item) && !is_object($item)) {
            $item = ModUtil::apiFunc($this->name, 'selection', 'getEntity', array('ot' => $objectType, 'id' => $item, 'useJoins' => false, 'slimMode' => true));
        }

        if (!$item) {
            return;
        }

        $instanceId = $item->createCompositeIdentifier();

        $logger = $this->get('logger');
        $logger->info('{app}: User {user} caused clearing the cache for entity {entity} with id {id}.', array('app' => 'ZikulaRoutesModule', 'user' => UserUtil::getVar('uname'), 'entity' => $objectType, 'id' => $instanceId));

        // Clear View_cache
        $cacheIds = array();
        switch ($objectType) {
            case 'route':
                $cacheIds[] = 'route_index';
                $cacheIds[] = $objectType . '_view';
                $cacheIds[] = $objectType . '_display|' . $instanceId;

                $cacheIds[] = $objectType . '|' . 'reload';
                $cacheIds[] = $objectType . '|' . 'renew';
                break;
        }

        $view = Zikula_View::getInstance('ZikulaRoutesModule');
        foreach ($cacheIds as $cacheId) {
            $view->clear_cache(null, $cacheId);
        }

        // Clear Theme_cache
        $cacheIds = array();
        $cacheIds[] = 'homepage'; // for homepage (can be assigned in the Settings module)
        switch ($objectType) {
            case 'route':
                $cacheIdPrefix = 'ZikulaRoutesModule/' . $objectType . '/';
                $cacheIds[] = $cacheIdPrefix . 'index'; // index function
                $cacheIds[] = $cacheIdPrefix . 'view/'; // view function (list views)
                $cacheIds[] = $cacheIdPrefix . 'display/' . $instanceId; // display function (detail views)

                $cacheIds[] = $cacheIdPrefix . 'reload'; // reload function
                $cacheIds[] = $cacheIdPrefix . 'renew'; // renew function
                break;
        }
        $theme = Zikula_View_Theme::getInstance();
        $theme->clear_cacheid_allthemes($cacheIds);
    }
}
