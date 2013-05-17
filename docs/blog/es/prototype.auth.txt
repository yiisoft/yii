Autenticación de Usuario
========================

Nuestra Aplicación de Blog necesita diferenciar entre el propietario del sistema y el resto de los usuarios. Para ello, necesitamos implementar la función de [autenticación de usuarios](http://www.yiiframework.com/doc/guide/topics.auth).

Como ya se podrá haber visto, la aplicación provee autenticación de usuario chequeando que el nombre de usuario y contraseña son ambas `demo` o `admin`. En esta sección, vamos a modificar el código correspondiente para que la autenticación se realice contra la tabla de la base de datos de usuarios `User`.

La autenticación de usuarios es realizada en una clase implementado la interfaz [IUserIdentity]. El esqueleto de la aplicación usa la clase `UserIdentity`para éste propósito. La clase se encuentra en el archivo `/wwwroot/blog/protected/components/UserIdentity.php`.

> Tip|Consejo: Por convención, el nombre de la clase debe ser el mismo que el nombre de la clase correspondiente con la extensión `.php`. Siguiendo esta convención, uno se puede referir a la clase usando el [alias de camino]((http://www.yiiframework.com/doc/guide/basics.namespace). Por ejemplo, podemos hacer referencia a la clase `UserIdentity`con el alias `application.components.UserIdentity`. Muchas APIs en Yii reconocen alias de caminos (e.g. [Yii::createComponent()|YiiBase::createComponent]), y usando alias de camino evitamos la necesidad de embeber caminos absolutos de archivos en el código. La existencia de éstos caminos absolutos por lo general causan problemas cuando se pone en producción una aplicación.

Modificamos el clase `UserIdentity` de la siguiente forma,

~~~
[php]
<?php
class UserIdentity extends CUserIdentity
{
	private $_id;

	public function authenticate()
	{
		$username=strtolower($this->username);
		$user=User::model()->find('LOWER(username)=?',array($username));
		if($user===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if(!$user->validatePassword($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$user->id;
			$this->username=$user->username;
			$this->errorCode=self::ERROR_NONE;
		}
		return $this->errorCode==self::ERROR_NONE;
	}

	public function getId()
	{
		return $this->_id;
	}
}
~~~

En el método `authenticate()`, vamos a usar la clase `User` para fijarnos por una fila en la tabla `tbl_user` cuya columna `username` sea la misma que el nombre de usuario provisto sin importar las mayúsculas o minúsculas. Recordar que la clase `User`fue creada usando la herramienta `gii` en la sección anterior. Como la clase `User` extiende de [CActiveRecord], podemos aprovechar [la función de ActiveRecord](http://www.yiiframework.com/doc/guide/database.ar) para acceder a la tabla `tbl_user` como en la programación orientada a objetos (POO).

Para revisar si un usuario ingresó una contraseña válida, invocamos al método `validatePassword` de la clase `User`. Necesitamos modificar el archivo `/wwwroot/blog/protected/models/User.php` de la siguiente forma. Notar que en vez de guardar la contraseña en texto plano en la base de datos, guardamos el resultado de hash de la contraseña y una clave salt generada aleatoriamente. Cuando validamos la contraseña ingresada por el usuario, vamos a compararla con el resultado de hash.

~~~
[php]
class User extends CActiveRecord
{
	......
	public function validatePassword($password)
	{
		return $this->hashPassword($password,$this->salt)===$this->password;
	}

	public function hashPassword($password,$salt)
	{
		return md5($salt.$password);
	}
}
~~~

En la clase `UserIdentity`, vamos a sobrecargar el método `getId()` que devuelve el valor del `id` del usuario encontrado en la tabla `tbl_user`. Su implementación anterior retornaba el nombre de usuario. Las propiedades de `nombre de usuario` e `id` van a ser guardadas en la sesión de usuario y pueden ser accedidas via `Yii::app()->user` desde cualquier parte de nuestro código.

> Tip|Consejo: En la clase `UserIdentity`, nos referimos a la clase [CUserIdentity] sin incluirla explícitamente en el archivo de la clase correspondiente. Esto se debe a que [CUserIdentity] es una clase del núcleo de Yii. Yii incluirá automáticamente el archivo de la clase o cualquier otra clase del núcleo cuando sea referenciada por primera vez.
>
> También hacemos lo mismo con la clase `User`. Esto es porque el archivo de la clase `User` está ubicado en el directorio `/wwwroot/blog/protected/models` que fue agregado al `include path` de PHP de acuerdo a las siguientes líneas de código encontradas en la configuración de la aplicación:
>
> ~~~
> [php]
> return array(
>     ......
>     'import'=>array(
>         'application.models.*',
>         'application.components.*',
>     ),
>     ......
> );
> ~~~
>
> Esta configuración dice que cualquier clase cuyo archivo de clase sea ubicado en `/wwwroot/blog/protected/models` o `/wwwroot/blog/protected/components` va a ser incluida automáticamente cuando la clase sea referenciada por primera vez.

La clase `UserIdentity` es principalmente usada por la clase `LoginForm` para autenticar un usuario basado en el ingreso de nombre de usuario y contraseña en la página de login. El siguiente fragmento de código nos muestra cómo es usada `UserIdentity`:

~~~
[php]
$identity=new UserIdentity($username,$password);
$identity->authenticate();
switch($identity->errorCode)
{
	case UserIdentity::ERROR_NONE:
		Yii::app()->user->login($identity);
		break;
	......
}
~~~

> Info: La gente tiende a confundirse con la identidad y el componente de aplicación `user`. La primera representa una forma de autenticación, mientras que la segunda es usada para representar la información relacionada al usuario actual. Una aplicación puede tener un sólo componente `user`, pero puede tener varias clases de identidad, dependiendo en el tipo de autenticación que soporte. Una vez autenticado, la instancia de identidad puede pasar su información de estado al componente `user` para que sea accesible globalmente a través de `user`.

Para hacer un test de la clase modificada `UserIdentity`, podemos ir a la URL `http://www.example.com/blog/index.php` y probar ingresar con el nombre de usuario y contraseña que almacenamos en la tabla `tbl_user`. Si usamos la base de datos provista por [el demo de blog](http://www.yiiframework.com/demos/blog/), deberíamos ser capaces de ingresar con el nombre de usuario `demo`y contraseña `demo`. Notar que este sistema de blog no provee la función de gestión de usuarios. Como resultado, un usuario no puede cambiar su cuenta o crear una nueva a través de la interface Web. La función de gestión de usuarios se puede considerar como una mejora futura a nuestra Aplicación de Blog.

<div class="revision">$Id$</div>