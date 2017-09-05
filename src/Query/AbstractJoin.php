<?php

namespace Happyr\DoctrineSpecification\Query;

use Doctrine\ORM\QueryBuilder;
use Happyr\DoctrineSpecification\Filter\Filter;
use Happyr\DoctrineSpecification\Spec;
use Happyr\DoctrineSpecification\Specification\Specification;

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
     * @param string $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        if ($this->dqlAlias !== null) {
            $dqlAlias = $this->dqlAlias;
        }

        if (!($this->condition instanceof Specification) && $this->condition instanceof Filter) {
            $this->condition = Spec::andX($this->condition);
        }

        $condition = ($this->condition instanceof Specification)
            ? (string)$this->condition->getFilter($qb, $dqlAlias)
            : $this->condition;

        $join = $this->getJoinType();
        if (strpos($this->field, '\\') === false) { // Check if class, as we support directly mapping Entities (handy with inheritance) //TODO: Make this implementation nicer and work across all Spec queries
            $qb->$join(sprintf('%s.%s', $dqlAlias, $this->field), $this->newAlias, 'WITH', $condition);
        }
        else {
            $qb->$join(sprintf('%s', $this->field), $this->newAlias, 'WITH', $condition);
        }
    }
}
