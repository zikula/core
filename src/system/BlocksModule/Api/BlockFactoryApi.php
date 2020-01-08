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
use RuntimeException;
use Zikula\BlocksModule\Api\ApiInterface\BlockFactoryApiInterface;
use Zikula\BlocksModule\BlockHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    public function __construct(
        ContainerInterface $container,
        TranslatorInterface $translator
    ) {
        $this->container = $container;
        $this->translator = $translator;
    }

    public function getInstance(string $blockClassName): BlockHandlerInterface
    {
        if (!class_exists($blockClassName)) {
            throw new RuntimeException($this->translator->trans('Block class %c does not exist.', ['%c' => $blockClassName]));
        }
        if (!is_subclass_of($blockClassName, BlockHandlerInterface::class)) {
            throw new RuntimeException(sprintf('Block class %s must implement Zikula\BlocksModule\BlockHandlerInterface.', $blockClassName));
        }

        if (0 === mb_strpos($blockClassName, '\\')) {
            $blockClassName = mb_substr($blockClassName, 1);
        }

        if (!$this->container->has($blockClassName)) {
            throw new RuntimeException($this->translator->trans('Block class %c not found in container.', ['%c' => $blockClassName]));
        }

        return $this->container->get($blockClassName);
    }
}
