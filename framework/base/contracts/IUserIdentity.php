<?php

declare(strict_types=1);

/**
 * IUserIdentity interface is implemented by a user identity class.
 *
 * An identity represents a way to authenticate a user and retrieve
 * information needed to uniquely identity the user. It is normally
 * used with the {@link CWebApplication::user user application component}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
interface IUserIdentity
{
    /**
     * Authenticates the user.
     * The information needed to authenticate the user
     * are usually provided in the constructor.
     * @return boolean whether authentication succeeds.
     */
    public function authenticate();
    /**
     * Returns a value indicating whether the identity is authenticated.
     * @return boolean whether the identity is valid.
     */
    public function getIsAuthenticated();
    /**
     * Returns a value that uniquely represents the identity.
     * @return mixed a value that uniquely represents the identity (e.g. primary key value).
     */
    public function getId();
    /**
     * Returns the display name for the identity (e.g. username).
     * @return string the display name for the identity.
     */
    public function getName();
    /**
     * Returns the additional identity information that needs to be persistent during the user session.
     * @return array additional identity information that needs to be persistent during the user session (excluding {@link id}).
     */
    public function getPersistentStates();
}

