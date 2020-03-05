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

use Doctrine\Common\Collections\Criteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Composer\MetaData;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\ExtensionsModule\Constant;
use Zikula\ExtensionsModule\Entity\RepositoryInterface\ExtensionRepositoryInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;
use Zikula\ThemeModule\Form\Type\ThemeType;

/**
 * Class ThemeController
 *
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
    public function configAction(
        Request $request,
        VariableApiInterface $variableApi,
        CacheClearer $cacheClearer,
        ExtensionRepositoryInterface $extensionRepository
    ) {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->in("type", [MetaData::TYPE_THEME, MetaData::TYPE_SYSTEM_THEME]))
            ->andWhere(Criteria::expr()->eq('state', Constant::STATE_ACTIVE));
        $themes = $extensionRepository->matching($criteria)->toArray();
        foreach ($themes as $k => $theme) {
            if (!isset($theme['capabilities']['admin']['theme']) || (false === $theme['capabilities']['admin']['theme'])) {
                unset($themes[$k]);
            }
        }
        $dataValues = [
            'Default_Theme' => $variableApi->get(VariableApi::CONFIG, 'defaulttheme'),
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
                $variableApi->set(VariableApi::CONFIG, 'Default_Theme', $formData['Default_Theme']);
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
