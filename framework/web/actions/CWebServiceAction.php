<?php
/**
 * CWebServiceAction class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebServiceAction implements an action that provides Web services.
 *
 * PHP SOAP extension is required for this action.
 *
 * By default, CWebServiceAction will use the current controller as
 * the Web service provider. See {@link CWsdlGenerator} on how to declare
 * methods that can be remotely invoked.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.actions
 * @since 1.0
 */
class CWebServiceAction extends CAction
{
	/**
	 * @var mixed the Web service provider object or class name.
	 * Defaults to null, meaning the current controller is used as the service provider.
	 * If the provider implements the interface {@link IWebServiceProvider},
	 * it will be able to intercept the remote method invocation and perform
	 * additional tasks (e.g. authentication, logging).
	 */
	public $provider;
	/**
	 * @var string the URL for the Web service. Defaults to null, meaning
	 * the URL for this action is used to provide Web services.
	 */
	public $serviceUrl;
	/**
	 * @var string the URL for WSDL. Defaults to null, meaning
	 * the URL for this action is used to serve WSDL document.
	 * In this case, a GET parameter named {@link wsdlVar} will be used to deteremine whether
	 * the current request is for WSDL or Web service.
	 */
	public $wsdlUrl;
	/**
	 * @var string the name of the GET parameter that differentiates a WSDL request
	 * from a Web service request. Defaults to 'wsdl'.
	 */
	public $wsdlVar='wsdl';

	private $_service;


	/**
	 * Runs the action.
	 * If the GET parameter {@link wsdlVar} exists, the action will serve WSDL content;
	 * If not, the action will handle the remote method invocation.
	 */
	public function run()
	{
		$hostInfo=Yii::app()->getRequest()->getHostInfo();
		$controller=$this->getController();
		if(($serviceUrl=$this->serviceUrl)===null)
			$serviceUrl=$hostInfo.$controller->createUrl($this->getId());
		if(($wsdlUrl=$this->wsdlUrl)===null)
			$wsdlUrl=$hostInfo.$controller->createUrl($this->getId(),array($this->wsdlVar=>1));
		if(($provider=$this->provider)===null)
			$provider=$controller;

		$this->_service=$this->createWebService($provider,$wsdlUrl,$serviceUrl);

		if(isset($_GET[$this->wsdlVar]))
			$this->_service->renderWsdl();
		else
			$this->_service->run();

		Yii::app()->terminate();
	}

	/**
	 * Returns the Web service instance currently being used.
	 * @return CWebService the Web service instance
	 */
	public function getService()
	{
		return $this->_service;
	}

	/**
	 * Creates a {@link CWebService} instance.
	 * You may override this method to customize the created instance.
	 * @param mixed the web service provider class name or object
	 * @param string the URL for WSDL.
	 * @param string the URL for the Web service.
	 * @return CWebService the Web service instance
	 */
	protected function createWebService($provider,$wsdlUrl,$serviceUrl)
	{
		return new CWebService($provider,$wsdlUrl,$serviceUrl);
	}
}