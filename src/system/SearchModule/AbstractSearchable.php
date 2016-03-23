<?php
/**
 * Copyright Zikula Foundation 2014 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPLv3 (or at your option, any later version).
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */

namespace Zikula\SearchModule;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Zikula\Core\AbstractModule;
use Zikula_View;
use ZLanguage;
use Zikula\Common\I18n\Translator;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityManager;

abstract class AbstractSearchable extends Translator
{
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
     * @var Zikula_View
     */
    protected $view;

    /**
     * @var array
     */
    private $errors = array();

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param AbstractModule $bundle
     */
    public function __construct(ContainerInterface $container, AbstractModule $bundle)
    {
        $this->container = $container;
        $this->entityManager = $container->get('doctrine.orm.default_entity_manager');
        $this->name = $bundle->getName();
        $this->view = Zikula_View::getInstance($this->name);
        parent::__construct(ZLanguage::getModuleDomain($this->name));
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
