<?php

namespace Happyr\DoctrineSpecification;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Happyr\DoctrineSpecification\Filter\Filter;
use Happyr\DoctrineSpecification\Query\QueryModifier;
use Happyr\DoctrineSpecification\Specification\Specification;

/**
 * Extend this abstract class if you want to build a new spec with your domain logic.
 */
abstract class BaseSpecification implements Specification
{
    /**
     * @var string|null dqlAlias
     */
    private $dqlAlias = null;

    private $registeredFilters = [];

    private $filterValues = [];

    /**
     * @param string $dqlAlias
     */
    public function __construct($dqlAlias = null)
    {
        $this->dqlAlias = $dqlAlias;
    }

    /**
     * Return all the specifications.
     *
     * @return Specification
     */
    protected function getSpec()
    {
        return;
    }

    public function registerFilter($filter, BaseSpecification $spec = null)
    {
        $spec = $spec ?: $this;
        $this->registeredFilters[$filter] = $spec;
    }

    public function isFilterRegistered($filter)
    {
        return array_key_exists($filter, $this->registeredFilters);
    }

    public function hasFilterValue($filter)
    {
        return array_key_exists($filter, $this->filterValues);
    }

    public function setFilterValue($filter, $value)
    {
        if ($this->isFilterRegistered($filter)) {
            $this->registeredFilters[$filter]->doSetFilterValue($filter, $value);
            return true;
        }
        else return false;
    }

    public function doSetFilterValue($filter, $value)
    {
        $this->filterValues[$filter] = $value;
    }

    public function getFilterValue($filter)
    {
        if ($this->hasFilterValue($filter)) {
            return $this->filterValues[$filter];
        }

    }

    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     *
     * @return string
     */
    public function getFilter(QueryBuilder $qb, $dqlAlias)
    {
        $spec = $this->getSpec();
        if ($spec instanceof Filter) {
            return $spec->getFilter($qb, $this->getAlias($dqlAlias));
        }

        return;
    }

    /**
     * @param QueryBuilder $qb
     * @param string       $dqlAlias
     */
    public function modify(QueryBuilder $qb, $dqlAlias)
    {
        $spec = $this->getSpec();
        if ($spec instanceof QueryModifier) {
            $spec->modify($qb, $this->getAlias($dqlAlias));
        }
    }

    public function modifyResult(AbstractQuery $query)
    {
        $spec = $this->getSpec();
        if (method_exists($spec, 'modifyResult')) {
            $spec->modifyResult($query);
        }
    }

    /**
     * @param string $dqlAlias
     *
     * @return string
     */
    private function getAlias($dqlAlias)
    {
        if ($this->dqlAlias !== null) {
            return $this->dqlAlias;
        }

        return $dqlAlias;
    }
}
