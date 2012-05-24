<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Security_Token
 * @subpackage Generator
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

/**
 * Zikula_Token_Generator class.
 */
class Zikula_Token_Generator
{
    /**
     * Storage driver.
     *
     * @var Zikula_Token_Storage
     */
    protected $storage;

    /**
     * Token ID.
     *
     * @var string
     */
    protected $id;

    /**
     * Secret.
     *
     * @var string
     */
    protected $secret;

    /**
     * Generated token.
     *
     * @var string
     */
    protected $token;

    /**
     * The token hash.
     *
     * @var string
     */
    protected $hash;

    /**
     * Timestamp of generated token.
     *
     * @var integer
     */
    protected $timestamp;

    /**
     * Constructor.
     *
     * @param Zikula_Token_StorageInterface $storage Storage driver.
     * @param string                        $secret  Secret to sign tokens with.
     */
    public function __construct(Zikula_Token_StorageInterface $storage, $secret)
    {
        $this->storage = $storage;
        $this->secret = $secret;
    }

    /**
     * Save token.
     *
     * @return void
     */
    public function save()
    {
        $this->storage->save($this->id, $this->token, $this->timestamp);
    }

    /**
     * Delete token.
     *
     * @return void
     */
    public function delete()
    {
        $this->storage->delete($this->token);
    }

    /**
     * Generate a unique ID.
     *
     * @return string
     */
    public function uniqueId()
    {
        return uniqid('', true);
    }

    /**
     * Generate token based on ID.
     *
     * If tokens are intended to be one-time tokens then use a unique ID each
     * time.  They are validated with delete=true.  If the are per-session, then
     * they should be generated with the same unique ID and validated with
     * delete = false leaving storage expire/GC to remove the token.
     *
     * @param string  $id        Token ID.
     * @param integer $timestamp Create with this timestamp.
     *
     * @return Zikula_Security_TokenGenerator
     */
    public function generate($id, $timestamp)
    {
        $this->id = $id;
        $this->timestamp = $timestamp;
        $this->hash = md5($this->id . $this->secret . $this->timestamp);
        $this->token = base64_encode("{$this->id}:{$this->hash}:{$this->timestamp}");

        return $this;
    }

    /**
     * Decode a token.
     *
     * @param string $token Token.
     *
     * @return array
     */
    public function decode($token)
    {
        return explode(':', base64_decode($token));
    }

    /**
     * Get storage driver.
     *
     * @return Zikula_Token_Storage
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Get token ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get signing secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Get generated token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get hash for of token.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Get timestamp of token creation.
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
