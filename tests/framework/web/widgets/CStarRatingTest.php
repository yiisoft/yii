<?php
/**
 * CStarRatingTest
 */
class CStarRatingTest extends CTestCase
{
	public function testCallbackEncoding()
	{
		$expected = "'callback':function() { /* callback */ }";

		$out=$this->getWidgetScript('js:function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (js:): ".$out);

		$out=$this->getWidgetScript('function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (w/o js:): ".$out);

		$out=$this->getWidgetScript(new CJavaScriptExpression('function() { /* callback */ }'));
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (wrap): ".$out);
	}

	private function getWidgetScript($callback)
	{
		Yee::app()->clientScript->scripts = array();
		ob_start();
		$widget = new CStarRating(null);
		$widget->name = 'test';
		$widget->callback = $callback;
		$widget->init();
		$widget->run();
		Yee::app()->clientScript->render($out);
		ob_end_clean();
		return $out;
	}
}
