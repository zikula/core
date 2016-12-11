<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Api;

use RandomLib\Factory as RandomLibFactory;
use Zikula\Common\Translator\TranslatorInterface;

class PasswordApi
{
    const SALT_DELIM = '$';

    const DEFAULT_HASH_METHOD = 'sha256';

    /**
     * @var array
     *
     * NOTICE: Be extremely cautious about removing entries from this array! If a hash method is no longer
     * to be used, then it probably should be removed from the available options at display time. If an entry is
     * removed from this array but a password has been hashed with that method, then that password will no
     * longer work! Only remove an entry if you are absolutely positive the method is not is use in any record!
     * NOTICE: DO NOT change the numbers assigned to each hash method. The number is the identifier for the
     * method stored in the database. If a number is changed to a different method, then any password that
     * was hashed with the method previously identified by that number will no longer work!
     */
    private $methods = [
        'md5' => 1,
        'sha1' => 5,
        'sha256' => 8
    ];

    /**
     * A list of characters not suited to 'human readable' strings
     * @var array
     */
    private $passwordIncompatibleCharacters = ['0', 'o', 'O', 'l', '1', 'i', 'I', 'j', '!', '|'];

    /**
     * A string of characters to use in random string generation
     * @var string
     */
    private $randomStringCharacters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~@#$%^*()_+-={}|][";

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Get an array of hash algorithms valid for hashing user passwords.
     *
     * Either as an array of
     * algorithm names index by internal integer code, or as an array of internal integer algorithm
     * codes indexed by algorithm name.
     *
     * @param bool $flip If false, then return an array of codes indexed by name (e.g. given $name, then $code = $methods[$name]);
     *                          if true, return an array of names indexed by code (e.g. given $code, then $name = $methods[$code]);
     *                          optional, default = false
     *
     * @return array Depending on the value of $reverse, an array of codes indexed by name or an
     *                  array of names indexed by code
     */
    public function getPasswordHashMethods($flip = false)
    {
        $flip = is_bool($flip) ? $flip : false; // can remove in php7 and typehint to bool

        return $flip ? array_flip($this->methods) : $this->methods;
    }

    /**
     * For a given password hash algorithm name, return its internal integer code.
     *
     * @param string $hashAlgorithmName The name of a hash algorithm suitable for hashing user passwords
     *
     * @return integer|bool The internal integer code corresponding to the given algorithm name; false if the name is not valid
     */
    public function getPasswordHashMethodCode($hashAlgorithmName)
    {
        if (!is_string($hashAlgorithmName) || empty($hashAlgorithmName) || !isset($this->methods[$hashAlgorithmName])
            || empty($this->methods[$hashAlgorithmName]) || !is_numeric($this->methods[$hashAlgorithmName])) {
            throw new \InvalidArgumentException($this->translator->__f('Invalid argument %s', ['%s' => 'hashAlgorithmName']));
        }

        return $this->methods[$hashAlgorithmName];
    }

    /**
     * For a given internal password hash algorithm code, return its name suitable for use with the hash() function.
     *
     * @param int $hashAlgorithmCode The internal code representing a hashing algorithm suitable for hashing user passwords
     *
     * @return string|bool The hashing algorithm name corresponding to that code, suitable for use with hash(); false if the code is invalid
     */
    public function getPasswordHashMethodName($hashAlgorithmCode)
    {
        $hashMethodNamesByCode = array_flip($this->methods);

        if (!is_numeric($hashAlgorithmCode) || !isset($hashMethodNamesByCode[$hashAlgorithmCode])
            || !is_string($hashMethodNamesByCode[$hashAlgorithmCode]) || empty($hashMethodNamesByCode[$hashAlgorithmCode])) {
            throw new \InvalidArgumentException($this->translator->__f('Invalid argument %s', ['%s' => 'hashAlgorithmCode']));
        }

        return $hashMethodNamesByCode[$hashAlgorithmCode];
    }

    /**
     * Given a string return it's hash and the internal integer hashing algorithm code used to hash that string.
     *
     * Note that this can be used for more than just user login passwords. If a user-readale password-like code is needed,
     * then this method may be suitable.
     *
     * @param string $unhashedPassword An unhashed password, as might be entered by a user or generated by the system, that meets
     *                                  all of the constraints of a valid password for a user account
     * @param int $hashMethodCode An internal code identifying one of the valid user password hashing methods; optional, leave this
     *                                  unset (null) when creating a new password for a user to get the currently configured system
     *                                  hashing method, otherwise to hash a password for comparison, specify the method used to hash
     *                                  the original password
     * @param string $hashAlgorithmName
     *
     * @return array|bool An array containing two elements: 'hash' containing the hashed password, and 'hashMethodCode' containing the
     *                      internal integer hashing algorithm code used to hash the password; false if the password does not meet the
     * constraints of a valid password, or if the hashing method (stored in the Users module 'hash_method' var) is
     * not valid
     */
    public function getHashedPassword($unhashedPassword, $hashMethodCode = null, $hashAlgorithmName = self::DEFAULT_HASH_METHOD)
    {
        if (isset($hashMethodCode)) {
            if (!is_numeric($hashMethodCode) || ((int)$hashMethodCode != $hashMethodCode)) {
                throw new \InvalidArgumentException();
            }
            $hashAlgorithmName = $this->getPasswordHashMethodName($hashMethodCode);
            if (!$hashAlgorithmName) {
                throw new \InvalidArgumentException();
            }
        } else {
            $hashMethodCode = $this->getPasswordHashMethodCode($hashAlgorithmName);
            if (!$hashMethodCode) {
                throw new \InvalidArgumentException();
            }
        }

        return $this->getSaltedHash($unhashedPassword, $hashAlgorithmName, $this->methods, 5, self::SALT_DELIM);
    }

    /**
     * Create a system-generated password or password-like code, meeting the configured constraints for a password.
     *
     * @param int $length
     * @return string The generated (unhashed) password-like string
     */
    public function generatePassword($length = 5)
    {
        if (!is_numeric($length) || ((int)$length != $length) || ($length < 5)) {
            $length = 5;
        }
        $length = min($length, 25);
        $factory = new RandomLibFactory();
        $generator = $factory->getMediumStrengthGenerator();
        $chars = str_replace($this->passwordIncompatibleCharacters, '', $this->randomStringCharacters);

        return $generator->generateString($length, $chars);
    }

    /**
     * Compare a password-like code to a hashed value, to determine if they match.
     *
     * Note that this is not limited only to use for user login passwords, but can be used where ever a human-readable
     * password-like code is needed.
     *
     * @param string $unhashedPassword The password-like code entered by the user
     * @param string $hashedPassword   The hashed password-like code that the entered password-like code is to be compared to
     *
     * @return bool True if the $unhashedPassword matches the $hashedPassword with the given hashing method; false if they do not
     *                  match, or if there was an error (such as an empty password or invalid code)
     */
    public function passwordsMatch($unhashedPassword, $hashedPassword)
    {
        if (!is_string($unhashedPassword) || empty($unhashedPassword)) {
            throw new \InvalidArgumentException();
        }
        if (!is_string($hashedPassword) || empty($hashedPassword) || (strpos($hashedPassword, self::SALT_DELIM) === false)) {
            throw new \InvalidArgumentException();
        }

        $passwordsMatch = $this->checkSaltedHash($unhashedPassword, $hashedPassword);

        return $passwordsMatch;
    }

    /**
     * Hashes the data with the specified salt value and returns a string containing the hash method, salt and hash.
     *
     * @param string $unhashedData         The data to be salted and hashed
     * @param string $hashMethodName       Any value returned by hash_algo()
     * @param string $saltStr              Any valid string, including the empty string, with which to salt the unhashed data before hashing
     * @param array  $hashMethodNameToCode An array indexed by algorithm names (from hash_algos()) used to encode the hashing algorithm
     *                                         name and include it on the salted hash string; optional, if not specified, then the
     *                                         algorithm name is included in the string returned (which could be considered less than secure!)
     * @param string $saltDelimiter The delimiter between the salt and the hash, must be a single character
     *
     * @return string|bool The algorithm name (or code if $hashMethodNameToCode specified), salt and hashed data separated by the salt delimiter;
     *                      false if an error occurred
     */
    public function buildSaltedHash($unhashedData, $hashMethodName, $saltStr, array $hashMethodNameToCode = [], $saltDelimiter = self::SALT_DELIM)
    {
        $saltedHash = false;
        $algoList = hash_algos();

        if ((array_search($hashMethodName, $algoList) !== false) && is_string($saltStr) && is_string($saltDelimiter) && (strlen($saltDelimiter) == 1)) {
            $hashedData = hash($hashMethodName, $saltStr . $unhashedData);
            if (!empty($hashMethodNameToCode)) {
                if (isset($hashMethodNameToCode[$hashMethodName])) {
                    $saltedHash = $hashMethodNameToCode[$hashMethodName] . $saltDelimiter . $saltStr . $saltDelimiter . $hashedData;
                } else {
                    $saltedHash = false;
                }
            } else {
                $saltedHash = $hashMethodName . $saltDelimiter . $saltStr . $saltDelimiter . $hashedData;
            }
        }

        return $saltedHash;
    }

    /**
     * Hashes the data with a random salt value and returns a string containing the hash method, salt and hash.
     *
     * @param string $unhashedData         The data to be salted and hashed
     * @param string $hashMethodName       Any value returned by hash_algo()
     * @param array  $hashMethodNameToCode An array indexed by algorithm names (from hash_algos()) used to encode the hashing algorithm
     *                                         name and include it on the salted hash string; optional, if not specified, then the
     *                                         algorithm name is included in the string returned (which could be considered less than secure!)
     * @param int    $saltLength    The number of random characters to use in the salt
     * @param string $saltDelimiter The delimiter between the salt and the hash, must be a single character
     *
     * @return string|bool The algorithm name (or code if $hashMethodNameToCode specified), salt and hashed data separated by the salt delimiter;
     *                      false if an error occured
     */
    public function getSaltedHash($unhashedData, $hashMethodName, array $hashMethodNameToCode = [], $saltLength = 5, $saltDelimiter = self::SALT_DELIM)
    {
        $factory = new RandomLibFactory();
        $generator = $factory->getMediumStrengthGenerator();
        $chars = str_replace($saltDelimiter, '', $this->randomStringCharacters);
        $saltStr =  $generator->generateString($saltLength, $chars);

        return $this->buildSaltedHash($unhashedData, $hashMethodName, $saltStr, $hashMethodNameToCode, $saltDelimiter);
    }

    /**
     * Checks the given data against the given salted hash to see if they match.
     *
     * @param string $unhashedData  The data to be salted and hashed
     * @param string $saltedHash    The salted hash
     * @param string $saltDelimiter The delimiter between the salt and the hash, must be a single character
     *
     * @return integer|bool If the data matches the salted hash, then 1; If the data does not match, then 0; false if an error occured (Note:
     *                      both 0 and false evaluate to false in boolean expressions--use strict comparisons to differentiate)
     */
    public function checkSaltedHash($unhashedData, $saltedHash, $saltDelimiter = self::SALT_DELIM)
    {
        $dataMatches = false;
        $algoList = hash_algos();
        $hashMethodCodeToName = array_flip($this->methods);

        if (is_string($unhashedData) && is_string($saltedHash) && is_string($saltDelimiter) && (strlen($saltDelimiter) == 1)
            && (strpos($saltedHash, $saltDelimiter) !== false)) {
            list($hashMethod, $saltStr, $correctHash) = explode($saltDelimiter, $saltedHash);

            if (is_numeric($hashMethod) && ((int)$hashMethod == $hashMethod)) {
                $hashMethod = (int)$hashMethod;
            }
            if (isset($hashMethodCodeToName[$hashMethod])) {
                $hashMethodName = $hashMethodCodeToName[$hashMethod];
            } else {
                $hashMethodName = $hashMethod;
            }

            if (array_search($hashMethodName, $algoList) !== false) {
                $dataHash = hash($hashMethodName, $saltStr . $unhashedData);
                $dataMatches = is_string($dataHash) ? (int)($dataHash == $correctHash) : false;
            }
        }

        return $dataMatches;
    }
}
