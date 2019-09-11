<?php
/**
 * CMultiFileUploadTest
 */
class CMultiFileUploadTest extends CTestCase
{
	public function testCallbackEncoding()
	{
		$expected = "'onFileSelect':function() { /* callback */ }";

		$out=$this->getWidgetScript('js:function() { /* callback */ }');
		$this->assertNotFalse(mb_strpos($out,$expected, null, Yii::app()->charset), "Unexpected JavaScript (js:): ".$out);

		$out=$this->getWidgetScript('function() { /* callback */ }');
		$this->assertNotFalse(mb_strpos($out,$expected, null, Yii::app()->charset), "Unexpected JavaScript (w/o js:): ".$out);

		$out=$this->getWidgetScript(new CJavaScriptExpression('function() { /* callback */ }'));
		$this->assertNotFalse(mb_strpos($out,$expected, null, Yii::app()->charset), "Unexpected JavaScript (wrap): ".$out);
	}

	private function getWidgetScript($callback)
	{
		Yii::app()->clientScript->scripts = array();
		ob_start();
		$widget = new CMultiFileUpload(null);
		$widget->name = 'test';
		$widget->options['onFileSelect'] = $callback;
		$widget->init();
		$widget->run();
        $out = '';
		Yii::app()->clientScript->render($out);
		ob_end_clean();
		return $out;
	}
}
