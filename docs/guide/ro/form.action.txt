Creare action
=============

O data ce avem un model, putem incepe sa scriem codul care este necesar pentru a ne folosi
de model. Putem crea acest cod intr-un action al unui controller. Pentru exemplul
cu formularul de logare, este necesar urmatorul cod:

~~~
[php]
public function actionLogin()
{
	$form=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// collects user input data
		$form->attributes=$_POST['LoginForm'];
		// valideaza input-urile si se face redirect spre pagina anterioara,
		// daca are succes validarea
		if($form->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// afiseaza formularul de logare
	$this->render('login',array('user'=>$form));
}
~~~

In codul de mai sus, mai intai cream o instanta `LoginForm`; daca cererea este
o cerere POST (adica s-a apasat butonul de submit), populam `$form`
cu datele primite aflate in `$_POST['LoginForm']`; apoi putem valida input-urile
si daca sunt valide, atunci se face redirectarea utilizatorului catre pagina care
a cerut autentificarea. Daca validarea esueaza, sau daca action-ul este accesat prima data,
atunci generam view-ul `login` al carui continut va fi descris in urmatoarea sub-sectiune.

> Tip|Sfat: In action-ul `login`, folosim `Yii::app()->user->returnUrl` pentru a afla
URL-ul paginii care a avut nevoie anterior de autentificare. Componenta
`Yii::app()->user` este de tip [CWebUser] (sau de timpul unei clase derivate din aceasta) care
contine informatiile despre utilizator (ex. username, status). Pentru mai mult detalii,
trebuie vazuta sectiunea [Autentificare si autorizare](/doc/guide/topics.auth).

Trebuie sa acordam o atentie speciala urmatoarei instructiuni PHP care apare in action-ul `login`:

~~~
[php]
$form->attributes=$_POST['LoginForm'];
~~~

Dupa cum am explicat in [Securizarea asignarilor de atribute](/doc/guide/form.model#securing-attribute-assignments),
aceasta linie de cod populeaza modelul cu datele trimise de catre utilizator.
Proprietatea `attributes` este definita de catre [CModel], care asteapta
un array de perechi nume-valoare si care asigneaza fiecare valoare la
atributul corespunzator al modelului. Deci, daca `$_POST['LoginForm']` ne da
un astfel de array, presupunand ca fiecare atribut necesar este in acest array,
codul de mai sus ar fi echivalent cu urmatorul cod (care poate fi foarte lung daca sunt multe atribute):

~~~
[php]
$form->username=$_POST['LoginForm']['username'];
$form->password=$_POST['LoginForm']['password'];
$form->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|Nota: Pentru a permite `$_POST['LoginForm']` sa ne dea un array in loc de un string,
aderam la o conventie atunci cand denumim campurile input din view. In particular,
pentru un camp input care corespunde cu atributul `a1` din clasa modelului
`C`, vom numi acest input `C[a1]`. De exemplu, vom folosi
`LoginForm[username]` pentru a numi campul input corespunzator cu atributul `username`.

Ce mai ramane de facut este sa cream view-ul `login` care ar trebui sa contina un
formular HTML care sa contina campurile input necesare.

<div class="revision">$Id: form.action.txt 626 2009-02-04 20:51:13Z qiang.xue $</div>