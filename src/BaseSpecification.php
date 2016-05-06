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

    /**
     * Registers a filter against this spec. Optionally forwards the filter on to another spec.
     * @param $filter
     * @param BaseSpecification|null $spec
     */
    public function registerFilter($filter, BaseSpecification $forwardedSpec = null)
    {
        if (isset($forwardedSpec)) {
            if ($forwardedSpec->hasRegisteredFilter($filter)) {
                $spec = $forwardedSpec->getRegisteredFilter($filter);
            }
            else {
                throw new \Exception('Filter '.$filter.' is not registered on forwarded Spec');
            }
        }
        else {
            $spec = $this;
        }
        $this->registeredFilters[$filter] = $spec;
    }

    /**
     * Register (and forward) all filters from the given spec, into this spec.
     * @param BaseSpecification $forwardedSpec
     */
    public function registerFiltersFromSpec(BaseSpecification $forwardedSpec)
    {
        foreach ($forwardedSpec->getAllRegisteredFilters() as $filter => $spec) {
            $this->registerFilter($filter, $spec);
        }
    }
    
    public function getRegisteredFilter($filter)
    {
        if ($this->isFilterRegistered($filter)) {
            return $this->registeredFilters[$filter];
        }
        else throw new \Exception('Filter '.$filter.' is not registered');
    }

    public function getAllRegisteredFilters()
    {
        return $this->registeredFilters;
    }

    public function hasRegisteredFilter($filter)
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
        }
        else throw new \Exception('Filter '.$filter.' is not registered');
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
        else throw new \Exception('Filter value for '.$filter.' not found');
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
