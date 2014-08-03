<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

/**
 * Filter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FilterInterface
{
    /**
     * Inject the query builder
     *
     * @param mixed $queryBuilder
     */
    public function setQueryBuilder($queryBuilder);

    /**
     * This filter supports the field
     *
     * @param string $field
     * @param string $operator
     *
     * @return boolean
     */
    public function supports($field, $operator);
}
