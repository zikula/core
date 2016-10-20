<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\GroupsModule\Entity\RepositoryInterface;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Zikula\Common\Translator\TranslatorInterface;

interface GroupRepositoryInterface extends ObjectRepository, Selectable
{
    public function setTranslator(TranslatorInterface $translator);

    public function findAllAndIndexBy($indexField);

    public function getGroupNamesById($includeAll = true, $includeUnregistered = true);
}
