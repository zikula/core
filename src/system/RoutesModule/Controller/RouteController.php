<?php
/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <support@zikula.org>.
 * @link http://www.zikula.org
 * @link http://zikula.org
 * @version Generated by ModuleStudio 0.7.1 (http://modulestudio.de).
 */

namespace Zikula\RoutesModule\Controller;

use Zikula\RoutesModule\Controller\Base\AbstractRouteController;

use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\RoutesModule\Entity\RouteEntity;

/**
 * Route controller class providing navigation and interaction functionality.
 */
class RouteController extends AbstractRouteController
{
    /**
     * This is the default action handling the main admin area called without defining arguments.
     *
     * @Route("/admin/routes",
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function adminIndexAction(Request $request)
    {
        return parent::adminIndexAction($request);
    }
    
    /**
     * This is the default action handling the main area called without defining arguments.
     *
     * @Route("/routes",
     *        methods = {"GET"}
     * )
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function indexAction(Request $request)
    {
        return parent::indexAction($request);
    }

    /**
     * This action provides an item list overview in the admin area.
     *
     * @Route("/admin/routes/view/{sort}/{sortdir}/{pos}/{num}.{_format}",
     *        requirements = {"sortdir" = "asc|desc|ASC|DESC", "pos" = "\d+", "num" = "\d+", "_format" = "html|kml"},
     *        defaults = {"sort" = "", "sortdir" = "asc", "pos" = 1, "num" = 0, "_format" = "html"},
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     *
     * @param Request $request Current request instance
     * @param string  $sort    Sorting field
     * @param string  $sortdir Sorting direction
     * @param int     $pos     Current pager position
     * @param int     $num     Amount of entries to display
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function adminViewAction(Request $request, $sort, $sortdir, $pos, $num)
    {
        return parent::adminViewAction($request, $sort, $sortdir, $pos, $num);
    }

    /**
     * This action provides an item list overview.
     *
     * @Route("/routes/view/{sort}/{sortdir}/{pos}/{num}.{_format}",
     *        requirements = {"sortdir" = "asc|desc|ASC|DESC", "pos" = "\d+", "num" = "\d+", "_format" = "html|kml"},
     *        defaults = {"sort" = "", "sortdir" = "asc", "pos" = 1, "num" = 0, "_format" = "html"},
     *        methods = {"GET"}
     * )
     *
     * @param Request $request Current request instance
     * @param string  $sort    Sorting field
     * @param string  $sortdir Sorting direction
     * @param int     $pos     Current pager position
     * @param int     $num     Amount of entries to display
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function viewAction(Request $request, $sort, $sortdir, $pos, $num)
    {
        return parent::viewAction($request, $sort, $sortdir, $pos, $num);
    }

    /**
     * This method includes the common implementation code for adminView() and view().
     */
    protected function viewInternal(Request $request, $sort, $sortdir, $pos, $num, $isAdmin = false)
    {
        // Always force to see all entries to make sortable working.
        $request->query->set('all', 1);

        return parent::viewInternal($request, $sort, $sortdir, $pos, $num, $isAdmin);
    }

    /**
     * This action provides a item detail view in the admin area.
     *
     * @Route("/admin/route/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html|kml|ics"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     *
     * @param Request     $request Current request instance
     * @param RouteEntity $route   Treated route instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown by param converter if item to be displayed isn't found
     */
    public function adminDisplayAction(Request $request, RouteEntity $route)
    {
        return parent::adminDisplayAction($request, $route);
    }

    /**
     * This action provides a item detail view.
     *
     * @Route("/route/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html|kml|ics"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET"}
     * )
     *
     * @param Request     $request Current request instance
     * @param RouteEntity $route   Treated route instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown by param converter if item to be displayed isn't found
     */
    public function displayAction(Request $request, RouteEntity $route)
    {
        return parent::displayAction($request, $route);
    }

    /**
     * This action provides a handling of edit requests in the admin area.
     *
     * @Route("/admin/route/edit/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"id" = "0", "_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     * @Theme("admin")
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown by form handler if item to be edited isn't found
     * @throws RuntimeException      Thrown if another critical error occurs (e.g. workflow actions not available)
     */
    public function adminEditAction(Request $request)
    {
        return parent::adminEditAction($request);
    }

    /**
     * This action provides a handling of edit requests.
     *
     * @Route("/route/edit/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"id" = "0", "_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown by form handler if item to be edited isn't found
     * @throws RuntimeException      Thrown if another critical error occurs (e.g. workflow actions not available)
     */
    public function editAction(Request $request)
    {
        return parent::editAction($request);
    }

    /**
     * This action provides a handling of simple delete requests in the admin area.
     *
     * @Route("/admin/route/delete/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     * @Theme("admin")
     *
     * @param Request     $request Current request instance
     * @param RouteEntity $route   Treated route instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown by param converter if item to be deleted isn't found
     * @throws RuntimeException      Thrown if another critical error occurs (e.g. workflow actions not available)
     */
    public function adminDeleteAction(Request $request, RouteEntity $route)
    {
        return parent::adminDeleteAction($request, $route);
    }

    /**
     * This action provides a handling of simple delete requests.
     *
     * @Route("/route/delete/{id}.{_format}",
     *        requirements = {"id" = "\d+", "_format" = "html"},
     *        defaults = {"_format" = "html"},
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request     $request Current request instance
     * @param RouteEntity $route   Treated route instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     * @throws NotFoundHttpException Thrown by param converter if item to be deleted isn't found
     * @throws RuntimeException      Thrown if another critical error occurs (e.g. workflow actions not available)
     */
    public function deleteAction(Request $request, RouteEntity $route)
    {
        return parent::deleteAction($request, $route);
    }

    /**
     * This is a custom action in the admin area.
     *
     * @Route("/admin/routes/reload",
     *        methods = {"GET", "POST"}
     * )
     * @Theme("admin")
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function adminReloadAction(Request $request)
    {
        //return parent::adminReloadAction($request);

        $objectType = 'route';
        if (!$this->hasPermission($this->name . ':' . ucfirst($objectType) . ':', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $cacheClearer = $this->get('zikula.cache_clearer');
        $routeDumperHelper = $this->get('zikula_routes_module.route_dumper_helper');

        $cacheClearer->clear('symfony.routing');

        $this->addFlash('status', $this->__('Done! Routes reloaded.'));

        // reload **all** JS routes
        $result = $routeDumperHelper->dumpJsRoutes();
        if ($result == '') {
            $this->addFlash('status', $this->__f('Done! Exposed JS Routes dumped to %s.', ['%s' => 'web/js/fos_js_routes.js']));
        } else {
            $this->addFlash('error', $this->__f('Error! There was an error dumping exposed JS Routes: %s', ['%s' => $result]));
        }

        return $this->redirectToRoute('zikularoutesmodule_route_adminview');
    }

    /**
     * This is a custom action.
     *
     * @Route("/routes/reload",
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function reloadAction(Request $request)
    {
        throw new AccessDeniedException();
    }

    /**
     * This is a custom action in the admin area.
     *
     * @Route("/admin/routes/renew",
     *        methods = {"GET", "POST"}
     * )
     * @Theme("admin")
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function adminRenewAction(Request $request)
    {
        $objectType = 'route';
        if (!$this->hasPermission($this->name . ':' . ucfirst($objectType) . ':', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // Renew the routing settings.
        $this->get('zikula_routes_module.multilingual_routing_helper')->reloadMultilingualRoutingSettings();

        $this->addFlash('status', $this->__('Done! Routing settings renewed.'));

        return $this->redirectToRoute('zikularoutesmodule_route_adminview');
    }

    /**
     * This is a custom action.
     *
     * @Route("/routes/renew",
     *        methods = {"GET", "POST"}
     * )
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function renewAction(Request $request)
    {
        throw new AccessDeniedException();
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
     * @Theme("admin")
     *
     * @param Request $request Current request instance
     *
     * @return bool true on sucess, false on failure
     *
     * @throws RuntimeException Thrown if executing the workflow action fails
     */
    public function adminHandleSelectedEntriesAction(Request $request)
    {
        return parent::adminHandleSelectedEntriesAction($request);
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
     * @param Request $request Current request instance
     *
     * @return bool true on sucess, false on failure
     *
     * @throws RuntimeException Thrown if executing the workflow action fails
     */
    public function handleSelectedEntriesAction(Request $request)
    {
        return parent::handleSelectedEntriesAction($request);
    }

    /**
     * This is a custom method.
     * Dump the routes exposed to javascript to '/web/js/fos_js_routes.js'
     *
     * @Route("/routes/dump/{lang}",
     *        name = "zikularoutesmodule_route_dumpjsroutes",
     *        methods = {"GET"}
     * )
     * @Theme("admin")
     *
     * @param Request $request Current request instance
     *
     * @return mixed Output
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function dumpJsRoutesAction(Request $request, $lang = null)
    {
        $objectType = 'route';
        if (!$this->hasPermission($this->name . ':' . ucfirst($objectType) . ':', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $routeDumperHelper = $this->get('zikula_routes_module.route_dumper_helper');
        $result = $routeDumperHelper->dumpJsRoutes($lang);

        if ($result == '') {
            $this->addFlash('status', $this->__f('Done! Exposed JS Routes dumped to %s.', ['%s' => 'web/js/fos_js_routes.js']));
        } else {
            $this->addFlash('error', $this->__f('Error! There was an error dumping exposed JS Routes: %s', ['%s' => $result]));
        }

        return $this->redirectToRoute('zikularoutesmodule_route_adminview');
    }
}
