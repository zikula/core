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

namespace Zikula\ExtensionsModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\CacheClearer;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\ExtensionsModule\Form\Type\ConfigType;
use Zikula\ExtensionsModule\Helper\BundleSyncHelper;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\ThemeModule\Engine\Annotation\Theme;

/**
 * Class ConfigController
 *
 * @Route("/config")
 * @PermissionCheck("admin")
 */
class ConfigController extends AbstractController
{
    /**
     * @Route("")
     * @Theme("admin")
     * @Template("@ZikulaExtensionsModule/Config/config.html.twig")
     *
     * @return array|Response
     */
    public function configAction(
        Request $request,
        BundleSyncHelper $bundleSyncHelper,
        CacheClearer $cacheClearer
    ) {
        $form = $this->createForm(ConfigType::class, $this->getVars());
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $this->setVars($form->getData());
                if (true === $form->get('hardreset')->getData()) {
                    $extensionsInFileSystem = $bundleSyncHelper->scanForBundles();
                    $bundleSyncHelper->syncExtensions($extensionsInFileSystem, true);
                    $cacheClearer->clear('symfony.routing');
                }
                $this->addFlash('status', 'Done! Configuration updated.');
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }

            return $this->redirectToRoute('zikulaextensionsmodule_extension_list');
        }

        return [
            'form' => $form->createView()
        ];
    }
}
