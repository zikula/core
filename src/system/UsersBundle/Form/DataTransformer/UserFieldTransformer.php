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

namespace Zikula\UsersBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Zikula\UsersBundle\Entity\User;
use Zikula\UsersBundle\Repository\UserRepositoryInterface;

/**
 * This data transformer treats user fields.
 */
class UserFieldTransformer implements DataTransformerInterface
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    /**
     * Transforms the object values to the normalised value.
     *
     * @param User|null $value
     *
     * @return int|null
     */
    public function transform($value)
    {
        if (null === $value || !$value) {
            return null;
        }

        if ($value instanceof User) {
            return $value->getUid();
        }

        return (int) $value;
    }

    /**
     * Transforms the form value back to the user entity.
     *
     * @param int $value
     *
     * @return User|null
     */
    public function reverseTransform($value): mixed
    {
        if (!$value) {
            return null;
        }

        return $this->userRepository->find($value);
    }
}
