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

abstract class AbstractSearchable implements SearchableInterface
{
    use TranslatorTrait;

    /**
     * @var string the bundle name
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
     * @var \Zikula_View
     * @deprecated remove at Core-2.0
     */
    protected $view;

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
        $this->name = $bundle->getName();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->view = \Zikula_View::getInstance($bundle->getName());
        $this->setTranslator($this->container->get('translator.default'));
        $this->translator->setDomain($bundle->getTranslationDomain());
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getOptions($active, $modVars = null);

    /**
     * {@inheritdoc}
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
    protected function formatWhere(QueryBuilder $qb, array $words, array $fields, $searchtype = 'AND')
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
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }
}
