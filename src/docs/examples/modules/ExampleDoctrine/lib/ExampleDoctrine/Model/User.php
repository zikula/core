<?php
/**
 * Copyright Zikula Foundation 2010 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * This is the model class that define the entity structure and behaviours.
 */
class ExampleDoctrine_Model_User extends Doctrine_Record
{
    /**
     * Set table definition.
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $this->setTableName('exampledoctrine_users');
        $this->hasColumn('username', 'string', 255);
        $this->hasColumn('password', 'string', 255);
    }

    /**
     * Record setup.
     *
     * @return void
     */
    public function setUp()
    {
        // doctrine templates provided by zikula
        $this->actAs('Zikula_Doctrine_Template_StandardFields');
        $this->actAs('Zikula_Doctrine_Template_Categorisable');
        $this->actAs('Zikula_Doctrine_Template_Attributable');
        $this->actAs('Zikula_Doctrine_Template_MetaData');
        $this->actAs('Zikula_Doctrine_Template_Logging');
    }
}
