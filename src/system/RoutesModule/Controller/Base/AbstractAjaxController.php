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

namespace Zikula\RoutesModule\Controller\Base;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;

/**
 * Ajax controller class.
 */
abstract class AbstractAjaxController extends AbstractController
{


    /**
     * This is the default action handling the main area called without defining arguments.
     *
     * @param Request  $request      Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function indexAction(Request $request)
    {
        // parameter specifying which type of objects we are treating
        $objectType = $request->query->getAlnum('ot', 'route');
        
        $permLevel = ACCESS_OVERVIEW;
        if (!$this->hasPermission($this->name . '::', '::', $permLevel)) {
            throw new AccessDeniedException();
        }
    }

}
