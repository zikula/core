<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Token\Storage;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
     * @param \Zikula_Session $session \Zikula_Session instance
     * @param string          $key
     */
    public function __construct(\Zikula_Session $session, $key = '_tokens')
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

        $tokens = $this->session->get($this->key, []);

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
        $tokens = $this->session->get($this->key, []);
        $tokens[$id] = [
            'token' => $token,
            'time' => (int)$timestamp
        ];
        $this->session->set($this->key, $tokens);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        $tokens = $this->session->get($this->key, []);
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
        $tokens = $this->session->get($this->key, []);
        $now = time();
        foreach ($tokens as $key => $token) {
            if ($now - (int)$token['time'] > $lifetime) {
                unset($token[$key]);
            }
        }

        $this->session->set($this->key, $tokens);
    }
}
