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

use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;
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
     * @var string
     */
    private $securityLevel = self::SECURITY_LEVEL_MEDIUM;

    /**
     * @var int
     */
    private $inactiveSeconds = 1200;

    /**
     * @var int
     */
    private $autoLogoutAfterSeconds = 604800;

    /**
     * @var int
     */
    private $cookieLifeTime = 604800;

    /**
     * @param VariableApi $variableApi
     * @param array $options
     * @param AbstractProxy|NativeSessionHandler|\SessionHandlerInterface|null $handler
     * @param MetadataBag $metaBag
     */
    public function __construct(VariableApi $variableApi, array $options = [], $handler = null, MetadataBag $metaBag = null)
    {
        $this->securityLevel = $variableApi->getSystemVar('seclevel', self::SECURITY_LEVEL_MEDIUM);
        $this->inactiveSeconds = $variableApi->getSystemVar('secinactivemins', 20) * 60;
        $this->autoLogoutAfterSeconds = $variableApi->getSystemVar('secmeddays', 7) * 24 * 60 * 60;

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
            $inactiveTime = $now - $this->inactiveSeconds;
            $daysOldTime = $now - $this->autoLogoutAfterSeconds;
            $cookieLastUsed = $this->getMetadataBag()->getLastUsed();
            $cookieExpired = $cookieLastUsed < $inactiveTime;
            $cookieAgedOut = $cookieLastUsed < $daysOldTime;
            $rememberMe = $this->getBag('attributes')->get('rememberme');
            $uid = $this->getBag('attributes')->get('uid', 0); // @todo default to anonymous uid?
            switch ($this->securityLevel) {
                case self::SECURITY_LEVEL_LOW:
                    break;
                case self::SECURITY_LEVEL_MEDIUM:
                    if ((!$rememberMe && $cookieExpired) || ($cookieAgedOut) || ($uid == 0 && $cookieExpired)) {
                        parent::regenerate(true, 2 * 365 * 24 * 60 * 60); // two years
                    }
                    break;
                case self::SECURITY_LEVEL_HIGH:
                default:
                    if ($cookieExpired) {
                        parent::regenerate(true, $this->cookieLifeTime);
                    }
                    break;
            }
        }

        return true;
    }
}
