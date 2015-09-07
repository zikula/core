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

namespace Zikula\RoutesModule\Controller;

use SecurityUtil;
use Symfony\Component\HttpFoundation\Request;
use Zikula\Core\Response\Ajax\AjaxResponse;
use Zikula\RoutesModule\Controller\Base\AjaxController as BaseAjaxController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Ajax controller class providing navigation and interaction functionality.
 *
 * @Route("/ajax")
 */
class AjaxController extends BaseAjaxController
{
    /**
     * Retrieve item list for finder selections in Forms, Content type plugin and Scribite.
     *
     * @Route("/getItemListFinder", options={"expose"=true})

     *
     * @param string $ot      Name of currently used object type.
     * @param string $sort    Sorting field.
     * @param string $sortdir Sorting direction.
     *
     * @return AjaxResponse
     */
    public function getItemListFinderAction(Request $request)
    {
        return parent::getItemListFinderAction($request);
    }
    // feel free to add your own controller methods here

    public function sort(Request $request)
    {
        if (!SecurityUtil::checkPermission($this->name . '::Ajax', '::', ACCESS_EDIT)) {
            return true;
        }

        $objectType = $request->request->filter('ot', 'route', false, FILTER_SANITIZE_STRING);
        $sort = $request->request->get('sort', array());

        foreach ($sort as $position => $id) {
            $id = substr($id, 4);
            $object = $this->entityManager->find($this->name . ":" . ucfirst($objectType) . "Entity", $id);
            $object->setSort($position);
            $this->entityManager->persist($object);
        }

        $this->entityManager->flush();

        $cacheClearer = $this->get('zikula.cache_clearer');
        $cacheClearer->clear("symfony.routing");

        return new AjaxResponse(array());
    }
}
