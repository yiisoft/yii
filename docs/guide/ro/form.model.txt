Creare model
============

Inainte de a scrie cod HTML necesar pt un formular, trebuie sa ne decidem de ce fel
de date vom avea nevoie de la utilizatori si ce reguli trebuie sa indeplineasca
aceste date. O clasa de model poate fi folosita pentru a inregistra aceste date.
Un model, asa cum este definit in sb-sectiunea [Model](/doc/guide/basics.model),
este locul central pentru pastrarea input-urilor de la utilizatori si pentru
validarea lor.

In functie de cum folosim input-urile primite de la utilizator, putem crea
doua tipuri de modele. Daca datele de la utilizator sunt colectate, folosite
si apoi abandonate, atunci cream un [model de formular](/doc/guide/basics.model); daca
datele de la utilizator sunt colectate si apoi salvate in baza de date,
atunci folosim un [active record](/doc/guide/database.ar). Ambele tipuri de model
sunt derivate din aceeasi clasa [CModel] care defineste interfata necesara unui formular.

> Note|Nota: In general, folosim modele de formular in exemplele din aceasta sectiune.
Dar toate aceste exemple pot fi aplicate si modelelor de tip [active record](/doc/guide/database.ar).

Definirea clasei modelului
--------------------------

Mai jos, cream un model `LoginForm` folosit pentru a colecta input-urile de la
utilizator dintr-o pagina de logare. Pentru ca informatiile despre logare sunt
folosite doar pentru a autentifica utilizatorul, nu trebuie sa le salvam in baza de date.
Si deci vom crea `LoginForm` ca fiind un model de formular.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

Am declarat trei atribute in `LoginForm`: `$username`, `$password` si
`$rememberMe`. Sunt folosite pentru a pastra username-ul si parola introduse
de catre utilizator (plus optiunea `remember me`). Pentru optiunea `$rememberMe`,
valoarea implicita este `false`, care inseamna ca optiunea va fi initial afisata
ne-bifata. 

> Info: In loc sa denumim proprietati aceste trei variabile, folosim termenul de
*atribute* pentru a face diferenta fata de proprietatile normale. Un atribut
este o proprietate care este in special folosita pentru a pastra date care au venit
de la utilizator sau din baza de date.

Declarare reguli de validare
----------------------------

O data ce utilizatorul trimite datele din formular si modelul este populat, trebuie
sa ne asiguram ca input-urile sunt valide inainte de a ne folosi de ele.
Facem acest lucru prin executarea validarii fata de un set de reguli.
Specificam regulile de validare in metoda `rules()`, care ar trebui sa returneze
un array cu configuratia regulilor.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;

	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('password', 'authenticate'),
	);
	}

	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())  // vrem sa permitem autentificarea doar cand nu sunt erori
		{
			$identity=new UserIdentity($this->username,$this->password);
			if($identity->authenticate())
			{
				$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 de zile
				Yii::app()->user->login($identity,$duration);
			}
			else
				$this->addError('password','Incorrect password.');
		}
	}
}
~~~

In codul de mai sus, `username` si `password` sunt obligatorii, iar
`password` va fi validata de catre metoda `authenticate()`.

Fiecare regula returnata de catre metoda `rules()` trebuie sa fie in formatul urmator:

~~~
[php]
array('AttributeList', 'Validator', 'on'=>'ScenarioList', ...optiuni aditionale)
~~~

`AttributeList` este un string de nume de atribute separate prin virgula
care trebuie sa fie validate cu regula in cauza; `Validator` specifica ce fel de
validare ar trebui executata; parametrul `on` este optional si specifica
o lista de scenarii in care regula ar trebui sa fie aplicata; optiunile aditionale
sunt date in perechi de nume-valoare care sunt folosite pentru a initializa valorile
proprietatilor corespunzatoare din clasa validator.

Sunt trei cazuri in care putem specifica `Validator` in regula de validare.
In primul caz, `Validator` poate fi numele unei metode dintr-o clasa de model,
ca de exemplu metoda `authenticate` din exemplul de mai sus. Metoda validator trebuie sa
aiba urmatoarea declaratie:

~~~
[php]
/**
 * @param string numele atributului care trebuie validat
 * @param array optiuni specificate in regula de validare
 */
public function ValidatorName($attribute,$params) { ... }
~~~

In al doilea caz, `Validator` poate fi numele unei clase validator. Cand este aplicata
regula, o instanta a acestei clase validator va fi creata pentru a executa validarea
efectiva. Optiunile aditionale din regula sunt folosite pentru a initializa
valorile atributelor instantei. O clasa validator trebui sa fie derivata din [CValidator].

> Note|Nota: Cand specificam reguli pentru un model de tip active record, putem folosi
o optiune speciala `on`. Optiunea poate fi `'insert'` sau `'update'`, astfel incat
regula va fi aplicata doar la inserarea, sau respectiv actualizarea inregistrarii.
Daca nu este precizat `on`, regula va fi aplicata in ambele cazuri atunci cand vom
apela metoda `save()`.

In al treilea caz, `Validator` poate fi un alias predefinit catre o clasa validator.
In exemplul de mai sus, numele `required` este un alias catre [CRequiredValidator],
care asigura ca valoarea atributului va contine ceva. Mai jos avem o lista completa de
alias-uri predefinite de validatori:

   - `captcha`: alias pentru of [CCaptchaValidator], asigura ca atributul este acelasi
cu codul de verificare afisat intr-un [CAPTCHA](http://en.wikipedia.org/wiki/Captcha).

   - `compare`: alias pentru [CCompareValidator], asigura ca atributul este egal cu un
alt atribut sau o constanta.

   - `email`: alias pentru [CEmailValidator], asigura ca astributul este o adresa
de email valida.

   - `default`: alias pentru [CDefaultValueValidator], defineste o valoare implicita
atributelor specificate.

   - `file`: alias pentru [CFileValidator], asigura ca atributul contine un nume
pentru un fisier pentru care se face upload.

   - `filter`: alias pentru [CFilterValidator], transforma atributul cu un filtru.

   - `in`: alias pentru [CRangeValidator], asigura ca atributul este intr-o lista predefinita de valori.

   - `length`: alias pentru [CStringValidator], asigura ca lungimea valorii atributului
este intr-un anumit interval.

   - `match`: alias pentru [CRegularExpressionValidator], asigura ca valoarea atributului se potriveste
cu o expresie regulata.

   - `numerical`: alias pentru [CNumberValidator], asigura ca valoarea atributului este este un numar valid.

   - `required`: alias pentru [CRequiredValidator], asigura ca atributul va contine ceva.

   - `type`: alias pentru [CTypeValidator], asigura ca atributul este de un anumit tip de date.

   - `unique`: alias pentru [CUniqueValidator], asigura ca valoarea atributului este unica
intr-o coloana a unei tabele din baza de date.

   - `url`: alias pentru [CUrlValidator], asigura ca valoarea atributului este un URL valid.

Mai jos, prezentam exemple folosind validatori predefiniti:

~~~
[php]
// username este obligatoriu
array('username', 'required'),

// username trebuie sa aiba intre 3 si 12 caractere
array('username', 'length', 'min'=>3, 'max'=>12),

// in scenariul de inregistrare utilizator, password trebuie sa fie la fel cu password2
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// in scenariul de logare, password trebuie sa fie analizat de metoda authenticate()
array('password', 'authenticate', 'on'=>'login'),
~~~


Securizarea asignarilor de atribute
-----------------------------------

> Note|Nota: Asignarea de atribute in functie de scenariu este disponibila incepand cu versiunea 1.0.2 a Yii.

Dupa ce o instanta a unui model a fost creata, trebuie sa populam atributele sale cu
datele trimise de catre utilizatorul web. Putem face acest lucru mai usor folosind
o asignare masiva:

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
	$model->setAttributes($_POST['LoginForm'], 'login');
~~~

Ultima instructiune este o asignare masiva care asigneaza fiecare intrare din
`$_POST['LoginForm']` la atributul corespunzator din model in scenariul
`login` (specificat in al doilea parametru). Codul de mai sus este echivalent cu codul de mai jos:

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name este atribut sigur)
		$model->$name=$value;
}
~~~

Task-ul de a decide daca o informatie de intrare este sigura sau nu revine
unei metode `safeAttributes()` cu un scenariu specificat. In cazul modelului
[CFormModel], implicit, metoda returneaza toate variabilele publice ale modelului, 
acest lucru insemnand ca toate aceste variabile sunt sigure.
In cazul modelului [CActiveRecord], implicit, metoda returneaza toate coloanele
tabelei cu exceptia cheii primare, acest lucru insemnand ca toate aceste atribute sunt sigure.
In practica, trebuie sa suprascriem de obicei aceasta metoda pentru a enumera
acele atribute care sunt intr-adevar sigure, in functie de scenariu.
De exemplu, un model user poate contine multe atribute, dar in scenariul `login`
avem nevoie doar de atributele `username` si `password`.
Putem specifica aceasta limitare in felul urmator:

~~~
[php]
public function safeAttributes()
{
	return array(
		parent::safeAttributes(),
		'login' => 'username, password',
	);
}
~~~

Mai precis, valoarea returnata de metoda `safeAttributes` ar trebui sa fie de forma urmatoare:

~~~
[php]
array(
   // aceste atribute pot fi asignate masiv in orice scenariu
   // care nu este specificat mai jos
   'attr1, attr2, ...',
	 *
   
   // aceste atribute pot fi asignate masiv doar in scenariul 1
   'scenario1' => 'attr2, attr3, ...',
	 *
   
   // aceste atribute pot fi asignate masiv doar in scenariul 2
   'scenario2' => 'attr1, attr3, ...',
)
~~~

Daca un model nu se potriveste cu vreun scenariu (spre exemplu este folosit
doar intr-un scenariu, sau toate scenariile impart acelasi set de atribute sigure)
valoarea returnata poate fi simplificata sub forma unui singur string:

~~~
[php]
'attr1, attr2, ...'
~~~

In cazul intrarilor de date care nu sunt sigure, trebuie sa le asignam atributelor
corespunzatoare folosind instructiuni de asignare individuale, in felul urmator:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~


Declansarea validarii
---------------------

O data ce modelul deste populat cu datele trimise de utilizator, putem apela [CModel::validate()]
pentru a declansa procesul de validare a datelor. Metoda returneaza o valoare care indica
daca procesul de validare a avut succes sau nu. In cazul modelului [CActiveRecord],
validarea poate de asemenea fi declansata atunci cand apelam metoda [CActiveRecord::save()].

Cand apelam [CModel::validate()], putem specifica un parametru de scenariu.
Vor fi executate doar regulile de validare care se aplica scenariului respectiv.
O regula de validare se aplica intr-un scenariu daca optiunea `on` a regulii
nu este setata, sau daca contine numele de scenariu specificat. Daca nu specificam
scenariul atunci cand apelam [CModel::validate()], vor fi executate doar acele reguli
pentru care optiunea `on` nu este setata.

De exemplu, executam urmatoarea instructiune pentru a executa validarea in cazul
inregistrarii unui utilizator:

~~~
[php]
$model->validate('register');
~~~

Putem declara regulie de validare in clasa modelului formularului in felul urmator:

~~~
[php]
public function rules()
{
	return array(
		array('username, password', 'required'),
		array('password_repeat', 'required', 'on'=>'register'),
		array('password', 'compare', 'on'=>'register'),
	);
}
~~~

Prin urmare, prima regula va fi aplicata in toate scenariile, in timp ce urmatoarele
doua reguli vor fi aplicate doar in cazul scenariului `register`.

> Note|Nota: Validarea in functie de scenariu este disponibila incepand cu versiunea 1.0.1 a Yii.


Obtinerea regulilor de validare
-------------------------------

Putem folosi [CModel::hasErrors()] pentru a verifica daca au fost erori de validare.
Daca au fost erori, putem folosi [CModel::getErrors()] pentru a obtine mesajele de eroare.
Ambele metode pot fi folosite pentru toate atributele sau pentru un singur atribut.

Label-uri de atribute
---------------------

Cand proiectam un formular, de obicei trebuie sa afisam un label label pentru fiecare camp input.
Label-ul explica utilizatorului ce fel de informatie trebuie introdusa in campul input.
Deci putem adauga manual un label intr-un view, ar fi mult mai flexibil si convenabil
sa specificam label-ul in modelul formularului.

Implicit, [CModel] va returna label-ul unui atribut ca fiind numele respectivului atribut.
Acest comportament poate fi modificat prin suprascrierea metodei [attributeLabels()|CModel::attributeLabels].
Dupa cum vom vedea in urmatoarele sub-sectiuni, specificand label-uri in model ne permite sa cream
un formular mult mai puternic si mult mai rapid.

<div class="revision">$Id: form.model.txt 598 2009-01-29 20:19:28Z qiang.xue $</div>