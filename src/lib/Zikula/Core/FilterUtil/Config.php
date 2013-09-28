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

/**
 * This is the configuration class for all FilterUtil classes.
 */
class Config
{

    /**
     * Doctrine2 Query Builder instance
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
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Metadata of all Entitys.
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
     * Sets parameters each Class could need.
     * Array $args must hold:
     * module: The module name.
     * entity: The entity name.
     * It also may contain:
     * join: The join array in form join=>alias
     * alias: Alias to use with the main entity, default tbl.
     *
     * @param EntityManager $em
     * @param QueryBuilder  $qb
     * @param array         $args Arguments as listed above.
     */
    public function __construct(EntityManager $em, QueryBuilder $qb, $args)
    {
        $this->setQueryBuilder($qb);

        $this->entityManager = $em;

        $this->collectMeta();
    }

    /**
     * collectMetadata for all Entitys
     */
    private function collectMeta()
    {
        $parts = $this->queryBuilder->getDQLParts();

        $entity = reset($parts['from']);
        $this->setEntityName($entity->getFrom());
        $this->setAlias($entity->getAlias());

        $mdf = $this->entityManager->getMetadataFactory();

        $this->meta[$this->alias] = $mdf->getMetadataFor($this->entityName);

        foreach ($parts['join'][$this->alias] as $join) {
            $j = explode('.', $join->getJoin(), 2);
            if (count($j) != 2) {
                throw new \Exception('Join in wrong format: ' . $join->getJoin());
            }
            if (!isset($this->meta[$j[0]])) {
                throw new \Exception('Unknown alias in join or wrong order: ' . $join->getJoin());
            }
            if (!isset($this->meta[$j[0]]->associationMappings[$j[1]])) {
                throw new \Exception('Unknown Mapping in join: ' . $join->getJoin());
            }

            $jEntity = $this->meta[$j[0]]->associationMappings[$j[1]]['targetEntity'];
            $this->meta[$join->getAlias()] = $mdf->getMetadataFor($jEntity);
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
     * @return string key form :$number_$pluginname_$fieldname
     */
    public function nextUniqueParamkey($pluginname, $fieldname)
    {
        $this->paramNumber++;

        return ':' . $this->paramNumber . '_' . $pluginname . '_' . $fieldname;
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
     * @param string $table
     *            Table name.
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
     * @return \Doctrine\ORM\EntityManager EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Sets alias.
     *
     * @param string $alias
     *            Entity alias.
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
     * returns $s with alias in front if there is no .
     * in the string
     * else only $s.
     *
     * @param string $field
     *
     * @return string with alias
     */
    public function testFieldExists($field)
    {
        $parts = explode('.', $field, 2);
        if (count($parts) < 2
            || !isset($this->meta[$parts[0]])
            || !isset($this->meta[$parts[0]]->fieldMappings[$parts[1]])
        ) {
            throw new \Exception('Unknown Fieldname: ' . $field);
        }
    }
}
