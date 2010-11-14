Data Access Objects (DAO)
=========================

Data Access Objects (DAO) permet l'accès aux données stockées dans divers SGBD
au travers d'une API générique. Ainsi, le SGBD sous jacent peut etre changé sans
avoir à modifier le code utilisant DAO pour accéder aux données.

Yii DAO est basé sur les [PHP Data Objects
(PDO)](http://php.net/manual/en/book.pdo.php), une extension fournissant
un accès unifié aux données de multiples SGBD, tels que MySQL, PostgreSQL.
C'est pour cela que l'utilisation de Yii DAO requiert la présence de l'extension
PDO et du pilote PDO spécifique pour le SGBD utilisé (par exemple `PDO_MYSQL`).

Yii DAO est constitué des quatres classes suivantes:

   - [CDbConnection]: représente une connection à une base.
   - [CDbCommand]: représente une requête SQL.
   - [CDbDataReader]: représente un flux de donnée résultant d'une requête.
   - [CDbTransaction]: représente une transaction.

L'utilisation de Yii DAO dans différents cas de figure va maintenant être présenté.

Etablir une connection à une base
---------------------------------

Pour établir une connection à une base, il est nécessaire de créer une instance de [CDbConnection]
et de l'activer. Pour cela un DSN (Data Source Name) contenant les informations
de connection devra être spécifié. Un nom d'utilisateur et un mot de passe, optionnels,
peuvent aussi être renseigné ici. Une exception sera levée si une erreur se produit durant
la connection (par exemple en cas de mauvais DSN ou un utilisateur/mot de passe invalide).

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// établit une connection. try...catch pour récupérer les éventuelles exceptions
$connection->active=true;
......
$connection->active=false;  // fermeture de la connection
~~~

Le format du DSN varie selon le pilote PDO choisi. De manière générale, un DSN
est constitué du nom du pilote PDO, suivi d'un point virgule, suivi d'une chaine
dont la syntaxe dépend du pilote. Voir la [documentation
PDO](http://www.php.net/manual/en/pdo.construct.php) pour plus d'information.

Ci dessous une liste avec les formats courammant utilisés:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`
   - SQL Server: `mssql:host=localhost;dbname=testdb`
   - Oracle: `oci:dbname=//localhost:1521/testdb`

Comme [CDbConnection] hérite de [CApplicationComponent], il est aussi possible
de l'utiliser comme un [application
component](/doc/guide/basics.application#application-component). Pour cela, il
faut configurer l'application component `db` (ou un autre nom) dans l'[application
configuration](/doc/guide/basics.application#application-configuration) selon le modèle
suivant,

~~~
[php]
array(
	......
	'components'=>array(
		......
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'mysql:host=localhost;dbname=testdb',
			'username'=>'root',
			'password'=>'password',
			'emulatePrepare'=>true,  // requit pour certaines installations MySQL
		),
	),
)
~~~

Il est dès lors possible d'accéder à la connection au travers de `Yii::app()->db`
qui est activé automatiquement, à moins que [CDbConnection::autoConnect] ne soit explicitement
mis à false. Grâce à cette méthode, cette connection unique sera accessible où que l'on soit
dans le code.

Exécution de requêtes SQL
-------------------------

Une fois qu'une connection à été établit, les requêtes SQL peuvent être exécutées
grâce à la classe [CDbCommand]. Il faut pour cela créer une instance [CDbCommand]
en appelant [CDbConnection::createCommand()] et en spécifiant la requête SQL.

~~~
[php]
$connection=Yii::app()->db;   // vous devez avoir une connection "db"
// Sinon il est possible de créer cette connection explicitement:
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// Si besoin, la requête peut etre mise à jour comme suivant:
// $command->text=$newSQL;
~~~

Une requete SQL est exécutée au travers de [CDbCommand] selon l'une des 2 méthodes suivantes:

   - [execute()|CDbCommand::execute]: Exécute une requête SQL sans retour de données,
tels que `INSERT`, `UPDATE` ou `DELETE`. Si la requête se déroule sans erreur,
cette fonction retournera le nombre de ligne affectées.

   - [query()|CDbCommand::query]: Exécute une requête SQL retournant des données
tels que `SELECT`. Si la requête se déroule sans erreur, cette fonction retournera une
instance de [CDbDataReader] dans laquelle on pourra parcourir les lignes. Dans un soucis de simplicité,
plusieurs méthodes `queryXXX()` ont aussi été implémentées retournant directement les résultats.

Une exception sera levée si une erreur se produit durant l'exécution de la requête SQL.

~~~
[php]
$rowCount=$command->execute();   // exécute une requête SQL sans reour de données
$dataReader=$command->query();   // exécute une requête SQL
$rows=$command->queryAll();      // exécute et retourne toutes les lignes
$row=$command->queryRow();       // exécute et retourne la première ligne
$column=$command->queryColumn(); // exécute et retourne la première colonne
$value=$command->queryScalar();  // exécute et retourne le premier champ de la première ligne
~~~

Parcourir les résultats
-----------------------

Une fois que [CDbCommand::query()] à généré l'instance [CDbDataReader], il
est possible de récupérer les lignes en appelant successivement la méthode
[CDbDataReader::read()]. Il est aussi possible d'utiliser [CDbDataReader]
directement dans une boucle `foreach` pour récupérer les données lignes par lignes.

~~~
[php]
$dataReader=$command->query();
// appel de read() jusqu'a ce qu'il retourne false
while(($row=$dataReader->read())!==false) { ... }
// utilisation de foreach pour parcourir chaque ligne de données
foreach($dataReader as $row) { ... }
// récupération de toutes les lignes d'un coup dans un tableau
$rows=$dataReader->readAll();
~~~

> Note: Contrairement à [query()|CDbCommand::query], les méthodes `queryXXX()`
renvoient les données directement. Par exemple, [queryRow()|CDbCommand::queryRow]
retourne un tableau representant la première ligne du résultat.

Utilisation des transactions
----------------------------

Lorsqu'une application exécute plusieurs requêtes de lecture et/ou écriture,
il est important d'etre certain que la base ne sera pas laissée dans un état ou
toutes les requêtes n'ont pas été achevées. Une transaction, représentée comme une
instance de [CDbTransaction] dans Yii, pourra être utilisée selon le schéma suivant:

   - Début de la transaction.
   - Exécution des requêtes une à une. Aucune modification à la base ne sera visible en dehors du contexte.
   - Commit la transaction. Toutes les modifications sont alors visibles si la transaction se déroule sans erreur.
   - Si une seule requête échoue, la transaction entière est annulée (roll back).

Le déroulement ci dessus peut etre implémenté comme suivant:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... autres requêtes SQL
	$transaction->commit();
}
catch(Exception $e) // une exception est levée si une requête échoue
{
	$transaction->rollBack();
}
~~~

Paramètres liés
---------------

Pour faire face aux [attaques par injection SQL](http://en.wikipedia.org/wiki/SQL_injection)
et pour améliorer les performances lors des exécutions répétées de requêtes SQL, il est possible
de "préparer" les requêtes SQL, avec optionnellement des marques pour les paramètres, qui seront
remplacées par les vrai paramètres durant l'étape de "liage" (binding) des paramètres.

Les marques pour ces paramètres peuvent être nommés (représenté comme des "tokens") ou non-nommés
(représenté comme des points d'interrogation). Un appel à [CDbCommand::bindParam()] ou [CDbCommand::bindValue()]
permettra de remplacer ces marques par les vrai valeurs. Ces paramètres n'ont pas besoin d'être entre
parenthèses: Le pilote de la base le fera pour vous. Le liage des paramètre devra être fait avant
l'exécution de la requête.

~~~
[php]
// requête SQL avec 2 marques ":username" et ":email"
$sql="INSERT INTO tbl_user (username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// remplace la marque ":username" avec la vrai valeur de username
$command->bindParam(":username",$username,PDO::PARAM_STR);
// remplace la marque ":email" avec la vrai valeur email
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// insertion d'une ligne avec les nouveaux paramètres
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

Les méthodes [bindParam()|CDbCommand::bindParam] et
[bindValue()|CDbCommand::bindValue] sont très similaires. La seule différence
est que le premier lie un paramètre avec une référence de variable PHP alors que
le deuxième le lie avec la valeur de cette variable. Pour les paramètres représentant
de gros blocs mémoire, le premier est préféré pour des raisons de performance.

Pour plus de détails sur les paramètres liant, voir la [
documentation PHP en rapport](http://www.php.net/manual/en/pdostatement.bindparam.php).

Colonnes liées
--------------

Lors du parcours des résultats d'une requête, il est aussi possible de lier
des variables PHP afin qu'elles soient automatiquement mises à jour avec les
données les plus récentes à chaque fois qu'une ligne est lue.

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// lie la 1ère colonne (username) avec la variable $username
$dataReader->bindColumn(1,$username);
// lie la 2ème colonne (email) avec la variable $email
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username et $email contiennent les username et email de la ligne courante
}
~~~

Utilisation des préfixes de table
---------------------------------

A partir de la version 1.1.0, Yii supporte les préfixes de tables.
Un préfixe de table est une chaine de caractère préfixant le nom d'une table.
Cette technique est très utilisée dans des environnements d'hébergements partagés
dans lesquels des applications partagent une meme base de donnée et ainsi utilisent
différents préfixes pour se différencier l'une de l'autre. Par exemple, une application
pourrait utiliser un préfix `tbl_` et une autre `yii_`.

Pour utiliser les préfixes de table, il faut configurer la propriété [CDbConnection::tablePrefix]
comme la valeur du préfixe souhaité. Puis, dans les requêtes SQL, il est possible d'utiliser
`{{TableName}}` qui correspond au nom de la table sans le préfixe. Par exemple, si la base contient
une table `tbl_user` dans laquelle `tbl_` est configuré pour être le préfixe, il est possible d'utiliser
le code suivant pour connaitre les utilisateurs:

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id: database.dao.txt 2266 2010-07-17 13:58:30Z qiang.xue $</div>
