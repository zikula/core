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

/**
 * Zikula_Token_Storage_Session class.
 */
class Zikula_Token_Storage_Session implements Zikula_Token_Storage
{
    /**
     * Session.
     * 
     * @var Zikula_Session
     */
    protected $session;

    public function __construct(Zikula_Session $session)
    {
        $this->session = $session;
    }

    public function get($id)
    {
        $tokens = $this->session->get('_tokens', array());
        if (!array_key_exists($id, $tokens)) {
            return false;
        }

        return $tokens[$id];
    }

    public function save($id, $token, $timestamp)
    {
        $tokens = $this->session->get('_tokens', array());
        $tokens[$id] = array('token' => $token, 'timestamp' => $timestamp);
        $this->session->set('_tokens', $tokens);
    }

    public function delete($id)
    {
        $tokens = $this->session->get('_tokens', array());
        if (array_key_exists($id, $tokens)) {
            unset($tokens[$id]);
        }
        $this->session->set('_tokens', $tokens);
    }

}
