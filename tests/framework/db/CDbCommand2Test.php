<?php

Yii::import('system.db.CDbConnection');

class CDbCommand2Test extends CTestCase
{
	private $_connection;

	public function setUp()
	{
		if(!extension_loaded('pdo') || !extension_loaded('pdo_sqlite'))
			$this->markTestSkipped('PDO and SQLite extensions are required.');

		$this->_connection=new CDbConnection('sqlite::memory:');
		$this->_connection->active=true;
		$this->_connection->pdoInstance->exec(file_get_contents(dirname(__FILE__).'/data/sqlite.sql'));
	}

	public function tearDown()
	{
		$this->_connection->active=false;
	}

	public function testSelect()
	{
		$command=$this->_connection->createCommand();

		// default
		$command->select();
		$this->assertEquals('*', $command->select);

		// string input
		$command->select('id, username');
		$this->assertEquals('"id", "username"', $command->select);

		// string input with expression
		$command->select('id, count(id) as num');
		$this->assertEquals('id, count(id) as num', $command->select);

		// array input
		$command->select(array('id2', 'username2'));
		$this->assertEquals('"id2", "username2"', $command->select);

		// table prefix and expression
		$command->select(array('user.id', 'count(id) as num', 'profile.*'));
		$this->assertEquals('\'user\'."id", count(id) as num, \'profile\'.*', $command->select);

		// alias
		$command->select(array('id2 as id', 'profile.username2 AS username'));
		$this->assertEquals('"id2" AS "id", \'profile\'."username2" AS "username"', $command->select);

		// getter and setter
		$command->select=array('id2', 'username2');
		$this->assertEquals('"id2", "username2"', $command->select);
	}

	public function testDistinct()
	{
		$command=$this->_connection->createCommand();

		// default value
		$this->assertEquals(false, $command->distinct);

		// select distinct
		$command->selectDistinct('id, username');
		$this->assertEquals(true, $command->distinct);
		$this->assertEquals('"id", "username"', $command->select);

		// getter and setter
		$command->distinct=false;
		$this->assertEquals(false, $command->distinct);
		$command->distinct=true;
		$this->assertEquals(true, $command->distinct);
	}

	public function testFrom()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals('', $command->from);

		// string input
		$command->from('user');
		$this->assertEquals('\'user\'', $command->from);
		$command->from('user, profile');
		$this->assertEquals('\'user\', \'profile\'', $command->from);

		// string input with expression
		$command->from('user, (select * from profile) p');
		$this->assertEquals('user, (select * from profile) p', $command->from);

		// array input
		$command->from(array('user', 'profile'));
		$this->assertEquals('\'user\', \'profile\'', $command->from);

		// table alias, expression, schema
		$command->from(array('user u', '(select * from profile) p', 'public.post'));
		$this->assertEquals('\'user\' \'u\', (select * from profile) p, \'public\'.\'post\'', $command->from);

		// getter and setter
		$command->from=array('user', 'profile');
		$this->assertEquals('\'user\', \'profile\'', $command->from);
	}

	public function testWhere()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals('', $command->where);
		$this->assertEquals(array(), $command->params);

		// string input
		$command->where('id=1 or id=:id2', array(':id2'=>2));
		$this->assertEquals('id=1 or id=:id2', $command->where);
		$this->assertEquals(array(':id2'=>2), $command->params);

		// array input, and/or
		$command->where(array('and', 'id=1', 'id=2'));
		$this->assertEquals('(id=1) AND (id=2)', $command->where);
		$command->where(array('and', 'id=1', array('or', 'id=3', 'id=4'), 'id=2'));
		$this->assertEquals('(id=1) AND ((id=3) OR (id=4)) AND (id=2)', $command->where);

		// empty input
		$command->where(array());
		$this->assertEquals('', $command->where);

		// in, empty
		$command->where(array('in', 'id', array()));
		$this->assertEquals('0=1', $command->where);

		// in
		$command->where(array('in', 'id', array(1,'2',3)));
		$this->assertEquals("\"id\" IN (1, '2', 3)", $command->where);

		// not in, empty
		$command->where(array('not in', 'id', array()));
		$this->assertEquals('', $command->where);

		// not in
		$command->where(array('not in', 'id', array(1,'2',3)));
		$this->assertEquals("\"id\" NOT IN (1, '2', 3)", $command->where);

		// like, string
		$command->where(array('like', 'name', '%tester'));
		$this->assertEquals('"name" LIKE \'%tester\'', $command->where);

		$command->where(array('like', 'name', array('%tester', '%tester2')));
		$this->assertEquals('"name" LIKE \'%tester\' AND "name" LIKE \'%tester2\'', $command->where);

		$command->where(array('not like', 'name', array('tester%', 'tester2%')));
		$this->assertEquals('"name" NOT LIKE \'tester%\' AND "name" NOT LIKE \'tester2%\'', $command->where);

		$command->where(array('or like', 'name', array('%tester', '%tester2')));
		$this->assertEquals('"name" LIKE \'%tester\' OR "name" LIKE \'%tester2\'', $command->where);

		$command->where(array('or not like', 'name', array('%tester', '%tester2')));
		$this->assertEquals('"name" NOT LIKE \'%tester\' OR "name" NOT LIKE \'%tester2\'', $command->where);
	}

	public function testJoin()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals('', $command->join);

		// inner join
		$command->join('user', 'user.id=t.id and id=:id', array(':id'=>1));
		$this->assertEquals(array('JOIN \'user\' ON user.id=t.id and id=:id'), $command->join);
		$this->assertEquals(array(':id'=>1), $command->params);

		// left join
		$join=$command->join;
		$command->leftJoin('user', 'user.id=t.id and id=:id');
		$join[]='LEFT JOIN \'user\' ON user.id=t.id and id=:id';
		$this->assertEquals($join, $command->join);

		// right join
		$command->rightJoin('user', 'user.id=t.id and id=:id');
		$join[]='RIGHT JOIN \'user\' ON user.id=t.id and id=:id';
		$this->assertEquals($join, $command->join);

		// cross join
		$command->crossJoin('user');
		$join[]='CROSS JOIN \'user\'';
		$this->assertEquals($join, $command->join);

		// natural join
		$command->naturalJoin('user');
		$join[]='NATURAL JOIN \'user\'';
		$this->assertEquals($join, $command->join);
	}

	public function testGroup()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals('', $command->group);

		// string input
		$command->group('id, username');
		$this->assertEquals('"id", "username"', $command->group);

		// string input with expression
		$command->group('id, count(id)');
		$this->assertEquals('id, count(id)', $command->group);

		// array input
		$command->group(array('id2', 'username2'));
		$this->assertEquals('"id2", "username2"', $command->group);

		// table prefix and expression
		$command->group(array('user.id', 'count(id)'));
		$this->assertEquals('\'user\'."id", count(id)', $command->group);

		// getter and setter
		$command->group=array('id2', 'username2');
		$this->assertEquals('"id2", "username2"', $command->group);
	}

	public function testHaving()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals('', $command->having);
		$this->assertEquals(array(), $command->params);

		// string input
		$command->having('id=1 or id=:id2', array(':id2'=>2));
		$this->assertEquals('id=1 or id=:id2', $command->having);
		$this->assertEquals(array(':id2'=>2), $command->params);

		// array input, and/or
		$command->having(array('and', 'id=1', 'id=2'));
		$this->assertEquals('(id=1) AND (id=2)', $command->having);
		$command->having(array('and', 'id=1', array('or', 'id=3', 'id=4'), 'id=2'));
		$this->assertEquals('(id=1) AND ((id=3) OR (id=4)) AND (id=2)', $command->having);
	}

	public function testOrder()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals('', $command->order);

		// string input
		$command->order('id, username desc');
		$this->assertEquals('"id", "username" DESC', $command->order);

		// string input with expression
		$command->order('id, count(id) desc');
		$this->assertEquals('id, count(id) desc', $command->order);

		// array input
		$command->order(array('id2 asc', 'username2 DESC'));
		$this->assertEquals('"id2" ASC, "username2" DESC', $command->order);

		// table prefix and expression
		$command->order(array('user.id asc', 'count(id)'));
		$this->assertEquals('\'user\'."id" ASC, count(id)', $command->order);

		// getter and setter
		$command->order=array('id2 asc', 'username2');
		$this->assertEquals('"id2" ASC, "username2"', $command->order);
	}

	public function testLimit()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals(-1, $command->limit);

		$command->limit(10);
		$this->assertEquals(10, $command->limit);

		$command->limit(20,30);
		$this->assertEquals(20, $command->limit);
		$this->assertEquals(30, $command->offset);

		// invalid string
		$command->limit('abc');
		$this->assertEquals(0, $command->limit);
	}

	public function testOffset()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals(-1, $command->offset);

		$command->offset(10);
		$this->assertEquals(10, $command->offset);

		// invalid string
		$command->offset('abc');
		$this->assertEquals(0, $command->offset);
	}

	public function testUnion()
	{
		$command=$this->_connection->createCommand();

		// default
		$this->assertEquals('', $command->union);

		$command->union('select * from user');
		$this->assertEquals(array('select * from user'), $command->union);

		$command->union('select * from post');
		$this->assertEquals(array('select * from user', 'select * from post'), $command->union);
	}

	/*
	public function testInsert()
	{
		$command=$this->_connection->createCommand();

		$command->insert('user', array('id'=>1, 'username'=>'tester'));
		$this->assertEquals('INSERT INTO \'user\' ("id", "username") VALUES (:id, :username)', $command->text);
		$this->assertEquals(array(':id'=>1, ':username'=>'tester'), $command->params);
	}

	public function testUpdate()
	{
		$command=$this->_connection->createCommand();

		$command->update('user', array('id'=>1, 'username'=>'tester'), 'status=:status', array(':status'=>2));
		$this->assertEquals('UPDATE \'user\' SET "id"=:id, "username"=:username WHERE status=:status', $command->text);
		$this->assertEquals(array(':id'=>1, ':username'=>'tester', ':status'=>2), $command->params);
	}

	public function testDelete()
	{
		$command=$this->_connection->createCommand();

		$command->delete('user', 'status=:status', array(':status'=>2));
		$this->assertEquals('DELETE FROM \'user\' WHERE status=:status', $command->text);
		$this->assertEquals(array(':status'=>2), $command->params);
	}
	*/

	public function testQuery()
	{
		// simple query
		$command=$this->_connection->createCommand()
			->select('username, password')
			->from('users')
			->where('email=:email or email=:email2', array(':email'=>'email2', ':email2'=>'email4'))
			->order('username desc')
			->limit(2,1);

		$sql="SELECT \"username\", \"password\"\nFROM 'users'\nWHERE email=:email or email=:email2\nORDER BY \"username\" DESC LIMIT 2 OFFSET 1";
		$this->assertEquals($sql, $command->text);

		$rows=$command->queryAll();
		$this->assertEquals(1,count($rows));
		$this->assertEquals('user2',$rows[0]['username']);
		$this->assertEquals('pass2',$rows[0]['password']);
	}

	public function testArraySyntax()
	{
		$command=$this->_connection->createCommand(array(
			'select'=>'username, password',
			'from'=>'users',
			'where'=>'email=:email or email=:email2',
			'params'=>array(':email'=>'email2', ':email2'=>'email4'),
			'order'=>'username desc',
			'limit'=>2,
			'offset'=>1,
		));

		$sql="SELECT \"username\", \"password\"\nFROM 'users'\nWHERE email=:email or email=:email2\nORDER BY \"username\" DESC LIMIT 2 OFFSET 1";
		$this->assertEquals($sql, $command->text);

		$rows=$command->queryAll();
		$this->assertEquals(1,count($rows));
		$this->assertEquals('user2',$rows[0]['username']);
		$this->assertEquals('pass2',$rows[0]['password']);
	}
}