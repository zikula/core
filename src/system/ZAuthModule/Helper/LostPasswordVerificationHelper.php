<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\ZAuthModule\Helper;

use Exception;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
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
     * @var VariableApiInterface
     */
    private $variableApi;

    /**
     * @var EncoderFactoryInterface
     */
    private $encoderFactory;

    /**
     * Concatenation delimiter
     */
    private $delimiter = '#';

    /**
     * Amount of encoding iterations
     */
    private $iterations = 3;

    public function __construct(
        UserVerificationRepositoryInterface $userVerificationRepository,
        VariableApiInterface $variableApi,
        EncoderFactoryInterface $encoderFactory
    ) {
        $this->userVerificationRepository = $userVerificationRepository;
        $this->variableApi = $variableApi;
        $this->encoderFactory = $encoderFactory;
    }

    /**
     * Creates an identifier for the lost password link.
     * This link carries the user's id, name and email address as well as the actual confirmation code.
     */
    public function createLostPasswordId(AuthenticationMappingEntity $mapping): string
    {
        $confirmationCode = $this->delimiter;
        while (false !== mb_strpos($confirmationCode, $this->delimiter)) {
            $confirmationCode = bin2hex(random_bytes(8));
        }
        $hashedCode = $this->encoderFactory->getEncoder($mapping)->encodePassword($confirmationCode, null);
        $this->userVerificationRepository->setVerificationCode($mapping->getUid(), ZAuthConstant::VERIFYCHGTYPE_PWD, $hashedCode);

        $params = [
            $mapping->getUid(),
            $mapping->getUname(),
            $mapping->getEmail(),
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
     * @throws Exception
     */
    public function decodeLostPasswordId(string $identifier = ''): array
    {
        if (empty($identifier)) {
            throw new Exception('Invalid id in lost password verification helper.');
        }

        $id = $identifier;
        for ($i = 1; $i <= $this->iterations; $i++) {
            $id = base64_decode($id);
        }

        $params = explode($this->delimiter, $id);
        if (4 !== count($params)) {
            throw new Exception('Unexpected extraction results in lost password verification helper.');
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
     */
    public function checkConfirmationCode(int $userId, string $code): bool
    {
        /** @var UserVerificationEntity $userVerificationEntity */
        $userVerificationEntity = $this->userVerificationRepository->findOneBy([
            'uid' => $userId,
            'changetype' => ZAuthConstant::VERIFYCHGTYPE_PWD
        ]);
        $passwordValid = $this->encoderFactory->getEncoder(AuthenticationMappingEntity::class)->isPasswordValid($userVerificationEntity->getVerifycode(), $code, null);

        return isset($userVerificationEntity) && $passwordValid;
    }
}
