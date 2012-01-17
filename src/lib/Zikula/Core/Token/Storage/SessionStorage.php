<?php
/**
 * Copyright 2010 Zikula Foundation
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 * @package Security_Storage
 * @subpackage Validate
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\Core\Token\Storage;

use Symfony\Component\HttpFoundation\SessionInterface;

/**
 * Stores tokens in session.
 */
class SessionStorage implements StorageInterface
{
    /**
     * Session.
     *
     * @var SessionInterface
     */
    private $session;

    /**
     * Storage key.
     *
     * @var string
     */
    private $key;

    /**
     * Constructor.
     *
     * @param SessionInterface $session SessionInterface instance.
     */
    public function __construct(SessionInterface $session, $key = '_tokens')
    {
        $this->session = $session;
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (empty($id)) {
            return false;
        }

        $tokens = $this->session->get($this->key, array());

        if (!array_key_exists($id, $tokens)) {
            return false;
        }

        return $tokens[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function save($id, $token, $timestamp)
    {
        $tokens = $this->session->get('_tokens', array());
        $tokens[$id] = array('token' => $token, 'time' => (int)$timestamp);
        $this->session->set($this->key, $tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $tokens = $this->session->get($this->key, array());
        if (array_key_exists($id, $tokens)) {
            unset($tokens[$id]);
        }

        $this->session->set($this->key, $tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        $tokens = $this->session->get($this->key, array());
        $now = time();
        foreach ($tokens as $key => $token) {
            if ($now - (int)$token['time'] > $lifetime) {
                unset($token[$key]);
            }
        }

        $this->session->set($this->key, $tokens);
    }
}
