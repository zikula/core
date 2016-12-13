<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\BlocksModule\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Api\BlockFilterApi;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\ThemeModule\Engine\Engine;

class BlocksExtension extends \Twig_Extension
{
    /**
     * @var BlockApi
     */
    private $blockApi;

    /**
     * @var BlockFilterApi
     */
    private $blockFilter;

    /**
     * @var Engine
     */
    private $themeEngine;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var \Twig_Loader_Filesystem
     */
    private $loader;

    /**
     * BlocksExtension constructor.
     * @param BlockApi $blockApi
     * @param BlockFilterApi $blockFilterApi
     * @param Engine $themeEngine
     * @param KernelInterface $kernel
     * @param \Twig_Loader_Filesystem $loader
     */
    public function __construct(
        BlockApi $blockApi,
        BlockFilterApi $blockFilterApi,
        Engine $themeEngine,
        KernelInterface $kernel,
        \Twig_Loader_Filesystem $loader
    ) {
        $this->blockApi = $blockApi;
        $this->blockFilter = $blockFilterApi;
        $this->themeEngine = $themeEngine;
        $this->kernel = $kernel;
        $this->loader = $loader;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('showblockposition', [$this, 'showBlockPosition'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('showblock', [$this, 'showBlock'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('positionavailable', [$this, 'positionAvailable']),
        ];
    }

    public function getFilters()
    {
        return [];
    }

    /**
     * Show all the blocks in a position by name.
     * @param string $positionName
     * @param bool|true $implode
     * @return array|string
     */
    public function showBlockPosition($positionName, $implode = true)
    {
        $instance = $this->kernel->getModule('ZikulaBlocksModule');
        if (!isset($instance)) {
            return "Blocks not currently available.";
        }
        $blocks = $this->blockApi->getBlocksByPosition($positionName);
        foreach ($blocks as $key => $block) {
            $blocks[$key] = $this->showBlock($block, $positionName);
        }

        return $implode ? implode("\n", $blocks) : $blocks;
    }

    /**
     * Display one block.
     *
     * @param BlockEntity $block
     * @param string $positionName @deprecated argument. remove at Core-2.0
     * @return string
     */
    public function showBlock(BlockEntity $block, $positionName = '')
    {
        $blocksModuleInstance = $this->kernel->getModule('ZikulaBlocksModule');
        if (!isset($blocksModuleInstance)) {
            return "Blocks not currently available.";
        }
        // Check if providing module not available, if block is inactive, if block filter prevents display.
        $moduleInstance = $this->kernel->getModule($block->getModule()->getName());
        if ((!$block->getActive()) || (!$this->blockFilter->isDisplayable($block))) {
            return '';
        }

        // add theme path to twig loader for theme overrides using namespace notation (e.g. @BundleName/foo)
        // this duplicates functionality from \Zikula\ThemeModule\EventListener\TemplatePathOverrideListener::setUpThemePathOverrides
        // but because blockHandlers don't call (and are not considered) a controller, that listener doesn't get called.
        $theme = $this->themeEngine->getTheme();
        $bundleName = $block->getModule()->getName();
        if ($theme) {
            $overridePath = $theme->getPath() . '/Resources/' . $bundleName . '/views';
            if (is_readable($overridePath)) {
                $paths = $this->loader->getPaths($bundleName);
                // inject themeOverridePath before the original path in the array
                array_splice($paths, count($paths) - 1, 0, [$overridePath]);
                $this->loader->setPaths($paths, $bundleName);
            }
        }

        $blockInstance = $this->blockApi->createInstanceFromBKey($block->getBkey());
        $legacy = false;
        $content = '';
        if ($blockInstance instanceof BlockHandlerInterface) {
            $blockProperties = $block->getProperties();
            $blockProperties['bid'] = $block->getBid();
            $blockProperties['title'] = $block->getTitle();
            $blockProperties['position'] = $positionName;
            $content = $blockInstance->display($blockProperties);
        }
        if (!$legacy) {
            if (isset($moduleInstance)) {
                // add module stylesheet to page
                $moduleInstance->addStylesheet();
            }
        }

        return !empty($content) ? $this->themeEngine->wrapBlockContentInTheme($content, $block->getTitle(), $block->getBlocktype(), $block->getBid(), $positionName, $legacy) : $content;
    }

    /**
     * @param $name
     * @return bool
     */
    public function positionAvailable($name)
    {
        return $this->themeEngine->positionIsAvailableInTheme($name);
    }
}
