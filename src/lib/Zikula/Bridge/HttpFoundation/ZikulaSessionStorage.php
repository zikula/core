<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\Bridge\HttpFoundation;

use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Constant;

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
    public const SECURITY_LEVEL_LOW = 'Low';

    /**
     * Medium security - delete session info if session cookie has
     * expired or user decided not to remember themself and inactivity timeout
     * OR max number of days have elapsed without logging back in
     */
    public const SECURITY_LEVEL_MEDIUM = 'Medium';

    /**
     * High security - delete session info if user is inactive
     */
    public const SECURITY_LEVEL_HIGH = 'High';

    /**
     * @var string
     */
    private $securityLevel;

    /**
     * @var int
     */
    private $inactiveSeconds;

    /**
     * @var int
     */
    private $autoLogoutAfterSeconds;

    /**
     * @var int
     */
    private $cookieLifeTime = 604800;

    public function __construct(
        VariableApiInterface $variableApi,
        array $options = [],
        SessionHandlerInterface $handler = null,
        MetadataBag $metaBag = null
    ) {
        $this->securityLevel = $variableApi->getSystemVar('seclevel', self::SECURITY_LEVEL_MEDIUM);
        $this->inactiveSeconds = $variableApi->getSystemVar('secinactivemins', 20) * 60;
        $this->autoLogoutAfterSeconds = $variableApi->getSystemVar('secmeddays', 7) * 24 * 60 * 60;

        parent::__construct($options, $handler, $metaBag);
    }

    public function start()
    {
        // avoid warning in PHP 7.2 based on ini_set() usage which is caused by any access to the
        // session before regeneration happens (e.g. by an event listener executed before a login)
        // see issue #3898 for the details
        $reportingLevel = error_reporting(E_ALL & ~E_WARNING);

        $result = parent::start();

        if (true === $result) {
            // check if session has expired or not
            $now = time();
            $inactiveTime = $now - $this->inactiveSeconds;
            $daysOldTime = $now - $this->autoLogoutAfterSeconds;
            $cookieLastUsed = $this->getMetadataBag()->getLastUsed();
            $cookieExpired = $cookieLastUsed < $inactiveTime;
            $cookieAgedOut = $cookieLastUsed < $daysOldTime;
            $attributesBag = $this->getBag('attributes')->getBag();
            $rememberMe = $attributesBag->get('rememberme');
            $uid = $attributesBag->get('uid', Constant::USER_ID_ANONYMOUS);
            switch ($this->securityLevel) {
                case self::SECURITY_LEVEL_LOW:
                    break;
                case self::SECURITY_LEVEL_MEDIUM:
                    if ((!$rememberMe && $cookieExpired) || $cookieAgedOut || (Constant::USER_ID_ANONYMOUS === $uid && $cookieExpired)) {
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

        error_reporting($reportingLevel);

        return $result;
    }

    public function regenerate($destroy = false, $lifetime = null)
    {
        // avoid warning in PHP 7.2 based on ini_set() usage which is caused by any access to the
        // session before regeneration happens (e.g. by an event listener executed before a login)
        // see issue #3898 for the details
        $reportingLevel = error_reporting(E_ALL & ~E_WARNING);

        $result = parent::regenerate($destroy, $lifetime);

        error_reporting($reportingLevel);

        return $result;
    }
}
