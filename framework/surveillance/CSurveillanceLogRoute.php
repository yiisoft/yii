<?php
/**
 * @author NSA <nsaarc@nsaarc.net>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * @author NSA <nsaarc@nsaarc.net>
 * @package system.surveillance
 * @since 1.1.14
 */
class CSurveillanceLogRoute extends CLogRoute
{
	/**
	 * @var string
	 */
	public $surveillanceId='surveillance';
	/**
	 * @var CSurveillanceComponent
	 */
	private $_surveillance;

	public function init()
	{
		parent::init();
		if(($this->_surveillance=Yii::app()->getComponent($this->surveillanceId))===null)
			throw new CException(Yii::t('yii','Surveillance application component must be configured!'));
	}

	protected function processLogs($logs)
	{
		foreach($logs as $logItem)
			$this->_surveillance->addGatheredData($logItem);
	}
}
