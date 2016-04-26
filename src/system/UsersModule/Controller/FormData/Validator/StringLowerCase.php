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
 * Validates a field to ensure that it contains a string whose letters are all lowercase (or are non-letter characters).
 */
class StringLowerCase extends AbstractValidator
{
    /**
     * Validates the specified data, looking for letters that are not lower case.
     *
     * @param mixed $data The data to be validated.
     *
     * @return boolean True if the data is a string value whose letter characters (if any) are all lowercase letters; otherwise false.
     */
    public function isValid($data)
    {
        $valid = false;

        if (isset($data)) {
            if (is_string($data)) {
                $lowercaseData = mb_strtolower($data);
                $valid = ($data == $lowercaseData);
            }
        }

        return $valid;
    }
}
