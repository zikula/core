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

namespace Zikula\SearchModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\PermissionsModule\Annotation\PermissionCheck;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Form\Type\ConfigType;
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
     * @Route("/config")
     * @Theme("admin")
     * @Template("@ZikulaSearchModule/Config/config.html.twig")
     */
    public function configAction(
        Request $request,
        ZikulaHttpKernelInterface $kernel,
        SearchableModuleCollector $collector
    ): array {
        $modVars = $this->getVars();
        $plugins = [];

        // get Core-2.0 type searchable modules and add to array
        $searchableModules = $collector->getAll();
        foreach (array_keys($searchableModules) as $searchableModuleName) {
            $displayName = $kernel->getModule($searchableModuleName)->getMetaData()->getDisplayName();
            $plugins[$displayName] = $searchableModuleName;
        }

        $disabledPlugins = [];
        // get the disabled state
        foreach ($plugins as $moduleName) {
            $modVarKey = 'disable_' . $moduleName;
            if (!isset($modVars[$modVarKey])) {
                continue;
            }
            if ($modVars[$modVarKey]) {
                $disabledPlugins[] = $moduleName;
            }
            unset($modVars[$modVarKey]);
        }
        $modVars['plugins'] = $disabledPlugins;

        $form = $this->createForm(ConfigType::class, $modVars, [
            'plugins' => $plugins
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $formData = $form->getData();
                foreach ($plugins as $searchPlugin) {
                    // set the disabled flag
                    $disabledFlag = in_array($searchPlugin, $formData['plugins'], true);
                    $this->setVar('disable_' . $searchPlugin, $disabledFlag);
                }
                unset($formData['plugins']);
                $this->setVars($formData);
                $this->addFlash('status', 'Done! Configuration updated.');
            } elseif ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', 'Operation cancelled.');
            }
        }

        return [
            'form' => $form->createView(),
            'plugins' => $plugins
        ];
    }
}
