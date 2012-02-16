Application
===========

L'application représente le contexte d'exécution de gestion des requêtes.
Son principal objet est de résoudre les requêtes utilisateurs et de les
rediriger vers le contrôleur adéquat. Elle sert aussi à centraliser toutes
les informations de configuration qui lui sont relatives.
C'est pour cela que l'application peut aussi être appelé `front-controller`

L'application est un singleton créé par le [script de démarrage](/doc/guide/basics.entry).
Le singleton peut être accédé de n'importe ou en utilisant [Yii::app()|YiiBase::app].

Configuration de l'Application
------------------------------

Par défaut, l'application est une instance de [CWebApplication]. Pour
la paramétrer, dans la plupart des cas, il suffit de fournir un fichier de
configuration (ou un array) qui permet d'initialiser les valeurs des
propriétés de l'instance à sa création. Lorsque le paramétrage par
fichier de configuration est inadapté ou insuffisant, il est possible
d'étendre directement [CWebApplication].

La configuration est un tableau de paires clef-valeur ou chaque clef représente
le nom d'une propriété de l'instance de l'application et chaque valeur, la valeur
à affecter lors de l'initialisation.
Par exemple, la configuration suivante permet de paramétrer les
propriétés [name|CApplication::name] et [defaultController|CWebApplication::defaultController]
d'une application.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

De façon générale, la configuration est stockée dans un script PHP
séparé (par exemple `protected/config/main.php`) qui retourne le tableau
de paramétrage.

~~~
[php]
return array(...);
~~~

Pour utiliser cette méthode de configuration, il suffit de passer le nom du
fichier au constructeur de l'application ou à la méthode [Yii::createWebApplication()]
comme dans l'exemple suivant, ce qui est fait la plupart du temps dans le
[script de démarrage](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|Astuce : si la configuration est très complexe, il est possible de la segmenter
en plusieurs fichiers, chacun retournant une fraction du tableau de configuration.
Ensuite, il ne reste qu'à assembler au sein du fichier de configuration général les
différents segments (en utilisant la directive PHP `include()`).

Dossier de base de l'Application
--------------------------------

Le dossier de base de l'application est le dossier racine qui contient tous les
élément sensibles de l'application (scripts PHP, données, ...). En standard,
c'est le sous dossier `protected` qui se trouve à l'intérieur du dossier
qui contient le script de démarrage. Ce dossier peut être changé en modifiant
la propriété [basePath|CWebApplication::basePath] dans le fichier de [configuration de l'application](/doc/guide/basics.application#application-configuration).

Les utilisateurs Web ne doivent pas pouvoir accéder aux contenus de ce dossier.
Cela peut être réalisé très simplement sur [serveur Web Apache](http://httpd.apache.org/),
en déposant dans le dossier à protéger un fichier `.htaccess` contenant

~~~
deny from all
~~~

Composant d'Application
-----------------------

Une application peut être aisément modifiée et enrichie de nouvelles fonctionnalités
grâce à son architecture très souple basée sur des composants.
En pratique, une application fédère un batterie de composants ou chacun prend en charge
une fonctionnalité spécifique.
Par exemple, l'application résoud une requête utilisateur grâce aux composants
[CUrlManager] et [CHttpRequest].

Il est possible de paramétrer les propriétés  de tous les [composants|CApplication::components] d'une
application,  Par example, il est possible de configurer le composant
[CMemCache] pour qu'il utilise plusieurs serveurs de cache,

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

Dans la configuration ci-dessus, l'élément `cache` a été ajouté à la liste
des `components`. Le sous-élément `cache` défini la classe de cache à utiliser,
ici `CMemCache` et la liste des serveurs `servers` avec leur paramètres à utiliser.

Pour accéder à un composant de l'application, il suffit d'effectuer l'appel
`Yii::app()->ComponentID`, ou `ComponentID` correspond à l'ID du composant.
Pour l'exemple précédent, l'accès au composant `cache` se ferait tout simplement
par l'appel `Yii::app()->cache`.

Un composant peut être désactivé en forçant sa propriété `enabled` à false.
Lors de l'accès au composant, si il a été désactivé, la valeur null est retournée.

> Tip|Astuce : En standard, les composants de l'application sont créés à la demande.
Cela signifie qu'un composant de l'application n'est pas instancié si
il n'est pas accédé. Cela permet de ne pas dégrader les performances de
l'application même si beaucoup de composants sont déclarés. Pour certains composants
tel que le [CLogRouter], il peut être nécessaire de les instancier,
même si il ne sont pas accédés pendant le cycle de vie de l'application. Pour ce
faire, il suffit de les déclarer dans la section [preload|CApplication::preload] du
paramétrage de l'application.

Composants du noyau de l'application
------------------------------------

Par défaut, Yii prédéfini les propriétés de toute une série de composants
communs aux applications web. C'est, par exemple, le cas du composant
[request|CWebApplication::request] chargé de gérer les requêtes utilisateur.
En paramétrant ces composants, il est possible d'adapter quasiment tous les
aspects du comportement de Yii.

Voici une liste des composants du noyau pré-déclarés par [CWebApplication].

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
gestion de la publication des fichiers de ressource.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - gestion
du contrôle d'accès par rôle utilisateur(RBAC).

   - [cache|CApplication::cache]: [CCache] - gestion du cache de données.
Attention, il faut impérativement spécifier la classe de cache (e.g.
[CMemCache], [CDbCache]). Si elle n'est pas définie, la valeur null est retournée
lors de l'accès au composant.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
gestions des scripts client (javascripts et CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
fourni la traduction des messages utilisés par le noyau de Yii.

   - [db|CApplication::db]: [CDbConnection] - fourni la connexion à la
base de données. Attention, il faut définir correctement la propriété
[connectionString|CDbConnection::connectionString] avant d'utiliser le
composant.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - gestion
des erreurs PHP et des exceptions.

   - [format|CApplication::format]: [CFormatter] - formate les données pour
l'affichage.

   - [messages|CApplication::messages]: [CPhpMessageSource] -
fourni la traduction des messages utilisés par Yii.

   - [request|CWebApplication::request]: [CHttpRequest] - fourni
les fonctionnalités relatives aux requêtes utilisateurs.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
fourni les services de sécurity tels que le hashage ou le cryptage de données.

   - [session|CWebApplication::session]: [CHttpSession] - fourni
les fonctionnalités relatives aux sessions.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
fourni une méthode globale de gestion de la persistance des données.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - fourni
les fonctionnalités de création et d'interprétation des URLs.

   - [user|CWebApplication::user]: [CWebUser] - information relative
à l'identité de l'utilisateur courant.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - gestion des thèmes.


Cycle de vie d'une Application
------------------------------

Lors de la prise en charge d'une requête, l'application passe
par les étapes suivantes :

   0. Pré-initialisation de l'application via [CApplication::preinit()];

   1. Mise en place de l'autoloader et de la gestion des erreurs;

   2. Chargement des composants du noyau;

   3. Chargement de la configuration de l'application;

   4. Initialisation de l'application avec [CApplication::init()]
	   - Enregistrement des "comportements" de l'application;
	   - Chargement des composants statiques;

   5. Levée de l'évènement [onBeginRequest|CApplication::onBeginRequest];

   6. Traitement de la requête utilisateur:
	   - Résolution de la requête;
	   - Création du contrôleur;
	   - Exécution du contrôleur;

   7. Levée de l'évènement [onEndRequest|CApplication::onEndRequest];

<div class="revision">$Id: basics.application.txt 1906 $</div>
