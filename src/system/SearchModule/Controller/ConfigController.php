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

namespace Zikula\SearchModule\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Core\Controller\AbstractController;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Form\Type\ConfigType;
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
     * @Template("@ZikulaSearchModule/Config/config.html.twig")
     *
     * @throws AccessDeniedException Thrown if the user doesn't have admin access to the module
     */
    public function configAction(Request $request, SearchableModuleCollector $collector): array
    {
        // Security check
        if (!$this->hasPermission('ZikulaSearchModule::', '::', ACCESS_ADMIN)) {
            throw new AccessDeniedException();
        }

        $modVars = $this->getVars();
        $plugins = [];

        // get Core-2.0 type searchable modules and add to array
        $searchableModules = $collector->getAll();
        foreach (array_keys($searchableModules) as $searchableModuleName) {
            $displayName = $this->get('kernel')->getModule($searchableModuleName)->getMetaData()->getDisplayName();
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
                $this->addFlash('status', $this->__('Done! Module configuration updated.'));
            }
            if ($form->get('cancel')->isClicked()) {
                $this->addFlash('status', $this->__('Operation cancelled.'));
            }
        }

        return [
            'form' => $form->createView(),
            'plugins' => $plugins
        ];
    }
}
