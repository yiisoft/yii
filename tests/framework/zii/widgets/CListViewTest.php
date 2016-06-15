<?php
/**
 * CListViewTest
 */
class CListViewTest extends CTestCase
{
	public function testCallbackEncoding()
	{
		$expected = "'beforeAjaxUpdate':function() { /* callback1 */ },'afterAjaxUpdate':function() { /* callback2 */ }";

		$out=$this->getWidgetScript('js:function() { /* callback1 */ }', 'js:function() { /* callback2 */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (js:): ".$out);

		$out=$this->getWidgetScript('function() { /* callback1 */ }', 'function() { /* callback2 */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (w/o js:): ".$out);

		$out=$this->getWidgetScript(new CJavaScriptExpression('function() { /* callback1 */ }'), new CJavaScriptExpression('function() { /* callback2 */ }'));
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (wrap): ".$out);
	}

	private function getWidgetScript($callback1, $callback2)
	{
		Yee::import('zii.widgets.CListView');
		Yee::app()->clientScript->scripts = array();
		ob_start();
		$widget = new CListView(null);
		$widget->beforeAjaxUpdate = $callback1;
		$widget->afterAjaxUpdate = $callback2;
		$widget->itemView = 'dummy';
		$widget->dataProvider = new CArrayDataProvider(array(1, 2, 3));
		$widget->init();
		$widget->registerClientScript();
		Yee::app()->clientScript->render($out);
		ob_end_clean();
		return $out;
	}
}