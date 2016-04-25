<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Doctrine filter for the Attributable doctrine template.
 *
 * @deprecated since 1.4.0
 */
class Zikula_Doctrine_Template_Filter_Attributable extends Doctrine_Record_Filter
{
    /**
     * Filters write access to the unknown property __ATTRIBUTES__.
     *
     * @param Doctrine_Record $record Record.
     * @param string          $name   Name of the unkown property.
     * @param mixed           $value  Value of to set.
     *
     * @return void
     * @throws Doctrine_Record_UnknownPropertyException If $name is not __ATTRIBUTES__.
     */
    public function filterSet(Doctrine_Record $record, $name, $value)
    {
        if ($name == '__ATTRIBUTES__') {
            $record->mapValue('__ATTRIBUTES__', new ArrayObject($value, ArrayObject::ARRAY_AS_PROPS));
            if ($record->state() == Doctrine_Record::STATE_CLEAN) {
                $record->state(Doctrine_Record::STATE_DIRTY);
            }
        } else {
            throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
        }
    }

    /**
     * Filters read access to the unknown property __ATTRIBUTES__.
     *
     * @param Doctrine_Record $record Record.
     * @param string          $name   Name of the unkown property.
     *
     * @return mixed
     * @throws Doctrine_Record_UnknownPropertyException If $name is not __ATTRIBUTES__.
     */
    public function filterGet(Doctrine_Record $record, $name)
    {
        if ($name == '__ATTRIBUTES__') {
            $value = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);

            $record->mapValue('__ATTRIBUTES__', $value);
            if ($record->state() == Doctrine_Record::STATE_CLEAN) {
                $record->state(Doctrine_Record::STATE_DIRTY);
            }

            return $value;
        } else {
            throw new Doctrine_Record_UnknownPropertyException(sprintf('Unknown record property / related component "%s" on "%s"', $name, get_class($record)));
        }
    }
}
