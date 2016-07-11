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

use Zikula\BlocksModule\Api\BlockApi;
use Zikula\BlocksModule\Api\BlockFilterApi;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\ThemeModule\Engine\Engine;
use Zikula\ExtensionsModule\Api\ExtensionApi;

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
     * @var ExtensionApi
     */
    private $extensionApi;

    /**
     * BlocksExtension constructor.
     * @param BlockApi $blockApi
     * @param BlockFilterApi $blockFilterApi
     * @param Engine $themeEngine
     * @param ExtensionApi $extensionApi
     */
    public function __construct(BlockApi $blockApi, BlockFilterApi $blockFilterApi, Engine $themeEngine, ExtensionApi $extensionApi)
    {
        $this->blockApi = $blockApi;
        $this->blockFilter = $blockFilterApi;
        $this->themeEngine = $themeEngine;
        $this->extensionApi = $extensionApi;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'zikulablocksmodule';
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
     * @todo at Core-2.0 remove all $legacy use and other checks.
     * @param string $positionName
     * @param bool|true $implode
     * @return array|string
     */
    public function showBlockPosition($positionName, $implode = true)
    {
        if (!\ModUtil::available('ZikulaBlocksModule')) { // @TODO refactor to Core-2.0
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
        if (!\ModUtil::available('ZikulaBlocksModule')) { // @TODO refactor to Core-2.0
            return "Blocks not currently available.";
        }
        // Check if providing module not available, if block is inactive, if block filter prevents display.
        if (!\ModUtil::available($block->getModule()->getName()) // @todo replace ModUtil in Core-2.0
            || (!$block->getActive())
            || (!$this->blockFilter->isDisplayable($block))) {
            return '';
        }

        $blockInstance = $this->blockApi->createInstanceFromBKey($block->getBkey());
        $legacy = false;
        $content = '';
        if ($blockInstance instanceof BlockHandlerInterface) {
            $blockProperties = $block->getContent();
            $blockProperties['bid'] = $block->getBid();
            $blockProperties['title'] = $block->getTitle();
            $blockProperties['position'] = $positionName;
            $content = $blockInstance->display($blockProperties);
        } elseif ($blockInstance instanceof \Zikula_Controller_AbstractBlock) { // @todo remove at Core-2.0
            $legacy = true;
            $args = \BlockUtil::getBlockInfo($block->getBid());
            $args['position'] = $positionName;
            $content = $blockInstance->display($args);
        }
        if (!$legacy) {
            if (null !== $moduleInstance = $this->extensionApi->getModuleInstanceOrNull($block->getModule()->getName())) {
                // @todo can remove check for null at Core-2.0
                // add module stylesheet to page - legacy blocks load stylesheets automatically on ModUtil::load()
                $moduleInstance->addStylesheet();
            }
        }

        return $this->themeEngine->wrapBlockContentInTheme($content, $block->getTitle(), $block->getBlocktype(), $block->getBid(), $positionName, $legacy);
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
