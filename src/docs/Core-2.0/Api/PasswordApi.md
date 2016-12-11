PasswordApi
===========

classname: \Zikula\ZAuthModule\Api\PasswordApi

service id=""

This class is used to manage passwords. 

The class makes the following methods available:

    - getHashedPassword($unhashedPassword, $hashMethodCode = self::DEFAULT_HASH_METHOD_CODE)
    - generatePassword($length = self::MIN_LENGTH)
    - passwordsMatch($unhashedPassword, $hashedPassword)

The class is fully tested.
