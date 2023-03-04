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

namespace Zikula\UsersBundle\ProfileBundle;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Zikula\UsersBundle\ProfileBundle\ProfileBundleInterface;

class ProfileBundleCollector
{
    /**
     * @var ProfileBundleInterface[] e.g. [<BundleName> => <ServiceObject>]
     */
    private array $profileBundles = [];

    public function __construct(
        #[TaggedIterator(ProfileBundleInterface::class)]
        iterable $bundles,
        private readonly ?string $currentProfileBundleName
    ) {
        foreach ($bundles as $bundle) {
            $this->add($bundle);
        }
    }

    /**
     * Add a service to the collection.
     */
    public function add(/* ProfileBundleInterface */ $service): void
    {
        $bundleName = $service->getBundleName();
        if ('ZikulaUsersBundle' === $bundleName) {
            return;
        }
        if (isset($this->profileBundles[$bundleName])) {
            throw new InvalidArgumentException('Attempting to register a profile bundle with a duplicate bundle name. (' . $bundleName . ')');
        }
        $this->profileBundles[$bundleName] = $service;
    }

    /**
     * Get a ProfileBundleInterface from the collection by bundle name.
     */
    public function get(string $bundleName): ?ProfileBundleInterface
    {
        return $this->profileBundles[$bundleName] ?? null;
    }

    /**
     * Get all the bundles in the collection.
     *
     * @return ProfileBundleInterface[]
     */
    public function getAll(): iterable
    {
        return $this->profileBundles;
    }

    /**
     * Get an array of service aliases.
     */
    public function getKeys(): array
    {
        return array_keys($this->profileBundles);
    }

    public function getSelected(): ProfileBundleInterface
    {
        if (!empty($this->currentProfileBundleName) && isset($this->profileBundles[$this->currentProfileBundleName])) {
            return $this->profileBundles[$this->currentProfileBundleName];
        }

        return $this->profileBundles['ZikulaUsersBundle'];
    }

    public function getSelectedName(): string
    {
        return $this->currentProfileBundleName ?? 'ZikulaUsersBundle';
    }
}
