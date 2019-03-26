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

namespace Zikula\SearchModule\Block;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Zikula\BlocksModule\AbstractBlockHandler;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\SearchModule\Block\Form\Type\SearchBlockType;
use Zikula\SearchModule\Collector\SearchableModuleCollector;
use Zikula\SearchModule\Form\Type\AmendableModuleSearchType;
use Zikula\SearchModule\Form\Type\SearchType;

/**
 * Block to display a search form
 */
class SearchBlock extends AbstractBlockHandler
{
    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var SearchableModuleCollector
     */
    private $searchableCollector;

    /**
     * display block
     *
     * @param array $properties
     * @return string the rendered bock
     */
    public function display(array $properties)
    {
        $title = !empty($properties['title']) ? $properties['title'] : '';
        if (!$this->hasPermission('Searchblock::', "${title}::", ACCESS_READ)) {
            return '';
        }
        // set defaults
        $properties['displaySearchBtn'] = $properties['displaySearchBtn'] ?? false;
        $properties['active'] = $properties['active'] ?? [];

        // get Core-2.0 searchable modules
        $searchableModules = $this->searchableCollector->getAll();
        $moduleFormBuilder = $this->formFactory
            ->createNamedBuilder('modules', FormType::class, [], [
                'auto_initialize' => false,
                'required' => false
            ]);
        foreach ($searchableModules as $moduleName => $searchableInstance) {
            if (!in_array($moduleName, $properties['active'])) {
                continue;
            }
            if (!$this->hasPermission('ZikulaSearchModule::Item', $moduleName . '::', ACCESS_READ)) {
                continue;
            }
            $moduleFormBuilder->add($moduleName, AmendableModuleSearchType::class, [
                'label' => $this->kernel->getModule($moduleName)->getMetaData()->getDisplayName(),
                'active' => true
            ]);
            $searchableInstance->amendForm($moduleFormBuilder->get($moduleName));
        }
        $form = $this->formFactory->create(SearchType::class, [], [
            'action' => $this->router->generate('zikulasearchmodule_search_execute')
        ]);
        $form->add($moduleFormBuilder->getForm());

        $templateParameters = [
            'form' => $form->createView(),
            'properties' => $properties
        ];

        return $this->renderView('@ZikulaSearchModule/Block/search.html.twig', $templateParameters);
    }

    /**
     * Returns the fully qualified class name of the block's form class.
     *
     * @return string Template path
     */
    public function getFormClassName()
    {
        return SearchBlockType::class;
    }

    /**
     * Returns any array of form options.
     *
     * @return array Options array
     */
    public function getFormOptions()
    {
        $searchModules = [];
        $searchableModules = $this->searchableCollector->getAll();
        foreach (array_keys($searchableModules) as $moduleName) {
            $displayName = $this->kernel->getModule($moduleName)->getMetaData()->getDisplayName();
            $searchModules[$displayName] = $moduleName;
        }
        // remove disabled
        foreach ($searchModules as $displayName => $moduleName) {
            if ((bool)$this->getVar('disable_' . $moduleName, false)) {
                unset($searchModules[$displayName]);
            }
        }

        // get all the search plugins
        return [
            'activeModules' => $searchModules
        ];
    }

    /**
     * Returns the template used for rendering the editing form.
     *
     * @return string Template path
     */
    public function getFormTemplate()
    {
        return '@ZikulaSearchModule/Block/search_modify.html.twig';
    }

    /**
     * @required
     * @param ZikulaHttpKernelInterface $kernel
     */
    public function setKernel(ZikulaHttpKernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @required
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @required
     * @param FormFactoryInterface $formFactory
     */
    public function setFormFactory(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @required
     * @param SearchableModuleCollector $searchableCollector
     */
    public function setSearchableModuleCollector(SearchableModuleCollector $searchableCollector)
    {
        $this->searchableCollector = $searchableCollector;
    }
}
