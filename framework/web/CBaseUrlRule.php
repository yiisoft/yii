<?php

/**
 * CBaseUrlRule is the base class for a URL rule class.
 *
 * Custom URL rule classes should extend from this class and implement two methods:
 * {@link createUrl} and {@link parseUrl}.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since   1.1.8
 */
abstract class CBaseUrlRule extends CComponent
{
    /**
     * @var boolean whether this rule will also parse the host info part. Defaults to false.
     */
    public $hasHostInfo = false;

    /**
     * Creates a URL based on this rule.
     *
     * @param CUrlManager $manager   the manager
     * @param string      $route     the route
     * @param array       $params    list of parameters (name=>value) associated with the route
     * @param string      $ampersand the token separating name-value pairs in the URL.
     *
     * @return mixed the constructed URL. False if this rule does not apply.
     */
    abstract public function createUrl($manager, $route, $params, $ampersand);

    /**
     * Parses a URL based on this rule.
     *
     * @param CUrlManager  $manager     the URL manager
     * @param CHttpRequest $request     the request object
     * @param string       $pathInfo    path info part of the URL (URL suffix is already removed based on {@link CUrlManager::urlSuffix})
     * @param string       $rawPathInfo path info that contains the potential URL suffix
     *
     * @return mixed the route that consists of the controller ID and action ID. False if this rule does not apply.
     */
    abstract public function parseUrl($manager, $request, $pathInfo, $rawPathInfo);
}