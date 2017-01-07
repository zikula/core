<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bridge\HttpFoundation;

use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Zikula\ExtensionsModule\Api\VariableApi;

/**
 * Class DoctrineSessionStorage
 */
class ZikulaSessionStorage extends NativeSessionStorage
{
    /**
     * Low security - delete session info if user decided not to
     * remember themself and inactivity timeout
     * Users stay logged in permanently
     */
    const SECURITY_LEVEL_LOW = 'Low';

    /**
     * Medium security - delete session info if session cookie has
     * expired or user decided not to remember themself and inactivity timeout
     * OR max number of days have elapsed without logging back in
     */
    const SECURITY_LEVEL_MEDIUM = 'Medium';

    /**
     * High security - delete session info if user is inactive
     */
    const SECURITY_LEVEL_HIGH = 'High';

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * @param VariableApi $variableApi
     * @param array $options
     * @param null $handler
     * @param MetadataBag $metaBag
     */
    public function __construct(VariableApi $variableApi, array $options = [], $handler = null, MetadataBag $metaBag = null)
    {
        $this->variableApi = $variableApi;
        parent::__construct($options, $handler, $metaBag);
    }

    /**
     * {@inheritdoc}
     */
    public function start()
    {
        if (parent::start()) {
            // check if session has expired or not
            $now = time();
            $inactive = ($now - (int)($this->variableApi->getSystemVar('secinactivemins', 20) * 60));
            $daysold = ($now - (int)($this->variableApi->getSystemVar('secmeddays', 7) * 86400));
            $lastused = $this->getMetadataBag()->getLastUsed();
            $rememberme = $this->getBag('attributes')->get('rememberme');
            $uid = $this->getBag('attributes')->get('uid');

            switch ($this->variableApi->getSystemVar('seclevel')) {
                case self::SECURITY_LEVEL_LOW:
                    break;
                case self::SECURITY_LEVEL_MEDIUM:
                    if ((!$rememberme && $lastused < $inactive) || ($lastused < $daysold) || ($uid == '0' && $lastused < $inactive)) {
                        parent::regenerate(true);
                    }
                    break;
                case self::SECURITY_LEVEL_HIGH:
                default:
                    if ($lastused < $inactive) {
                        parent::regenerate(true);
                    }
                    break;
            }
        }

        return true;
    }
}
