<?php

class SiteController extends CController implements IWebServiceProvider
{
	/**
	 * Declares the 'phonebook' Web service action.
	 */
	public function actions()
	{
		return array(
			'phonebook'=>array(
				'class'=>'CWebServiceAction',
				'classMap'=>array(
					'Contact',
				),
			),
		);
	}

	/**
	 * This is the default action that displays the phonebook Flex client.
	 */
	public function actionIndex()
	{
		$this->render('index');
	}

	/**
	 * This action serves as a SOAP client to test the phonebook Web service.
	 */
	public function actionTest()
	{
		$wsdlUrl=Yii::app()->request->hostInfo.$this->createUrl('phonebook');
		$client=new SoapClient($wsdlUrl);
		echo "<pre>";
		echo "login...\n";
		$client->login('demo','demo');
		echo "fetching all contacts\n";
		print_r($client->getContacts());
		echo "\ninserting a new contact...";
		$contact=new Contact;
		$contact->name='Tester Name';
		$contact->phone='123-123-1234';
		$client->saveContact($contact);
		echo "done\n\n";
		echo "fetching all contacts\n";
		print_r($client->getContacts());
		echo "</pre>";
	}

	/**
	 * This method is required by IWebServiceProvider.
	 * It makes sure the user is logged in before making changes to data.
	 * @param CWebService the currently requested Web service.
	 * @return boolean whether the remote method should be executed.
	 */
	public function beforeWebMethod($service)
	{
		$safeMethods=array(
			'login',
			'getContacts',
		);
		$pattern='/^('.implode('|',$safeMethods).')$/i';
		if(!Yii::app()->user->isGuest || preg_match($pattern,$service->methodName))
			return true;
		else
			throw new CException('Login required.');
	}

	/**
	 * This method is required by IWebServiceProvider.
	 * @param CWebService the currently requested Web service.
	 */
	public function afterWebMethod($service)
	{
	}

	/*** The following methods are Web service APIs ***/

	/**
	 * @param string username
	 * @param string password
	 * @return boolean whether login is valid
	 * @soap
	 */
	public function login($username,$password)
	{
		$identity=new UserIdentity($username,$password);
		if($identity->authenticate())
			Yii::app()->user->login($identity);
		return $identity->isAuthenticated;
	}

	/**
	 * Returns all contact records.
	 * @return Contact[] the contact records
	 * @soap
	 */
	public function getContacts()
	{
		return Contact::model()->findAll();
	}

	/**
	 * Updates or inserts a contact.
	 * If the ID is null, an insertion will be performed;
	 * Otherwise it updates the existing one.
	 * @param Contact contact model
	 * @return boolean whether saving is successful
	 * @soap
	 */
	public function saveContact($contact)
	{
		if($contact->id > 0) // update
		{
			$contact->isNewRecord=false;
			if(($oldContact=Contact::model()->findByPk($contact->id))!==null)
			{
				$oldContact->attributes=$contact->attributes;
				return $oldContact->save();
			}
			else
				return false;
		}
		else // insert
		{
			$contact->isNewRecord=true;
			$contact->id=null;
			return $contact->save();
		}
	}

	/**
	 * Deletes the specified contact record.
	 * @param integer ID of the contact to be deleted
	 * @return integer number of records deleted
	 * @soap
	 */
	public function deleteContact($id)
	{
		return Contact::model()->deleteByPk($id);
	}
}