<?php

declare(strict_types=1);

/**
 * IAction is the interface that must be implemented by controller actions.
 *
 * @package system.base
 * @since 1.0
 */
interface IAction
{
    /**
     * @return string id of the action
     */
    public function getId();
    /**
     * @return CController the controller instance
     */
    public function getController();
}

