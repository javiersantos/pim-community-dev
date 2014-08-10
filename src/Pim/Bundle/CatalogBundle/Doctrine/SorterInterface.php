<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

/**
 * Sorter interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SorterInterface
{
    /**
     * Inject the query builder
     *
     * @param mixed $queryBuilder
     */
    public function setQueryBuilder($queryBuilder);
}
