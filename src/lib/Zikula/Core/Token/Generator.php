<?php
/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Token;

use Zikula\Core\Token\Storage\StorageInterface;

/**
 * Token generator class.
 */
class Generator
{
    /**
     * Storage driver.
     *
     * @var StorageInterface
     */
    private $storage;

    /**
     * Token ID.
     *
     * @var string
     */
    private $id;

    /**
     * Secret.
     *
     * @var string
     */
    private $secret;

    /**
     * Generated token.
     *
     * @var string
     */
    private $token;

    /**
     * The token hash.
     *
     * @var string
     */
    private $hash;

    /**
     * Timestamp of generated token.
     *
     * @var integer
     */
    private $timestamp;

    /**
     * Max life of a token.
     *
     * @var integer
     */
    private $maxLifetime;

    /**
     * Constructor.
     *
     * @param StorageInterface $storage     Storage driver.
     * @param string           $secret      Secret to sign tokens with.
     * @param integer          $maxLifetime Max lifetime for a token.
     */
    public function __construct(StorageInterface $storage, $secret, $maxLifetime = 3600)
    {
        $this->storage = $storage;
        $this->secret = $secret;
        $this->maxLifetime = $maxLifetime;
    }

    /**
     * Save token.
     *
     * @return void
     */
    public function save()
    {
        $this->storage->save($this->id, $this->token, $this->timestamp);
        $this->garbageCollection();
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
     * @param integer $timestamp Create with this timestamp (defaults null = now)
     *
     * @return Generator
     */
    public function generate($id, $timestamp = null)
    {
        $this->id = $id;
        $this->timestamp = is_null($timestamp) ? time() : $timestamp;
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
     * Gets storage driver.
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * Gets token ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets signing secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Sets secret.
     *
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }

    /**
     * Gets generated token.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Gets hash for of token.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Gets timestamp of token creation.
     *
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Runs garbage collection to clean up tokens that expire
     * before the session expired.
     *
     * Generates a number between 1 and $probability and runs
     * garbage collection if result is 1.
     *
     * @param integer $probability Defaults to 20, ie 1/20 = 5%
     */
    public function garbageCollection($probability = 20)
    {
        if (mt_rand(1, $probability) === 1) {
            $this->storage->gc($this->maxLifetime);
        }
    }

    /**
     * Gets the max lifetime in seconds.
     *
     * @return integer
     */
    public function getMaxLifetime()
    {
        return $this->maxLifetime;
    }
}
