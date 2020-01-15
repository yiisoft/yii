<?php

class CActiveDataProviderTest extends CTestCase
{
	/**
	 * @var CDbConnection
	 */
	private $db;

	public function setUp(): void
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->db=new CDbConnection('sqlite::memory:');
		$this->db->active=true;
		$this->db->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/../db/data/sqlite.sql'));
		CActiveRecord::$db=$this->db;
	}

	public function tearDown(): void
	{
		$this->db->active=false;
	}

	public function testCountCriteria()
	{
		// 1
		$dataProvider=new CActiveDataProvider('Post',array(
			'criteria'=>array(
				'condition'=>'content LIKE "%content%"',
				'order'=>'create_time DESC',
				'with'=>array('author'),
			),
			'pagination'=>array(
				'pageSize'=>5,
			),
		));
		$this->assertSame($dataProvider->countCriteria,$dataProvider->criteria);
		$this->assertEquals(5,$dataProvider->getTotalItemCount(true));

		// 2
		$dataProvider->setCountCriteria(array(
			'condition'=>'content LIKE "%content 1%"',
		));
		$this->assertNotSame($dataProvider->countCriteria,$dataProvider->criteria);
		$this->assertEquals(1,$dataProvider->getTotalItemCount(true));

		// 3
		$dataProvider->setCountCriteria(array(
			'condition'=>'content LIKE "%content%"',
		));
		$this->assertNotSame($dataProvider->countCriteria,$dataProvider->criteria);
		$this->assertEquals(5,$dataProvider->getTotalItemCount(true));
	}

    /**
     * @test
     */
    public function it_should_provide_phpstan_types(): void
    {
        $model = (static function (): Post {
            $sut = new CActiveDataProvider(Post::model());

            return $sut->model;
        })();
        self::assertInstanceOf(Post::class, $model);

        $model = (static function (): Post {
            $sut = new CActiveDataProvider(Post::model());
            $l = $sut->getData();
            return $l[0];
        })();
        self::assertInstanceOf(Post::class, $model);

        $sut = $this->postsProvider();
        self::assertInstanceOf(Post::class, $sut->model);
	}

    /**
     * @phpstan-return \CActiveDataProvider<\Post>
     */
    private function postsProvider(): CActiveDataProvider
    {
        return new CActiveDataProvider(Post::model());
    }
}
