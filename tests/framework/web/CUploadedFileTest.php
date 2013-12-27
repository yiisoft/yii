<?php

Yii::import('system.web.CUploadedFile');

/**
 * Unit test for "system.web.CUploadedFile".
 * @see CUploadedFile
 */
class CUploadedFileTest extends CTestCase
{
	public function setUp()
	{
		$_FILES=array();
		CUploadedFile::reset();
	}

	public function testGetInstanceByName()
	{
		$inputName='test_name';
		$_FILES[$inputName]=array(
			'name'=>'test_file.dat',
			'type'=>'somemime/type',
			'tmp_name'=>'/tmp/test_file',
			'error'=>UPLOAD_ERR_OK,
			'size'=>100,
		);
		$uploadedFile=CUploadedFile::getInstanceByName($inputName);
		$this->assertTrue(is_object($uploadedFile),'Unable to get uploaded file by name!');
		$this->assertEquals($_FILES[$inputName]['name'],$uploadedFile->getName(),'Wrong name!');
		$this->assertEquals($_FILES[$inputName]['type'],$uploadedFile->getType(),'Wrong type!');
		$this->assertEquals($_FILES[$inputName]['tmp_name'],$uploadedFile->getTempName(),'Wrong temp name!');
		$this->assertEquals($_FILES[$inputName]['error'],$uploadedFile->getError(),'Wrong error!');
		$this->assertEquals($_FILES[$inputName]['size'],$uploadedFile->getSize(),'Wrong size!');
	}

	public function testGetInstancesByName()
	{
		$inputName='test_name';
		$inputCount=3;
		$_FILES[$inputName]=array(
			'name'=>array(),
			'type'=>array(),
			'tmp_name'=>array(),
			'error'=>array(),
			'size'=>array(),
		);
		for($i=0;$i<$inputCount;$i++)
		{
			$_FILES[$inputName]['name'][$i]='test_file_'.$i.'.dat';
			$_FILES[$inputName]['type'][$i]='mime/type'.$i;
			$_FILES[$inputName]['tmp_name'][$i]='/tmp/file'.$i;
			$_FILES[$inputName]['error'][$i]=UPLOAD_ERR_OK;
			$_FILES[$inputName]['size'][$i]=500+$i*10;
		}

		$uploadedFiles=CUploadedFile::getInstancesByName($inputName);
		$this->assertFalse(empty($uploadedFiles),'Unable to get instances by name!');
		for($i=0;$i<$inputCount;$i++)
		{
			$this->assertEquals($_FILES[$inputName]['name'][$i],$uploadedFiles[$i]->getName(),'Wrong name!');
			$this->assertEquals($_FILES[$inputName]['type'][$i],$uploadedFiles[$i]->getType(),'Wrong type!');
			$this->assertEquals($_FILES[$inputName]['tmp_name'][$i],$uploadedFiles[$i]->getTempName(),'Wrong temp name!');
			$this->assertEquals($_FILES[$inputName]['error'][$i],$uploadedFiles[$i]->getError(),'Wrong error!');
			$this->assertEquals($_FILES[$inputName]['size'][$i],$uploadedFiles[$i]->getSize(),'Wrong size!');
		}
	}

	public function testGetInstanceByNestedName()
	{
		$baseInputName='SomeModel';
		$subInputName='test_name';
		$_FILES[$baseInputName]=array(
			'name'=>array(
				$subInputName=>'test_file.dat'
			),
			'type'=>array(
				$subInputName=>'somemime/type'
			),
			'tmp_name'=>array(
				$subInputName=>'/tmp/test_file'
			),
			'error'=>array(
				$subInputName=>UPLOAD_ERR_OK
			),
			'size'=>array(
				$subInputName=>100
			),
		);
		$uploadedFile=CUploadedFile::getInstanceByName("{$baseInputName}[{$subInputName}]");
		$this->assertTrue(is_object($uploadedFile),'Unable to get uploaded file by nested name!');
		$this->assertEquals($_FILES[$baseInputName]['name'][$subInputName],$uploadedFile->getName(),'Wrong name!');
		$this->assertEquals($_FILES[$baseInputName]['type'][$subInputName],$uploadedFile->getType(),'Wrong type!');
		$this->assertEquals($_FILES[$baseInputName]['tmp_name'][$subInputName],$uploadedFile->getTempName(),'Wrong temp name!');
		$this->assertEquals($_FILES[$baseInputName]['error'][$subInputName],$uploadedFile->getError(),'Wrong error!');
		$this->assertEquals($_FILES[$baseInputName]['size'][$subInputName],$uploadedFile->getSize(),'Wrong size!');
	}

	/**
	 * @depends testGetInstancesByName
	 *
	 * @see https://github.com/yiisoft/yii/issues/159
	 */
	public function testGetInstancesByNamePartOfOtherName()
	{
		$baseInputName='base_name';
		$tailedInputName=$baseInputName.'_tail';

		$_FILES[$baseInputName]=array(
			'name'=>$baseInputName.'.dat',
			'type'=>'somemime/'.$baseInputName,
			'tmp_name'=>'/tmp/'.$baseInputName,
			'error'=>UPLOAD_ERR_OK,
			'size'=>100,
		);
		$_FILES[$tailedInputName]=array(
			'name'=>$tailedInputName.'.dat',
			'type'=>'somemime/'.$tailedInputName,
			'tmp_name'=>'/tmp/'.$tailedInputName,
			'error'=>UPLOAD_ERR_OK,
			'size'=>100,
		);

		$uploadedFiles=CUploadedFile::getInstancesByName($baseInputName);
		foreach($uploadedFiles as $uploadedFile)
			$this->assertEquals($_FILES[$baseInputName]['name'],$uploadedFile->getName(),'Wrong file fetched!');
	}
}
