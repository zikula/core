<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Helper;

use Zikula\Core\Doctrine\EntityAccess;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\ZAuthModule\Entity\AuthenticationMappingEntity;
use Zikula\ZAuthModule\Entity\RepositoryInterface\UserVerificationRepositoryInterface;
use Zikula\ZAuthModule\Entity\UserVerificationEntity;
use Zikula\ZAuthModule\ZAuthConstant;

class LostPasswordVerificationHelper
{
    /**
     * @var UserVerificationRepositoryInterface
     */
    private $userVerificationRepository;

    /**
     * @var VariableApi
     */
    private $variableApi;

    /**
     * LostPasswordVerificationHelper constructor.
     *
     * @param UserVerificationRepositoryInterface $userVerificationRepository
     * @param VariableApi                         $variableApi
     */
    public function __construct(UserVerificationRepositoryInterface $userVerificationRepository, VariableApi $variableApi)
    {
        $this->userVerificationRepository = $userVerificationRepository;
        $this->variableApi = $variableApi;
    }

    /**
     * Concatenation delimiter
     */
    private $delimiter = '#';

    /**
     * Amount of encoding iterations
     */
    private $iterations = 3;

    /**
     * Creates an identifier for the lost password link.
     * This link carries the user's id, name and email address as well as the actual confirmation code.
     *
     * @param EntityAccess $record instance of UserEntity or AuthenticationMappingEntity
     * @return string The created identifier
     */
    public function createLostPasswordId(EntityAccess $record)
    {
        if (!($record instanceof UserEntity) && !($record instanceof AuthenticationMappingEntity)) {
            throw new Exception('Record must be an instance of UserEntity or AuthenticationMappingEntity.');
        }

        $confirmationCode = $this->userVerificationRepository->setVerificationCode($record->getUid());

        $params = [
            $record->getUid(),
            $record->getUname(),
            $record->getEmail(),
            $confirmationCode
        ];

        $id = implode($this->delimiter, $params);

        for ($i = 1; $i <= $this->iterations; $i++) {
            $id = base64_encode($id);
        }

        return $id;
    }

    /**
     * Decodes a given link identifier.
     *
     * @param string $identifier
     * @return array The extracted values
     */
    public function decodeLostPasswordId($identifier = '')
    {
        if (empty($identifier)) {
            throw new \Exception('Invalid id in lost password verification helper.');
        }

        $id = $identifier;
        for ($i = 1; $i <= $this->iterations; $i++) {
            $id = base64_decode($id);
        }

        $params = explode($this->delimiter, $id);
        if (count($params) != 4) {
            throw new \Exception('Unexpected extraction results in lost password verification helper.');
        }

        return [
            'userId' => $params[0],
            'userName' => $params[1],
            'emailAddress' => $params[2],
            'confirmationCode' => $params[3]
        ];
    }

    /**
     * Check if confirmation code is neither expired nor invalid.
     *
     * @param integer $userId
     * @param string  $code
     * @return bool True if code is valid, false otherwise
     */
    public function checkConfirmationCode($userId, $code)
    {
        $changePasswordExpireDays = $this->variableApi->get('ZikulaZAuthModule', ZAuthConstant::MODVAR_EXPIRE_DAYS_CHANGE_PASSWORD, ZAuthConstant::DEFAULT_EXPIRE_DAYS_CHANGE_PASSWORD);
        $this->userVerificationRepository->purgeExpiredRecords($changePasswordExpireDays);

        /** @var UserVerificationEntity $userVerificationEntity */
        $userVerificationEntity = $this->userVerificationRepository->findOneBy([
            'uid' => $userId,
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_PWD
        ]);

        if (!isset($userVerificationEntity) || (!\UserUtil::passwordsMatch($code, $userVerificationEntity->getVerifycode()))) {
            return false;
        }

        return true;
    }
}
