<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ORM;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Query\Expr;

/**
 * This class is responsible for building DQL query strings via an object oriented
 * PHP interface.
 *
 * @since 2.0
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class QueryBuilder
{
    /* The query types. */
    const SELECT = 0;
    const DELETE = 1;
    const UPDATE = 2;

    /** The builder states. */
    const STATE_DIRTY = 0;
    const STATE_CLEAN = 1;

    /**
     * @var EntityManager The EntityManager used by this QueryBuilder.
     */
    private $_em;

    /**
     * @var array The array of DQL parts collected.
     */
    private $_dqlParts = array(
        'distinct' => false,
        'select'  => array(),
        'from'    => array(),
        'join'    => array(),
        'set'     => array(),
        'where'   => null,
        'groupBy' => array(),
        'having'  => null,
        'orderBy' => array()
    );

    /**
     * @var integer The type of query this is. Can be select, update or delete.
     */
    private $_type = self::SELECT;

    /**
     * @var integer The state of the query object. Can be dirty or clean.
     */
    private $_state = self::STATE_CLEAN;

    /**
     * @var string The complete DQL string for this query.
     */
    private $_dql;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection The query parameters.
     */
    private $parameters = array();

    /**
     * @var integer The index of the first result to retrieve.
     */
    private $_firstResult = null;

    /**
     * @var integer The maximum number of results to retrieve.
     */
    private $_maxResults = null;

    /**
     * @var array Keeps root entity alias names for join entities.
     */
    private $joinRootAliases = array();

    /**
     * Initializes a new <tt>QueryBuilder</tt> that uses the given <tt>EntityManager</tt>.
     *
     * @param EntityManager $em The EntityManager to use.
     */
    public function __construct(EntityManager $em)
    {
        $this->_em = $em;
        $this->parameters = new ArrayCollection();
    }

    /**
     * Gets an ExpressionBuilder used for object-oriented construction of query expressions.
     * This producer method is intended for convenient inline usage. Example:
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where($qb->expr()->eq('u.id', 1));
     * </code>
     *
     * For more complex expression construction, consider storing the expression
     * builder object in a local variable.
     *
     * @return Query\Expr
     */
    public function expr()
    {
        return $this->_em->getExpressionBuilder();
    }

    /**
     * Get the type of the currently built query.
     *
     * @return integer
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get the associated EntityManager for this query builder.
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    /**
     * Get the state of this query builder instance.
     *
     * @return integer Either QueryBuilder::STATE_DIRTY or QueryBuilder::STATE_CLEAN.
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * Get the complete DQL string formed by the current specifications of this QueryBuilder.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *     echo $qb->getDql(); // SELECT u FROM User u
     * </code>
     *
     * @return string The DQL query string.
     */
    public function getDQL()
    {
        if ($this->_dql !== null && $this->_state === self::STATE_CLEAN) {
            return $this->_dql;
        }

        $dql = '';

        switch ($this->_type) {
            case self::DELETE:
                $dql = $this->_getDQLForDelete();
                break;

            case self::UPDATE:
                $dql = $this->_getDQLForUpdate();
                break;

            case self::SELECT:
            default:
                $dql = $this->_getDQLForSelect();
                break;
        }

        $this->_state = self::STATE_CLEAN;
        $this->_dql   = $dql;

        return $dql;
    }

    /**
     * Constructs a Query instance from the current specifications of the builder.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u');
     *     $q = $qb->getQuery();
     *     $results = $q->execute();
     * </code>
     *
     * @return Query
     */
    public function getQuery()
    {
        $parameters = clone $this->parameters;

        return $this->_em->createQuery($this->getDQL())
            ->setParameters($parameters)
            ->setFirstResult($this->_firstResult)
            ->setMaxResults($this->_maxResults);
    }

    /**
     * Finds the root entity alias of the joined entity.
     *
     * @param string $alias The alias of the new join entity
     * @param string $parentAlias The parent entity alias of the join relationship
     * @return string
     */
    private function findRootAlias($alias, $parentAlias)
    {
        $rootAlias = null;

        if (in_array($parentAlias, $this->getRootAliases())) {
            $rootAlias = $parentAlias;
        } elseif (isset($this->joinRootAliases[$parentAlias])) {
            $rootAlias = $this->joinRootAliases[$parentAlias];
        } else {
            // Should never happen with correct joining order. Might be
            // thoughtful to throw exception instead.
            $rootAlias = $this->getRootAlias();
        }

        $this->joinRootAliases[$alias] = $rootAlias;

        return $rootAlias;
    }

    /**
     * Gets the FIRST root alias of the query. This is the first entity alias involved
     * in the construction of the query.
     *
     * <code>
     * $qb = $em->createQueryBuilder()
     *     ->select('u')
     *     ->from('User', 'u');
     *
     * echo $qb->getRootAlias(); // u
     * </code>
     *
     * @deprecated Please use $qb->getRootAliases() instead.
     * @return string $rootAlias
     */
    public function getRootAlias()
    {
        $aliases = $this->getRootAliases();
        return $aliases[0];
    }

    /**
     * Gets the root aliases of the query. This is the entity aliases involved
     * in the construction of the query.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u');
     *
     *     $qb->getRootAliases(); // array('u')
     * </code>
     *
     * @return array $rootAliases
     */
    public function getRootAliases()
    {
        $aliases = array();

        foreach ($this->_dqlParts['from'] as &$fromClause) {
            if (is_string($fromClause)) {
                $spacePos = strrpos($fromClause, ' ');
                $from     = substr($fromClause, 0, $spacePos);
                $alias    = substr($fromClause, $spacePos + 1);

                $fromClause = new Query\Expr\From($from, $alias);
            }

            $aliases[] = $fromClause->getAlias();
        }

        return $aliases;
    }

    /**
     * Gets the root entities of the query. This is the entity aliases involved
     * in the construction of the query.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u');
     *
     *     $qb->getRootEntities(); // array('User')
     * </code>
     *
     * @return array $rootEntities
     */
    public function getRootEntities()
    {
        $entities = array();

        foreach ($this->_dqlParts['from'] as &$fromClause) {
            if (is_string($fromClause)) {
                $spacePos = strrpos($fromClause, ' ');
                $from     = substr($fromClause, 0, $spacePos);
                $alias    = substr($fromClause, $spacePos + 1);

                $fromClause = new Query\Expr\From($from, $alias);
            }

            $entities[] = $fromClause->getFrom();
        }

        return $entities;
    }

    /**
     * Sets a query parameter for the query being constructed.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = :user_id')
     *         ->setParameter('user_id', 1);
     * </code>
     *
     * @param string|integer $key The parameter position or name.
     * @param mixed $value The parameter value.
     * @param string|null $type PDO::PARAM_* or \Doctrine\DBAL\Types\Type::* constant
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function setParameter($key, $value, $type = null)
    {
        $filteredParameters = $this->parameters->filter(
            function ($parameter) use ($key)
            {
                // Must not be identical because of string to integer conversion
                return ($key == $parameter->getName());
            }
        );

        if (count($filteredParameters)) {
            $parameter = $filteredParameters->first();
            $parameter->setValue($value, $type);

            return $this;
        }

        $parameter = new Query\Parameter($key, $value, $type);

        $this->parameters->add($parameter);

        return $this;
    }

    /**
     * Sets a collection of query parameters for the query being constructed.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = :user_id1 OR u.id = :user_id2')
     *         ->setParameters(new ArrayCollection(array(
     *             new Parameter('user_id1', 1),
     *             new Parameter('user_id2', 2)
              )));
     * </code>
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|array $params The query parameters to set.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function setParameters($parameters)
    {
        // BC compatibility with 2.3-
        if (is_array($parameters)) {
            $parameterCollection = new ArrayCollection();

            foreach ($parameters as $key => $value) {
                $parameter = new Query\Parameter($key, $value);

                $parameterCollection->add($parameter);
            }

            $parameters = $parameterCollection;
        }

        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Gets all defined query parameters for the query being constructed.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection The currently defined query parameters.
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Gets a (previously set) query parameter of the query being constructed.
     *
     * @param mixed $key The key (index or name) of the bound parameter.
     *
     * @return Query\Parameter|null The value of the bound parameter.
     */
    public function getParameter($key)
    {
        $filteredParameters = $this->parameters->filter(
            function ($parameter) use ($key)
            {
                // Must not be identical because of string to integer conversion
                return ($key == $parameter->getName());
            }
        );

        return count($filteredParameters) ? $filteredParameters->first() : null;
    }

    /**
     * Sets the position of the first result to retrieve (the "offset").
     *
     * @param integer $firstResult The first result to return.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function setFirstResult($firstResult)
    {
        $this->_firstResult = $firstResult;

        return $this;
    }

    /**
     * Gets the position of the first result the query object was set to retrieve (the "offset").
     * Returns NULL if {@link setFirstResult} was not applied to this QueryBuilder.
     *
     * @return integer The position of the first result.
     */
    public function getFirstResult()
    {
        return $this->_firstResult;
    }

    /**
     * Sets the maximum number of results to retrieve (the "limit").
     *
     * @param integer $maxResults The maximum number of results to retrieve.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function setMaxResults($maxResults)
    {
        $this->_maxResults = $maxResults;

        return $this;
    }

    /**
     * Gets the maximum number of results the query object was set to retrieve (the "limit").
     * Returns NULL if {@link setMaxResults} was not applied to this query builder.
     *
     * @return integer Maximum number of results.
     */
    public function getMaxResults()
    {
        return $this->_maxResults;
    }

    /**
     * Either appends to or replaces a single, generic query part.
     *
     * The available parts are: 'select', 'from', 'join', 'set', 'where',
     * 'groupBy', 'having' and 'orderBy'.
     *
     * @param string $dqlPartName
     * @param string $dqlPart
     * @param string $append
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function add($dqlPartName, $dqlPart, $append = false)
    {
        $isMultiple = is_array($this->_dqlParts[$dqlPartName]);

        // This is introduced for backwards compatibility reasons.
        // TODO: Remove for 3.0
        if ($dqlPartName == 'join') {
            $newDqlPart = array();

            foreach ($dqlPart as $k => $v) {
                $k = is_numeric($k) ? $this->getRootAlias() : $k;

                $newDqlPart[$k] = $v;
            }

            $dqlPart = $newDqlPart;
        }

        if ($append && $isMultiple) {
            if (is_array($dqlPart)) {
                $key = key($dqlPart);

                $this->_dqlParts[$dqlPartName][$key][] = $dqlPart[$key];
            } else {
                $this->_dqlParts[$dqlPartName][] = $dqlPart;
            }
        } else {
            $this->_dqlParts[$dqlPartName] = ($isMultiple) ? array($dqlPart) : $dqlPart;
        }

        $this->_state = self::STATE_DIRTY;

        return $this;
    }

    /**
     * Specifies an item that is to be returned in the query result.
     * Replaces any previously specified selections, if any.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u', 'p')
     *         ->from('User', 'u')
     *         ->leftJoin('u.Phonenumbers', 'p');
     * </code>
     *
     * @param mixed $select The selection expressions.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function select($select = null)
    {
        $this->_type = self::SELECT;

        if (empty($select)) {
            return $this;
        }

        $selects = is_array($select) ? $select : func_get_args();

        return $this->add('select', new Expr\Select($selects), false);
    }

    /**
     * Add a DISTINCT flag to this query.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->distinct()
     *         ->from('User', 'u');
     * </code>
     *
     * @param bool
     * @return QueryBuilder
     */
    public function distinct($flag = true)
    {
        $this->_dqlParts['distinct'] = (bool) $flag;

        return $this;
    }

    /**
     * Adds an item that is to be returned in the query result.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->addSelect('p')
     *         ->from('User', 'u')
     *         ->leftJoin('u.Phonenumbers', 'p');
     * </code>
     *
     * @param mixed $select The selection expression.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function addSelect($select = null)
    {
        $this->_type = self::SELECT;

        if (empty($select)) {
            return $this;
        }

        $selects = is_array($select) ? $select : func_get_args();

        return $this->add('select', new Expr\Select($selects), true);
    }

    /**
     * Turns the query being built into a bulk delete query that ranges over
     * a certain entity type.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->delete('User', 'u')
     *         ->where('u.id = :user_id');
     *         ->setParameter('user_id', 1);
     * </code>
     *
     * @param string $delete The class/type whose instances are subject to the deletion.
     * @param string $alias The class/type alias used in the constructed query.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function delete($delete = null, $alias = null)
    {
        $this->_type = self::DELETE;

        if ( ! $delete) {
            return $this;
        }

        return $this->add('from', new Expr\From($delete, $alias));
    }

    /**
     * Turns the query being built into a bulk update query that ranges over
     * a certain entity type.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->update('User', 'u')
     *         ->set('u.password', md5('password'))
     *         ->where('u.id = ?');
     * </code>
     *
     * @param string $update The class/type whose instances are subject to the update.
     * @param string $alias The class/type alias used in the constructed query.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function update($update = null, $alias = null)
    {
        $this->_type = self::UPDATE;

        if ( ! $update) {
            return $this;
        }

        return $this->add('from', new Expr\From($update, $alias));
    }

    /**
     * Create and add a query root corresponding to the entity identified by the given alias,
     * forming a cartesian product with any existing query roots.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     * </code>
     *
     * @param string $from   The class name.
     * @param string $alias  The alias of the class.
     * @param string $indexBy The index for the from.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function from($from, $alias, $indexBy = null)
    {
        return $this->add('from', new Expr\From($from, $alias, $indexBy), true);
    }

    /**
     * Creates and adds a join over an entity association to the query.
     *
     * The entities in the joined association will be fetched as part of the query
     * result if the alias used for the joined association is placed in the select
     * expressions.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->join('u.Phonenumbers', 'p', Expr\Join::WITH, 'p.is_primary = 1');
     * </code>
     *
     * @param string $join The relationship to join
     * @param string $alias The alias of the join
     * @param string $conditionType The condition type constant. Either ON or WITH.
     * @param string $condition The condition for the join
     * @param string $indexBy The index for the join
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function join($join, $alias, $conditionType = null, $condition = null, $indexBy = null)
    {
        return $this->innerJoin($join, $alias, $conditionType, $condition, $indexBy);
    }

    /**
     * Creates and adds a join over an entity association to the query.
     *
     * The entities in the joined association will be fetched as part of the query
     * result if the alias used for the joined association is placed in the select
     * expressions.
     *
     *     [php]
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->innerJoin('u.Phonenumbers', 'p', Expr\Join::WITH, 'p.is_primary = 1');
     *
     * @param string $join The relationship to join
     * @param string $alias The alias of the join
     * @param string $conditionType The condition type constant. Either ON or WITH.
     * @param string $condition The condition for the join
     * @param string $indexBy The index for the join
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function innerJoin($join, $alias, $conditionType = null, $condition = null, $indexBy = null)
    {
        $parentAlias = substr($join, 0, strpos($join, '.'));

        $rootAlias = $this->findRootAlias($alias, $parentAlias);

        $join = new Expr\Join(
            Expr\Join::INNER_JOIN, $join, $alias, $conditionType, $condition, $indexBy
        );

        return $this->add('join', array($rootAlias => $join), true);
    }

    /**
     * Creates and adds a left join over an entity association to the query.
     *
     * The entities in the joined association will be fetched as part of the query
     * result if the alias used for the joined association is placed in the select
     * expressions.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->leftJoin('u.Phonenumbers', 'p', Expr\Join::WITH, 'p.is_primary = 1');
     * </code>
     *
     * @param string $join The relationship to join
     * @param string $alias The alias of the join
     * @param string $conditionType The condition type constant. Either ON or WITH.
     * @param string $condition The condition for the join
     * @param string $indexBy The index for the join
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function leftJoin($join, $alias, $conditionType = null, $condition = null, $indexBy = null)
    {
        $parentAlias = substr($join, 0, strpos($join, '.'));

        $rootAlias = $this->findRootAlias($alias, $parentAlias);

        $join = new Expr\Join(
            Expr\Join::LEFT_JOIN, $join, $alias, $conditionType, $condition, $indexBy
        );

        return $this->add('join', array($rootAlias => $join), true);
    }

    /**
     * Sets a new value for a field in a bulk update query.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->update('User', 'u')
     *         ->set('u.password', md5('password'))
     *         ->where('u.id = ?');
     * </code>
     *
     * @param string $key The key/field to set.
     * @param string $value The value, expression, placeholder, etc.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function set($key, $value)
    {
        return $this->add('set', new Expr\Comparison($key, Expr\Comparison::EQ, $value), true);
    }

    /**
     * Specifies one or more restrictions to the query result.
     * Replaces any previously specified restrictions, if any.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = ?');
     *
     *     // You can optionally programatically build and/or expressions
     *     $qb = $em->createQueryBuilder();
     *
     *     $or = $qb->expr()->orx();
     *     $or->add($qb->expr()->eq('u.id', 1));
     *     $or->add($qb->expr()->eq('u.id', 2));
     *
     *     $qb->update('User', 'u')
     *         ->set('u.password', md5('password'))
     *         ->where($or);
     * </code>
     *
     * @param mixed $predicates The restriction predicates.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function where($predicates)
    {
        if ( ! (func_num_args() == 1 && $predicates instanceof Expr\Composite)) {
            $predicates = new Expr\Andx(func_get_args());
        }

        return $this->add('where', $predicates);
    }

    /**
     * Adds one or more restrictions to the query results, forming a logical
     * conjunction with any previously specified restrictions.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.username LIKE ?')
     *         ->andWhere('u.is_active = 1');
     * </code>
     *
     * @param mixed $where The query restrictions.
     * @return QueryBuilder This QueryBuilder instance.
     * @see where()
     */
    public function andWhere($where)
    {
        $where = $this->getDQLPart('where');
        $args  = func_get_args();

        if ($where instanceof Expr\Andx) {
            $where->addMultiple($args);
        } else {
            array_unshift($args, $where);
            $where = new Expr\Andx($args);
        }

        return $this->add('where', $where, true);
    }

    /**
     * Adds one or more restrictions to the query results, forming a logical
     * disjunction with any previously specified restrictions.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->where('u.id = 1')
     *         ->orWhere('u.id = 2');
     * </code>
     *
     * @param mixed $where The WHERE statement
     * @return QueryBuilder $qb
     * @see where()
     */
    public function orWhere($where)
    {
        $where = $this->getDqlPart('where');
        $args  = func_get_args();

        if ($where instanceof Expr\Orx) {
            $where->addMultiple($args);
        } else {
            array_unshift($args, $where);
            $where = new Expr\Orx($args);
        }

        return $this->add('where', $where, true);
    }

    /**
     * Specifies a grouping over the results of the query.
     * Replaces any previously specified groupings, if any.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->groupBy('u.id');
     * </code>
     *
     * @param string $groupBy The grouping expression.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function groupBy($groupBy)
    {
        return $this->add('groupBy', new Expr\GroupBy(func_get_args()));
    }


    /**
     * Adds a grouping expression to the query.
     *
     * <code>
     *     $qb = $em->createQueryBuilder()
     *         ->select('u')
     *         ->from('User', 'u')
     *         ->groupBy('u.lastLogin');
     *         ->addGroupBy('u.createdAt')
     * </code>
     *
     * @param string $groupBy The grouping expression.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function addGroupBy($groupBy)
    {
        return $this->add('groupBy', new Expr\GroupBy(func_get_args()), true);
    }

    /**
     * Specifies a restriction over the groups of the query.
     * Replaces any previous having restrictions, if any.
     *
     * @param mixed $having The restriction over the groups.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function having($having)
    {
        if ( ! (func_num_args() == 1 && ($having instanceof Expr\Andx || $having instanceof Expr\Orx))) {
            $having = new Expr\Andx(func_get_args());
        }

        return $this->add('having', $having);
    }

    /**
     * Adds a restriction over the groups of the query, forming a logical
     * conjunction with any existing having restrictions.
     *
     * @param mixed $having The restriction to append.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function andHaving($having)
    {
        $having = $this->getDqlPart('having');
        $args   = func_get_args();

        if ($having instanceof Expr\Andx) {
            $having->addMultiple($args);
        } else {
            array_unshift($args, $having);
            $having = new Expr\Andx($args);
        }

        return $this->add('having', $having);
    }

    /**
     * Adds a restriction over the groups of the query, forming a logical
     * disjunction with any existing having restrictions.
     *
     * @param mixed $having The restriction to add.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function orHaving($having)
    {
        $having = $this->getDqlPart('having');
        $args   = func_get_args();

        if ($having instanceof Expr\Orx) {
            $having->addMultiple($args);
        } else {
            array_unshift($args, $having);
            $having = new Expr\Orx($args);
        }

        return $this->add('having', $having);
    }

    /**
     * Specifies an ordering for the query results.
     * Replaces any previously specified orderings, if any.
     *
     * @param string $sort The ordering expression.
     * @param string $order The ordering direction.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function orderBy($sort, $order = null)
    {
        $orderBy = ($sort instanceof Expr\OrderBy) ? $sort : new Expr\OrderBy($sort, $order);

        return $this->add('orderBy', $orderBy);
    }

    /**
     * Adds an ordering to the query results.
     *
     * @param string $sort The ordering expression.
     * @param string $order The ordering direction.
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function addOrderBy($sort, $order = null)
    {
        return $this->add('orderBy', new Expr\OrderBy($sort, $order), true);
    }

    /**
     * Get a query part by its name.
     *
     * @param string $queryPartName
     * @return mixed $queryPart
     * @todo Rename: getQueryPart (or remove?)
     */
    public function getDQLPart($queryPartName)
    {
        return $this->_dqlParts[$queryPartName];
    }

    /**
     * Get all query parts.
     *
     * @return array $dqlParts
     * @todo Rename: getQueryParts (or remove?)
     */
    public function getDQLParts()
    {
        return $this->_dqlParts;
    }

    private function _getDQLForDelete()
    {
         return 'DELETE'
              . $this->_getReducedDQLQueryPart('from', array('pre' => ' ', 'separator' => ', '))
              . $this->_getReducedDQLQueryPart('where', array('pre' => ' WHERE '))
              . $this->_getReducedDQLQueryPart('orderBy', array('pre' => ' ORDER BY ', 'separator' => ', '));
    }

    private function _getDQLForUpdate()
    {
         return 'UPDATE'
              . $this->_getReducedDQLQueryPart('from', array('pre' => ' ', 'separator' => ', '))
              . $this->_getReducedDQLQueryPart('set', array('pre' => ' SET ', 'separator' => ', '))
              . $this->_getReducedDQLQueryPart('where', array('pre' => ' WHERE '))
              . $this->_getReducedDQLQueryPart('orderBy', array('pre' => ' ORDER BY ', 'separator' => ', '));
    }

    private function _getDQLForSelect()
    {
        $dql = 'SELECT'
             . ($this->_dqlParts['distinct']===true ? ' DISTINCT' : '')
             . $this->_getReducedDQLQueryPart('select', array('pre' => ' ', 'separator' => ', '));

        $fromParts   = $this->getDQLPart('from');
        $joinParts   = $this->getDQLPart('join');
        $fromClauses = array();

        // Loop through all FROM clauses
        if ( ! empty($fromParts)) {
            $dql .= ' FROM ';

            foreach ($fromParts as $from) {
                $fromClause = (string) $from;

                if ($from instanceof Expr\From && isset($joinParts[$from->getAlias()])) {
                    foreach ($joinParts[$from->getAlias()] as $join) {
                        $fromClause .= ' ' . ((string) $join);
                    }
                }

                $fromClauses[] = $fromClause;
            }
        }

        $dql .= implode(', ', $fromClauses)
              . $this->_getReducedDQLQueryPart('where', array('pre' => ' WHERE '))
              . $this->_getReducedDQLQueryPart('groupBy', array('pre' => ' GROUP BY ', 'separator' => ', '))
              . $this->_getReducedDQLQueryPart('having', array('pre' => ' HAVING '))
              . $this->_getReducedDQLQueryPart('orderBy', array('pre' => ' ORDER BY ', 'separator' => ', '));

        return $dql;
    }

    private function _getReducedDQLQueryPart($queryPartName, $options = array())
    {
        $queryPart = $this->getDQLPart($queryPartName);

        if (empty($queryPart)) {
            return (isset($options['empty']) ? $options['empty'] : '');
        }

        return (isset($options['pre']) ? $options['pre'] : '')
             . (is_array($queryPart) ? implode($options['separator'], $queryPart) : $queryPart)
             . (isset($options['post']) ? $options['post'] : '');
    }

    /**
     * Reset DQL parts
     *
     * @param array $parts
     * @return QueryBuilder
     */
    public function resetDQLParts($parts = null)
    {
        if (is_null($parts)) {
            $parts = array_keys($this->_dqlParts);
        }

        foreach ($parts as $part) {
            $this->resetDQLPart($part);
        }

        return $this;
    }

    /**
     * Reset single DQL part
     *
     * @param string $part
     * @return QueryBuilder;
     */
    public function resetDQLPart($part)
    {
        $this->_dqlParts[$part] = is_array($this->_dqlParts[$part]) ? array() : null;
        $this->_state           = self::STATE_DIRTY;

        return $this;
    }

    /**
     * Gets a string representation of this QueryBuilder which corresponds to
     * the final DQL query being constructed.
     *
     * @return string The string representation of this QueryBuilder.
     */
    public function __toString()
    {
        return $this->getDQL();
    }

    /**
     * Deep clone of all expression objects in the DQL parts.
     *
     * @return void
     */
    public function __clone()
    {
        foreach ($this->_dqlParts as $part => $elements) {
            if (is_array($this->_dqlParts[$part])) {
                foreach ($this->_dqlParts[$part] as $idx => $element) {
                    if (is_object($element)) {
                        $this->_dqlParts[$part][$idx] = clone $element;
                    }
                }
            } else if (is_object($elements)) {
                $this->_dqlParts[$part] = clone $elements;
            }
        }

        $parameters = array();

        foreach ($this->parameters as $parameter) {
            $parameters[] = clone $parameter;
        }

        $this->parameters = new ArrayCollection($parameters);
    }
}
