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
use Zikula\ZAuthModule\Api\ApiInterface\PasswordApiInterface;

class PasswordApi implements PasswordApiInterface
{
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
     * {@inheritdoc}
     */
    public function getHashedPassword($unhashedPassword, $hashMethodCode = self::DEFAULT_HASH_METHOD_CODE)
    {
        if (!is_numeric($hashMethodCode) || ((int)$hashMethodCode != $hashMethodCode)) {
            throw new \InvalidArgumentException();
        }
        $hashMethodNamesByCode = array_flip($this->methods);
        $hashAlgorithmName = $hashMethodNamesByCode[$hashMethodCode]; // throws ContextErrorException if not set

        return $this->getSaltedHash($unhashedPassword, $hashAlgorithmName, $this->methods, self::SALT_LENGTH, self::SALT_DELIM);
    }

    /**
     * {@inheritdoc}
     */
    public function generatePassword($length = self::MIN_LENGTH)
    {
        if (!is_numeric($length) || ((int)$length != $length) || ($length < self::MIN_LENGTH)) {
            $length = self::MIN_LENGTH;
        }
        $factory = new RandomLibFactory();
        $generator = $factory->getMediumStrengthGenerator();
        $chars = str_replace($this->passwordIncompatibleCharacters, '', $this->randomStringCharacters);

        return $generator->generateString($length, $chars);
    }

    /**
     * {@inheritdoc}
     */
    public function passwordsMatch($unhashedPassword, $hashedPassword)
    {
        if (empty($unhashedPassword) || !is_string($unhashedPassword)) {
            throw new \InvalidArgumentException();
        }
        if (empty($hashedPassword) || !is_string($hashedPassword) || (strpos($hashedPassword, self::SALT_DELIM) === false) || (2 != substr_count($hashedPassword, self::SALT_DELIM))) {
            throw new \InvalidArgumentException();
        }

        $passwordsMatch = $this->checkSaltedHash($unhashedPassword, $hashedPassword);

        return $passwordsMatch;
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
     * @return string The algorithm name (or code if $hashMethodNameToCode specified), salt and hashed data separated by the salt delimiter
     */
    protected function getSaltedHash($unhashedData, $hashMethodName, array $hashMethodNameToCode = [], $saltLength = self::SALT_LENGTH, $saltDelimiter = self::SALT_DELIM)
    {
        $factory = new RandomLibFactory();
        $generator = $factory->getMediumStrengthGenerator();
        $chars = str_replace($saltDelimiter, '', $this->randomStringCharacters);
        $saltStr =  $generator->generateString($saltLength, $chars);

        return $this->buildSaltedHash($unhashedData, $hashMethodName, $saltStr, $hashMethodNameToCode, $saltDelimiter);
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
     * @return string The algorithm name (or code if $hashMethodNameToCode specified), salt and hashed data separated by the salt delimiter
     */
    protected function buildSaltedHash($unhashedData, $hashMethodName, $saltStr, array $hashMethodNameToCode = [], $saltDelimiter = self::SALT_DELIM)
    {
        $saltedHash = false;
        $algoList = hash_algos();

        if ((array_search($hashMethodName, $algoList) !== false) && is_string($saltStr) && is_string($saltDelimiter) && (strlen($saltDelimiter) == 1)) {
            $hashedData = hash($hashMethodName, $saltStr . $unhashedData);
            if (!empty($hashMethodNameToCode)) {
                if (isset($hashMethodNameToCode[$hashMethodName])) {
                    $saltedHash = $hashMethodNameToCode[$hashMethodName] . $saltDelimiter . $saltStr . $saltDelimiter . $hashedData;
                } else {
                    throw new \InvalidArgumentException();
                }
            } else {
                $saltedHash = $hashMethodName . $saltDelimiter . $saltStr . $saltDelimiter . $hashedData;
            }
        }

        return $saltedHash;
    }

    /**
     * Checks the given data against the given salted hash to see if they match.
     *
     * @param string $unhashedData  The data to be salted and hashed
     * @param string $saltedHash    The salted hash
     * @param string $saltDelimiter The delimiter between the salt and the hash, must be a single character
     *
     * @return bool If the data matches the salted hash, then true; If the data does not match, then false
     */
    protected function checkSaltedHash($unhashedData, $saltedHash, $saltDelimiter = self::SALT_DELIM)
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
                $dataHash = hash($hashMethodName, $saltStr . $unhashedData); // throws ContextErrorException if $hashMethodName is unknown algorithm
                $dataMatches = $dataHash == $correctHash;
            }
        }

        return $dataMatches;
    }
}
