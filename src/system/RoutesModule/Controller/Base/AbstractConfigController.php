<?php

/**
 * Routes.
 *
 * @copyright Zikula contributors (Zikula)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Zikula contributors <info@ziku.la>.
 * @see https://ziku.la
 * @version Generated by ModuleStudio 1.4.0 (https://modulestudio.de).
 */

declare(strict_types=1);

namespace Zikula\RoutesModule\Controller\Base;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\RoutesModule\AppSettings;
use Zikula\RoutesModule\Form\Type\ConfigType;
use Zikula\RoutesModule\Helper\PermissionHelper;

/**
 * Config controller base class.
 */
abstract class AbstractConfigController extends AbstractController
{
    /**
     * This method takes care of the application configuration.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have required permissions
     */
    public function configAction(
        Request $request,
        PermissionHelper $permissionHelper,
        AppSettings $appSettings,
        LoggerInterface $logger,
        CurrentUserApiInterface $currentUserApi
    ): Response {
        if (!$permissionHelper->hasPermission(ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }
        
        $form = $this->createForm(ConfigType::class, $appSettings);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $appSettings = $form->getData();
                $appSettings->save();
        
                $this->addFlash('status', 'Done! Configuration updated.');
                $userName = $currentUserApi->get('uname');
                $logger->notice(
                    '{app}: User {user} updated the configuration.',
                    ['app' => 'ZikulaContentModule', 'user' => $userName]
                );
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        
            // redirect to config page again (to show with GET request)
            return $this->redirectToRoute('zikularoutesmodule_config_config');
        }
        
        $templateParameters = [
            'form' => $form->createView()
        ];
        
        // render the config form
        return $this->render('@ZikulaRoutesModule/Config/config.html.twig', $templateParameters);
    }
}
