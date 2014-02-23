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

namespace Zikula\Module\SearchModule;

use Zikula\Core\AbstractModule;
use Zikula_ServiceManager;
use Zikula_View;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ZLanguage;
use Zikula\Common\I18n\Translator;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractSearchable extends Translator
{
    const SEARCHABLE = 'searchable';

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var string The module name
     */
    protected $name;

    /**
     * @var \Doctrine\ORM\EntityManager;
     */
    protected $entityManager;

    /**
     * @var Zikula_View
     */
    protected $view;

    /**
     * Constructor.
     *
     * @param Zikula_ServiceManager $serviceManager ServiceManager instance.
     * @param AbstractModule        $bundle
     */
    public function __construct(Zikula_ServiceManager $serviceManager, AbstractModule $bundle)
    {
        $this->setContainer($serviceManager);
        $this->entityManager = $this->getContainer()->get('doctrine.entitymanager');
        $this->name = $bundle->getName();
        $this->view = Zikula_View::getInstance($this->name);
        parent::__construct(ZLanguage::getModuleDomain($this->name));
    }

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Get the Container.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * get the UI options for search form
     *
     * @param $args
     * @return string
     */
    abstract public function getOptions($args);

    /**
     * Get the search results
     * @param $args
     * @return array
     */
    abstract function getResults($args);

    /**
     * Construct a QueryBuilder Where orX|andX Expr instance
     *
     * @param QueryBuilder $qb
     * @param array $args
     * @param array $fields
     * @return null|\Doctrine\ORM\Query\Expr\Composite
     */
    public function formatWhere(QueryBuilder $qb, array $args, array $fields)
    {
        if (empty($args) || !isset($args['q']) || !isset($args['searchtype']) || empty($fields)) {
            return null;
        }
        $method = ($args['searchtype'] == 'EXACT') ? 'andX' : 'orX';
        /** @var $where \Doctrine\ORM\Query\Expr\Composite */
        $where = $qb->expr()->$method();
        foreach ($args['q'] as $word) {
            $subWhere = $qb->expr()->orX();
            foreach ($fields as $field) {
                $expr = $qb->expr()->like($field, $qb->expr()->literal('%' . $word . '%'));
                $subWhere->add($expr);
            }
            $where->add($subWhere);
        }

        return $where;
    }
} 