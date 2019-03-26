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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;
use Zikula\UsersModule\Entity\UserSessionEntity;

/**
 * Class DoctrineSessionHandler
 */
class DoctrineSessionHandler extends AbstractSessionHandler
{
    /**
     * @var SessionStorageInterface
     */
    private $storage;

    /**
     * @var UserSessionRepositoryInterface
     */
    private $userSessionRepository;

    /**
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var bool is Zikula installed?
     */
    private $installed;

    /**
     * @var bool Whether gc() has been called
     */
    private $gcCalled = false;

    /**
     * @param UserSessionRepositoryInterface $userSessionRepository
     * @param VariableApiInterface $variableApi
     * @param RequestStack $requestStack
     * @param $installed
     */
    public function __construct(
        UserSessionRepositoryInterface $userSessionRepository,
        VariableApiInterface $variableApi,
        RequestStack $requestStack,
        $installed
    ) {
        $this->userSessionRepository = $userSessionRepository;
        $this->variableApi = $variableApi;
        $this->requestStack = $requestStack;
        $this->installed = $installed;
    }

    public function setStorage(SessionStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId)
    {
        if (!$this->installed) {
            return '';
        }

        $sessionEntity = $this->userSessionRepository->find($sessionId);
        if ($sessionEntity) {
            $vars = $sessionEntity->getVars();
        }

        return !empty($vars) ? $vars : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $data)
    {
        if (!$this->installed) {
            return true;
        }

        $sessionEntity = $this->getSessionEntity($sessionId);
        $sessionEntity->setVars($data);
        $this->userSessionRepository->persistAndFlush($sessionEntity);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        if (!$this->installed) {
            return true;
        }

        // We delay gc() to close() so that it is executed outside the transactional and blocking read-write process.
        // This way, pruning expired sessions does not block them from being started while the current session is used.
        $this->gcCalled = true;

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId)
    {
        // expire the cookie
        if ('cli' !== php_sapi_name()) {
            setcookie(session_name(), '', 0, ini_get('session.cookie_path'));
        }
        $this->userSessionRepository->removeAndFlush($sessionId);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $result = true;
        if (!$this->installed) {
            return $result;
        }

        if ($this->gcCalled) {
            $this->gcCalled = false;

            $result = $this->userSessionRepository->gc(
                $this->variableApi->getSystemVar('seclevel', 'Medium'),
                $this->variableApi->getSystemVar('secinactivemins', 20),
                $this->variableApi->getSystemVar('secmeddays', 7)
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data)
    {
        $sessionEntity = $this->getSessionEntity($sessionId);
        $sessionEntity->setVars($data);
        $this->userSessionRepository->persistAndFlush($sessionEntity);
    }

    /**
     * Returns the session entity.
     *
     * @param string $sessionId
     *
     * @return UserSessionEntity
     */
    private function getSessionEntity($sessionId)
    {
        $sessionEntity = $this->userSessionRepository->find($sessionId);
        if (!$sessionEntity) {
            $sessionEntity = new UserSessionEntity();
        }
        $sessionEntity->setSessid($sessionId);
        $sessionEntity->setIpaddr($this->getCurrentIp());
        $sessionEntity->setLastused(date('Y-m-d H:i:s', $this->storage->getMetadataBag()->getLastUsed()));

        $attributesBag = $this->storage->getBag('attributes')->getBag();
        $sessionEntity->setUid($attributesBag->get('uid', Constant::USER_ID_ANONYMOUS));
        $sessionEntity->setRemember($attributesBag->get('rememberme', 0));

        return $sessionEntity;
    }

    /**
     * find the current IP address
     * @param string $default
     * @return string
     */
    private function getCurrentIp($default = '127.0.0.1')
    {
        if ('cli' !== php_sapi_name()) {
            $ipAddress = $this->requestStack->getCurrentRequest()->getClientIp();
            $ipAddress = !empty($ipAddress) ? $ipAddress : $this->requestStack->getCurrentRequest()->server->get('HTTP_HOST');
        }

        return !empty($ipAddress) ? $ipAddress : $default;
    }
}
