<?php
require_once dirname(__FILE__) . '/NewModule.php';
require_once dirname(__FILE__) . '/NewApplicationComponent.php';

class CModuleTest extends CTestCase {
	protected $parent;
	protected $mod;
	protected $d;

	public function setUp() {
		$this->parent = new NewModule('root',NULL);
		$this->mod = new NewModule('foo',$this->parent);
		$this->d = dirname(__FILE__);
	}
	public function tearDown() {
		unset($this->parent);
		unset($this->mod);
	}
	public function testGetId() {
		$this->assertEquals('foo',$this->mod->getId());
	}
	public function testSetId() {
		$this->mod->setId('bar');
		$this->assertEquals('bar',$this->mod->getId());
	}
	public function testSetBasePath() {
		$d = dirname($this->d.'/..');
		$this->mod->setBasePath($d);
		$this->assertEquals($d,$this->mod->getBasePath());
	}
	public function testGetBasePath() {
		$this->assertEquals($this->d,$this->mod->getBasePath());
	}
	public function testGetParams() {
		$expected = new CAttributeCollection;
		$expected->caseSensitive = TRUE;
		$this->assertEquals($expected,$this->mod->getParams());
	}
	public function testSetParams() {
		$expected = array('foo' => 'bar');
		$this->mod->setParams($expected);
		$this->assertEquals($expected,$this->mod->getParams()->toArray());
	}
	public function testGetModulePath() {
		$expected = $this->d.DIRECTORY_SEPARATOR.'modules';
		$this->assertEquals($expected,$this->mod->getModulePath());
	}
	public function testSetModulePath() {
		$this->mod->setModulePath($this->d);
		$this->assertEquals($this->d,$this->mod->getModulePath());
	}
	public function testGetParentModule() {
		$this->assertSame($this->parent,$this->mod->getParentModule());
	}
	/**
	 * @depends testGetId
	 */
	public function testGetModule() {
		$p = $this->parent;
		$p->setModulePath($this->d);
		$p->setModules(array('foo' => array('class' => 'NewModule')));
		$this->assertEquals('root/foo',$p->getModule('foo')->getId());
	}
	public function testGetModules() {
		$p = $this->parent;
		$p->setModulePath($this->d);
		$expected = array('foo' => array('class' => 'NewModule'),'bar');
		$p->setModules($expected);
		$expected['bar'] = array('class' => 'bar.BarModule');
		unset($expected[0]);
		$this->assertEquals($expected,$p->getModules());
	}
	public function testGetComponents() {
		$c = new NewApplicationComponent;
		$this->mod->setComponent('foo',$c);
		$this->assertSame(array('foo' => $c),$this->mod->getComponents());
	}
	public function testSetComponents() {
		$expected = array('foo' => new NewApplicationComponent);
		$this->mod->setComponents($expected);
		$this->assertSame($expected,$this->mod->getComponents());
	}
	public function testSetComponentsViaConfig() {
		$this->mod = new NewModule('foo',$this->parent,array(
			'components' => array(
				'bar' => array('class' => 'NewApplicationComponent')
			)
		));
		$this->assertEquals('hello world',$this->mod->bar->getText('hello world'));
	}
	public function testSetAliases() {
		$this->mod->setAliases(array('modules' => $this->d));
		$this->assertEquals($this->d,Yii::getPathOfAlias('modules'));
	}
}
