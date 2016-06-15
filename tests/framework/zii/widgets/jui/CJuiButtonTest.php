<?php
/**
 * CJuiButtonTest
 */
class CJuiButtonTest extends CTestCase
{
	public function testCallbackEncoding()
	{
		$expected = ".click(function() { /* callback */ });";

		$out=$this->getWidgetScript('js:function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (js:): ".$out);

		$out=$this->getWidgetScript('function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (w/o js:): ".$out);

		$out=$this->getWidgetScript(new CJavaScriptExpression('function() { /* callback */ }'));
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (wrap): ".$out);
	}

	private function getWidgetScript($callback)
	{
		Yee::import('zii.widgets.jui.CJuiButton');
		Yee::app()->clientScript->scripts = array();
		ob_start();
		$widget = new CJuiButton(null);
		$widget->name = 'test';
		$widget->onclick = $callback;
		$widget->init();
		$widget->run();
		Yee::app()->clientScript->render($out);
		ob_end_clean();
		return $out;
	}
}
