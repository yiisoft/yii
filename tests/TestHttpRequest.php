<?php

Yii::import('system.web.CHttpRequest');

class TestHttpRequest extends CHttpRequest
{
	private $myPathInfo;
	private $myScriptUrl;

	public function getScriptUrl()
	{
		return $this->myScriptUrl;
	}

	public function setScriptUrl($value)
	{
		$this->myScriptUrl=$value;
	}

	public function getPathInfo()
	{
		return $this->myPathInfo;
	}

	public function setPathInfo($value)
	{
		$this->myPathInfo=$value;
	}
}
