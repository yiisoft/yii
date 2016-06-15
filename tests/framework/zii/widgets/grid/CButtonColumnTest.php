<?php
/**
 * CButtonColumnTest
 */
class CButtonColumnTest extends CTestCase
{
	public function testCallbackEncoding()
	{
		$expected = "jQuery(document).on('click','#grid1 a.view',function() { /* callback */ });";

		$out=$this->getWidgetScript('js:function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (js:): ".$out);

		$out=$this->getWidgetScript('function() { /* callback */ }');
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (w/o js:): ".$out);

		$out=$this->getWidgetScript(new CJavaScriptExpression('function() { /* callback */ }'));
		$this->assertTrue(mb_strpos($out,$expected, null, Yee::app()->charset)!==false, "Unexpected JavaScript (wrap): ".$out);
	}

	private function getWidgetScript($callback)
	{
		Yee::import('zii.widgets.grid.CButtonColumn');
		Yee::app()->clientScript->scripts = array();
		ob_start();
		$grid = new stdClass();
		$grid->id = 'grid1';
		$widget = $this->getMock('CButtonColumn', array('initDefaultButtons'), array($grid));
		$widget->buttons = array(
			'view' => array(
				'click'=>$callback,
			),
		);
		$widget->init();
		Yee::app()->clientScript->render($out);
		ob_end_clean();
		return $out;
	}
}
