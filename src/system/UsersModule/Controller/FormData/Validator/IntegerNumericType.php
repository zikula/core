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
 * Validates that a field's data is compatible with an integer numeric type.
 */
class IntegerNumericType extends AbstractValidator
{
    /**
     * Validates that the specified data is an integer or an integer numeric string.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is an integer or is an integer numeric string; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_int($data)) {
                $valid = true;
            } elseif (is_numeric($data)) {
                $valid = ((string)((int)$data) === $data);
            }
        }

        return $valid;
    }
}
