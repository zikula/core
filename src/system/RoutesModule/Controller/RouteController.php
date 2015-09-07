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

use ModUtil;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\RoutesModule\Controller\Base\RouteController as BaseRouteController;
use SecurityUtil;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Zikula\RoutesModule\Entity\RouteEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Zikula\Core\Response\PlainResponse;

/**
 * Route controller class providing navigation and interaction functionality.
 */
class RouteController extends BaseRouteController
{
    /**
     * This method is the default function handling the main area called without defining arguments.
     *
     * @Route("/routes",
     *        methods = {"GET"}
     * )
     *
     * @param Request  $request      Current request instance
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions.
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }

    /**
     * This method provides a item list overview.
     *
     * @Route("/routes/view/{sort}/{sortdir}/{pos}/{num}.{_format}",
     *        requirements = {"sortdir" = "asc|desc|ASC|DESC", "pos" = "\d+", "num" = "\d+", "_format" = "html|kml"},
     *        defaults = {"sort" = "", "sortdir" = "asc", "pos" = 1, "num" = 0, "_format" = "html"},
     *        methods = {"GET"}
     * )
     *
     * @param Request  $request      Current request instance
     * @param string  $sort         Sorting field.
     * @param string  $sortdir      Sorting direction.
     * @param int     $pos          Current pager position.
     * @param int     $num          Amount of entries to display.
     * @param string  $tpl          Name of alternative template (to be used instead of the default template).
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions.
     */
    public function viewAction(Request $request, $sort, $sortdir, $pos, $num)
    {
        // Always force to see all entries to make sortable working.
        $request->query->set('all', 1);

        $groupMessages = array(
            RouteEntity::POSITION_FIXED_TOP => $this->__('Routes fixed to the top of the list:'),
            RouteEntity::POSITION_MIDDLE => $this->__('Normal routes:'),
            RouteEntity::POSITION_FIXED_BOTTOM => $this->__('Routes fixed to the bottom of the list:'),
        );
        $this->view->assign('groupMessages', $groupMessages);
        $this->view->assign('sortableGroups', array(RouteEntity::POSITION_MIDDLE));

        $configDumper = $this->get('zikula.dynamic_config_dumper');
        $configuration = $configDumper->getConfigurationForHtml('jms_i18n_routing');
        $this->view->assign('jms_i18n_routing', $configuration);

        return parent::viewAction($request, $sort, $sortdir, $pos, $num);
    }

    /**
     * This method provides a handling of edit requests.
     *
     * @Route("/route/edit/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"id" = "0", "_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request  $request      Current request instance
     * @param string  $tpl          Name of alternative template (to be used instead of the default template).
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions.
     * @throws NotFoundHttpException Thrown by form handler if item to be edited isn't found.
     * @throws RuntimeException      Thrown if another critical error occurs (e.g. workflow actions not available).
     */
    public function editAction(Request $request)
    {
        return parent::editAction($request);
    }

    /**
     * This method provides a item detail view.
     *
     * @Route("/route/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html|kml|ics"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET"}
     * )
     *
     * @param Request  $request      Current request instance
     * @param RouteEntity $route      Treated route instance.
     * @param string  $tpl          Name of alternative template (to be used instead of the default template).
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions.
     * @throws NotFoundHttpException Thrown by param converter if item to be displayed isn't found.
     */
    public function displayAction(Request $request, RouteEntity $route)
    {
        return parent::displayAction($request, $route);
    }

    /**
     * This method provides a handling of simple delete requests.
     *
     * @Route("/route/delete/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request  $request      Current request instance
     * @param RouteEntity $route      Treated route instance.
     * @param boolean $confirmation Confirm the deletion, else a confirmation page is displayed.
     * @param string  $tpl          Name of alternative template (to be used instead of the default template).
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions.
     * @throws NotFoundHttpException Thrown by param converter if item to be deleted isn't found.
     * @throws RuntimeException      Thrown if another critical error occurs (e.g. workflow actions not available).
     */
    public function deleteAction(Request $request, RouteEntity $route)
    {
        return parent::deleteAction($request, $route);
    }

    /**
     * This is a custom method.
     *
     * @Route("/routes/reload",
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request  $request      Current request instance
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions.
     */
    public function reloadAction(Request $request)
    {
        $objectType = 'route';
        if (!SecurityUtil::checkPermission($this->name . ':' . ucfirst($objectType) . ':', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $cacheClearer = $this->get('zikula.cache_clearer');
        $controllerHelper = $this->get('zikularoutesmodule.controller_helper');

        $cacheClearer->clear("symfony.routing");
        $this->view->clear_cache();
        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Routes reloaded.'));

        // reload **all** JS routes
        $result = $controllerHelper->dumpJsRoutes();
        if($result == '') {
            $request->getSession()->getFlashBag()->add('status', $this->__f('Done! Exposed JS Routes dumped to %s.', 'web/js/fos_js_routes.js'));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! There was an error dumping exposed JS Routes: %s', $result));
        }

        $redirectUrl = $this->serviceManager->get('router')->generate('zikularoutesmodule_route_view', array('lct' => 'admin'), UrlGeneratorInterface::ABSOLUTE_URL);

        return new RedirectResponse($redirectUrl);
    }

    /**
     * This is a custom method.
     *
     * @Route("/routes/renew",
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request  $request      Current request instance
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions.
     */
    public function renewAction(Request $request)
    {
        $objectType = 'route';
        if (!SecurityUtil::checkPermission($this->name . ':' . ucfirst($objectType) . ':', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Renew the routing settings.
        ModUtil::apiFunc('ZikulaRoutesModule', 'admin', 'reloadMultilingualRoutingSettings');

        $request->getSession()->getFlashBag()->add('status', $this->__('Done! Routing settings renewed.'));
        $redirectUrl = $this->serviceManager->get('router')->generate('zikularoutesmodule_route_view', array('lct' => 'admin'));

        return new RedirectResponse(\System::normalizeUrl($redirectUrl));
    }

    /**
     * This is a custom method.
     * Dump the routes exposed to javascript to '/web/js/fos_js_routes.js'
     *
     * @Route("/routes/dump/{lang}",
     *        name = "zikularoutesmodule_route_dumpjsroutes",
     *        methods = {"GET"}
     * )
     *
     * @param Request  $request      Current request instance
     *
     * @return mixed Output.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function dumpJsRoutesAction(Request $request, $lang = null)
    {
        $objectType = 'route';
        if (!SecurityUtil::checkPermission($this->name . ':' . ucfirst($objectType) . ':', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $controllerHelper = $this->get('zikularoutesmodule.controller_helper');
        $result = $controllerHelper->dumpJsRoutes($lang);

        if ($result == '') {
            $request->getSession()->getFlashBag()->add('status', $this->__f('Done! Exposed JS Routes dumped to %s.', 'web/js/fos_js_routes.js'));
        } else {
            $request->getSession()->getFlashBag()->add('error', $this->__f('Error! There was an error dumping exposed JS Routes: %s', $result));
        }
        $redirectUrl = $this->serviceManager->get('router')->generate('zikularoutesmodule_route_view', array('lct' => 'admin'));

        return new RedirectResponse(\System::normalizeUrl($redirectUrl));
    }

    /**
     * Process status changes for multiple items.
     *
     * This function processes the items selected in the admin view page.
     * Multiple items may have their state changed or be deleted.
     *
     * @Route("/routes/handleSelectedEntries",
     *        methods = {"POST"}
     * )
     *
     * @param string $action The action to be executed.
     * @param array  $items  Identifier list of the items to be processed.
     *
     * @return bool true on sucess, false on failure.
     *
     * @throws RuntimeException Thrown if executing the workflow action fails
     */
    public function handleSelectedEntriesAction(Request $request)
    {
        return parent::handleSelectedEntriesAction($request);
    }

    /**
     * This method cares for a redirect within an inline frame.
     *
     * @Route("/route/handleInlineRedirect/{idPrefix}/{commandName}/{id}",
     *        requirements = {"id" = "\d+"},
     *        defaults = {"commandName" = "", "id" = 0},
     *        methods = {"GET"}
     * )
     *
     * @param string  $idPrefix    Prefix for inline window element identifier.
     * @param string  $commandName Name of action to be performed (create or edit).
     * @param integer $id          Id of created item (used for activating auto completion after closing the modal window).
     *
     * @return boolean Whether the inline redirect has been performed or not.
     */
    public function handleInlineRedirectAction($idPrefix, $commandName, $id = 0)
    {
        return parent::handleInlineRedirectAction($idPrefix, $commandName, $id);
    }

    // feel free to add your own controller methods here
}
