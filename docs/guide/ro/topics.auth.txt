Autentificare si autorizare
===========================

Autentificarea si autorizarea sunt necesare daca o pagina Web trebuie
sa fie accesata doar de anumiti utilizatori. Autentificarea in sine asigura
ca cineva este cine pretinde ca este. De obicei, este nevoie de un nume de 
utilizator si o parola, dar poate include si alte metode pentru demonstrarea identitatii,
precum smart card, amprente, etc. Autorizarea este calea prin care o persoana,
o data ce a fost identificata (autentificata), are permisiunea sa acceseze/modifice
anumite resurse. Acest lucru se face de obicei verificand daca respectiva persoana
are un anumit rol care are permisiunea de a accesa resursele in cauza.

Yii incorporeaza un sistem de autentificare/autorizare care este usor de folosit
si de modificat pentru orice nevoi.

Centrul sistemului de autentificare al Yii este *componenta
de aplicatie user*, un obiect care implementeaza interfata [IWebUser].
Componenta user reprezinta informatiile persistente despre identitatea utilizatorului
curent. Putem accesa aceasta componenta de oriunde din cod prin
`Yii::app()->user`.

Folosind componenta user, putem verifica daca un utilizator este logat sau nu prin
[CWebUser::isGuest]; putem sa logam un user prin metoda [login|CWebUser::login] si sa
il delogam prin metoda [logout|CWebUser::logout]; putem sa verificam daca utilizatorul
are dreptul sa execute anumite operatii prin apelul metodei [CWebUser::checkAccess];
de asemenea, putem sa obtinem [identificatorul unic|CWebUser::name] si alte informatii
persistente despre identitatea utilizatorului.

Definirea clasei de identitate
------------------------------

Pentru a autentifica un utilizator, definim o clasa de identitate care
contine codul efectiv de autentificare. Clasa de identitate ar trebui sa
implementeze interfata [IUserIdentity]/ Mai multe clase pot implementa
abordari diferite de autentificare (ex. OpenID, LDAP). Un start bun ar fi
sa derivam clasa [CUserIdentity] care este clasa de baza pentru autentificarea
pe baza unui nume si a unei parole.

Cel mai important lucru in definirea unei clase de identitate este implementarea
metodei [IUserIdentity::authenticate]. O clasa de identitate poate sa declare de asemenea
informatii aditionale despre identitate care trebuie sa fie persistente in timpul sesiunii
utilizatorului.

In urmatorul exemplu, validam numele si parola folosind tabela
user dintr-o baza de date prin intermediul [Active
Record](/doc/guide/database.ar). De asemenea, suprascriem metoda `getId` pentru a
returna variabila `_id` care este setata in timpul autentificarii (valoarea returnata
implicit pentru ID este numele utilizatorului username). In timpul autentificarii,
memoram informatia `title` prin apelarea metodei [CBaseUserIdentity::setState].

~~~
[php]
class UserIdentity extends CUserIdentity
{
	private $_id;
	public function authenticate()
	{
		$record=User::model()->findByAttributes(array('username'=>$this->username));
		if($record===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($record->password!==md5($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$record->id;
			$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
    }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

Informatiile memorate (prin apelul metodei [CBaseUserIdentity::setState]) vor fi
trimise catre [CWebUser] care le memoreaza mai departe intr-un mediu de stocare persistent,
ca de exemplu sesiunea utilizatorului. Aceste informatii pot fi accesate ca proprietati
ale clasei [CWebUser]. De exemplu, putem obtine informatia
`title` a utilizatorului curent prin `Yii::app()->user->title`
(Acest lucru este disponibil incepand cu versiunea 1.0.3. In versiunile anterioare, trebuie sa
folosim `Yii::app()->user->getState('title')`).

> Info: Implicit, [CWebUser] foloseste sesiunea utilizatorului ca mediu de stocare persistent
pentru informatiile referitoare la identitatea utilizatorului. Daca logarea bazata pe cookie-uri
este activata (prin setarea [CWebUser::allowAutoLogin] cu valoarea `true`), informatiile despre
identitatea utilizatorului pot fi salvate si in cookie-uri. Totusi trebuie sa ne asiguram ca nu
memoram in cookie-uri informatii care ar trebui sa fie ascunse (ex. parola).

Login si Logout
---------------

Folosind calasa de identitate si componenta user, putem implementa usor action-urile
de logare si delogare.

~~~
[php]
// Logheaza un utilizator cu numele si parola date
$identity=new UserIdentity($username,$password);
if($identity->authenticate())
	Yii::app()->user->login($identity);
else
	echo $identity->errorMessage;
......
// Delogheaza utilizatorului curent
Yii::app()->user->logout();
~~~

Implicit, un utilizator va fi delogat dupa o anumita perioada de timp
de inactivitate, perioada care depinde de [configuratia sesiunii](http://www.php.net/manual/en/session.configuration.php).
Pentru a modifica acest comportament, putem seta cu true proprietatea [allowAutoLogin|CWebUser::allowAutoLogin]
componentei user si sa transmitem un parametru cu durata
catre metoda [CWebUser::login]. Utilizatorul va ramane dupa aceea logat o perioada egala
cu perioada specificata in acest parametru, chiar daca inchide fereastra browser-ului web.
Este de notat faptul ca acest feature presupune ca browser-ul utilizatorului accepta cookie-uri.

~~~
[php]
// tinem userul logat timp de 7 zile.
// trebuie sa ne asiguram ca proprietatea allowAutoLogin este setata cu true in componenta user.
Yii::app()->user->login($identity,3600*24*7);
~~~

Filtrul de control al accesului
-------------------------------

Filtrul de control al accesului este o schema preliminara de autorizare care verifica
daca utilizatorul curent poate executa action-ul cerut. Autorizarea se foloseste de
numele utilizatorului, IP-ul clientului si tipul cererii.
Este pus la dispozitie ca un filtru cu numele
["accessControl"|CController::filterAccessControl].

> Tip|Sfat: Filtrul de control al accesului este suficient pentru scenariile simple.
Pentru un control al accesului complex, va trebui probabil sa folositi RBAC
(acces bazat pe roluri) care va fi explicat mai jos.

Pentru a controla accesul la action-urile unui controller, instalam filtrul
de control al accesului prin suprascrierea [CController::filters] (vedeti
[Filter](/doc/guide/basics.controller#filter) pentru mia multe detalii despre
instalarea filtrelor).

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
}
~~~

In codul de mai sus, specificam ca filtrul [access
control|CController::filterAccessControl] ar trebui sa fie aplicat pentru
toate action-urile controller-ului `PostController`. Regulile de autorizarea detaliate
folosite de catre filtru sunt specificate prin suprascrierea
[CController::accessRules] din clasa controller-ului.

~~~
[php]
class PostController extends CController
{
	......
	public function accessRules()
	{
		return array(
			array('deny',
				'actions'=>array('create', 'edit'),
				'users'=>array('?'),
			),
			array('allow',
				'actions'=>array('delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'actions'=>array('delete'),
				'users'=>array('*'),
			),
		);
	}
}
~~~

Codul de mai sus specifica trei reguli, fiecare reprezentata de un array. 
Primul element din array este `'allow'` sau `'deny'` iar restul perechilor
nume-valoare specifica parametrii pattern ai regulii. Aceste reguli
ne spun: action-urile `create` si `edit` nu pot fi executate de catre
utilizatorii anonimi; action-ul `delete` poate fi executat de utilizatorii cu rolul `admin`;
action-ul `delete` nu poate fi executat de nimeni.

Regulile de acces sunt evaluate una cate una in ordinea in care sunt specificate.
Prima regula care se potriveste cu pattern-ul curent (ex. numele utilizatorului, rolurile,
IP-ul client, adresa) determina rezultatul autorizarii. Daca aceasta regula este o regula `allow`,
action-ul poate fi executat; daca este o regula `deny`, action-ul nu poate fi executat;
daca nici una dintre aceste reguli nu se potriveste cu contextul, action va fi executat.

> Tip|Sfat: Pentru a ne asigura ca un action nu va fi executat in anumite contexte,
> este bine sa specificam mereu o regula `deny` la sfarsitul setului de reguli care sa
> interzica executarea action-ului:
> ~~~
> [php]
> return array(
>     // ... alte reguli...
>     // urmatoarea regula interzice action-ul 'delete' in absolut orice context
>     array('deny',
>         'action'=>'delete',
>     ),
> );
> ~~~
> Motivul pentru care adaugam aceasta regula este ca daca nici o regula nu se
> potriveste, action-ul va fi executat. 

O regula de acces poate sa se potriveasca cu urmatorii parametri de context:

   - [actions|CAccessRule::actions]: precizeaza lista de action-uri pentru care se aplica regula.

   - [users|CAccessRule::users]: precizeaza utilizatorii pentru care se aplica regula.
Este folosit [numele|CWebUser::name] utilizatorului curent. Trei caractere speciale pot fi folosite aici:

	   - `*`: orice utilizator, anonim sau autentificat.
	   - `?`: utilizatorii anonimi.
	   - `@`: utilizatorii autentificati.

   - [roles|CAccessRule::roles]: precizeaza caror roluri li se aplica regula curenta.
Se foloseste [controlul accesului bazat pe roluri](#role-based-access-control)
care va fi descris in sub-sectiunea urmatoare. In particular, regula
este aplicata daca [CWebUser::checkAccess] returneaza true pentru unul dintre roluri.
De notat este faptul ca ar trebui sa folosim rolurile in regulile `allow` pentru ca, prin definitie,
un rol reprezinta o permisiune de a face ceva.

   - [ips|CAccessRule::ips]: precizeaza caror adrese IP li se va aplica regula.

   - [verbs|CAccessRule::verbs]: precizeaza caror tipuri de cereri (ex.
`GET`, `POST`) li se va aplica regula.

   - [expression|CAccessRule::expression]: precizeaza o expresie PHP a carei valoare
indica daca aceasta regula se va aplica sau nu. In aceasta expresie putem folosi variabila `$user`
care se refera la `Yii::app()->user`. Aceasta optiune este disponibila incepand cu versiunea 1.0.3.


### Tratarea rezultatelor de autorizare

Cand autorizarea esueaza, adica utilizatorul nu are dreptul sa executa action-ul specificat,
se poate intampla unul dintre urmatoarele doua scenarii:

   - If the user is not logged in and if the [loginUrl|CWebUser::loginUrl]
property of the user component is configured to be the URL of the login
page, the browser will be redirected to that page.

   - Otherwise an HTTP exception will be displayed with error code 401.

When configuring the [loginUrl|CWebUser::loginUrl] property, one can
provide a relative or absolute URL. One can also provide an array which
will be used to generate a URL by calling [CWebApplication::createUrl]. The
first array element should specify the
[route](/doc/guide/basics.controller#route) to the login controller
action, and the rest name-value pairs are GET parameters. For example,

~~~
[php]
array(
	......
	'components'=>array(
		'user'=>array(
			// this is actually the default value
			'loginUrl'=>array('site/login'),
		),
	),
)
~~~

If the browser is redirected to the login page and the login is
successful, we may want to redirect the browser back to the page that
caused the authorization failure. How do we know the URL for that page? We
can get this information from the [returnUrl|CWebUser::returnUrl] property
of the user component. We can thus do the following to perform the
redirection:

~~~
[php]
Yii::app()->request->redirect(Yii::app()->user->returnUrl);
~~~

Role-Based Access Control
-------------------------

Role-Based Access Control (RBAC) provides a simple yet powerful
centralized access control. Please refer to the [Wiki
article](http://en.wikipedia.org/wiki/Role-based_access_control) for more
details about comparing RBAC with other more traditional access control
schemes.

Yii implements a hierarchical RBAC scheme via its
[authManager|CWebApplication::authManager] application component. In the
following ,we first introduce the main concepts used in this scheme; we
then describe how to define authorization data; at the end we show how to
make use of the authorization data to perform access checking.

### Overview

A fundamental concept in Yii's RBAC is *authorization item*. An
authorization item is a permission to do something (e.g. creating new blog
posts, managing users). According to its granularity and targeted audience,
authorization items can be classified as *operations*,
*tasks* and *roles*. A role consists of tasks, a task
consists of operations, and an operation is a permission that is atomic.
For example, we can have a system with `administrator` role which consists
of `post management` task and `user management` task. The `user management`
task may consist of `create user`, `update user` and `delete user`
operations. For more flexibility, Yii also allows a role to consist of
other roles or operations, a task to consist of other tasks, and an
operation to consist of other operations.

An authorization item is uniquely identified by its name.

An authorization item may be associated with a *business rule*. A
business rule is a piece of PHP code that will be executed when performing
access checking with respect to the item. Only when the execution returns
true, will the user be considered to have the permission represented by the
item. For example, when defining an operation `updatePost`, we would like
to add a business rule that checks if the user ID is the same as the post's
author ID so that only the author himself can have the permission to update
a post.

Using authorization items, we can build up an *authorization
hierarchy*. An item `A` is a parent of another item `B` in the
hierarchy if `A` consists of `B` (or say `A` inherits the permission(s)
represented by `B`). An item can have multiple child items, and it can also
have multipe parent items. Therefore, an authorization hierarchy is a
partial-order graph rather than a tree. In this hierarchy, role items sit
on top levels, operation items on bottom levels, while task items in
between.

Once we have an authorization hierarchy, we can assign roles in this
hierarchy to application users. A user, once assigned with a role, will
have the permissions represented by the role. For example, if we assign the
`administrator` role to a user, he will have the administrator permissions
which include `post management` and `user management` (and the
corresponding operations such as `create user`).

Now the fun part starts. In a controller action, we want to check if the
current user can delete the specified post. Using the RBAC hierarchy and
assignment, this can be done easily as follows:

~~~
[php]
if(Yii::app()->user->checkAccess('deletePost'))
{
	// delete the post
}
~~~

### Configuring Authorization Manager

Before we set off to define an authorization hierarchy and perform access
checking, we need to configure the
[authManager|CWebApplication::authManager] application component. Yii
provides two types of authorization managers: [CPhpAuthManager] and
[CDbAuthManager]. The former uses a PHP script file to store authorization
data, while the latter stores authorization data in database. When we
configure the [authManager|CWebApplication::authManager] application
component, we need to specify which component class to use and what are the
initial property values for the component. For example,

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'sqlite:path/to/file.db',
		),
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'connectionID'=>'db',
		),
	),
);
~~~

We can then access the [authManager|CWebApplication::authManager]
application component using `Yii::app()->authManager`.

### Defining Authorization Hierarchy

Defining authorization hierarchy involves three steps: defining
authorization items, establishing relationships between authorization
items, and assigning roles to application users. The
[authManager|CWebApplication::authManager] application component provides a
whole set of APIs to accomplish these tasks.

To define an authorization item, call one of the following methods,
depending on the type of the item:

   - [CAuthManager::createRole]
   - [CAuthManager::createTask]
   - [CAuthManager::createOperation]

Once we have a set of authorization items, we can call the following
methods to establish relationships between authorization items:

   - [CAuthManager::addItemChild]
   - [CAuthManager::removeItemChild]
   - [CAuthItem::addChild]
   - [CAuthItem::removeChild]

And finally, we call the following methods to assign role items to
individual users:

   - [CAuthManager::assign]
   - [CAuthManager::revoke]

Below we show an example about building an authorization hierarchy with
the provided APIs:

~~~
[php]
$auth=Yii::app()->authManager;

$auth->createOperation('createPost','create a post');
$auth->createOperation('readPost','read a post');
$auth->createOperation('updatePost','update a post');
$auth->createOperation('deletePost','delete a post');

$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
$task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
$task->addChild('updatePost');

$role=$auth->createRole('reader');
$role->addChild('readPost');

$role=$auth->createRole('author');
$role->addChild('reader');
$role->addChild('createPost');
$role->addChild('updateOwnPost');

$role=$auth->createRole('editor');
$role->addChild('reader');
$role->addChild('updatePost');

$role=$auth->createRole('admin');
$role->addChild('editor');
$role->addChild('author');
$role->addChild('deletePost');

$auth->assign('reader','readerA');
$auth->assign('author','authorB');
$auth->assign('editor','editorC');
$auth->assign('admin','adminD');
~~~

Note that we associate a business rule with the `updateOwnPost` task. In
the business rule we simply check if the current user ID is the same as the
specified post's author ID. The post information in the `$params` array is
supplied by developers when performing access checking.

> Info: While the above example looks long and tedious, it is mainly for
demonstrative purpose. Developers usually need to develop some user
interfaces so that end users can use to establish an authorization
hierarchy more intuitively.

### Access Checking

To perform access checking, we first need to know the name of the
authorization item. For example, to check if the current user can create a
post, we would check if he has the permission represented by the
`createPost` operation. We then call [CWebUser::checkAccess] to perform the
access checking:

~~~
[php]
if(Yii::app()->user->checkAccess('createPost'))
{
	// create post
}
~~~

If the authorization rule is associated with a business rule which
requires additional parameters, we can pass them as well. For example, to
check if a user can update a post, we would do

~~~
[php]
$params=array('post'=>$post);
if(Yii::app()->user->checkAccess('updateOwnPost',$params))
{
	// update post
}
~~~


### Using Default Roles

> Note: The default role feature has been available since version 1.0.3

Many Web applications need some very special roles that would be assigned to
every or most of the system users. For example, we may want to assign some
privileges to all authenticated users. It poses a lot of maintenance trouble
if we explicitly specify and store these role assignments. We can exploit
*default roles* to solve this problem.

A default role is a role that is implicitly assigned to every user, including
both authenticated and guest. We do not need to explicitly assign it to a user.
When [CWebUser::checkAccess], default roles will be checked first as if they are
assigned to the user.

Default roles must be declared in the [CAuthManager::defaultRoles] property.
For example, the following configuration declares two roles to be default roles: `authenticated` and `guest`.

~~~
[php]
return array(
	'components'=>array(
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'defaultRoles'=>array('authenticated', 'guest'),
		),
	),
);
~~~

Because a default role is assigned to every user, it usually needs to be
associated with a business rule that determines whether the role
really applies to the user. For example, the following code defines two
roles, "authenticated" and "guest", which effectively apply to authenticated
users and guest users, respectively.

~~~
[php]
$bizRule='return !Yii::app()->user->isGuest;';
$auth->createRole('authenticated',$bizRule);

$bizRule='return Yii::app()->user->isGuest;';
$auth->createRole('guest',$bizRule);
~~~

<div class="revision">$Id: topics.auth.txt 759 2009-02-26 21:23:53Z qiang.xue $</div>