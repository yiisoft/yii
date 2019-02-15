<?php

declare(strict_types=1);

/**
 * IBehavior interfaces is implemented by all behavior classes.
 *
 * A behavior is a way to enhance a component with additional methods that
 * are defined in the behavior class and not available in the component class.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 */
interface IBehavior
{
    /**
     * Attaches the behavior object to the component.
     *
     * @param CComponent $component the component that this behavior is to be attached to.
     */
    public function attach($component);

    /**
     * Detaches the behavior object from the component.
     *
     * @param CComponent $component the component that this behavior is to be detached from.
     */
    public function detach($component);

    /**
     * @return boolean whether this behavior is enabled
     */
    public function getEnabled();

    /**
     * @param boolean $value whether this behavior is enabled
     */
    public function setEnabled($value);
}