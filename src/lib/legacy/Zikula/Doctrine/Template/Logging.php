<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * This behavior manages logging changes of a record.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Template_Logging extends Doctrine_Template
{
    /**
     * Adds as Zikula_Doctrine_Template_Listener_MetaData listener.
     *
     * @return void
     */
    public function setUp()
    {
        $this->addListener(new Zikula_Doctrine_Template_Listener_Logging());
    }
}
