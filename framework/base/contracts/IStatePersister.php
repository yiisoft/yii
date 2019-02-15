<?php

declare(strict_types=1);

/**
 * IStatePersister is the interface that must be implemented by state persister classes.
 *
 * This interface must be implemented by all state persister classes (such as
 * {@link CStatePersister}.
 *
 * @package system.base
 * @since 1.0
 */
interface IStatePersister
{
    /**
     * Loads state data from a persistent storage.
     * @return mixed the state
     */
    public function load();
    /**
     * Saves state data into a persistent storage.
     * @param mixed $state the state to be saved
     */
    public function save($state);
}

