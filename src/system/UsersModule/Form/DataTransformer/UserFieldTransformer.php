<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\UsersModule\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zikula\UsersModule\Entity\RepositoryInterface\UserRepositoryInterface;
use Zikula\UsersModule\Entity\UserEntity;

/**
 * User field transformer class.
 *
 * This data transformer treats user fields.
 */
class AbstractUserFieldTransformer implements DataTransformerInterface
{
    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * UserFieldTransformer constructor.
     *
     * @param UserRepositoryInterface $userRepository UserRepository service instance
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Transforms the object values to the normalised value.
     *
     * @param UserEntity|null $value
     *
     * @return int|null
     */
    public function transform($value)
    {
        if (null === $value || !$value) {
            return null;
        }

        if ($value instanceof UserEntity) {
            return $value->getUid();
        }

        return intval($value);
    }

    /**
     * Transforms the form value back to the user entity.
     *
     * @param int $value
     *
     * @return UserEntity|null
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        return $this->userRepository->find($value);
    }
}
