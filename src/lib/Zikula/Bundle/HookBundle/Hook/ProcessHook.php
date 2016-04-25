<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bundle\HookBundle\Hook;

use Zikula\Core\UrlInterface;

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

    public function __construct($id, UrlInterface $url = null)
    {
        $this->id = $id;
        $this->url = $url;
    }

    /**
     * Gets the ModUrl
     *
     * @return UrlInterface
     */
    public function getUrl()
    {
        return $this->url;
    }
}
