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
 * Validates a field's value, ensuring that is is a string value.
 */
class StringType extends AbstractValidator
{
    /**
     * Validates the specified data, ensuring that the data is a string.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is a string; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_string($data)) {
                $valid = true;
            }
        }

        return $valid;
    }
}
