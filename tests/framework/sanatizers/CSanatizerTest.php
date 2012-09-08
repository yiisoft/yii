<?php
require_once dirname(__FILE__).'/SanatizeTestModel.php';
class CSanatizerTest extends CTestCase
{
    /**
     * @var CModel
     */
    private $model;
    public function setUp()
    {
        $this->model = new SanatizeTestModel();
        $this->model->foo = "   Some String    ";
        $this->model->bar = "\nRemove Only Left Newline\n";
        $this->model->foobar = "some value";
    }
    /**
     * 
     * @covers CModel::sanatize
     */
    public function testSanatize()
    {
        $this->model->sanatize();
        $this->assertEquals("Some String", $this->model->foo);
        $this->assertEquals("Remove Only Left Newline\n", $this->model->bar);
        $this->assertEquals("succeeded", $this->model->foobar);
    }
}
