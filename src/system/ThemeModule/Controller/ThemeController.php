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
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;

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
        $vetoEvent = new GenericEvent();
        $this->get('event_dispatcher')->dispatch(ExtensionEvents::REGENERATE_VETO, $vetoEvent);
        if (!$vetoEvent->isPropagationStopped()) {
            $this->get('zikula_theme_module.helper.bundle_sync_helper')->regenerate();
        }

        $themes = $this->get('zikula_theme_module.theme_entity.repository')->get(ThemeEntityRepository::FILTER_ALL, ThemeEntityRepository::STATE_ALL);

        return [
            'themes' => $themes,
            'currenttheme' => $this->get('zikula_extensions_module.api.variable')->get(VariableApi::CONFIG, 'Default_Theme')
        ];
    }

    /**
     * @Route("/activate/{themeName}")
     */
    public function activateAction($themeName)
    {
        $theme = $this->get('zikula_theme_module.theme_entity.repository')->findOneBy(['name' => $themeName]);
        $theme->setState(ThemeEntityRepository::STATE_ACTIVE);
        $this->getDoctrine()->getManager()->flush();
        $this->get('zikula.cache_clearer')->clear('symfony.config');

        return $this->redirectToRoute('zikulathememodule_theme_view');
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
            ->add('resetuserselected', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [ // @todo remove at Core-2.0
                'label' => $this->__('Override users\' theme settings'),
                'required' => false,
            ])
            ->add('accept', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Accept'),
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('accept')->isClicked()) {
                $data = $form->getData();
                // Set the default theme
                $this->get('zikula_extensions_module.api.variable')->set(VariableApi::CONFIG, 'Default_Theme', $data['themeName']);
                if ($data['resetuserselected']) {
                    // @todo remove this at Core-2.0
                    $query = $this->getDoctrine()->getManager()->createQueryBuilder()
                        ->update('ZikulaUsersModule:UserEntity', 'u')
                        ->set('u.theme', ':null')
                        ->setParameter('null', '')
                        ->getQuery();
                    $query->getResult();
                }
                $this->get('zikula.cache_clearer')->clear('twig');
                $this->get('zikula.cache_clearer')->clear('symfony.config');
                $this->addFlash('status', $this->__('Done! Changed default theme.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulathememodule_theme_view');
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
     * @throws NotFoundHttpException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the module
     */
    public function deleteAction(Request $request, $themeName)
    {
        if (!$this->hasPermission('ZikulaThemeModule::', "$themeName::", ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder(['themeName' => $themeName, 'deletefiles' => false])
            ->add('themeName', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('deletefiles', 'Symfony\Component\Form\Extension\Core\Type\CheckboxType', [
                'label' => $this->__('Also delete theme files, if possible'),
                'required' => false,
            ])
            ->add('delete', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Delete'),
                'icon' => 'fa-trash-o',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', 'Symfony\Component\Form\Extension\Core\Type\SubmitType', [
                'label' => $this->__('Cancel'),
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $data = $form->getData();
                $themeEntity = $this->getDoctrine()->getRepository('ZikulaThemeModule:ThemeEntity')->findOneBy(['name' => $themeName]);
                if (empty($themeEntity)) {
                    throw new NotFoundHttpException($this->__('Sorry! No such theme found.'), null, 404);
                }
                if ($data['deletefiles']) {
                    $fs = new Filesystem();
                    $path = realpath($this->get('kernel')->getRootDir() . '/../themes/' . $themeEntity->getDirectory());
                    try {
                        // attempt to delete files
                        $fs->remove($path);
                        $this->addFlash('status', $this->__('Files removed as requested.'));
                    } catch (IOException $e) {
                        $this->addFlash('danger', $this->__('Could not remove files as requested.') . ' (' . $e->getMessage() . ') ' . $this->__('The files must be removed manually.'));
                    }
                }
                // remove theme
                $this->getDoctrine()->getManager()->remove($themeEntity);
                // remove any theme vars
                $vars = $this->get('zikula_extensions_module.api.variable')->getAll($themeName);
                foreach ($vars as $var) {
                    $this->getDoctrine()->getManager()->remove($var);
                }
                $this->getDoctrine()->getManager()->flush();
                // clear all caches
                $this->get('zikula.cache_clearer')->clear('twig');
                $this->get('zikula.cache_clearer')->clear('symfony.config');
                $this->addFlash('status', $data['deletefiles'] ? $this->__('Done! Deleted the theme.') : $this->__('Done! Deactivated the theme.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulathememodule_theme_view');
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
        $themeInfo = $this->getDoctrine()->getRepository('ZikulaThemeModule:ThemeEntity')->findOneBy(['name' => $themeName]);

        return ['themeinfo' => $themeInfo->toArray()];
    }
}
