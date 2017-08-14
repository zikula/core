<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 1.0.1 (https://modulestudio.de).
 */

namespace Zikula\RoutesModule\Entity;

use Zikula\RoutesModule\Entity\Base\AbstractRouteEntity as BaseEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entity class that defines the entity structure and behaviours.
 *
 * This is the concrete entity class for route entities.
 * @ORM\Entity(repositoryClass="Zikula\RoutesModule\Entity\Repository\RouteRepository")
 * @ORM\Table(name="zikula_routes_route",
 *     indexes={
 *         @ORM\Index(name="workflowstateindex", columns={"workflowState"})
 *     }
 * )
 */
class RouteEntity extends BaseEntity
{
    // feel free to add your own methods here
}
