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

namespace Zikula\ProfileModule\Bridge;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use function Symfony\Component\String\s;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\ProfileModule\Helper\GravatarHelper;
use Zikula\ProfileModule\ProfileConstant;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\UserEntity;
use Zikula\UsersModule\ProfileModule\ProfileModuleInterface;
use Zikula\UsersModule\Repository\UserRepositoryInterface;

class ProfileModuleBridge implements ProfileModuleInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly RequestStack $requestStack,
        private readonly VariableApiInterface $variableApi,
        private readonly CurrentUserApiInterface $currentUser,
        private readonly UserRepositoryInterface $userRepository,
        private readonly GravatarHelper $gravatarHelper,
        private readonly string $projectDir,
        private readonly string $prefix
    ) {
    }

    public function getDisplayName($uid = null): string
    {
        $userEntity = $this->findUser($uid);
        if (!$userEntity) {
            throw new InvalidArgumentException('Invalid UID provided');
        }

        $key = $this->prefix . ':' . ProfileConstant::ATTRIBUTE_NAME_DISPLAY_NAME;
        if ($userEntity->getAttributes()->containsKey($key)) {
            return $userEntity->getAttributes()->get($key)->getValue();
        }

        return $userEntity->getUname();
    }

    public function getProfileUrl($uid = null): string
    {
        $userEntity = $this->findUser($uid);
        if (!$userEntity) {
            throw new InvalidArgumentException('Invalid UID provided');
        }

        return $this->router->generate('zikulaprofilemodule_profile_display', ['uid' => $userEntity->getUid()]);
    }

    public function getAvatar($uid = null, array $parameters = []): string
    {
        $userEntity = $this->findUser($uid);
        if (!$userEntity) {
            throw new InvalidArgumentException('Invalid UID provided');
        }

        $gravatarImage = $this->variableApi->get(UsersConstant::MODNAME, ProfileConstant::MODVAR_GRAVATAR_IMAGE, ProfileConstant::DEFAULT_GRAVATAR_IMAGE);
        $avatarPath = $this->variableApi->get(UsersConstant::MODNAME, ProfileConstant::MODVAR_AVATAR_IMAGE_PATH, ProfileConstant::DEFAULT_AVATAR_IMAGE_PATH);
        $allowGravatars = (bool) $this->variableApi->get(UsersConstant::MODNAME, ProfileConstant::MODVAR_GRAVATARS_ENABLED, ProfileConstant::DEFAULT_GRAVATARS_ENABLED);

        $userAttributes = $userEntity->getAttributes();
        $key = $this->prefix . ':avatar';
        $avatar = $userAttributes[$key] ? $userAttributes[$key]->getValue() : $gravatarImage;

        $avatarUrl = '';
        if (!in_array($avatar, ['blank.gif', 'blank.jpg'], true)) {
            if (isset($avatar) && !empty($avatar) && $avatar !== $gravatarImage && file_exists($this->projectDir . '/' . $avatarPath . '/' . $avatar)) {
                $request = $this->requestStack->getCurrentRequest();
                if (null !== $request) {
                    $avatarPath = s($avatarPath)->after('public/')->toString();
                    $avatarUrl = $request->getSchemeAndHttpHost() . $request->getBasePath() . '/' . $avatarPath . '/' . $avatar;
                }
            } elseif (true === $allowGravatars) {
                $parameters = $this->squareSize($parameters);
                $avatarUrl = $this->gravatarHelper->getGravatarUrl($userEntity->getEmail(), $parameters);
            }
        }

        if (empty($avatarUrl)) {
            // e.g. blank.gif or empty avatars
            return '';
        }

        if (!isset($parameters['class'])) {
            $parameters['class'] = 'img-fluid img-thumbnail';
        }
        $attributes = ' class="' . str_replace('"', '', htmlspecialchars($parameters['class'])) . '"';
        $attributes .= isset($parameters['width']) ? ' width="' . (int) $parameters['width'] . '"' : '';
        $attributes .= isset($parameters['height']) ? ' height="' . (int) $parameters['height'] . '"' : '';

        $result = '<img src="' . str_replace('"', '', htmlspecialchars($avatarUrl)) . '" title="' . str_replace('"', '', htmlspecialchars($userEntity->getUname())) . '" alt="' . str_replace('"', '', htmlspecialchars($userEntity->getUname())) . '"' . $attributes . ' />';

        return $result;
    }

    /**
     * Finds a certain user based on either it's id or it's name.
     *
     * @param int|string $uid The user's id or name
     */
    private function findUser($uid = null): ?UserEntity
    {
        if (empty($uid) && $this->currentUser->isLoggedIn()) {
            $uid = $this->currentUser->get('uid');
        }
        if (is_numeric($uid)) {
            return $this->userRepository->find($uid);
        }

        // select user id by user name
        $results = $this->userRepository->searchActiveUser(['operator' => '=', 'operand' => $uid], 1);
        if (!count($results)) {
            return null;
        }

        return $results[0];
    }

    /**
     * Checks and updates the avatar image size parameters.
     */
    private function squareSize(array $parameters = []): array
    {
        if (!isset($parameters['size'])) {
            if (isset($parameters['width']) || isset($parameters['height'])) {
                $hasWidth = isset($parameters['width']);
                $hasHeight = isset($parameters['height']);
                if (($hasWidth && !$hasHeight) || ($hasWidth && $hasHeight && $parameters['width'] < $parameters['height'])) {
                    $parameters['size'] = $parameters['width'];
                } elseif ((!$hasWidth && $hasHeight) || ($hasWidth && $hasHeight && $parameters['width'] > $parameters['height'])) {
                    $parameters['size'] = $parameters['height'];
                } else {
                    $parameters['size'] = 80;
                }
            } else {
                $parameters['size'] = 80;
            }
        }
        $parameters['width'] = $parameters['size'];
        $parameters['height'] = $parameters['size'];

        return $parameters;
    }

    public function getBundleName(): string
    {
        return 'ZikulaProfileModule';
    }
}
