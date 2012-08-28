<?php
/**
 * CAutoCompleteTest
 */
class CAutoCompleteTest extends CTestCase
{
	public function testCallbackEncoding()
	{
		$expected = "'highlight':function() { /* callback */ }";

		$out=$this->getWidgetScript('js:function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yii::app()->charset)!==false, "Unexpected JavaScript (js:): ".$out);

		$out=$this->getWidgetScript('function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yii::app()->charset)!==false, "Unexpected JavaScript (w/o js:): ".$out);

		$out=$this->getWidgetScript(new CJavaScriptExpression('function() { /* callback */ }'));
		$this->assertTrue(mb_strpos($out,$expected, null, Yii::app()->charset)!==false, "Unexpected JavaScript (wrap): ".$out);
	}

	private function getWidgetScript($callback)
	{
		Yii::app()->clientScript->scripts = array();
		ob_start();
		$widget = new CAutoComplete(null);
		$widget->name = 'test';
		$widget->highlight = $callback;
		$widget->data = array(1, 2, 3);
		$widget->init();
		$widget->run();
		Yii::app()->clientScript->render($out);
		ob_end_clean();
		return $out;
	}
}
