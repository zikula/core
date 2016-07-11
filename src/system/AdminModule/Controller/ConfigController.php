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

use ModUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 * @Route("/config")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template
     *
     * @param Request $request
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     * @return Response
     */
    public function configAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaAdminModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        // get admin capable mods
        // TODO replace by Zikula\ExtensionsModule\Api\CapabilityApi for 2.0
        $adminModules = ModUtil::getAdminMods();

        // Get all categories
        $categories = [];
        $items = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getall');
        foreach ($items as $item) {
            if ($this->hasPermission('ZikulaAdminModule::', $item['name'] . '::' . $item['cid'], ACCESS_READ)) {
                $categories[$item['name']] = $item['cid'];
            }
        }

        $variableApi = $this->get('zikula_extensions_module.api.variable');
        $modVars = $variableApi->getAll('ZikulaAdminModule');
        $dataValues = $modVars;
        $dataValues['ignoreinstallercheck'] = ($dataValues['ignoreinstallercheck'] == 1) ? true : false;
        $dataValues['admingraphic'] = ($dataValues['admingraphic'] == 1) ? true : false;

        $modules = [];
        foreach ($adminModules as $adminModule) {
            // Get the category assigned to this module
            $category = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'getmodcategory',
                    ['mid' => ModUtil::getIdFromName($adminModule['name'])]);

            if (false === $category) {
                // it's not set, so we use the default category
                $category = $this->getVar('defaultcategory');
            }
            // output module category selection
            $modules[] = [
                'displayname' => $adminModule['displayname'],
                'name' => $adminModule['name']
            ];
            $dataValues['modulecategory' . $adminModule['name']] = $category;
        }

        $form = $this->createForm('Zikula\AdminModule\Form\Type\ConfigType',
            $dataValues, [
                'translator' => $this->get('translator.default'),
                'categories' => $categories,
                'modules' => $modules
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

                    // Add the module to the category
                    $result = ModUtil::apiFunc('ZikulaAdminModule', 'admin', 'addmodtocategory', [
                        'module' => $moduleName,
                        'category' => $category
                    ]);

                    if (false == $result) {
                        /** @var $cat \Zikula\AdminModule\Entity\AdminCategoryEntity */
                        $cat = ModUtil::apiFunc($this->name, 'admin', 'getCategory', ['cid' => $category]);
                        $this->addFlash('error', $this->__f('Error! Could not add module %1$s to module category %2$s.', [$moduleName, $cat->getName()]));
                    }
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
