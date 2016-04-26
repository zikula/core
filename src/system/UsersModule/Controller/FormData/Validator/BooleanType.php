<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Controller\FormData\Validator;

/**
 * Ensures that the field contains data compatible with a boolean type.
 */
class BooleanType extends AbstractValidator
{
    /**
     * Validate the data for compatibility with the boolean type.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is compatible with the boolean type; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_bool($data)) {
                $valid = true;
            } elseif (is_int($data)) {
                $valid = (($data === 0) || ($data === 1));
            } elseif (is_numeric($data)) {
                // To support radio buttons
                $valid = (($data === '0') || ($data === '1'));
            } elseif (is_string($data)) {
                // To support unchecked check boxes
                $valid = ($data == '');
            }
        }

        return $valid;
    }
}
