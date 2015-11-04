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

namespace Zikula\RoutesModule;

use Zikula\RoutesModule\Base\RoutesModuleInstaller as BaseRoutesModuleInstaller;

/**
 * Installer implementation class.
 */
class RoutesModuleInstaller extends BaseRoutesModuleInstaller
{
    /**
     * {@inheritdoc}
     */
    public function upgrade($oldVersion)
    {
        switch ($oldVersion) {
            case '1.0.0':
                $sql = "DELETE FROM zikula_routes_route WHERE userRoute = 0";
                $this->entityManager->getConnection()->exec($sql);

                $this->get('zikula.doctrine.schema_tool')->update(['\Zikula\RoutesModule\Entity\RouteEntity']);
                break;
            default:
                return false;
        }

        return true;
    }
}
