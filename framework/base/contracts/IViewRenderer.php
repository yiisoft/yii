<?php

declare(strict_types=1);

/**
 * IViewRenderer interface is implemented by a view renderer class.
 *
 * A view renderer is {@link CWebApplication::viewRenderer viewRenderer}
 * application component whose wants to replace the default view rendering logic
 * implemented in {@link CBaseController}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
interface IViewRenderer
{
    /**
     * Renders a view file.
     * @param CBaseController $context the controller or widget who is rendering the view file.
     * @param string $file the view file path
     * @param mixed $data the data to be passed to the view
     * @param boolean $return whether the rendering result should be returned
     * @return mixed the rendering result, or null if the rendering result is not needed.
     */
    public function renderFile($context,$file,$data,$return);
}

