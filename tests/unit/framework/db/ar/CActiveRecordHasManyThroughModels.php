<?php
/**
 * Models for CActiveRecordHasManyThroughTest
 */
class User extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'user';
    }

	public function relations() {
        return array(
           'usergroups'=>array(self::HAS_MANY, 'UserGroup', 'user_id'),
           'groups'=>array(self::HAS_MANY, 'Group', 'through'=>'usergroups'),
        );
    }
}

class Group extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'group';
    }

	public function relations() {
        return array(
           'usergroups'=>array(self::HAS_MANY, 'UserGroup', 'group_id'),
           'users'=>array(self::HAS_MANY, 'User', 'through'=>'usergroups'),
        );
    }
}

class UserGroup extends CActiveRecord {
	public static function model($className=__CLASS__) {
        return parent::model($className);
    }

	public function tableName() {
        return 'user_group';
    }

	public function relations() {
        return array(
           'users'=>array(self::BELONGS_TO, 'User', 'user_id'),
           'groups'=>array(self::BELONGS_TO, 'Group', 'group_id'),
        );
    }
}