<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.5 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Entity\Factory\Base;

use Zikula\RoutesModule\Entity\RouteEntity;
use Zikula\RoutesModule\Helper\ListEntriesHelper;

/**
 * Entity initialiser class used to dynamically apply default values to newly created entities.
 */
abstract class AbstractEntityInitialiser
{
    /**
     * @var ListEntriesHelper Helper service for managing list entries
     */
    protected $listEntriesHelper;

    /**
     * EntityInitialiser constructor.
     *
     * @param ListEntriesHelper $listEntriesHelper Helper service for managing list entries
     */
    public function __construct(ListEntriesHelper $listEntriesHelper)
    {
        $this->listEntriesHelper = $listEntriesHelper;
    }

    /**
     * Initialises a given route instance.
     *
     * @param RouteEntity $entity The newly created entity instance
     *
     * @return RouteEntity The updated entity instance
     */
    public function initRoute(RouteEntity $entity)
    {
        $listEntries = $this->listEntriesHelper->getEntries('route', 'schemes');
        foreach ($listEntries as $listEntry) {
            if (true === $listEntry['default']) {
                $entity->setSchemes($listEntry['value']);
                break;
            }
        }

        $listEntries = $this->listEntriesHelper->getEntries('route', 'methods');
        foreach ($listEntries as $listEntry) {
            if (true === $listEntry['default']) {
                $entity->setMethods($listEntry['value']);
                break;
            }
        }


        return $entity;
    }

    /**
     * Returns the list entries helper.
     *
     * @return ListEntriesHelper
     */
    public function getListEntriesHelper()
    {
        return $this->listEntriesHelper;
    }
    
    /**
     * Sets the list entries helper.
     *
     * @param ListEntriesHelper $listEntriesHelper
     *
     * @return void
     */
    public function setListEntriesHelper($listEntriesHelper)
    {
        if ($this->listEntriesHelper != $listEntriesHelper) {
            $this->listEntriesHelper = $listEntriesHelper;
        }
    }
    
}
