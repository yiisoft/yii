Dostosowywanie modelu komentarza
=========================

Dla modelu komentarza `Comment` musimy głównie dostosować metody `rules()` oraz `attributeLabels()`. Metoda `attributeLabels()` zwraca mapowanie pomiędzy nazwą atrybutu a etykietą atrybutu. Nie potrzebujemy dotykać metody `relations()` ze względu na to, że kod wygenerowany przez narzędzie `yiic` jest wystarczająco dobry.


Dostosowywanie metody `rules()`
----------------------------

Najpierw dostosowujemy reguły sprawdzania poprawności wygenerowane przez narzędzie `yiic`. Następujące reguły są używane dla komentarzy:

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

W powyższym kodzie, określamy, że atrybuty: autor `author`, e-mail `email` oraz zawartość `content` są atrybutami wymaganymi (nie mogą być puste); długość atrybutu autor `author`, e-mail `email` oraz adres URL `url` nie może przekraczać 128 (znaków); atrybut e-maila `email` musi być poprawnym adresem mailowym; atrybut adresu URL `url` musi być poprawnym adresem URL.


Dostosowywanie metody `attributeLabels()`
--------------------------------------

Następnie dostosowujemy metodę `attributeLabels()` aby zdefiniować etykiety wyświetlane dla każdego z atrybutów modelu. Metoda ta zwraca tablicę par nazwa-etykieta. 

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

> Tip|Wskazówka: Jeśli etykieta dla atrybutu nie jest zadeklarowana w metodzie `attributeLabels()` do wygenerowania odpowiedniej etykiety zostanie użyty algorytm. Na przykład, etykieta `Create Time` będzie wygenerowana dla atrybutu `create_time` bądź też `createTime`.


Dostosowywanie procesu zapisywania
--------------------------

Ponieważ chcemy zapisać czas utworzenia komentarza, nadpisujemy metody `beforeSave()` komentarza `Comment` tak jak to zrobiliśmy dla modelu wiadomości `Post`:

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