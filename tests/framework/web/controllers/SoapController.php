<?php
/**
* Fake SOAP controller for unit tests
* Handle SOAP client requests & generate WSDL.
* 
* NOTE:
* This is a fake controller used only for generating WSDL files.
* Some functionlities are here as an example and may not be fully functional.
* 
* Sample call:
*   $client = new SoapClient('http://1.2.3.4/index.php?r=soap');
* 	$response = $client->connect();
*/
class SoapController extends CController implements IWebServiceProvider
{
	const LOGNAME = 'soap';
	
	public function actions(){
        return array(
        	// remap default action "index" to be a SOAP action
            'index'=>array(
                'class'=>'WebServiceAction',
                'classMap' => array(
                	'SoapPovCalculationInput', 'SoapPovCalculationOutput',
                ),
            ),
        );
    }

    /**
    * Preprocess actions before calling SOAP action.
    * This method must implement interface's abstract method.
    * @param mixed $service
    */
	public function beforeWebMethod($service) {
		// do whatever stuff before executing the action ...
		return true;
	}
    
    /**
    * Postprocess actions after SOAP action executed.
    * This method must implement interface's abstract method.
    * @param mixed $service
    */
    public function afterWebMethod($service) {
		// do whatever stuff after executing the action ...
    	return true;
	}
	
    /**
    * Return TRUE if the SOAP user is authorized.
    */
    protected function isAuthorized($user, $passwordHashed, $action) {
    	// load allowed users..
    	$users = array('tester123' => 'passwordHashed123');

    	if (isset($users[$user]) && $passwordHashed == $users[$user]) {
   			/**
    		* @var CHttpRequest
    		*/
    		$request = Yii::app()->request;
    		$url = $request->getHostInfo() . $request->getUrl();
    		Log::write('['.$user.'] => ['.$action.'] Granted access via ['.$url.']', self::LOGNAME);
    		return true;
		}

    	Log::write('['.$user.'] => ['.$action.'] Failed login with ['.$passwordHashed.']', self::LOGNAME);
    	return false;
	}
	
	/**
	* Check connection to soap server and return current timestamp on success. No authorization required - used simply for seting up the connection via proxy, firewall, http authentication etc...
	* @return string Current timestamp dd.mm.YYYY HH:ii:ss
	* @soap
	*/
	public function connect(){
    	$request = Yii::app()->request;
    	$url = $request->getHostInfo() . $request->getUrl();
   		Log::write( '['.__FUNCTION__.'] OK - Connection succesfull via ['.$url.']', self::LOGNAME);
		return 'OK - connection succesfull at ['.date('d.m.Y H:i:s').']';
	}
	
	/**
    * Return some calculation results for supplied input parameters.
    * 
    * @param string Authorized login username
    * @param string Authorized login password
    * @param SoapPovCalculationInput Calculation input object
    * @return SoapPovCalculationOutput Calculation output object
    * @soap
    */
	public function calculatePov($user, $password, $input) {
		
		if(!$this->isAuthorized($user, $password, __FUNCTION__)){
			throw new SoapFault(null, 'Unauthorized access ['.__FUNCTION__.']!');
		}
		
		$calc = new SoapPovCalculation($input);
		$calc->setPartner($user);
		$output = $calc->calculate();

		return $output;
	}
	
}