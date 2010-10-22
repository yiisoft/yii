<?php
/**
 * CDbAuthManager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbAuthManager represents an authorization manager that stores authorization information in database.
 *
 * The database connection is specified by {@link connectionID}. And the database schema
 * should be as described in "framework/web/auth/schema.sql". You may change the names of
 * the three tables used to store the authorization data by setting {@link itemTable},
 * {@link itemChildTable} and {@link assignmentTable}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.auth
 * @since 1.0
 */
class CDbAuthManager extends CAuthManager
{
	/**
	 * @var string the ID of the {@link CDbConnection} application component. Defaults to 'db'.
	 * The database must have the tables as declared in "framework/web/auth/schema.sql".
	 */
	public $connectionID='db';
	/**
	 * @var string the name of the table storing authorization items. Defaults to 'AuthItem'.
	 */
	public $itemTable='AuthItem';
	/**
	 * @var string the name of the table storing authorization item hierarchy. Defaults to 'AuthItemChild'.
	 */
	public $itemChildTable='AuthItemChild';
	/**
	 * @var string the name of the table storing authorization item assignments. Defaults to 'AuthAssignment'.
	 */
	public $assignmentTable='AuthAssignment';
	/**
	 * @var CDbConnection the database connection. By default, this is initialized
	 * automatically as the application component whose ID is indicated as {@link connectionID}.
	 */
	public $db;

	private $_usingSqlite;

	/**
	 * Initializes the application component.
	 * This method overrides the parent implementation by establishing the database connection.
	 */
	public function init()
	{
		parent::init();

		$this->getDbConnection()->setActive(true);
		$this->_usingSqlite=!strncmp($this->getDbConnection()->getDriverName(),'sqlite',6);
	}

	/**
	 * Performs access check for the specified user.
	 * @param string $itemName the name of the operation that need access check
	 * @param mixed $userId the user ID. This should can be either an integer and a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by the user.
	 */
	public function checkAccess($itemName,$userId,$params=array())
	{
		$assignments=$this->getAuthAssignments($userId);
		return $this->checkAccessRecursive($itemName,$userId,$params,$assignments);
	}

	/**
	 * Performs access check for the specified user.
	 * This method is internally called by {@link checkAccess}.
	 * @param string $itemName the name of the operation that need access check
	 * @param mixed $userId the user ID. This should can be either an integer and a string representing
	 * the unique identifier of a user. See {@link IWebUser::getId}.
	 * @param array $params name-value pairs that would be passed to biz rules associated
	 * with the tasks and roles assigned to the user.
	 * @param array $assignments the assignments to the specified user
	 * @return boolean whether the operations can be performed by the user.
	 * @since 1.1.3
	 */
	protected function checkAccessRecursive($itemName,$userId,$params,$assignments)
	{
		if(($item=$this->getAuthItem($itemName))===null)
			return false;
		Yii::trace('Checking permission "'.$item->getName().'"','system.web.auth.CDbAuthManager');
		if($this->executeBizRule($item->getBizRule(),$params,$item->getData()))
		{
			if(in_array($itemName,$this->defaultRoles))
				return true;
			if(isset($assignments[$itemName]))
			{
				$assignment=$assignments[$itemName];
				if($this->executeBizRule($assignment->getBizRule(),$params,$assignment->getData()))
					return true;
			}
			$sql="SELECT parent FROM {$this->itemChildTable} WHERE child=:name";
			foreach($this->db->createCommand($sql)->bindValue(':name',$itemName)->queryColumn() as $parent)
			{
				if($this->checkAccessRecursive($parent,$userId,$params,$assignments))
					return true;
			}
		}
		return false;
	}

	/**
	 * Adds an item as a child of another item.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @throws CException if either parent or child doesn't exist or if a loop has been detected.
	 */
	public function addItemChild($itemName,$childName)
	{
		if($itemName===$childName)
			throw new CException(Yii::t('yii','Cannot add "{name}" as a child of itself.',
					array('{name}'=>$itemName)));
		$sql="SELECT * FROM {$this->itemTable} WHERE name=:name1 OR name=:name2";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':name1',$itemName);
		$command->bindValue(':name2',$childName);
		$rows=$command->queryAll();
		if(count($rows)==2)
		{
			if($rows[0]['name']===$itemName)
			{
				$parentType=$rows[0]['type'];
				$childType=$rows[1]['type'];
			}
			else
			{
				$childType=$rows[0]['type'];
				$parentType=$rows[1]['type'];
			}
			$this->checkItemChildType($parentType,$childType);
			if($this->detectLoop($itemName,$childName))
				throw new CException(Yii::t('yii','Cannot add "{child}" as a child of "{name}". A loop has been detected.',
					array('{child}'=>$childName,'{name}'=>$itemName)));

			$sql="INSERT INTO {$this->itemChildTable} (parent,child) VALUES (:parent,:child)";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':parent',$itemName);
			$command->bindValue(':child',$childName);
			$command->execute();
		}
		else
			throw new CException(Yii::t('yii','Either "{parent}" or "{child}" does not exist.',array('{child}'=>$childName,'{parent}'=>$itemName)));
	}

	/**
	 * Removes a child from its parent.
	 * Note, the child item is not deleted. Only the parent-child relationship is removed.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the removal is successful
	 */
	public function removeItemChild($itemName,$childName)
	{
		$sql="DELETE FROM {$this->itemChildTable} WHERE parent=:parent AND child=:child";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':parent',$itemName);
		$command->bindValue(':child',$childName);
		return $command->execute()>0;
	}

	/**
	 * Returns a value indicating whether a child exists within a parent.
	 * @param string $itemName the parent item name
	 * @param string $childName the child item name
	 * @return boolean whether the child exists
	 */
	public function hasItemChild($itemName,$childName)
	{
		$sql="SELECT parent FROM {$this->itemChildTable} WHERE parent=:parent AND child=:child";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':parent',$itemName);
		$command->bindValue(':child',$childName);
		return $command->queryScalar()!==false;
	}

	/**
	 * Returns the children of the specified item.
	 * @param mixed $names the parent item name. This can be either a string or an array.
	 * The latter represents a list of item names (available since version 1.0.5).
	 * @return array all child items of the parent
	 */
	public function getItemChildren($names)
	{
		if(is_string($names))
			$condition='parent='.$this->db->quoteValue($names);
		else if(is_array($names) && $names!==array())
		{
			foreach($names as &$name)
				$name=$this->db->quoteValue($name);
			$condition='parent IN ('.implode(', ',$names).')';
		}
		$sql="SELECT name, type, description, bizrule, data FROM {$this->itemTable}, {$this->itemChildTable} WHERE $condition AND name=child";
		$children=array();
		foreach($this->db->createCommand($sql)->queryAll() as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$children[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		return $children;
	}

	/**
	 * Assigns an authorization item to a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @param string $bizRule the business rule to be executed when {@link checkAccess} is called
	 * for this particular authorization item.
	 * @param mixed $data additional data associated with this assignment
	 * @return CAuthAssignment the authorization assignment information.
	 * @throws CException if the item does not exist or if the item has already been assigned to the user
	 */
	public function assign($itemName,$userId,$bizRule=null,$data=null)
	{
		if($this->usingSqlite() && $this->getAuthItem($itemName)===null)
			throw new CException(Yii::t('yii','The item "{name}" does not exist.',array('{name}'=>$itemName)));

		$sql="INSERT INTO {$this->assignmentTable} (itemname,userid,bizrule,data) VALUES (:itemname,:userid,:bizrule,:data)";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':itemname',$itemName);
		$command->bindValue(':userid',$userId);
		$command->bindValue(':bizrule',$bizRule);
		$command->bindValue(':data',serialize($data));
		$command->execute();
		return new CAuthAssignment($this,$itemName,$userId,$bizRule,$data);
	}

	/**
	 * Revokes an authorization assignment from a user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether removal is successful
	 */
	public function revoke($itemName,$userId)
	{
		$sql="DELETE FROM {$this->assignmentTable} WHERE itemname=:itemname AND userid=:userid";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':itemname',$itemName);
		$command->bindValue(':userid',$userId);
		return $command->execute()>0;
	}

	/**
	 * Returns a value indicating whether the item has been assigned to the user.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return boolean whether the item has been assigned to the user.
	 */
	public function isAssigned($itemName,$userId)
	{
		$sql="SELECT itemname FROM {$this->assignmentTable} WHERE itemname=:itemname AND userid=:userid";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':itemname',$itemName);
		$command->bindValue(':userid',$userId);
		return $command->queryScalar()!==false;
	}

	/**
	 * Returns the item assignment information.
	 * @param string $itemName the item name
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return CAuthAssignment the item assignment information. Null is returned if
	 * the item is not assigned to the user.
	 */
	public function getAuthAssignment($itemName,$userId)
	{
		$sql="SELECT * FROM {$this->assignmentTable} WHERE itemname=:itemname AND userid=:userid";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':itemname',$itemName);
		$command->bindValue(':userid',$userId);
		if(($row=$command->queryRow($sql))!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthAssignment($this,$row['itemname'],$row['userid'],$row['bizrule'],$data);
		}
		else
			return null;
	}

	/**
	 * Returns the item assignments for the specified user.
	 * @param mixed $userId the user ID (see {@link IWebUser::getId})
	 * @return array the item assignment information for the user. An empty array will be
	 * returned if there is no item assigned to the user.
	 */
	public function getAuthAssignments($userId)
	{
		$sql="SELECT * FROM {$this->assignmentTable} WHERE userid=:userid";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':userid',$userId);
		$assignments=array();
		foreach($command->queryAll($sql) as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$assignments[$row['itemname']]=new CAuthAssignment($this,$row['itemname'],$row['userid'],$row['bizrule'],$data);
		}
		return $assignments;
	}

	/**
	 * Saves the changes to an authorization assignment.
	 * @param CAuthAssignment $assignment the assignment that has been changed.
	 */
	public function saveAuthAssignment($assignment)
	{
		$sql="UPDATE {$this->assignmentTable} SET bizrule=:bizrule, data=:data WHERE itemname=:itemname AND userid=:userid";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':bizrule',$assignment->getBizRule());
		$command->bindValue(':data',serialize($assignment->getData()));
		$command->bindValue(':itemname',$assignment->getItemName());
		$command->bindValue(':userid',$assignment->getUserId());
		$command->execute();
	}

	/**
	 * Returns the authorization items of the specific type and user.
	 * @param integer $type the item type (0: operation, 1: task, 2: role). Defaults to null,
	 * meaning returning all items regardless of their type.
	 * @param mixed $userId the user ID. Defaults to null, meaning returning all items even if
	 * they are not assigned to a user.
	 * @return array the authorization items of the specific type.
	 */
	public function getAuthItems($type=null,$userId=null)
	{
		if($type===null && $userId===null)
		{
			$sql="SELECT * FROM {$this->itemTable}";
			$command=$this->db->createCommand($sql);
		}
		else if($userId===null)
		{
			$sql="SELECT * FROM {$this->itemTable} WHERE type=:type";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':type',$type);
		}
		else if($type===null)
		{
			$sql="SELECT name,type,description,t1.bizrule,t1.data
				FROM {$this->itemTable} t1, {$this->assignmentTable} t2
				WHERE name=itemname AND userid=:userid";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':userid',$userId);
		}
		else
		{
			$sql="SELECT name,type,description,t1.bizrule,t1.data
				FROM {$this->itemTable} t1, {$this->assignmentTable} t2
				WHERE name=itemname AND type=:type AND userid=:userid";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':type',$type);
			$command->bindValue(':userid',$userId);
		}
		$items=array();
		foreach($command->queryAll() as $row)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			$items[$row['name']]=new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		return $items;
	}

	/**
	 * Creates an authorization item.
	 * An authorization item represents an action permission (e.g. creating a post).
	 * It has three types: operation, task and role.
	 * Authorization items form a hierarchy. Higher level items inheirt permissions representing
	 * by lower level items.
	 * @param string $name the item name. This must be a unique identifier.
	 * @param integer $type the item type (0: operation, 1: task, 2: role).
	 * @param string $description description of the item
	 * @param string $bizRule business rule associated with the item. This is a piece of
	 * PHP code that will be executed when {@link checkAccess} is called for the item.
	 * @param mixed $data additional data associated with the item.
	 * @return CAuthItem the authorization item
	 * @throws CException if an item with the same name already exists
	 */
	public function createAuthItem($name,$type,$description='',$bizRule=null,$data=null)
	{
		$sql="INSERT INTO {$this->itemTable} (name,type,description,bizrule,data) VALUES (:name,:type,:description,:bizrule,:data)";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':type',$type);
		$command->bindValue(':name',$name);
		$command->bindValue(':description',$description);
		$command->bindValue(':bizrule',$bizRule);
		$command->bindValue(':data',serialize($data));
		$command->execute();
		return new CAuthItem($this,$name,$type,$description,$bizRule,$data);
	}

	/**
	 * Removes the specified authorization item.
	 * @param string $name the name of the item to be removed
	 * @return boolean whether the item exists in the storage and has been removed
	 */
	public function removeAuthItem($name)
	{
		if($this->usingSqlite())
		{
			$sql="DELETE FROM {$this->itemChildTable} WHERE parent=:name1 OR child=:name2";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':name1',$name);
			$command->bindValue(':name2',$name);
			$command->execute();

			$sql="DELETE FROM {$this->assignmentTable} WHERE itemname=:name";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':name',$name);
			$command->execute();
		}

		$sql="DELETE FROM {$this->itemTable} WHERE name=:name";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':name',$name);

		return $command->execute()>0;
	}

	/**
	 * Returns the authorization item with the specified name.
	 * @param string $name the name of the item
	 * @return CAuthItem the authorization item. Null if the item cannot be found.
	 */
	public function getAuthItem($name)
	{
		$sql="SELECT * FROM {$this->itemTable} WHERE name=:name";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':name',$name);
		if(($row=$command->queryRow())!==false)
		{
			if(($data=@unserialize($row['data']))===false)
				$data=null;
			return new CAuthItem($this,$row['name'],$row['type'],$row['description'],$row['bizrule'],$data);
		}
		else
			return null;
	}

	/**
	 * Saves an authorization item to persistent storage.
	 * @param CAuthItem $item the item to be saved.
	 * @param string $oldName the old item name. If null, it means the item name is not changed.
	 */
	public function saveAuthItem($item,$oldName=null)
	{
		if($this->usingSqlite() && $oldName!==null && $item->getName()!==$oldName)
		{
			$sql="UPDATE {$this->itemChildTable} SET parent=:newName WHERE parent=:name";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':name',$oldName);
			$command->bindValue(':newName',$item->getName());
			$command->execute();
			$sql="UPDATE {$this->itemChildTable} SET child=:newName WHERE child=:name";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':name',$oldName);
			$command->bindValue(':newName',$item->getName());
			$command->execute();
			$sql="UPDATE {$this->assignmentTable} SET itemname=:newName WHERE itemname=:name";
			$command=$this->db->createCommand($sql);
			$command->bindValue(':name',$oldName);
			$command->bindValue(':newName',$item->getName());
			$command->execute();
		}

		$sql="UPDATE {$this->itemTable} SET name=:newName, type=:type, description=:description, bizrule=:bizrule, data=:data WHERE name=:name";
		$command=$this->db->createCommand($sql);
		$command->bindValue(':type',$item->getType());
		$command->bindValue(':name',$oldName===null?$item->getName():$oldName);
		$command->bindValue(':newName',$item->getName());
		$command->bindValue(':description',$item->getDescription());
		$command->bindValue(':bizrule',$item->getBizRule());
		$command->bindValue(':data',serialize($item->getData()));
		$command->execute();
	}

	/**
	 * Saves the authorization data to persistent storage.
	 */
	public function save()
	{
	}

	/**
	 * Removes all authorization data.
	 */
	public function clearAll()
	{
		$this->clearAuthAssignments();
		$this->db->createCommand("DELETE FROM {$this->itemChildTable}")->execute();
		$this->db->createCommand("DELETE FROM {$this->itemTable}")->execute();
	}

	/**
	 * Removes all authorization assignments.
	 */
	public function clearAuthAssignments()
	{
		$this->db->createCommand("DELETE FROM {$this->assignmentTable}")->execute();
	}

	/**
	 * Checks whether there is a loop in the authorization item hierarchy.
	 * @param string $itemName parent item name
	 * @param string $childName the name of the child item that is to be added to the hierarchy
	 * @return boolean whether a loop exists
	 */
	protected function detectLoop($itemName,$childName)
	{
		if($childName===$itemName)
			return true;
		foreach($this->getItemChildren($childName) as $child)
		{
			if($this->detectLoop($itemName,$child->getName()))
				return true;
		}
		return false;
	}

	/**
	 * @return CDbConnection the DB connection instance
	 * @throws CException if {@link connectionID} does not point to a valid application component.
	 */
	protected function getDbConnection()
	{
		if($this->db!==null)
			return $this->db;
		else if(($this->db=Yii::app()->getComponent($this->connectionID)) instanceof CDbConnection)
			return $this->db;
		else
			throw new CException(Yii::t('yii','CDbAuthManager.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
				array('{id}'=>$this->connectionID)));
	}

	/**
	 * @return boolean whether the database is a SQLite database
	 */
	protected function usingSqlite()
	{
		return $this->_usingSqlite;
	}
}
