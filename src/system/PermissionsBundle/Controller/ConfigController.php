<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\PermissionsBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsBundle\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsBundle\Annotation\PermissionCheck;
use Zikula\PermissionsBundle\Form\Type\ConfigType;
use Zikula\PermissionsBundle\Repository\PermissionRepositoryInterface;
use Zikula\ThemeBundle\Engine\Annotation\Theme;

/**
 * @PermissionCheck("admin")
 */
#[Route('/permissions')]
class ConfigController extends AbstractController
{
    /**
     * @Theme("admin")
     * @Template("@ZikulaPermissions/Config/config.html.twig")
     *
     * @return array|Response
     */
    #[Route('/config', name: 'zikulapermissionsbundle_config_config')]
    public function config(
        Request $request,
        VariableApiInterface $variableApi,
        PermissionRepositoryInterface $permissionRepository
    ) {
        $modVars = $variableApi->getAll('ZikulaPermissionsModule');
        $modVars['lockadmin'] = (bool) $modVars['lockadmin'];
        $modVars['filter'] = (bool) $modVars['filter'];

        $form = $this->createForm(ConfigType::class, $modVars);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                $error = false;

                $lockadmin = isset($formData['lockadmin']) ? (bool) $formData['lockadmin'] : false;
                $variableApi->set('ZikulaPermissionsModule', 'lockadmin', $lockadmin);

                $adminId = isset($formData['adminid']) ? (int) $formData['adminid'] : 1;
                if (0 !== $adminId) {
                    $perm = $permissionRepository->find($adminId);
                    if (!$perm) {
                        $adminId = 0;
                        $error = true;
                    }
                }
                $variableApi->set('ZikulaPermissionsModule', 'adminid', $adminId);

                $filter = isset($formData['filter']) ? (bool) $formData['filter'] : false;
                $variableApi->set('ZikulaPermissionsModule', 'filter', $filter);

                if (true === $error) {
                    $this->addFlash('error', 'Error! Could not save configuration: unknown permission rule ID.');
                } else {
                    $this->addFlash('status', 'Done! Configuration updated.');
                }
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulapermissionsbundle_permission_listpermissions');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
