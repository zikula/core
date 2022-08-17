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

namespace Zikula\ThemeModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ThemeModule\Form\Type\ThemeType;

/**
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaThemeModule/Config/config.html.twig")
     */
    public function config(
        Request $request,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        ZikulaHttpKernelInterface $kernel
    ) {
        $themes = $kernel->getThemes();
        foreach ($themes as $theme) {
            $metaData = $theme->getMetaData();
            foreach ($metaData as $k => $themeInfo) {
                if (!isset($themeInfo['capabilities']['admin']['theme']) || (false === $themeInfo['capabilities']['admin']['theme'])) {
                    unset($themes[$k]);
                }
            }
        }
        $dataValues = [
            'defaulttheme' => $variableApi->get(VariableApi::CONFIG, 'Default_Theme', 'ZikulaDefaultTheme'),
            'admintheme' => $variableApi->get('ZikulaAdminModule', 'admintheme')
        ];
        $form = $this->createForm(
            ThemeType::class,
            $dataValues,
            [
                'themes' => $themes
            ]
        );
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();

                // save module vars
                $variableApi->set(VariableApi::CONFIG, 'Default_Theme', $formData['defaulttheme']);
                $variableApi->set('ZikulaAdminModule', 'admintheme', $formData['admintheme']);
                $cacheClearer->clear('twig');
                $cacheClearer->clear('symfony.config');

                $this->addFlash('status', 'Done! Configuration updated.');
            }

            return $this->redirectToRoute('zikulathememodule_config_config');
        }

        return [
            'form' => $form->createView(),
            'themes' => $themes
        ];
    }
}
