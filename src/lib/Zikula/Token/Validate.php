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
     * Token generator.
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
     * @param Zikula_Token_Generator $tokenGenerator Token generator.
     * @param integer                $maxlifetime    Max lifetime in seconds (default = 86400).
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
     * Tokens should be deleted if they are generated as one-time tokens
     * with a unique ID each time.  If the are per-session, then they should be
     * generated with the same unique ID and not deleted when validated here.
     *
     * @param string  $token       Token to validate.
     * @param boolean $delete      Whether to delete the token if valid.
     * @param boolean $checkExpire Whether to check for token expiry.
     *
     * @return boolean
     */
    public function validate($token, $delete=true, $checkExpire=true)
    {
        if (!$token) {
            return false;
        }

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
            $this->storage->delete($decoded['id']);

            return false;
        }

        // Check if token has expired.
        if ($checkExpire) {
            $timeDiff = ((int)$decoded['timestamp'] + $this->maxlifetime) - time();
            if ($timeDiff < 0) {
                $this->storage->delete($decoded['id']);

                return false;
            }
        }

        // All checked out, delete the token and return true.
        if ($delete) {
            $this->storage->delete($decoded['id']);
        }

        return true;
    }
}
