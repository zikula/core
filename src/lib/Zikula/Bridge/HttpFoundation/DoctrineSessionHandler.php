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

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\UsersModule\Constant;
use Zikula\UsersModule\Entity\RepositoryInterface\UserSessionRepositoryInterface;
use Zikula\UsersModule\Entity\UserSessionEntity;

/**
 * Class DoctrineSessionHandler
 */
class DoctrineSessionHandler implements \SessionHandlerInterface
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
     * @param UserSessionRepositoryInterface $userSessionRepository
     * @param VariableApiInterface $variableApi
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
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
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
    public function write($sessionId, $vars)
    {
        if (!$this->installed) {
            return true;
        }

        $sessionEntity = $this->userSessionRepository->find($sessionId);
        if (!$sessionEntity) {
            $sessionEntity = new UserSessionEntity();
        }
        $sessionEntity->setSessid($sessionId);
        $sessionEntity->setIpaddr($this->getCurrentIp());
        $sessionEntity->setLastused(date('Y-m-d H:i:s', $this->storage->getMetadataBag()->getLastUsed()));
        $sessionEntity->setUid($this->storage->getBag('attributes')->get('uid', Constant::USER_ID_ANONYMOUS));
        $sessionEntity->setRemember($this->storage->getBag('attributes')->get('rememberme', 0));
        $sessionEntity->setVars($vars);
        $this->userSessionRepository->persistAndFlush($sessionEntity);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        // expire the cookie
        if (php_sapi_name() != 'cli') {
            setcookie(session_name(), '', 0, ini_get('session.cookie_path'));
        }
        $this->userSessionRepository->removeAndFlush($sessionId);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        if (!$this->installed) {
            return true;
        }

        return $this->userSessionRepository->gc(
            $this->variableApi->getSystemVar('seclevel', 'Medium'),
            $this->variableApi->getSystemVar('secinactivemins', 20),
            $this->variableApi->getSystemVar('secmeddays', 7)
        );
    }

    /**
     * find the current IP address
     * @param string $default
     * @return string
     */
    private function getCurrentIp($default = '127.0.0.1')
    {
        if (php_sapi_name() != 'cli') {
            $ipAddress = $this->requestStack->getCurrentRequest()->getClientIp();
            $ipAddress = !empty($ipAddress) ? $ipAddress : $this->requestStack->getCurrentRequest()->server->get('HTTP_HOST');
        }

        return !empty($ipAddress) ? $ipAddress : $default;
    }
}
