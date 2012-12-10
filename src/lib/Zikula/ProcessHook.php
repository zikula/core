<?php
/**
 * Copyright 2009 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_EventManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Event encapsulation class.
 *
 * Encapsulates events thus decoupling the observer from the subject they encapsulate.
 *
 */
class Zikula_ProcessHook extends Zikula_AbstractHook
{
    /**
     * Url container.
     *
     * @var Zikula_ModUrl
     */
    protected $url;

    public function __construct($id, $url=null)
    {
        $funcArgs = func_get_args();
        if (count($funcArgs) == 3) {
            $this->setName($funcArgs[0]);
            $id = isset($funcArgs[1]) ? $funcArgs[1]: null;
            $url = isset($funcArgs[2]) ? $funcArgs[2]: null;
        } else if (count($funcArgs) == 2 && null === $url) {
            // $name + $id
            $this->setName($funcArgs[0]);
            $id = $funcArgs[0];
            $url = $funcArgs[1];
        } else if (count($funcArgs) == 1) {
            $id = $funcArgs[0];
        }

        if (!$id) {
            throw new InvalidArgumentException('$id cannot be empty or null');
        }

        if (null !== $url && !$url instanceof Zikula_ModUrl) {
            throw new InvalidArgumentException('$url argument expected to be an instance of Zikula_ModUrl, but something else was given');
        }

        $this->id = $id;
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }
}
