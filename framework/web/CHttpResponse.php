<?php
/**
 * CHttpResponse class file.
 *
 * @author Charles Pick
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Encapsulates a HTTP response.
 *
 * After creating a response you can either send it explicitly by calling send() or you can return it from your controller action.
 * When a response is returned by an action, the controller will deal with sending it automatically
 *
 * Simple Usage:
 * <pre>
 * $response = new CHttpResponse();
 * $response->data = "Hello World";
 * $response->send(); // sends the response to the server
 * </pre>
 *
 * Adding Headers:
 * <pre>
 * $response = new CHttpResponse();
 * $response->data = "Hello World";
 * $response->addHeader("X-Served-With", "Yii");
 * $response->contentType = "text/plain";
 * $response->lastModified = time();
 * $response->contentDisposition = 'attachment; filename="hello-world.txt"';
 * $response->send();
 * </pre>
 *
 *
 * Rendering a view from within an action:
 * <pre>
 * public function actionIndex() {
 * 	$response = new CHttpResponse();
 * 	$response->render("viewName", array("model" => new Foo));
 * 	return $response; // when actions return responses, the controller will handle sending it
 * }
 * </pre>
 *
 * Capturing Output:
 * <pre>
 * $response = new CHttpResponse();
 * $response->capture(); // start capturing the output
 * echo "Hello World";
 * $response->addHeader("X-Served-With", "Yii");
 * $response->send(); // explicitly sends the output
 * </pre>
 *
 * Serving a file:
 * <pre>
 * $response = new CHttpResponse();
 * $response->file = "/path/to/your/image.jpg";
 * $response->send(); // explicitly sends the output, mime types will be detected automatically
 * </pre>
 *
 * Serving a stream:
 * <pre>
 * $response = new CHttpResponse();
 * $response->contentType = "image/jpeg"; // content type should be set explictly for streams
 * $response->stream = fopen("path/to/your/file.jpg", "r");
 * $response->send();
 * </pre>
 *
 * Serving JSON:
 * <pre>
 * $response = new CHttpResponse();
 * $response->format = "json";
 * $response->data = array(
 * 	"someKey" => "some value"
 * );
 * $response->send();
 * </pre>
 *
 * Serving XML:
 * <pre>
 * $response = new CHttpResponse();
 * $response->format = "xml";
 * $response->data = array(
 * 	"someKey" => "some value"
 * );
 * $response->send();
 * </pre>
 *
 * @property string $cacheControl the cache control header to send
 * @property string $contentDisposition the content disposition header to send
 * @property string $contentType the content type header to send
 * @property string $data the data to send to the client
 * @property string $etag the etag header to send
 * @property string $file the path to the filename to send to the client
 * @property string $jsonpCallback the name of the JavaScript function on the client to wrap JSONP calls in
 * @property integer $lastModified the last modified timestamp to send
 * @property resource $stream the stream to read and send to the client
 *
 *  @author Charles Pick
  * @since 1.1.1
  * @package system.web
 */
class CHttpResponse extends CComponent {
	/**
	 * @var string the format to use when sending the request.
	 * Can be one of:
	 * <li>raw - don't transform the output, this is hte default</li>
	 * <li>json - encode the data as JSON and set the appropriate headers</li>
	 * <li>JSONP - encode the data as JSONP and set the appropriate headers</li>
	 * <li>xml - encode the data as XML and set the appropriate headers</li>
	 *
	 * When this property is set to anything other than "raw", the corresponding
	 * format method on this class will be called, e.g. setting $format to "json" would
	 * call formatJSON() which sets the appropriate headers and encodes {@link $_data} as JSON.
	 */
	public $format = "raw";

	/**
	 * @var boolean whether to use X-Sendfile header when sending files. This is more efficient than reading the file
	 * with readfile() but is not supported on every server
	 */
	public $useXSendFile = false;

	/**
	 * @var string the name of the XML root node to use when {@link $format} is "xml".
	 * Defaults to "response"
	 */
	public $xmlRootNodeName = "response";

	/**
	 * @var string the name of the XML node to use when converting arrays with numerical keys to XML.
	 * Defaults to "item"
	 */
	public $xmlItemNodeName = "item";

	/**
	 * @var string the data to send in this request
	 */
	protected $_data;

	/**
	 * @var boolean whether the data has already been sent or not
	 */
	protected $_isSent = false;

	/**
	 * @var string the path to the file that should be sent, if any
	 */
	protected $_file;
	/**
	 * @var array the headers that will be sent to the client
	 */
	protected $_headers = array();
	/**
	 * @var resource the stream that should be sent
	 */
	protected $_stream;
	/**
	 * @var boolean whether {@link capture()} has been called and whether output buffering is active
	 */
	protected $_captureActive = false;
	/**
	 * @var string the name of the javascript callback function to use when {@link $format} is JSONP.
	 * If not set the request will attempt to use the value of $_GET['callback'], if this is also not set an exception will be thrown
	 */
	protected $_JSONPCallback;
	/**
	 * @param string $name the name of the header
	 * @param string $value the value of the header; if not set, no header with this name will be sent
	 * @param boolean $replace whether an existing header should be replaced, defaults to true
	 */
	public function setHeader($name, $value, $replace = true)
	{
		if ($replace || !isset($this->_headers[$name])) {
			$this->_headers[$name] = $value;
		}
	}

	/**
	 * Gets the header(s) about to be sent
	 * @param string|null $name the name of the header to return, if null all headers will be returned
	 * @return string|array|boolean the header, all the headers or false if no header with the given name can be found
	 */
	public function getHeader($name = null)
	{
		if ($name === null) {
			return $this->_headers;
		}
		if (!isset($this->_headers[$name])) {
			return false;
		}
		return $this->_headers[$name];
	}

	/**
	 * Sets the resource to be sent
	 * @param resource $stream already opened stream from which the data to send will be read
	 */
	public function setStream($stream)
	{
		$this->_stream = $stream;
	}

	/**
	 * Gets the resource to be sent
	 * @return resource already opened stream from which the data to send will be read
	 */
	public function getStream()
	{
		return $this->_stream;
	}

	/**
	 * Gets the path to the file that should be sent
	 * @param string $file the path to the file
	 */
	public function setFile($file)
	{
		$this->_file = $file;
	}

	/**
	 * Gets the path to the file that should be sent
	 * @return string the path to the file
	 */
	public function getFile()
	{
		return $this->_file;
	}

	/**
	 * Sets the current cache control setting to be sent
	 * @param string $cacheControl the cache control header value
	 */
	public function setCacheControl($cacheControl)
	{
		$this->setHeader('Cache-Control',$cacheControl);
	}

	/**
	 * Returns the current cache control setting as a string like sent in a header.
	 * @return string|false the cache control setting or false if there is no such header specified
	 */
	public function getCacheControl()
	{
		return $this->getHeader('Cache-Control');
	}

	/**
	 * Sets the ETag header to be sent
	 * @param string $etag the ETag header
	 */
	public function setEtag($etag)
	{
		$this->setHeader("ETag", $etag);
	}

	/**
	 * Gets the ETag header to be sent
	 * @return string the ETag header, or false if none is set
	 */
	public function getEtag()
	{
		return $this->getHeader("ETag");
	}

	/**
	 * Sets the last modified header to send
	 * @param integer $lastModified the unix time of the last modified date
	 */
	public function setLastModified($lastModified)
	{
		$this->setHeader("Last-Modified",$lastModified);
	}

	/**
	 * Gets the last modified header to send
	 * @return integer|false the last modified header, or fdalse if none is set
	 */
	public function getLastModified()
	{
		return $this->getHeader("Last-Modified");
	}

	/**
	 * Sets the data to send with the request
	 * @param string $data the data to send
	 */
	public function setData($data)
	{
		$this->_data = $data;
	}

	/**
	 * Gets the data to send with the request
	 * @return string the data to send
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Sets the content type header to send
	 * @param string $contentType the content type header
	 */
	public function setContentType($contentType)
	{
		$this->setHeader("Content-type",$contentType);
	}

	/**
	 * Gets the content type header to send
	 * @return string|false the content type header, or false if none is set
	 */
	public function getContentType()
	{
		return $this->getHeader("Content-type");
	}

	/**
	 * Sets the content disposition header to send
	 * @param string $contentDisposition the content disposition header
	 */
	public function setContentDisposition($contentDisposition)
	{
		$this->setHeader("Content-Disposition",$contentDisposition);
	}

	/**
	 * Gets the content disposition header to send
	 * @return string|false the content disposition, or false if none is set
	 */
	public function getContentDisposition()
	{
		return $this->getHeader("Content-Disposition");
	}
	/**
	 * Begins output buffering and captures the content to send as part of this request.
	 */
	public function capture() {
		if ($this->_captureActive) {
			throw new CException(Yii::t('yii',__CLASS__.' already capturing content, cannot start again'));
		}
		$this->_captureActive = true;
		ob_start();
	}
	/**
	 * Renders a view file using the currently active controller and captures the output ready to send.
	 * @see CController::render()
	 * @param string $view the view file to render
	 * @param array|null $data the data to pass to the view
	 */
	public function render($view, $data = null) {
		$this->setData(Yii::app()->controller->render($view, $data,true));
	}

	/**
	 * Sends the response to the client.
	 * @return boolean true if the response was sent
	 */
	public function send() {
		if ($this->_isSent || !$this->beforeSend()) {
			return false;
		}
		if ($this->_captureActive) {
			$this->_data = ob_get_clean();
		}
		$formatter = 'format'.$this->format;
		if (method_exists($this,$formatter)) {
			$this->{$formatter}();
		}
		$this->prepareHeaders();
		$this->sendHeaders();
		$this->sendContent();
		$this->_isSent = true;
		$this->afterSend();
		return true;
	}
	/**
	 * This method is invoked before the response is sent.
	 * The default implementation raises the {@link onBeforeSend} event.
	 * You may override this method to do any preparation work before the response is sent.
	 * Make sure that you call the parent implementation so that the event is raised correctly.
	 * @return boolean whether the response should be sent or not
	 */
	protected function beforeSend() {
		if ($this->hasEventHandler('onBeforeSend')) {
			$event = new CModelEvent($this);
			$this->onBeforeSend($event);
			return $event->isValid;
		}
		else {
			return true;
		}
	}
	/**
	 * This method is invoked after the response is sent.
	 * The default implementation raises the {@link onAfterSend} event.
	 * If you override this method, make sure you call the parent implementation so that the event is raised correctly
	 */
	protected function afterSend() {
		if ($this->hasEventHandler('onAfterSend')) {
			$event = new CModelEvent($this);
			$this->onAfterSend($event);
		}
	}
	/**
	 * This event is raised before a response is sent to the client.
	 * @param CModelEvent $event the event parameter
	 */
	public function onBeforeSend($event) {
		$this->raiseEvent('onBeforeSend',$event);
	}

	/**
	 * This event is raised after a response is sent to the client.
	 * @param CModelEvent $event the event parameter
	 */
	public function onAfterSend($event) {
		$this->raiseEvent('onAfterSend',$event);
	}

	/**
	 * Sets the default headers depending on the type of data to be sent
	 */
	protected function prepareHeaders() {
		if ($this->_file !== null) {
			if ($this->useXSendFile) {
				$this->setHeader('X-Sendfile',realpath($this->_file),false);
				if ($this->getContentType() === false) {
					$mimeType = CFileHelper::getMimeTypeByExtension($this->_file);
					if ($mimeType === null) {
						$mimeType = 'text/plain';
					}
					$this->setContentType($mimeType);
				}
				$this->setHeader('Content-Disposition','attachment; filename="'.basename($this->_file).'"',false);
			}
			else {
				$this->setHeader('Pragma','public',false);
				$this->setHeader('Expires', 0, false);
				$this->setHeader('Cache-Control','must-revalidate, post-check=0, pre-check=0', false);
				if ($this->getContentType() === false) {
					$mimeType = CFileHelper::getMimeTypeByExtension($this->_file);
					if ($mimeType === null) {
						$mimeType = 'text/plain';
					}
					$this->setContentType($mimeType);
				}
				if (ob_get_length() === false && $this->getHeader("Content-Length") === false) {
					$this->setHeader('Content-Length',filesize($this->_file), false);
				}
				$this->setHeader('Content-Disposition','attachment; filename="'.basename($this->_file).'"',false);
				$this->setHeader('Content-Transfer-Encoding','binary', false);
			}
		}
	}
	/**
	 * Sends the response headers to the client
	 */
	protected function sendHeaders() {
		foreach($this->_headers as $header => $value) {
			header($header.": ".$value);
		}
	}
	/**
	 * Sends the response content to the client
	 */
	protected function sendContent() {
		if ($this->_data !== null) {
			$this->sendData();
		}
		elseif($this->_file !== null) {
			$this->sendFile();
		}
		elseif($this->_stream !== null) {
			$this->sendStream();
		}
	}
	/**
	 * Sends the data to the client
	 */
	protected function sendData() {
		echo $this->_data;
	}

	/**
	 * Sends the stream to the client
	 */
	protected function sendStream() {
		while($buffer = fread($this->_stream,4096)) {
			echo $buffer;
		}
	}
	/**
	 * Sends the file to the client
	 */
	protected function sendFile() {
		if (!$this->useXSendFile) {
			readfile($this->_file);
		}
	}
	/**
	 * Formats the response as JSON and sets the appropriate headers
	 */
	protected function formatJSON() {
		$this->setHeader("Content-type","application/json",false);
		$this->_data = function_exists('json_encode') ? json_encode($this->_data) : CJSON::encode($this->_data);
		$this->disableWebLogRoutes();
	}
	/**
	 * Formats the response as JSONP and sets the appropriate headers
	 */
	protected function formatJSONP() {
		$this->setHeader("Content-type","application/json",false);
		$data = function_exists('json_encode') ? json_encode($this->_data) : CJSON::encode($this->_data);
		$callback = $this->getJSONPCallback();
		if ($callback === false) {
			throw new CHttpException(400, Yii::t('yii', 'Your request is invalid, no JSONP callback specified'));
		}
		$this->_data = $callback.'('.$data.')';
		$this->disableWebLogRoutes();
	}
	/**
	 * Formats the responnse as XML and sets the appropriate headers.
	 * Note that unlike JSON, there is no single correct way to structure an XML response based on an
	 * array or object, so this implementation might not meet your requirements exactly.
	 */
	protected function formatXML() {
		$this->setHeader('Content-type','application/xml',false);
		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->startDocument('1.0', Yii::app()->charset);
		$writer->startElement($this->xmlRootNodeName);
		if (!is_array($this->_data) && !is_object($this->_data)) {
			$this->_data = array($this->_data);
		}
		$this->encodeXMLElementInternal($writer,$this->_data);
		$writer->endElement();
		$this->_data = $writer->outputMemory(true);
		$this->disableWebLogRoutes();
	}
	/**
	 * Encodes an array or object to XML
	 * @param XMLWriter $writer the XML writer
	 * @param array|object $data the data to encode to XML
	 */
	private function encodeXMLElementInternal(XMLWriter $writer, $data) {
		foreach($data as $key => $value) {
			if (is_numeric($key)) {
				$key = $this->xmlItemNodeName;
			}
			elseif (!preg_match("/^_?(?!(xml|[_\d\W]))([\w.-]+)$/",$key)) {
				$key = preg_replace("/[^A-Za-z0-9\.\-$]/","_",$key);
			}
			if (is_array($value) || is_object($value)) {
				$writer->startElement($key);
				$this->encodeXMLElementInternal($writer,$value);
				$writer->endElement();
			}
			else {
				$writer->writeElement($key,$value);
			}
		}
	}
	/**
	 * Disables web log route output.
	 * This is called when outputting JSON, JSONP or XML to prevent the data from being corrupted
	 */
	protected function disableWebLogRoutes() {
		if (!isset(Yii::app()->log)) {
			return;
		}
		foreach(Yii::app()->log->routes as $route) {
			if ($route instanceof CWebLogRoute || $route instanceof CProfileLogRoute) {
				$route->enabled = false;
			}
		}
	}
	/**
	 * Sets the name of the JSONP callback function to use
	 * @param string $JSONPCallback the name of the callback function
	 */
	public function setJSONPCallback($JSONPCallback)
	{
		$this->_JSONPCallback = $JSONPCallback;
	}

	/**
	 * Gets the name of the JSONP callback function.
	 * If this is not expicitly set, the value of $_GET['callback'] will be used, if this is also not set false will be returned.
	 * @return string|false the callback name, or false if it cannot be determined
	 */
	public function getJSONPCallback()
	{
		if ($this->_JSONPCallback === null) {
			if (isset($_GET['callback'])) {
				$this->_JSONPCallback = $_GET['callback'];
			}
			else {
				$this->_JSONPCallback = false;
			}
		}
		return $this->_JSONPCallback;
	}

	/**
	 * Determines whether the response has been sent or not
	 * @return boolean true if the response has already been sent to the client
	 */
	public function getIsSent()
	{
		return $this->_isSent;
	}
}
