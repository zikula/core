<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Hook interface
 *
 * @deprecated
 */
interface Zikula_HookInterface extends Zikula_EventInterface
{
    public function getId();

    public function getCaller();

    public function setCaller($caller);

    public function getAreaId();

    public function setAreaId($areaId);
}
