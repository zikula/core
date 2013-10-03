<?php
/**
 * Copyright Zikula Foundation 2009 - Zikula Application Framework
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license GNU/LGPv3 (or at your option any later version).
 * @package Zikula\Core\FilterUtil
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
namespace Zikula\Core\FilterUtil;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * This is the configuration class for all FilterUtil classes.
 */
class Config
{
    /**
     * Doctrine2 QueryBuilder instance
     *
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * Entity name.
     *
     * @var string
     */
    private $entityName;

    /**
     * Entity name.
     *
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Request
     */
    private $request;

    /**
     * Metadata of all Entities.
     * array(alias => MetaObject)
     *
     * @var array
     */
    private $meta;

    /**
     * Root table alias to use.
     *
     * @var string
     */
    private $alias;

    /**
     * number for query parameters.
     *
     * @var int
     */
    protected $paramNumber = 0;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param QueryBuilder  $qb
     * @param Request       $request
     */
    public function __construct(EntityManager $em, QueryBuilder $qb, Request $request)
    {
        $this->entityManager = $em;
        $this->setQueryBuilder($qb);
        $this->request = $request;

        $this->collectMeta();
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * collectMetadata for all Entities
     */
    private function collectMeta()
    {
        $parts = $this->queryBuilder->getDQLParts();

        $entity = reset($parts['from']);
        $this->setEntityName($entity->getFrom());
        $this->setAlias($entity->getAlias());

        $mdf = $this->entityManager->getMetadataFactory();

        $this->meta[$this->alias] = $mdf->getMetadataFor($this->entityName);

        if (isset($parts['join'][$this->alias])) {
            foreach ($parts['join'][$this->alias] as $join) {
                $j = explode('.', $join->getJoin(), 2);
                if (count($j) != 2) {
                    throw new \InvalidArgumentException('Join in wrong format: ' . $join->getJoin());
                }
                if (!isset($this->meta[$j[0]])) {
                    throw new \InvalidArgumentException('Unknown alias in join or wrong order: ' . $join->getJoin());
                }
                if (!isset($this->meta[$j[0]]->associationMappings[$j[1]])) {
                    throw new \InvalidArgumentException('Unknown Mapping in join: ' . $join->getJoin());
                }

                $jEntity = $this->meta[$j[0]]->associationMappings[$j[1]]['targetEntity'];
                $this->meta[$join->getAlias()] = $mdf->getMetadataFor($jEntity);
            }
        }
    }

    /**
     * Sets the Doctrine2 Query Builder
     *
     * @param QueryBuilder $qb
     */
    public function setQueryBuilder(QueryBuilder $qb)
    {
        $this->queryBuilder = $qb;
    }

    /**
     * Gets the Doctrine2 Query Builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * Generate next parameter key
     *
     * @param string $pluginname
     * @param string $fieldname
     *
     * @return string key form :$pluginname_$fieldname_$number
     */
    public function nextUniqueParamkey($pluginname, $fieldname)
    {
        $this->paramNumber++;

        return ':' . $pluginname . '_' . $fieldname . '_' . $this->paramNumber;
    }

    /**
     * Generate next parameter key and
     * add the value to the QueryBuilder
     *
     * @param mixed  $value
     * @param string $pluginname
     * @param string $fieldname
     *
     * @return string key form :$pluginname_$fieldname_$number
     */
    public function toParam($value, $pluginname, $fieldname)
    {
        $paramkey = $this->nextUniqueParamkey($pluginname, $fieldname);
        $this->queryBuilder->setParameter($paramkey, $value);

        return $paramkey;
    }

    /**
     * Sets Entity.
     *
     * @param string $entityName Table name.
     *
     * @return bool true on success, false otherwise.
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * Gets entity name.
     *
     * @return string Entity name.
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * Gets entity name.
     *
     * @return EntityManager EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Sets alias.
     *
     * @param string $alias Entity alias.
     *
     * @return void
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * Gets alias.
     *
     * @return string Entity alias.
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * returns $s with alias in front if there is no .
     *
     *
     * in the string
     * else only $s.
     *
     * @param $s string
     *
     * @return string with alias
     */
    public function addAliasTo($s)
    {
        if (strpos($s, '.') === false)
            return $this->alias . '.' . $s;

        return $s;
    }

    /**
     * Returns $s with alias in front if there is no .
     * in the string else only $s.
     *
     * @param string $field
     *
     * @throws \Exception
     * @return string with alias
     */
    public function testFieldExists($field)
    {
        $parts = explode('.', $field, 2);
        if (count($parts) < 2 || !isset($this->meta[$parts[0]]) ||
             !isset($this->meta[$parts[0]]->fieldMappings[$parts[1]])) {
            throw new \InvalidArgumentException('Unknown Fieldname: ' . $field);
        }
    }
}
