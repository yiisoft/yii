<?php
/**
 * CGridViewTest
 */
class CGridViewTest extends CTestCase
{
	public function testCallbackEncoding()
	{
		$expected = "'beforeAjaxUpdate':function() { /* callback1 */ },'afterAjaxUpdate':function() { /* callback2 */ },'ajaxUpdateError':function() { /* callback3 */ },'selectionChanged':function() { /* callback4 */ }";

		$out=$this->getWidgetScript(
			'js:function() { /* callback1 */ }',
			'js:function() { /* callback2 */ }',
			'js:function() { /* callback3 */ }',
			'js:function() { /* callback4 */ }'
		);
		$this->assertTrue(mb_strpos($out,$expected, null, Yii::app()->charset)!==false, "Unexpected JavaScript (js:): ".$out);

		$out=$this->getWidgetScript(
			'function() { /* callback1 */ }',
			'function() { /* callback2 */ }',
			'function() { /* callback3 */ }',
			'function() { /* callback4 */ }'
		);
		$this->assertTrue(mb_strpos($out,$expected, null, Yii::app()->charset)!==false, "Unexpected JavaScript (w/o js:): ".$out);

		$out=$this->getWidgetScript(
			new CJavaScriptExpression('function() { /* callback1 */ }'),
			new CJavaScriptExpression('function() { /* callback2 */ }'),
			new CJavaScriptExpression('function() { /* callback3 */ }'),
			new CJavaScriptExpression('function() { /* callback4 */ }')
		);
		$this->assertTrue(mb_strpos($out,$expected, null, Yii::app()->charset)!==false, "Unexpected JavaScript (wrap): ".$out);
	}

	private function getWidgetScript($callback1, $callback2, $callback3, $callback4)
	{
		Yii::import('zii.widgets.grid.CGridView');
		Yii::app()->clientScript->scripts = array();
		ob_start();
		$widget = new CGridView(null);
		$widget->beforeAjaxUpdate = $callback1;
		$widget->afterAjaxUpdate = $callback2;
		$widget->ajaxUpdateError = $callback3;
		$widget->selectionChanged = $callback4;
		$widget->dataProvider = new CArrayDataProvider(array(1, 2, 3));
		$widget->init();
		$widget->registerClientScript();
		Yii::app()->clientScript->render($out);
		ob_end_clean();
		return $out;
	}
}