<?php
Yii::import("system.web.CHttpResponse");
/**
 * Tests for the {@link CHttpResponse} class
 * @author Charles Pick
 */
class CHttpResponseTest extends CTestCase {
	/**
	 * Tests formatting a response as JSON, JSONP, XML
	 */
	public function testFormat() {
		$response = new FakeHttpResponse();
		$response->format = "json";
		$response->setData(array("key" => "value"));
		$response->send();
		$i = 1;
		$this->assertEquals($i, $response->sendHeadersCalled);
		$this->assertEquals($i, $response->sendContentCalled);
		$this->assertEquals($i, $response->sendDataCalled);
		$this->assertEquals(0, $response->sendFileCalled);
		$this->assertEquals(0, $response->sendStreamCalled);

		$this->assertEquals('{"key":"value"}',$response->getData());
		$response->setData("string");
		$response->send();
		$i++;
		$this->assertEquals($i, $response->sendHeadersCalled);
		$this->assertEquals($i, $response->sendContentCalled);
		$this->assertEquals($i, $response->sendDataCalled);
		$this->assertEquals(0, $response->sendFileCalled);
		$this->assertEquals(0, $response->sendStreamCalled);

		$this->assertEquals('"string"',$response->getData());

		$response->format = "jsonp";
		$response->setData(array("key" => "value"));
		$thrown = false;
		try {
			$response->send();
		}
		catch(CException $e) {
			$thrown = true;
			$this->assertEquals("Your request is invalid, no JSONP callback specified", $e->getMessage());
		}
		$this->assertTrue($thrown);
		$this->assertEquals($i, $response->sendHeadersCalled);
		$this->assertEquals($i, $response->sendContentCalled);
		$this->assertEquals($i, $response->sendDataCalled);
		$this->assertEquals(0, $response->sendFileCalled);
		$this->assertEquals(0, $response->sendStreamCalled);

		$response->setData(array("key" => "value"));
		$response->setJSONPCallback("callback");
		$response->send();
		$i++;
		$this->assertEquals($i, $response->sendHeadersCalled);
		$this->assertEquals($i, $response->sendContentCalled);
		$this->assertEquals($i, $response->sendDataCalled);
		$this->assertEquals(0, $response->sendFileCalled);
		$this->assertEquals(0, $response->sendStreamCalled);
		$this->assertEquals('callback({"key":"value"})',$response->getData());

		$response->format = "xml";
		$response->setData(array("key" => "value"));
		$response->send();
		$expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<response><key>value</key></response>
XML;
		$i++;
		$this->assertEquals($i, $response->sendHeadersCalled);
		$this->assertEquals($i, $response->sendContentCalled);
		$this->assertEquals($i, $response->sendDataCalled);
		$this->assertEquals(0, $response->sendFileCalled);
		$this->assertEquals(0, $response->sendStreamCalled);
		$this->assertEquals($expected,$response->getData());

		$response->setData(array(
			"key" => array(
				"value1", "value2", "value3"
			),
			"a!key#with@an invalid xml name" => "value"
		));
		$response->send();
		$expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<response><key><item>value1</item><item>value2</item><item>value3</item></key><a_key_with_an_invalid_xml_name>value</a_key_with_an_invalid_xml_name></response>
XML;

		$this->assertEquals($expected,$response->getData());
	}

	/**
	 * Tests capturing the content
	 */
	public function testCapture() {
		$response = new FakeHttpResponse();
		$response->capture();
		echo "I am a fish";
		$response->send();
		$this->assertEquals("I am a fish", $response->getData());
	}
	/**
	 * Tests sending a file to the client
	 */
	public function testSendFile() {
		$response = new FakeHttpResponse();
		$response->setFile(__FILE__);
		$response->send();
		$this->assertEquals(1, $response->sendHeadersCalled);
		$this->assertEquals(1, $response->sendContentCalled);
		$this->assertEquals(1, $response->sendFileCalled);
		$headers = $response->getHeader();
		$this->assertArrayHasKey("Pragma",$headers);
		$this->assertArrayHasKey("Expires",$headers);
		$this->assertArrayHasKey("Cache-Control",$headers);
		$this->assertArrayHasKey("Content-type",$headers);
		$this->assertArrayHasKey("Content-Disposition",$headers);
		$this->assertArrayHasKey("Content-Transfer-Encoding",$headers);
		$this->assertEquals("text/plain",$headers['Content-type']);
		$this->assertEquals('attachment; filename="CHttpResponseTest.php"',$headers['Content-Disposition']);
	}
	/**
	 * Tests sending a file to the client using X-Sendfile
	 */
	public function testXSendFile() {
		$response = new FakeHttpResponse();
		$response->setFile(__FILE__);
		$response->useXSendFile = true;
		$response->send();
		$this->assertEquals(1, $response->sendHeadersCalled);
		$this->assertEquals(1, $response->sendContentCalled);
		$this->assertEquals(1, $response->sendFileCalled);
		$headers = $response->getHeader();
		$this->assertArrayHasKey("X-Sendfile",$headers);
		$this->assertArrayHasKey("Content-type",$headers);
		$this->assertArrayHasKey("Content-Disposition",$headers);
		$this->assertEquals("text/plain",$headers['Content-type']);
		$this->assertEquals('attachment; filename="CHttpResponseTest.php"',$headers['Content-Disposition']);
	}

}

/**
 * A fake class used to test {@link CHttpResponse} without actually sending any data.
 */
class FakeHttpResponse extends CHttpResponse {
	public $sendHeadersCalled = 0;
	public $sendContentCalled = 0;
	public $sendDataCalled = 0;
	public $sendFileCalled = 0;
	public $sendStreamCalled = 0;
	public function send() {
		$this->_isSent = false; // the fake is resendable
		parent::send();
	}
	protected function sendContent() {
		$this->sendContentCalled++;
		parent::sendContent();
	}

	protected function sendData() {
		$this->sendDataCalled++;
	}

	protected function sendHeaders() {
		$this->sendHeadersCalled++;
	}

	protected function sendFile() {
		$this->sendFileCalled++;
	}

	protected function sendStream() {
		$this->sendStreamCalled++;
	}
}