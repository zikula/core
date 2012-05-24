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
 * Common helper functions and constants.
 */
class Groups_Helper_Common extends Zikula_AbstractHelper
{
    /**
     * Constant value for core type groups.
     */
    const GTYPE_CORE = 0;

    /**
     * Constant value for public type groups.
     */
    const GTYPE_PUBLIC = 1;

    /**
     * Constant value for private type groups.
     */
    const GTYPE_PRIVATE = 2;

    /**
     * Constant value for groups in the Closed state (not accepting members).
     */
    const STATE_CLOSED = 0;

    /**
     * Constant value for groups in the Open state (accepting members).
     */
    const STATE_OPEN = 1;

    /**
     * Constructs an instance of this helper class.
     */
    public function __construct()
    {
    }

    /**
     * Return the standard set of labels for Group types.
     *
     * @staticvar array $gtypeLabels The array of standard group type labels.
     *
     * @return array An associative array of group type labels indexed by group type constants.
     */
    public function gtypeLabels()
    {
        static $gtypeLabels;

        if (!isset($gtypeLabels)) {
            $gtypeLabels = array(
                    self::GTYPE_CORE => $this->__('Core'),
                    self::GTYPE_PUBLIC => $this->__('Public'),
                    self::GTYPE_PRIVATE => $this->__('Private')
            );
        }

        return $gtypeLabels;
    }

    /**
     * Return the standard set of labels for Group states.
     *
     * @staticvar array $stateLabels The array of standard state labels.
     *
     * @return array An associative array of state labels indexed by state constants.
     */
    public function stateLabels()
    {
        static $stateLabels;

        if (!isset($stateLabels)) {
            $stateLabels = array(
                    self::STATE_CLOSED => $this->__('Closed'),
                    self::STATE_OPEN => $this->__('Open')
            );
        }

        return $stateLabels;
    }

}
