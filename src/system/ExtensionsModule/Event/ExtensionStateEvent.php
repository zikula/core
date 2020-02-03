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

namespace Zikula\ExtensionsModule\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Zikula\Bundle\CoreBundle\AbstractBundle;

/**
 * Class ExtensionStateEvent
 */
class ExtensionStateEvent extends Event
{
    /**
     * @var null|AbstractBundle The module instance. Null when Module object is not available
     */
    private $extension;

    /**
     * An array of info for the module. Possibly a result of calling $extensionEntity->toArray().
     *
     * @var null|array
     */
    private $info;

    public function __construct(AbstractBundle $extension = null, array $info = null)
    {
        $this->extension = $extension;
        $this->info = $info;
    }

    public function getExtension(): ?AbstractBundle
    {
        return $this->extension;
    }

    public function getInfo(): ?array
    {
        return $this->info;
    }
}
