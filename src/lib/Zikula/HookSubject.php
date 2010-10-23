<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage HookManager
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_HookSubject class.
 */
class Zikula_HookSubject
{
    /**
     * Type of hook.
     *
     * @var string
     */
    protected $type;

    /**
     * Who the hook belongs to.
     *
     * @var string
     */
    protected $who;

    /**
     * The subject of the hook event.
     *
     * @var object|null
     */
    protected $subject;

    /**
     * Constructor.
     *
     * @param string      $type    The type of the hook.
     * @param string      $who     Who the hook belongs too.
     * @param object|null $subject The subject of the hook.
     */
    public function __construct($type, $who, $subject = null)
    {
        $this->type = $type;
        $this->who = $who;
        $this->subject = $subject;
    }

    /**
     * Get hook type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get who the hook belongs to.
     *
     * @return string
     */
    public function getWho()
    {
        return $this->who;
    }

    /**
     * Get the subject of the hook.
     *
     * @return object|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

}