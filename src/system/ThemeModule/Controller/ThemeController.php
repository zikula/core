<?php
/**
 * Copyright Zikula Foundation 2016 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\ThemeModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Yaml\Yaml;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Theme\Annotation\Theme;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Util;

/**
 * Class ThemeController
 * @package Zikula\ThemeModule\Controller
 * @Route("/admin")
 */
class ThemeController extends AbstractController
{
    /**
     * @Route("/view")
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * view all themes
     *
     * @param Request $request
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     */
    public function viewAction(Request $request)
    {
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        if (isset($this->container['multisites.enabled']) && $this->container['multisites.enabled'] == 1) {
            // only the main site can regenerate the themes list
            if ($this->container['multisites.mainsiteurl'] == $request->query->get('sitedns', null)) {
                //return true but any action has been made
                Util::regenerate();
            }
        } else {
            Util::regenerate();
        }

        // call the API to get a list of all themes in the themes dir
        $themes = \ThemeUtil::getAllThemes(\ThemeUtil::FILTER_ALL, \ThemeUtil::STATE_ALL);

        return [
            'themes' => $themes,
            'currenttheme' => $this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'Default_Theme')
        ];
    }

    /**
     * @Route("/makedefault/{themeName}")
     * @Theme("admin")
     * @Template
     *
     * set theme as default for site
     *
     * @param Request $request
     * @param string $themeName
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function setAsDefaultAction(Request $request, $themeName)
    {
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder(['themeName' => $themeName, 'resetuserselected' => true])
            ->add('themeName', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('resetuserselected', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('Override users\' theme settings'),
                'required' => false,
            ])
            ->add('Accept', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->add('Cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('Accept')->isClicked()) {
                $data = $form->getData();
                // Set the default theme
                if (\ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'setasdefault', array('themename' => $data['themeName'], 'resetuserselected' => $data['resetuserselected']))) {
                    // Success
                    $this->addFlash('status', $this->__('Done! Changed default theme.'));
                }

                return $this->redirect($this->generateUrl('zikulathememodule_theme_view'));
            }
            if ($form->get('Cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirect($this->generateUrl('zikulathememodule_theme_view'));
        }

        return [
            'themeName' => $themeName,
            'theme_change' => $this->getVar('theme_change'),
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete/{themeName}")
     * @Theme("admin")
     * @Template
     *
     * delete a theme
     *
     * @param Request $request
     * @param string $themeName
     *
     * @return Response symfony response object if confirmation isn't provided
     *
     * @throws \InvalidArgumentException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the module
     */
    public function deleteAction(Request $request, $themeName)
    {
        if (!$this->hasPermission('ZikulaThemeModule::', "$themeName::", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $themeinfo = \ThemeUtil::getInfo(\ThemeUtil::getIDFromName($themeName));
        if ($themeinfo == false) {
            throw new NotFoundHttpException($this->__('Sorry! No such theme found.'), null, 404);
        }

        $form = $this->createFormBuilder(['themeName' => $themeName, 'deletefiles' => false])
            ->add('themeName', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('deletefiles', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('Also delete theme files, if possible'),
                'required' => false,
            ])
            ->add('Accept', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->add('Cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($form->get('Accept')->isClicked()) {
                $data = $form->getData();
                // Delete the theme
                if (\ModUtil::apiFunc('ZikulaThemeModule', 'admin', 'delete', array('themename' => $data['themeName'], 'deletefiles' => $data['deletefiles']))) {
                    // Success
                    $this->addFlash('status', $this->__('Done! Deleted the theme.'));
                }

                return $this->redirect($this->generateUrl('zikulathememodule_theme_view'));
            }
            if ($form->get('Cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirect($this->generateUrl('zikulathememodule_theme_view'));
        }

        return [
            'themeName' => $themeName,
            'form' => $form->createView()
        ];

    }

    /**
     * @Route("/credits/{themeName}")
     * @Method("GET")
     * @Theme("admin")
     * @Template
     *
     * display the theme credits
     *
     * @param string $themeName name of the theme
     *
     * @return Response symfony response object
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function creditsAction($themeName)
    {
        if (!$this->hasPermission('ZikulaThemeModule::', "$themeName::credits", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }

        return ['themeinfo' => \ThemeUtil::getInfo(\ThemeUtil::getIDFromName($themeName))];
    }
}
