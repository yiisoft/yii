<?php
/**
 * Models for CActiveRecordHasManyThroughTest
 */
class TestUser extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'tbl_user';
    }

	public function relations() {
        return array(
           'usergroups'=>array(self::HAS_MANY, 'UserGroup', 'user_id'),
           'groups'=>array(self::HAS_MANY, 'TestGroup', 'through'=>'usergroups'),
        );
    }
}

class TestGroup extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'tbl_group';
    }

	public function relations() {
        return array(
           'usergroups'=>array(self::HAS_MANY, 'TestUserGroup', 'group_id'),
           'users'=>array(self::HAS_MANY, 'TestUser', 'through'=>'usergroups'),
        );
    }
}

class TestUserGroup extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'tbl_user_group';
    }

	public function relations() {
        return array(
           'users'=>array(self::BELONGS_TO, 'TestUser', 'user_id'),
           'groups'=>array(self::BELONGS_TO, 'TestGroup', 'group_id'),
        );
    }
}