Доработка модели Comment
========================

В модели `Comment` нам необходимо поправить методы `rules()` и `attributeLabels()`.
Метод `attributeLabels()` возвращает массив заголовков для указанных полей.
Метод `relations()` исправлять не будем так как код, сгенерированный `yiic` нам
подходит.

Изменение метода `rules()`
--------------------------

Начнём с уточнения правил валидации, сгенерированных при помощи `yiic`.
Для комментариев будем использовать следующие правила:

~~~
[php]
public function rules()
{
	return array(
		array('content, author, email', 'required'),
		array('author, email, url', 'length', 'max'=>128),
		array('email','email'),
		array('url','url'),
	);
}
~~~

Здесь мы указываем, что атрибуты `author`, `email` и `content` обязательны.
Длина `author`, `email` и `url` не может превышать 128 символов. Атрибут `email`
должен содержать корректный email-адрес. `url` должен содержать корректный URL.


Изменение метода `attributeLabels()`
------------------------------------

Изменим метод `attributeLabels()`. Зададим свои подписи атрибутам. Этот метод
возвращает массив пар имя атрибута-подпись.

~~~
[php]
public function attributeLabels()
{
	return array(
		'id' => 'Id',
		'content' => 'Comment',
		'status' => 'Status',
		'create_time' => 'Create Time',
		'author' => 'Name',
		'email' => 'Email',
		'url' => 'Website',
		'post_id' => 'Post',
	);
}
~~~

> Tip|Подсказка: Если подпись атрибута не задана в `attributeLabels()`,
для её генерации используется специальный алгоритм. К примеру, для
атрибутов `create_time` и `createTime` подпись будет выглядеть как
`Create Time`.


Изменение процесса сохранения
-----------------------------

Для того, чтобы записывать время создания комментария, переопределим
метод `beforeSave()` класса `Comment` также, как это сделано для модели `Post`:

~~~
[php]
protected function beforeSave()
{
	if(parent::beforeSave())
	{
		if($this->isNewRecord)
			$this->create_time=time();
		return true;
	}
	else
		return false;
}
~~~


<div class="revision">$Id: comment.model.txt 1733 2010-01-21 16:54:29Z qiang.xue $</div>