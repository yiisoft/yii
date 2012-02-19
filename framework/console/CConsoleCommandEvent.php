<?php
/**
 * CConsoleCommandEvent class file.
 *
 * @author Evgeny Blinov <e.a.blinov@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CConsoleCommandEvent class.
 *
 * CConsoleCommandEvent represents the event parameters needed by events raised by a console command.
 *
 * @author Evgeny Blinov <e.a.blinov@gmail.com>
 * @package system.console
 * @since 1.1.11
 */
class CConsoleCommandEvent extends CEvent
{
	/**
	 * @var string the action name
	 */
	public $action;
	/**
	 * @var boolean whether the action should be executed.
	 * If this property is set false by the event handler, the console command action will quit after handling this event.
	 * If true, the normal execution cycles will continue, including performing the action and calling
	 * {@link CConsoleCommand::afterAction}.
	 */
	public $stopCommand=false;

	/**
	 * Constructor.
	 * @param mixed $sender sender of the event
	 * @param string $params the parameters to be passed to the action method.
	 * @param string $action the action name
	 */
	public function __construct($sender=null,$params=null,$action=null){
		parent::__construct($sender,$params);
		$this->action=$action;
	}
}