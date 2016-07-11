<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Core\Response\Ajax;

/**
 * Ajax class.
 */
abstract class AbstractErrorResponse extends AbstractBaseResponse
{
    /**
     * Constructor.
     *
     * @param mixed $message Response status/error message, may be string or array.
     * @param mixed $payload Payload.
     */
    public function __construct($message, $payload = null)
    {
        $this->messages = (array)$message;
        $this->payload = $payload;

        if ($this->newCsrfToken) {
            $this->csrfToken = \SecurityUtil::generateCsrfToken();
        }

        parent::__construct('', $this->statusCode);
    }

    /**
     * Generate system level payload.
     *
     * @return array
     */
    protected function generateCoreData()
    {
        $core = parent::generateCoreData();
        if (!isset($core['statusmsg']) || empty($core['statusmsg'])) {
            $core['statusmsg'] = __('An unknown error occurred');
        }

        return $core;
    }
}
