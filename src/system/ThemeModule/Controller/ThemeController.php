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

namespace Zikula\ThemeModule\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\Core\Controller\AbstractController;
use Zikula\Core\Event\GenericEvent;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\ExtensionEvents;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ThemeModule\Engine\Engine;
use Zikula\ThemeModule\Entity\Repository\ThemeEntityRepository;
use Zikula\ThemeModule\Helper\BundleSyncHelper;

/**
 * Class ThemeController
 * @Route("/admin")
 */
class ThemeController extends AbstractController
{
    /**
     * @Route("/view", methods = {"GET"})
     * @Theme("admin")
     * @Template("@ZikulaThemeModule/Theme/view.html.twig")
     *
     * View all themes.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions to the module
     * @throws Exception
     */
    public function viewAction(
        EventDispatcherInterface $eventDispatcher,
        BundleSyncHelper $syncHelper,
        ThemeEntityRepository $themeRepository,
        VariableApiInterface $variableApi
    ): array {
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $vetoEvent = new GenericEvent();
        $eventDispatcher->dispatch($vetoEvent, ExtensionEvents::REGENERATE_VETO);
        if (!$vetoEvent->isPropagationStopped()) {
            $syncHelper->regenerate();
        }

        $themes = $themeRepository->get(ThemeEntityRepository::FILTER_ALL, ThemeEntityRepository::STATE_ALL);

        return [
            'themes' => $themes,
            'currenttheme' => $variableApi->getSystemVar('Default_Theme')
        ];
    }

    /**
     * @Route("/preview/{themeName}")
     */
    public function previewAction(Engine $engine, string $themeName): Response
    {
        $engine->setActiveTheme($themeName);
        $this->addFlash('warning', $this->trans('Please note that blocks may appear out of place or even missing in a theme preview because position names are not consistent from theme to theme.'));

        return $this->forward('Zikula\Bundle\CoreBundle\Controller\MainController::homeAction');
    }

    /**
     * @Route("/activate/{themeName}")
     */
    public function activateAction(
        ThemeEntityRepository $themeRepository,
        CacheClearer $cacheClearer,
        string $themeName
    ): RedirectResponse {
        $theme = $themeRepository->findOneBy(['name' => $themeName]);
        if (null !== $theme) {
            $theme->setState(ThemeEntityRepository::STATE_ACTIVE);
            $this->getDoctrine()->getManager()->flush();
        }
        $cacheClearer->clear('symfony.config');

        return $this->redirectToRoute('zikulathememodule_theme_view');
    }

    /**
     * @Route("/makedefault/{themeName}")
     * @Theme("admin")
     * @Template("@ZikulaThemeModule/Theme/setAsDefault.html.twig")
     *
     * Set theme as default for site.
     *
     * @return array|RedirectResponse
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin permissions over the module
     */
    public function setAsDefaultAction(
        Request $request,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        string $themeName
    ) {
        if (!$this->hasPermission('ZikulaThemeModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder(['themeName' => $themeName])
            ->add('themeName', HiddenType::class)
            ->add('accept', SubmitType::class, [
                'label' => 'Accept',
                'icon' => 'fa-check',
                'attr' => [
                    'class' => 'btn btn-success'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('accept')->isClicked()) {
                $data = $form->getData();
                // Set the default theme
                $variableApi->set(VariableApi::CONFIG, 'Default_Theme', $data['themeName']);
                $cacheClearer->clear('twig');
                $cacheClearer->clear('symfony.config');
                $this->addFlash('status', $this->trans('Done! Changed default theme.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulathememodule_theme_view');
        }

        return [
            'themeName' => $themeName,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/delete/{themeName}")
     * @Theme("admin")
     * @Template("@ZikulaThemeModule/Theme/delete.html.twig")
     *
     * Delete a theme.
     *
     * @return array|RedirectResponse
     *
     * @throws NotFoundHttpException Thrown if themename isn't provided or doesn't exist
     * @throws AccessDeniedException Thrown if the user doesn't have delete permissions over the module
     */
    public function deleteAction(
        Request $request,
        ThemeEntityRepository $themeRepository,
        ZikulaHttpKernelInterface $kernel,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        string $themeName
    ) {
        if (!$this->hasPermission('ZikulaThemeModule::', $themeName . '::', ACCESS_DELETE)) {
            throw new AccessDeniedException();
        }

        $form = $this->createFormBuilder(['themeName' => $themeName, 'deletefiles' => false])
            ->add('themeName', HiddenType::class)
            ->add('deletefiles', CheckboxType::class, [
                'label' => 'Also delete theme files, if possible',
                'label_attr' => ['class' => 'switch-custom'],
                'required' => false,
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Delete',
                'icon' => 'fa-trash-alt',
                'attr' => [
                    'class' => 'btn btn-danger'
                ]
            ])
            ->add('cancel', SubmitType::class, [
                'label' => 'Cancel',
                'icon' => 'fa-times',
                'attr' => [
                    'class' => 'btn btn-default'
                ]
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $data = $form->getData();
                $themeEntity = $themeRepository->findOneBy(['name' => $themeName]);
                if (empty($themeEntity)) {
                    throw new NotFoundHttpException($this->trans('Sorry! No such theme found.'), null, 404);
                }
                if ($data['deletefiles']) {
                    $fs = new Filesystem();
                    $path = realpath($kernel->getProjectDir() . '/themes/' . $themeEntity->getDirectory());
                    try {
                        // attempt to delete files
                        $fs->remove($path);
                        $this->addFlash('status', $this->trans('Files removed as requested.'));
                    } catch (IOException $e) {
                        $this->addFlash('danger', $this->trans('Could not remove files as requested.') . ' (' . $e->getMessage() . ') ' . $this->trans('The files must be removed manually.'));
                    }
                }

                // remove any theme vars
                $variableApi->delAll($themeName);

                // remove theme
                $this->getDoctrine()->getManager()->remove($themeEntity);

                // clear all caches
                $cacheClearer->clear('twig');
                $cacheClearer->clear('symfony.config');
                $this->addFlash('status', $data['deletefiles'] ? $this->trans('Done! Deleted the theme.') : $this->trans('Done! Deactivated the theme.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->trans('Operation cancelled.'));
            }

            return $this->redirectToRoute('zikulathememodule_theme_view');
        }

        return [
            'themeName' => $themeName,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/credits/{themeName}", methods = {"GET"})
     * @Theme("admin")
     * @Template("@ZikulaThemeModule/Theme/credits.html.twig")
     *
     * Display the theme credits.
     *
     * @throws AccessDeniedException Thrown if the user doesn't have edit permissions over the theme
     */
    public function creditsAction(ThemeEntityRepository $themeRepository, string $themeName): array
    {
        if (!$this->hasPermission('ZikulaThemeModule::', "${themeName}::credits", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $themeInfo = $themeRepository->findOneBy(['name' => $themeName]);

        return [
            'themeinfo' => $themeInfo->toArray() ?? []
        ];
    }
}
