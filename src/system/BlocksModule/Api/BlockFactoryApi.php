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

namespace Zikula\BlocksModule\Api;

use Psr\Container\ContainerInterface;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\BlockHandlerInterface;
use Zikula\Common\Translator\TranslatorInterface;

/**
 * Class BlockFactoryApi
 *
 * This class provides an API for the instantiation of block classes.
 */
class BlockFactoryApi implements BlockFactoryApiInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BlockFactoryApi constructor.
     *
     * @param ContainerInterface $container
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ContainerInterface $container,
        TranslatorInterface $translator
    ) {
        $this->container = $container;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getInstance($blockClassName)
    {
        if (!class_exists($blockClassName)) {
            throw new \RuntimeException($this->translator->__f('Block class %c does not exist.', ['%c' => $blockClassName]));
        }
        if (!is_subclass_of($blockClassName, BlockHandlerInterface::class)) {
            throw new \RuntimeException(sprintf('Block class %s must implement Zikula\BlocksModule\BlockHandlerInterface.', $blockClassName));
        }

        if ('\\' === mb_substr($blockClassName, 0, 1)) {
            $blockClassName = mb_substr($blockClassName, 1);
        }

        if (!$this->container->has($blockClassName)) {
            throw new \RuntimeException($this->translator->__f('Block class %c not found in container.', ['%c' => $blockClassName]));
        }

        return $this->container->get($blockClassName);
    }
}
