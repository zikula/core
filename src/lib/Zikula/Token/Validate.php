<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Security_Token
 * @subpackage Validate
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Token_Validate class.
 */
class Zikula_Token_Validate
{
    /**
     *
     * @var Zikula_Token_Generator
     */
    protected $tokenGenerator;

    /**
     * Secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * Max life of a token.
     *
     * @var integer
     */
    protected $maxlifetime;

    /**
     * Storage driver.
     * 
     * @var Zikula_Token_Storage
     */
    protected $storage;

    /**
     * Constructor.
     *
     * @param Zikula_Token_Generator $tokenGenerator
     * @param integer                $maxlifetime
     */
    public function __construct(Zikula_Token_Generator $tokenGenerator, $maxlifetime = 86400)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->storage = $tokenGenerator->getStorage();
        $this->secret = $tokenGenerator->getSecret();
        $this->maxlifetime = (int)$maxlifetime;
    }

    /**
     * Validate a token.
     *
     * @param string $token Token to validate.
     *
     * @return boolean
     */
    public function validate($token)
    {
        list($id, $hash, $timestamp) = $this->tokenGenerator->decode($token);
        $decoded = array('id' => $id, 'timestamp' => $timestamp);
        
        // Check if token ID exists first.
        $stored = $this->storage->get($decoded['id']);
        if (!$stored) {
            return false;
        }

        // Check if the token has been tampered with.
        $duplicateToken = $this->tokenGenerator->generate($decoded['id'], $decoded['timestamp'])->getToken();
        if ($stored['token'] !== $duplicateToken) {
            $this->storage->delete($stored['id']);
            return false;
        }

        // Check if token has expired.
        $timeDiff = ((int)$stored['timestamp'] + $this->maxlifetime) - time();
        if ($timeDiff < 0) {
            $this->storage->delete($stored['id']);
            return false;
        }

        // All checked out, delete the token and return true.
        $this->storage->delete($stored['id']);
        return true;
    }
}
