<?php
/**
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
class AjaxResponse extends AbstractBaseResponse
{
    /**
     * Constructor.
     *
     * @param mixed $payload Application data.
     * @param mixed $message Response status/error message, may be string or array.
     * @param array $options Options.
     */
    public function __construct($payload, $message = null, array $options = array())
    {
        $this->payload = $payload;
        $this->messages = (array)$message;
        $this->options = $options;
        if ($this->newCsrfToken) {
            $this->csrfToken = \SecurityUtil::generateCsrfToken();
        }

        if (\System::isLegacyMode()) {
            $this->authid = \SecurityUtil::generateAuthKey(\ModUtil::getName());
        }

        parent::__construct('', $this->statusCode);
    }
}
