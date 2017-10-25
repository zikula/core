<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\AdminModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\AdminModule\Form\Type\ConfigType;
use Zikula\Core\Controller\AbstractController;
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
     * @Template("ZikulaAdminModule:Config:config.html.twig")
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return array|RedirectResponse
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get admin capable mods
        $adminModules = $this->get('zikula_extensions_module.api.capability')->getExtensionsCapableOf('admin');

        // Get all categories
        $categories = [];
        $items = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminCategoryEntity')->findBy([], ['sortorder' => 'ASC']);
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[$item['name']] = $item['cid'];
            }
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $modVars = $variableApi->getAll('ZikulaAdminModule');
        $dataValues = $modVars;
        $dataValues['ignoreinstallercheck'] = (bool)$dataValues['ignoreinstallercheck'];
        $dataValues['admingraphic'] = (bool)$dataValues['admingraphic'];

        $modules = [];
        foreach ($adminModules as $adminModule) {
            // Get the category assigned to this module
            $category = $this->get('doctrine')->getRepository('ZikulaAdminModule:AdminModuleEntity')->findOneBy(['mid' => $adminModule->getId()]);
            // output module category selection
            $modules[] = [
                'displayname' => $adminModule['displayname'],
                'name' => $adminModule['name']
            ];
            $dataValues['modulecategory' . $adminModule['name']] = (isset($category)) ? $category->getCid() : $this->getVar('defaultcategory');
        }
        $themes = $this->get('zikula_theme_module.theme_entity.repository')->get(ThemeEntityRepository::FILTER_ADMIN);

        $form = $this->createForm(ConfigType::class,
            $dataValues, [
                'translator' => $this->get('translator.default'),
                'categories' => $categories,
                'modules' => $modules,
                'themes' => $themes
            ]
        );

        if ($form->handleRequest($request)->isValid()) {
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
                    $this->get('zikula_admin_module.helper.admin_module_helper')->setAdminModuleCategory($adminModule, $category);
                }

                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
            if ($form->get('help')->isClicked()) {
                return $this->redirect($this->generateUrl('zikulaadminmodule_admin_help') . '#modifyconfig');
            }

            return $this->redirectToRoute('zikulaadminmodule_admin_view');
        }

        return [
            'form' => $form->createView(),
            'modules' => $modules
        ];
    }
}
