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

    public function display(array $properties = []): string
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
            if (!in_array($moduleName, $properties['active'], true)) {
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

    public function getFormClassName(): string
    {
        return SearchBlockType::class;
    }

    public function getFormOptions(): array
    {
        $searchModules = [];
        $searchableModules = $this->searchableCollector->getAll();
        foreach (array_keys($searchableModules) as $moduleName) {
            $displayName = $this->kernel->getModule($moduleName)->getMetaData()->getDisplayName();
            $searchModules[$displayName] = $moduleName;
        }
        // remove disabled
        foreach ($searchModules as $displayName => $moduleName) {
            if ((bool)$this->getVar('disable_' . $moduleName)) {
                unset($searchModules[$displayName]);
            }
        }

        return [
            'activeModules' => $searchModules
        ];
    }

    public function getFormTemplate(): string
    {
        return '@ZikulaSearchModule/Block/search_modify.html.twig';
    }

    /**
     * @required
     */
    public function setKernel(ZikulaHttpKernelInterface $kernel): void
    {
        $this->kernel = $kernel;
    }

    /**
     * @required
     */
    public function setRouter(RouterInterface $router): void
    {
        $this->router = $router;
    }

    /**
     * @required
     */
    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @required
     */
    public function setSearchableModuleCollector(SearchableModuleCollector $searchableCollector): void
    {
        $this->searchableCollector = $searchableCollector;
    }
}
