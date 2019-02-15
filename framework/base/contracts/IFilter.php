<?php

declare(strict_types=1);

/**
 * IFilter is the interface that must be implemented by action filters.
 *
 * @package system.base
 * @since 1.0
 */
interface IFilter
{
    /**
     * Performs the filtering.
     * This method should be implemented to perform actual filtering.
     * If the filter wants to continue the action execution, it should call
     * <code>$filterChain->run()</code>.
     * @param CFilterChain $filterChain the filter chain that the filter is on.
     */
    public function filter($filterChain);
}

