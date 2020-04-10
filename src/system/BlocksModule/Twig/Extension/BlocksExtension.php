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

namespace Zikula\BlocksModule\Twig\Extension;

use RuntimeException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigTest;
use Zikula\BlocksModule\Api\ApiInterface\BlockApiInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockFilterApiInterface;
use Zikula\BlocksModule\Collectible\PendingContentCollectible;
use Zikula\BlocksModule\Entity\BlockEntity;
use Zikula\Bundle\CoreBundle\HttpKernel\ZikulaHttpKernelInterface;
use Zikula\ThemeModule\Engine\Engine;

class BlocksExtension extends AbstractExtension
{
    /**
     * @var BlockApiInterface
     */
    private $blockApi;

    /**
     * @var BlockFilterApiInterface
     */
    private $blockFilter;

    /**
     * @var Engine
     */
    private $themeEngine;

    /**
     * @var ZikulaHttpKernelInterface
     */
    private $kernel;

    public function __construct(
        BlockApiInterface $blockApi,
        BlockFilterApiInterface $blockFilterApi,
        Engine $themeEngine,
        ZikulaHttpKernelInterface $kernel
    ) {
        $this->blockApi = $blockApi;
        $this->blockFilter = $blockFilterApi;
        $this->themeEngine = $themeEngine;
        $this->kernel = $kernel;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('showblockposition', [$this, 'showBlockPosition'], ['is_safe' => ['html']]),
            new TwigFunction('showblock', [$this, 'showBlock'], ['is_safe' => ['html']]),
            new TwigFunction('positionavailable', [$this, 'isPositionAvailable']),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('pendingContentCollectible', function ($obj) { return $obj instanceof PendingContentCollectible; }),
        ];
    }

    /**
     * Show all the blocks in a position by name.
     *
     * @return array|string
     */
    public function showBlockPosition(string $positionName, bool $implode = true)
    {
        $instance = $this->kernel->getModule('ZikulaBlocksModule');
        if (!isset($instance)) {
            return 'Blocks not currently available.';
        }
        $blocks = $this->blockApi->getBlocksByPosition($positionName);
        foreach ($blocks as $key => $block) {
            $blocks[$key] = $this->showBlock($block, $positionName);
        }

        return $implode ? implode("\n", $blocks) : $blocks;
    }

    /**
     * Display one block.
     */
    public function showBlock(BlockEntity $block, string $positionName = ''): string
    {
        $blocksModuleInstance = $this->kernel->getModule('ZikulaBlocksModule');
        if (!isset($blocksModuleInstance)) {
            return 'Blocks not currently available.';
        }
        // Check if providing module not available, if block is inactive, if block filter prevents display.
        $bundleName = $block->getModule()->getName();
        $moduleInstance = $this->kernel->getModule($bundleName);
        if (!isset($moduleInstance) || !$block->getActive() || !$this->blockFilter->isDisplayable($block)) {
            return '';
        }

        try {
            $blockInstance = $this->blockApi->createInstanceFromBKey($block->getBkey());
        } catch (RuntimeException $exception) {
            //return 'Error during block creation: ' . $exception->getMessage();
            return '';
        }
        $blockProperties = $block->getProperties();
        $blockProperties['bid'] = $block->getBid();
        $blockProperties['title'] = $block->getTitle();
        $blockProperties['position'] = $positionName;
        $content = $blockInstance->display($blockProperties);
        if (isset($moduleInstance)) {
            // add module stylesheet to page
            $moduleInstance->addStylesheet();
        }

        return $this->themeEngine->wrapBlockContentInTheme($content, $block->getTitle(), $block->getBlocktype(), $block->getBid(), $positionName);
    }

    public function isPositionAvailable(string $name): bool
    {
        return $this->themeEngine->positionIsAvailableInTheme($name);
    }
}
