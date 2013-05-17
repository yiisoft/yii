Objetos de Acceso a Datos (DAO)
===============================

Los Objetos de Acceso a Datos (DAO) proveen una API genérica para acceder
a los datos almacenados en diferentes sistemas de administraciond de bases
de datos (DBMS). Como resultado, se puede cambiar de un DBMS a otro sin la
necesidad de cambiar el código que usa DAO para acceder a los datos.

El DAO de Yii está construido sobre [Objetos de Datos de PHP](http://php.net/manual/en/book.pdo.php),
que es una extensión que provee acceso unificado a datos a los DBMS más
populares, como MySQL, PostgreSQL, etc. Por lo tanto, para usar el DAO de Yii,
tienen que ser instaladas tanto la extensión PDO como el driver PDO de la
base de datos (ej.: PDO_MYSQL).

El DAO de Yii principalmente consiste de las siguientes cuatro clases:

   - [CDbConnection]: representa una conexión a una base de datos.
   - [CDbCommand]: representa una sentencia SQL a jecutar en la base de datos.
   - [CDbDataReader]: representa un flujo (solo de avance) de filas del resultado de una consulta.
   - [CDbTransaction]: representa una transacción de base de datos.

A continuación, mencionaremos el uso del DAO Yii en diferentes escenarios.

Estableciendo la Conexión con la Base de Datos
----------------------------------------------

Para establecer la conexión con una base de datos, creamos una instancia de
[CDbConnetion] y la activamos. Es necesario un nombre de fuente de datos (DNS)
para especificar la información requerida para conectarse a la base de datos.
Un nombre de usuario y contraseña pueden ser también necesarios para establecer
la conexión. Será arrojada una excepción en el caso de que ocurra algún error
al establecer la conexión (ej.: DNS malo o nombre de usuario/contraseña inválidos).

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// establish connection. You may try...catch possible exceptions
$connection->active=true;
......
$connection->active=false;  // close connection
~~~

La forma del DNS depende del driver PDO de la base de datos en uso. En general,
un DNS consiste del nombre del driver PDO, seguido por dos puntos (:), seguido
por la sintaxis específica del driver. Mira la [Documentación PDO](http://www.php.net/manual/en/pdo.construct.php)
para una información completa. A continuación, una lista de los formatos DNS
más comunmente utilizados:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`

Puesto que [CDbConnetion] extiende de [CApplicationComponent], podemos también
usarla como un [Componente de Aplicación](/doc/guide/basics.application#application-component).
Para hacerlo, configuramos un componente de aplicación `db` (u otro nombre) en
[Configuración de la Aplicación](/doc/guide/basics.application#application-configuration) como sigue,

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
		),
	),
)
~~~

Podemos entonces acceder a la conexión de la base de datos a través de
`Yii::app()->db` (donde `db` es el nombre que le pusimos al componente)
que ya está activada, a menos que explicitamente configuremos a
[CDbConnection::autoConnect] en `false`. Usar este enfoque, una simple
conexión con la base de datos puede ser usada en diferentes lugares en
nuestro código.

Ejecutando Sentencias SQL
-------------------------

Una vez establecida la conexión con la base de datos, las sentencias SQL
pueden ser ejecutadas usando [CDbCommand]. Se crea una instancia [CDbCommand]
llamando a [CDbConnetion::createCommand()] con la sentencia SQL especificada:

~~~
[php]
$command=$connection->createCommand($sql);
// if needed, the SQL statement may be updated as follows:
// $command->text=$newSQL;
~~~

Una sentencia SQL es ejecutada a través de [CDbCommand] en una de las
siguientes dos maneras:

   - [execute()|CDbCommand::execute]: ejecuta una sentencia SQL que no es
consulta, como `INSERT`, `UPDATE` y `DELETE`. Si es exitosa, devuelve el
numero de filas afectadas por la ejecución.
   - [query()|CDbCommand::query]: ejecuta una sentencia SQL que devuelve
filas de datos, como `SELECT`. Si es exitosa, develve una instancia de
[CDbDataReader], a partid de la cual se recorrer el resultado de las filas
de datos. Por conveniencia, están implementados un conjunto de métodos
`queryXXX()`, los cuales devuelven directamente el resultado de la consulta.

Será arrojada una excepción si ocurre un error durante la ejecución de una
sentencia SQL.

~~~
[php]
$rowCount=$command->execute();   // ejecuta una sentencia SQL sin resultados
$dataReader=$command->query();   // ejecuta una consulta SQL
$rows=$command->queryAll();      // consulta y devuelve todas las filas de resultado
$row=$command->queryRow();       // consulta y devuelve la primera fila de resultado
$column=$command->queryColumn(); // consulta y devuelve la primera columna de resultado
$value=$command->queryScalar();  // consulta y devuelve el primer campo en la primer fila
~~~

Obteniendo Resultados de la Consulta
------------------------------------

Luego de que [CDbCommand::query()] genere la instancia de [CDbDataReader],
podemos recuperar filas del resultado llamando a [CDbDataReader::read()] de
manera repetida. Podemos tambien usar un [CDbDataReader] en un `foreach` de PHP
para recuperar fila a fila.

~~~
[php]
$dataReader=$command->query();
// calling read() repeatedly until it returns false
while(($row=$dataReader->read())!==false) { ... }
// usando foreach para atravesar cada fila de datos
foreach($dataReader as $row) { ... }
// recuperando todos los datos de una vez en un único arreglo
$rows=$dataReader->readAll();
~~~

> Note|Nota: A diferencia de [query()|CDbCommand::query], todos los
métodos `queryXXX()` devuelven datos directamente. Por ejemplo,
[queryRow()|CDbCommand::queryRow] devuelve un arreglo representando
la primera fila del resultado de la consulta.

Usando Transacciones
--------------------

Cuando una aplicación ejecuta unas pocas consultas, cada una leyendo y/o
escribiendo información en la base de datos, es importante aseguarse que
la base de datos no se quede sólo con algunas de las consultas llevadas a
cabo. Para evitar esto, puede ser iniciada una transacción, representada
en Yii como una instancia de [CDbTransaction]:

   - Comenzar la transacción.
   - Ejecutar consultas una a una. Ninguna actualización a la base de datos es visible al mundo exterior.
   - Consignar la transacción. Las actualizaciones se vuelven visibles si la transacción es exitosa.
   - Si una de las consultas falla, la transacción entera se deshace.

El anterior flujo de trabajo puede ser implementado usando el siguiente código:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... other SQL executions
	$transaction->commit();
}
catch(Exception $e) // se arroja una excepción si una consulta falla
{
	$transaction->rollBack();
}
~~~

Vinculando Parámetros
---------------------

Para evitar [ataques de SQL injection](http://en.wikipedia.org/wiki/SQL_injection)
y para mejorar el rendimiento de sentencias SQL usadas repetidas veces, podemos
"preparar" una sentencia SQL con marcadores de posición de parámetros opcionales, que
son marcadores que serán reemplazados con los parámetros reales durante el proceso
de vinculación de parámetros. El driver subyacente de la base de datos lo hará por
nosotros. La vinculación de parámetros debe hacerse antes de que la sentencia SQL
sea ejecutada.

~~~
[php]
// una SQL con dos marcadore de posición, ":username" and ":email"
$sql="INSERT INTO users(username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// reemplaza el marcador de posición ":username" con el valor real de username
$command->bindParam(":username",$username,PDO::PARAM_STR);
// reemplaza el marcador de posición ":email" con el valor real de email
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// inserta otra fila con un nuevo conjunto de parámetros
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

Los métodos [bindParam()|CDbCommand::bindParam] y [bindValue()|CDbCommand::bindValue]
son muy similares. La única diferencia es que el primero vincula un parámetro con una
variable PHP mientras que el último con un valor. Para parámetros  que representan
grandes bloques de memoria de datos, es preferible el primero por consideraciones de
rendimiento.

Para más detalles acerca de la vinculación de parámetros, mira la
[documentación PHP relevante](http://www.php.net/manual/en/pdostatement.bindparam.php).

Vinculando Columnas
-------------------

Al recoger los datos del resultado de una consulta, podemos tambien vincular
columnas con variables PHP para que sean automáticamente rellenadas con los
datos apropiados cada vez que una fila es recogida.

~~~
[php]
$sql="SELECT username, email FROM users";
$dataReader=$connection->createCommand($sql)->query();
// vincular la 1er columna (username) con la variable $username
$dataReader->bindColumn(1,$username);
// vincular la 2da columna (email) con la variable $email
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username e $email contienen el nombre de usuaario y el email de la fila actual
}
~~~

<div class="revision">$Id: database.dao.txt 368 2009-03-06 14:10:00Z freakpol $</div> 