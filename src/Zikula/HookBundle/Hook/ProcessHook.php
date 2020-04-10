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

namespace Zikula\Bundle\HookBundle\Hook;

use Zikula\Bundle\CoreBundle\UrlInterface;

/**
 * Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 */
class ProcessHook extends Hook
{
    /**
     * Url container.
     *
     * @var UrlInterface
     */
    protected $url;

    public function __construct(/*int type hint currently disabled as UsersModule assigns a UserEntity for LoginUiHooksSubscriber::LOGIN_PROCESS */$id, UrlInterface $url = null)
    {
        $this->id = $id;
        $this->url = $url;
    }

    public function getUrl(): UrlInterface
    {
        return $this->url;
    }
}
