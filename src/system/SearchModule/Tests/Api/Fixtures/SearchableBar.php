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

namespace Zikula\SearchModule\Tests\Api\Fixtures;

use DateTime;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Core\RouteUrl;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;

class SearchableBar implements SearchableInterface
{
    public function amendForm(FormBuilderInterface $form): void
    {
        // TODO: Implement amendForm() method.
    }

    public function getResults(array $words, string $searchType = 'AND', array $modVars = []): array
    {
        $results = [];
        if (in_array('top', $words, true)) {
            $r = $this->getBaseResult();
            $r->setText(sprintf('ZikulaBarModule found using %s', implode(', ', $words)));
            $results[] = $r;
        }

        return $results;
    }

    public function getErrors(): array
    {
        return [];
    }

    private function getBaseResult(): SearchResultEntity
    {
        $r = new SearchResultEntity();
        $r->setCreated(new DateTime())
            ->setModule($this->getBundleName())
            ->setSesid('test')
            ->setTitle('ZikulaBarModule result')
            ->setUrl(RouteUrl::createFromRoute('zikulabarmodule_user_index'));

        return $r;
    }

    public function getBundleName(): string
    {
        return 'ZikulaBarModule';
    }
}
