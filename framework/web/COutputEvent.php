<?php
/**
 * COutputEvent class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yeeframework.com/
 * @copyright 2008-2013 Yee Software LLC
 * @license http://www.yeeframework.com/license/
 */

/**
 * COutputEvent represents the parameter for events related with output handling.
 *
 * An event handler may retrieve the captured {@link output} for further processing.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web
 * @since 1.0
 */
class COutputEvent extends CEvent
{
	/**
	 * @var string the output to be processed. The processed output should be stored back to this property.
	 */
	public $output;

	/**
	 * Constructor.
	 * @param mixed $sender sender of the event
	 * @param string $output the output to be processed
	 */
	public function __construct($sender,$output)
	{
		parent::__construct($sender);
		$this->output=$output;
	}
}
