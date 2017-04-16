<?php

/**
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule\Tests\Api\Fixtures;

use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Core\RouteUrl;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;

class SearchableFoo implements SearchableInterface
{
    /**
     * * {@inheritDoc}
     */
    public function amendForm(FormBuilderInterface $form)
    {
        // TODO: Implement amendForm() method.
    }

    /**
     * * {@inheritDoc}
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        $results = [];
        if (in_array('bar', $words)) {
            $r = $this->getBaseResult();
            $r->setText(sprintf('ZikulaFooModule found using %s', implode(', ', $words)));
            $results[] = $r;
        }

        return $results;
    }

    /**
     * * {@inheritDoc}
     */
    public function getErrors()
    {
        return [];
    }

    private function getBaseResult()
    {
        $r = new SearchResultEntity();
        $r->setCreated(new \DateTime())
            ->setModule('ZikulaFooModule')
            ->setSesid('test')
            ->setTitle('ZikulaFooModule result')
            ->setUrl(RouteUrl::createFromRoute('zikulafoomodule_user_index'));

        return $r;
    }
}