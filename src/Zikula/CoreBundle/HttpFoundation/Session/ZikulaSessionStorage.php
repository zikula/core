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

namespace Zikula\Bundle\CoreBundle\HttpFoundation\Session;

use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionUtils;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Constant;

/**
 * Class ZikulaSessionStorage
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

    /**
     * @var string|null
     * remove again when https://github.com/symfony/symfony/issues/35460 is solved
     */
    private $emulateSameSite;

    public function __construct(
        VariableApiInterface $variableApi,
        array $options = [],
        SessionHandlerInterface $handler = null,
        SessionBagInterface $metaBag = null
    ) {
        $this->securityLevel = $variableApi->getSystemVar('seclevel', self::SECURITY_LEVEL_MEDIUM);
        $this->inactiveSeconds = $variableApi->getSystemVar('secinactivemins', 20) * 60;
        $this->autoLogoutAfterSeconds = $variableApi->getSystemVar('secmeddays', 7) * 24 * 60 * 60;

        parent::__construct($options, $handler, $metaBag);
    }

    public function start()
    {
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
                        $this->regenerate(true, 2 * 365 * 24 * 60 * 60); // two years
                    }
                    break;
                case self::SECURITY_LEVEL_HIGH:
                default:
                    if ($cookieExpired) {
                        $this->regenerate(true, $this->cookieLifeTime);
                    }
                    break;
            }
        }

        // remove again when https://github.com/symfony/symfony/issues/35460 is solved
        if (null !== $this->emulateSameSite) {
            $originalCookie = SessionUtils::popSessionCookie(session_name(), session_id());
            if (null !== $originalCookie) {
                header(sprintf('%s; SameSite=%s', $originalCookie, $this->emulateSameSite), false);
            }
        }

        return $result;
    }

    public function regenerate($destroy = false, $lifetime = null)
    {
        // Cannot regenerate the session ID for non-active sessions.
        if (\PHP_SESSION_ACTIVE !== session_status()) {
            return false;
        }

        if (headers_sent()) {
            return false;
        }

        if (null !== $lifetime) {
            // added due to https://github.com/symfony/symfony/issues/28577
            $this->save();

            ini_set('session.cookie_lifetime', (string) $lifetime);

            // added due to https://github.com/symfony/symfony/issues/28577
            $this->start();
        }

        if ($destroy) {
            $this->metadataBag->stampNew();
        }

        $isRegenerated = session_regenerate_id($destroy);

        // The reference to $_SESSION in session bags is lost in PHP7 and we need to re-create it.
        // @see https://bugs.php.net/70013
        $this->loadSession();

        if (null !== $this->emulateSameSite) {
            $originalCookie = SessionUtils::popSessionCookie(session_name(), session_id());
            if (null !== $originalCookie) {
                header(sprintf('%s; SameSite=%s', $originalCookie, $this->emulateSameSite), false);
            }
        }

        return $isRegenerated;
    }

    // remove again when https://github.com/symfony/symfony/issues/35460 is solved
    public function setOptions(array $options)
    {
        parent::setOptions($options);

        if (isset($options['cookie_samesite']) && \PHP_VERSION_ID < 70300) {
            // PHP < 7.3 does not support same_site cookies. We will emulate it in
            // the start() method instead.
            $this->emulateSameSite = $options['cookie_samesite'];
        }
    }
}
