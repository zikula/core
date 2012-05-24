<?php
/**
 * Copyright 2010 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Zikula
 * @subpackage Zikula_Doctrine
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Doctrine filter for the Attributable doctrine template.
 */
class Zikula_Doctrine_Template_Filter_MetaData extends Doctrine_Record_Filter
{
    /**
     * Filters write access to the unknown property __META__.
     *
     * @param Doctrine_Record $record Record.
     * @param string          $name   Name of the unkown property.
     * @param mixed           $value  Value of to set.
     *
     * @return void
     * @throws Doctrine_Record_UnknownPropertyException If $name is not __META__.
     */
    public function filterSet(Doctrine_Record $record, $name, $value)
    {
        if ($name == '__META__') {
            $record->mapValue('__META__', new ArrayObject($value, ArrayObject::ARRAY_AS_PROPS));
            if ($record->state() == Doctrine_Record::STATE_CLEAN) {
                $record->state(Doctrine_Record::STATE_DIRTY);
            }
        } else {
            throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
        }
    }

    /**
     * Filters read access to the unknown property __META__.
     *
     * @param Doctrine_Record $record Record.
     * @param String          $name   Name of the unkown property.
     *
     * @return mixed
     * @throws Doctrine_Record_UnknownPropertyException If $name is not __META__.
     */
    public function filterGet(Doctrine_Record $record, $name)
    {
        if ($name == '__META__') {
            $value = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);

            $record->mapValue('__META__', $value);
            if ($record->state() == Doctrine_Record::STATE_CLEAN) {
                $record->state(Doctrine_Record::STATE_DIRTY);
            }

            return $value;
        } else {
            throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
        }
    }
}

