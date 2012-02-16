Uwierzytelnianie użytkownika
===================

Nasza aplikacja blogu potrzebuje umieć rozróżnić użytkownika systemu od gościa. Dlatego też, potrzebujemy zaimplementować funkcjonalność [uwierzytelniania użytkowników](http://www.yiiframework.com/doc/guide/topics.auth).

Jak z pewnością się już zorientowałeś szkielet aplikacji dostarcza już uwierzytelnienia użytkownika poprzez sprawdzanie czy nazwa użytkownika i hasło to jednocześnie `demo` lub `admin`. W części tej zmodyfikujemy odpowiadający temu kod, tak aby uwierzytelnienie odbywało się w oparciu o bazę danych `User`.

Uwierzytelnienie użytkownika wykonywane jest w klasie implementującej interfejs [IUserIdentity]. Szkielet aplikacji używa do tego celu klasy `UserIdentity`. Klasa ta znajduje się w pliku `/wwwroot/blog/protected/components/UserIdentity.php`.

> Tip|Wskazówka: Dla wygody, nazwa pliku klasy musi być taka sama jak odpowiadająca mu nazwa klasy i zakończona rozszerzeniem `.php`. Używając tej konwencji, można odnosić się do klasy używając [aliasów ścieżek](http://www.yiiframework.com/doc/guide/basics.namespace). Na przykład, możemy odnosić się do klasy `UserIdentity` przy użyciu aliasu `application.components.UserIdentity`. Wiele API w Yii jest w stanie rozpoznawać aliasy ścieżek (np. [Yii::createComponent()|YiiBase::createComponent]), dodatkowo używanie aliasów, pozwala uniknąć konieczności umieszczania absolutnych ścieżek do plików w kodzie. Występowanie tych ostatnich często powoduje problemy podczas wdrażania aplikacji.

Zmodyfikujemy klasę `UserIdentity` w następujący sposób:

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

W metodzie `authenticate(), używamy klasy `User` do wyszukiwania wiersza danych w tabeli `tbl_user`, którego kolumna `username` posiada tą samą wartość co nazwa użytkownika, bez uwzględnienia wielkości liter. Przypominamy, że klasa `User` została utworzona za pomocą narzędzia `gii` w poprzedniej części. Ponieważ klasa `User` dziedziczy z [CActiveRecord], możemy wykorzystać [funkcjonalność rekordu aktywnego](http://www.yiiframework.com/doc/guide/database.ar) i uzyskać dostęp do tabeli `User` w obiektowy (OOP) sposób.
 
W celu sprawdzenia czy użytkownik wprowadził prawidłowe hasło, wywołujemy metodę `validatePassword` klasy użytkownika `User`. Musimy zmodyfikować plik `/wwwroot/blog/protected/models/User.php` w następujący sposób. Zauważ, że zamiast przechowywać hasło tekstowo w bazie danych, przechowujemy rezultat haszowania hasła oraz losowo wygenerowany klucz soli. Podczas sprawdzania poprawności hasła wprowadzonego przez użytkownika powinniśmy użyć do porównywania rezultatu haszowania hasła zamiast samego hasła.

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

W klasie `UserIdentity` nadpisujemy również metodę `getId()`, która zwraca wartość `id` dla znalezionego w tabeli `tbl_user` użytkownika. Poprzednia implementacja zwracała nazwę użytkownika (username). Obie właściwości: nazwa użytkownika `username` oraz jego ID `id` będą zachowane w sesji użytkownika user oraz będzie można do nich uzyskać dostęp poprzez `Yii::app()->user` z dowolnego miejsca w naszym kodzie.

> Tip|Wskazówka: W klasie `UserIdentity` odnosimy się do klasy [CUserIdentity] bez bezpośredniego włączania odpowiadającego jej pliku klasy. Dzieje się tak, gdyż klasa [CUserIdentity] jest rdzenną klasą dostarczaną przez framework Yii. Yii automatycznie załączy plik klasy dla każdej z rdzennych klas, podczas pierwszego odniesienia się do niej.
>
>Dokładnie to samo zrobimy z klasą `User`. Dzieje się tak ponieważ plik klasy `User` znajduje się w katalogu `/wwwroot/blog/protected/models`, który został dodany do `include_path` PHP zgodnie z następującymi liniami znajdującymi się w konfiguracji aplikacji:
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
> Powyższa konfiguracja mówi, iż każda z klas, której plik klasy znajduje się w katalogu `/wwwroot/blog/protected/models` lub też `/wwwroot/blog/protected/components` będzie automatycznie załączony jeśli odnosimy się do klasy po raz pierwszy.

Klasa `UserIdentity` jest używana przede wszystkim w klasie `LoginForm` w celu uwierzytelnienia użytkownika bazującego na wprowadzonej na stronie logowania nazwie użytkownika i haśle. Następujący fragment kodu pokazuje w jaki sposób klasa `UserIdentity` jest używana:

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

> Info|Info: Ludzie często mylą tożsamość (UserIdentity) i komponent użytkownika aplikacji `user`. Pierwsze (tożsamość) reprezentuje sposób przeprowadzania uwierzytelnienia, druga zaś używana jest do reprezentowania informacji związanych z aktualnym użytkownikiem. Aplikacja może posiadać tylko jeden komponent użytkownika `user`, ale może posiadać jedną lub więcej klas tożsamościowych w zależności od rodzaju wpieranego sposobu uwierzytelniania. Po uwierzytelnieniu, instancja zawierająca tożsamość może przekazać swoje informacje o stanie do komponentu użytkownika `user`, tak, że ten jest globalnie dostępny poprzez właściwość `user`.

W celu przetestowania zmodyfikowanej klasy `UserIdentity`, możemy otworzyć adres URL `http://www.example.com/blog/index.php` i spróbować zalogować się przy użyciu nazwy użytkownika oraz hasła przechowywanego w tabeli `tbl_user`. Jeśli użyjemy bazy danych dostarczonej przez [demo blogu](http://www.yiiframework.com/demos/blog/), powinniśmy być w stanie zalogować się przy użyciu nazwy użytkownika `demo` oraz hasła `demo`. Zauważ, że ten system blogowy nie dostarcza funkcjonalności zarządzania użytkownikami. W rezultacie użytkownik nie może zmienić swojego konta czy też stworzyć nowego poprzez interfejs sieciowy. Funkcjonalność zarządzania użytkownikami może zostać rozważona jako przyszłe rozszerzenie dla naszej aplikacji.

<div class="revision">$Id: prototype.auth.txt 2333 2010-08-24 21:11:55Z mdomba $</div>