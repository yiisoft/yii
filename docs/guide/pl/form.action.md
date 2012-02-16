Tworzenie akcji
===============

Kiedy mamy już model, możemy rozpocząć pisanie logiki, którą potrzebujemy aby 
manipulować modelem. Umieszczamy tą logikę wewnątrz kontrolera akcji. Na przykład, 
dla logowania, potrzebujemy następującego kodu:

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// collects user input data
		$model->attributes=$_POST['LoginForm'];
		// validates user input and redirect to previous page if validated
		if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// displays the login form
	$this->render('login',array('model'=>$model));
}
~~~

W powyższym przykładzie na początku utworzyliśmy instancję modelu `LoginForm`; gdy żądanie 
jest żądaniem POST (co oznacza, że dane z formularza logowania zostały przesłane), 
wypełniamy `$model` przesłanymi danymi z `$_POST['LoginForm']`; następnie sprawdzamy 
poprawność danych wejściowych i jeśli są poprawne, przekierowujemy przeglądarkę użytkownika
na stronę, która poprzednio wymagała uwierzytelnienia. W przypadku gdy walidacja nie powiedzie się,
albo gdy akcja wywoływana jest po raz pierwszy generujemy widok `login`, którego 
zawartość będzie opisana w następnej sekcji.

> Tip|Wskazówka: Podczas akcji `login` używamy `Yii::app()->user->returnUrl` aby otrzymać
adres URL strony, która wymagała uwierzytelnienia. Komponent `Yii::app()->user` jest typu [CWebUser] 
(lub jego klasy potomne)i reprezentuje dane sesyjne użytkownika (np. nazwę użytkownika, 
status). Aby otrzymać więcej informacji zobacz [Uwierzytelnienie i autoryzację](/doc/guide/topics.auth).

Przyjrzyjmy się uważnie następującemu wyrażeniu PHP, które pojawia się w akcji 
logowania `login`:

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

Tak jak napisaliśmy w [ochronie przypisania atrybutów](/doc/guide/form.model#securing-attribute-assignments),
ta linia kodu wypełnia model danymi przesłanymi przez użytkownika. Właściwość 
`attributes` jest definiowana przez klasę [CModel] i oczekuje tablicy par nazwa-wartość, 
które potem przypisuje do każdej wartości atrybutu odpowiedniego modelu. 
Zatem, jeśli `$_POST['LoginForm']` zwróci nam taką tablicę, powyższy kod będzie 
równoznaczny do następującego, długiego kodu (zakładając ze każdy wymagany atrybut 
jest obecny w tablicy):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|Uwaga: Aby umożliwić `$_POST['LoginForm']` zwracanie tablicy zamiast łańcucha znaków, 
pozostajemy przy pewnej konwencji podczas nazywania pól wejściowych w widoku. 
W szczególności, dla pól wejściowych odpowiadających atrybutowi `a` modelu klasy `C`
nazywamy go `C[a]`. Na przykład, użyjemy `LoginForm[username]` aby nazwać pole wejściowe 
odpowiadające atrybutowi `username`.

Zadanie jaki nam pozostało, to utworzenie widoku `login`, który powinien zawierać 
formularz HTML wraz z wymaganymi polami wejściowymi.

<div class="revision">$Id: form.action.txt 1837 2010-02-24 22:49:51Z qiang.xue $</div>