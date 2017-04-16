<?php

/*
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

class SearchableBar implements SearchableInterface
{
    /**
     * * {@inheritdoc}
     */
    public function amendForm(FormBuilderInterface $form)
    {
        // TODO: Implement amendForm() method.
    }

    /**
     * * {@inheritdoc}
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        $results = [];
        if (in_array('top', $words)) {
            $r = $this->getBaseResult();
            $r->setText(sprintf('ZikulaBarModule found using %s', implode(', ', $words)));
            $results[] = $r;
        }

        return $results;
    }

    /**
     * * {@inheritdoc}
     */
    public function getErrors()
    {
        return [];
    }

    private function getBaseResult()
    {
        $r = new SearchResultEntity();
        $r->setCreated(new \DateTime())
            ->setModule('ZikulaBarModule')
            ->setSesid('test')
            ->setTitle('ZikulaBarModule result')
            ->setUrl(RouteUrl::createFromRoute('zikulabarmodule_user_index'));

        return $r;
    }
}
