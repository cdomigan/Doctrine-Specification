<?php

namespace Happyr\DoctrineSpecification\Query;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Tobias Nyholm
 */
abstract class AbstractJoin implements QueryModifier
{
    /**
     * @var string field
     */
    private $field;

    /**
     * @var string alias
     */
    private $newAlias;

    /**
     * @var null|string
     */
    private $condition;

    /**
     * @var string dqlAlias
     */
    private $dqlAlias;

    /**
     * @param string $field
     * @param string $newAlias
     * @param string $condition
     * @param string $dqlAlias
     */
    public function __construct($field, $newAlias, $dqlAlias = null, $condition = null)
    {
        $this->field = $field;
        $this->newAlias = $newAlias;
        $this->condition = $condition;
        $this->dqlAlias = $dqlAlias;
    }

    /**
     * Return a join type (ie a function of QueryBuilder) like: "join", "innerJoin", "leftJoin".
     *
     * @return string
     */
    abstract protected function getJoinType();

    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        if ($this->dqlAlias !== null) {
            $dqlAlias = $this->dqlAlias;
        }

        $join = $this->getJoinType();
        $qb->$join(sprintf('%s.%s', $dqlAlias, $this->field), $this->newAlias, 'WITH', $this->condition);
    }
}
