<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminCategoryRepositoryInterface;
use Zikula\AdminModule\Entity\RepositoryInterface\AdminModuleRepositoryInterface;
use Zikula\AdminModule\Form\Type\ConfigType;
use Zikula\AdminModule\Helper\AdminModuleHelper;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\CapabilityApiInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaAdminModule/Config/config.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function configAction(
        Request $request,
        AdminCategoryRepositoryInterface $adminCategoryRepository,
        AdminModuleRepositoryInterface $adminModuleRepository,
        ThemeEntityRepository $themeEntityRepository,
        VariableApiInterface $variableApi,
        CapabilityApiInterface $capabilityApi,
        AdminModuleHelper $adminModuleHelper
    ) {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get admin capable mods
        $adminModules = $capabilityApi->getExtensionsCapableOf('admin');

        // Get all categories
        $categories = [];
        $items = $adminCategoryRepository->findBy([], ['sortorder' => 'ASC']);
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[$item['name']] = $item['cid'];
            }
        }

        $modVars = $variableApi->getAll('ZikulaAdminModule');
        $dataValues = $modVars;
        $dataValues['ignoreinstallercheck'] = (bool)$dataValues['ignoreinstallercheck'];
        $dataValues['admingraphic'] = (bool)$dataValues['admingraphic'];

        $modules = [];
        foreach ($adminModules as $adminModule) {
            // Get the category assigned to this module
            $category = $adminModuleRepository->findOneBy(['mid' => $adminModule->getId()]);
            // output module category selection
            $modules[] = [
                'displayname' => $adminModule['displayname'],
                'name' => $adminModule['name']
            ];
            $dataValues['modulecategory' . $adminModule['name']] = isset($category) ? $category->getCid() : $this->getVar('defaultcategory');
        }
        $themes = $themeEntityRepository->get(ThemeEntityRepository::FILTER_ADMIN);

        $form = $this->createForm(ConfigType::class,
            $dataValues, [
                'categories' => $categories,
                'modules' => $modules,
                'themes' => $themes
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // save module vars
                $vars = [];
                foreach (['ignoreinstallercheck', 'admingraphic', 'displaynametype', 'itemsperpage', 'modulesperrow', 'admintheme', 'startcategory', 'defaultcategory'] as $varName) {
                    $vars[$varName] = $formData[$varName];
                }
                $variableApi->setAll('ZikulaAdminModule', $vars);

                foreach ($adminModules as $adminModule) {
                    $moduleName = $adminModule['name'];
                    $category = $formData['modulecategory' . $moduleName];
                    if (!$category) {
                        continue;
                    }
                    $adminModuleHelper->setAdminModuleCategory($adminModule, $category);
                }

                $this->addFlash('status', 'Done! Configuration updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'modules' => $modules
        ];
    }
}
