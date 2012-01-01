<?php

/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Core
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\SessionStorage;

/**
 *
 */
class AbstractSessionStorage extends \Symfony\Component\HttpFoundation\SessionStorage\AbstractSessionStorage
{
    /**
     * {@inheritdoc}
     */
    public function getFlashes()
    {
        return $this->flashBag;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributeBag;
    }
}
