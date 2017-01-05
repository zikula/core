<?php

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula Foundation - http://zikula.org/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\SearchModule;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Common\Translator\TranslatorTrait;
use Zikula\Core\AbstractModule;

abstract class AbstractSearchable
{
    use TranslatorTrait;

    const SEARCHABLE = 'searchable';

    /**
     * @var string The module name
     */
    protected $name;

    /**
     * @var EntityManager;
     */
    protected $entityManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param AbstractModule $bundle
     */
    public function __construct(ContainerInterface $container, AbstractModule $bundle)
    {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->name = $bundle->getName();
        $this->setTranslator($this->container->get('translator.default'));
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * get the UI options for search form
     *
     * @param boolean $active if the module should be checked as active
     * @param array|null $modVars module form vars as previously set
     * @return string
     */
    abstract public function getOptions($active, $modVars = null);

    /**
     * Get the search results
     *
     * @param array $words array of words to search for
     * @param string $searchType AND|OR|EXACT
     * @param array|null $modVars module form vars passed though
     * @return array
     */
    abstract public function getResults(array $words, $searchType = 'AND', $modVars = null);

    /**
     * Construct a QueryBuilder Where orX|andX Expr instance
     *
     * @param QueryBuilder $qb
     * @param array $words the words to query for
     * @param array $fields
     * @param string $searchtype AND|OR|EXACT
     * @return null|\Doctrine\ORM\Query\Expr\Composite
     */
    public function formatWhere(QueryBuilder $qb, array $words, array $fields, $searchtype = 'AND')
    {
        if (empty($words) || empty($fields)) {
            return null;
        }
        $method = ($searchtype == 'OR') ? 'orX' : 'andX';
        /** @var $where \Doctrine\ORM\Query\Expr\Composite */
        $where = $qb->expr()->$method();
        $i = 1;
        foreach ($words as $word) {
            $subWhere = $qb->expr()->orX();
            foreach ($fields as $field) {
                $expr = $qb->expr()->like($field, "?$i");
                $subWhere->add($expr);
                $qb->setParameter($i, '%' . $word . '%');
                $i++;
            }
            $where->add($subWhere);
        }

        return $where;
    }

    /**
     * @param array $error
     */
    public function addError($error)
    {
        $this->errors[] = $this->name . ': ' . $error;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }
}
