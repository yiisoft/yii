<?php

class CStatePersisterTest extends CTestCase
{
	public function setUp()
	{
		// clean up runtime directory
		$app=new TestApplication;
		$app->reset();
	}

	public function testLoadSave()
	{
		$app=new TestApplication;
		$sp=$app->statePersister;
		$data=array('123','456','a'=>443);
		$sp->save($data);
		$this->assertEquals($sp->load(),$data);
		// TODO: test with cache on
	}

	public function testStateFile()
	{
		$sp=new CStatePersister;
		$file=dirname(__FILE__).DIRECTORY_SEPARATOR.'state.bin';
		$sp->stateFile=$file;
		$this->assertEquals($sp->stateFile,$file);

		$sp->stateFile=dirname(__FILE__).'/unknown/state.bin';
		$this->setExpectedException('CException');
		$sp->init();
	}
}
