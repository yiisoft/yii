<?php

declare(strict_types=1);

/**
 * ILogFilter is the interface that must be implemented by log filters.
 *
 * A log filter preprocesses the logged messages before they are handled by a log route.
 * You can attach classes that implement ILogFilter to {@link CLogRoute::$filter}.
 *
 * @package system.logging
 * @since 1.1.11
 */
interface ILogFilter
{
    /**
     * This method should be implemented to perform actual filtering of log messages
     * by working on the array given as the first parameter.
     * Implementation might reformat, remove or add information to logged messages.
     * @param array $logs list of messages. Each array element represents one message
     * with the following structure:
     * array(
     *   [0] => message (string)
     *   [1] => level (string)
     *   [2] => category (string)
     *   [3] => timestamp (float, obtained by microtime(true));
     */
    public function filter(&$logs);
}
