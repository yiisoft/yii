Tworzenie modelu
==============

Zanim rozpoczniemy pisanie kodu HTML wymaganego przez formularze, powinniśmy
przemyśleć jakich danych oczekujemy od użytkownika końcowego oraz do jakich reguł 
dane te powinny się stosować. Klasa modelu może być używana do zapisania tych 
informacji. Model, tak jak to opisano w podpunkcie [model](/doc/guide/basics.model),
jest centralnym miejscem do przechowywania danych wejściowych od użytkownika oraz 
do sprawdzania ich poprawności.

W zależności od tego w jaki sposób używamy danych wejściowych dostarczonych 
przez użytkownika, możemy stworzyć dwa typy modelów. Jeśli dane wejściowe są zbierane, 
używane a na końcu wyrzucane, powinniśmy stworzyć [model formularza](/doc/guide/basics.model); 
jeśli dane wejściowe są zbierane a następnie zapisywane w bazie danych, powinniśmy
w zamian używać [rekordu aktywnego](/doc/guide/database.ar). Oba typy modeli 
dziedziczą tą samą klasę bazową [CModel], która definiuje wspólny interfejs 
wymagany dla formularzy.

> Note|Uwaga: W tej sekcji używamy głównie modeli formularza jako przykładów.
Jednakże ma to również zastosowanie do modeli [rekordu aktywnego](/doc/guide/database.ar).

Definiowanie klasy modelu.
--------------------

Poniżej utworzymy klasę modelu `LoginForm` używaną do zbierania danych wejściowych 
od użytkownika na stronie logowania. Ponieważ informacje pozwalające się zalogować
używane są tylko do uwierzytelnienia użytkownika i nie muszą być zapisywane, utworzymy
`LoginForm` jako model formularza.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

W modelu `LoginForm` zadeklarowaliśmy 3 atrybuty: `$username` (nazwa użytkownika), 
`$password` (hasło) oraz `$rememberMe` (zapamiętaj mnie). Są one używane do 
zapamiętywania wprowadzonych przez użytkownika informacji o jego nazwie, haśle oraz 
opcji pozwalającej określić, czy użytkownik chce zapamiętać swoje dane logowania.
Ponieważ pole `$rememberMe` posiada domyślną wartość ustawioną na `false`, 
odpowiadająca mu opcja jest wyświetlana inicjalnie w formularzu jako odznaczona.

> Info|Info: Zamiast nazywać te zmienne właściwościami, będziemy używali nazwy *atrybuty* 
aby odróżnić jest od zwykłych właściwości. Atrybut jest właściwością, która jest
głównie używana do przechowywania danych pochodzących od danych wejściowych użytkownika 
lub z bazy danych.

Tworzenie reguł sprawdzania poprawności (ang. Declaring Validation Rules)
--------------------------

Kiedy użytkownik wyśle dane wejściowe a model zostanie nimi wypełniony, zanim ich użyjemy, 
musimy się upewnić, że dane wejściowe są poprawne. Robi się to poprzez wywołanie 
sprawdzania poprawności danych wejściowych względem zestawu reguł. Definiujemy je 
w metodzie `rules()`, która powinna zwracać tablicę zawierającą konfiguracje reguł.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;

	private $_identity;

	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);
	}

	public function authenticate($attribute,$params)
	{
		$this->_identity=new UserIdentity($this->username,$this->password);
		if(!$this->_identity->authenticate())
			$this->addError('password','Incorrect username or password.');
	}
}
~~~

Powyższy kod mówi, iż zarówno użytkownik `username` jak i hasło `password` są wymagane, 
hasło `password` powinno zostać uwierzytelnione a zapamiętaj mnie `rememberMe` powinno
przyjąc wartośc typu boolean.

Każda regułą zwrócona przez metodę `rules()` musi posiadać następujący format:

~~~
[php]
array('AttributeList', 'Validator', 'on'=>'ScenarioList', ...dodatkowe opcje)
~~~

gdzie `AttributeList` jest łańcuchem znaków, zawierającym oddzielone przecinkami 
nazwy atrybutów, które powinny zostać sprawdzone z regułą; `Validator` określa jaki rodzaj
porównywania powinien zostać użyty; parametr `on` jest opcjonalny i określa 
listę scenariuszy, gdzie reguła powinna mieć zastosowanie; dodatkowe opcje są parami
nazwa-wartość i są używane do zainicjalizowania wartości odpowiadających im właściwości 
walidatora.

Istnieją trzy sposoby aby określić `walidator` (ang. Validator) w regułach sprawdzania.  
W pierwszym `walidator` może być nazwą metody w klasie modelu, jak `authenticate` 
w przykładzie powyżej. Metoda walidatora musi posiadać następującą składnię:

~~~
[php]
/**
 * @param string $attribute nazwa atrybutu sprawdzanego
 * @param array $params opcje określone w regule walidacji
 */
public function ValidatorName($attribute,$params) { ... }
~~~

W drugim, `walidator` może być nazwą klasy walidacji. Kiedy reguła ma zastosowanie, 
instancja walidatora zostanie stworzona w celu dokonania sprawdzenia danych. Dodatkowe 
opcje w regule są używane do zainicjalizowania wartości instancji atrybutu. 
Klasa walidatora musi rozszerzać klasę [CValidator].

W trzecim, `walidator` może być predefiniowanym aliasem do klasy walidatora. 
W powyższym przykładzie, nazwa `required` (wymagany) jest aliasem  do klasy [CRequiredValidator], 
która sprawdza czy wartość atrybutu sprawdzanego nie jest pusta. Poniżej znajduje się
pełna lista predefiniowanych aliasów walidatorów:

    - `boolean`: alias klasy [CBooleanValidator], sprawdza czy atrybut posiada wartość
    zarówno [CBooleanValidator::trueValue] czy też [CBooleanValidator::falseValue].

   - `captcha`: alias klasy [CCaptchaValidator], sprawdza czy atrybut zgadza się
   z kodem weryfikującym wyświetlanym przy użyciu [CAPTCHA](http://en.wikipedia.org/wiki/Captcha).

   - `compare`: alias klasy [CCompareValidator], sprawdza czy atrybut jest równy
   innemu atrybutowi bądź stałej.

   - `email`: alias klasy [CEmailValidator], sprawdza czy atrybut jest poprawnym
   adresem email.
   
   - `date`: alias klasy [CDateValidator], sprawdza czy atrybut jest poprawną datą, czasem 
   lub datą z czasem (ang. datetime value).

   - `default`: alias klasy [CDefaultValueValidator], przypisuje domyślną wartość 
   do danego atrybutu.
   
   - `exist`: alias klasy [CExistValidator], sprawdza czy wartość atrybutu znajduje się w określonej kolumnie tabeli.

   - `file`: alias klasy [CFileValidator], sprawdza czy atrybut zawiera nazwę
   wczytywanego pliku.

   - `filter`: alias klasy [CFilterValidator], transformuje atrybut przy użyciu filtra.

   - `in`: alias klasy [CRangeValidator], sprawdza czy dana zawiera się 
   określonej wcześniej liście wartości.

   - `length`: alias klasy [CStringValidator], sprawdza czy długość danych zgadza się 
   z pewną wartością.

   - `match`: alias klasy [CRegularExpressionValidator], sprawdza czy dane 
   pasują do wyrażenia regularnego.

   - `numerical`: alias klasy [CNumberValidator], sprawdza czy dane są poprawnym 
   numerem.

   - `required`: alias klasy [CRequiredValidator], sprawdza czy atrybut nie jest pusty.

   - `type`: alias klasy [CTypeValidator], sprawdza czy atrybut jest określonego typu.

   - `unique`: alias klasy [CUniqueValidator], sprawdza czy dana jest unikalna w kolumnie 
   tabeli bazy danych.

   - `url`: alias klasy [CUrlValidator], sprawdza czy dana jest poprawnym adresem URL.

Poniżej pokazujemy przykłady pokazujące sposób użycia predefiniowanych walidatorów:

~~~
[php]
// nazwa użytkownika jest wymagana
array('username', 'required'),
// nazwa użytkownika musi zawierać się pomiędzy 3 a 12 znakami
array('username', 'length', 'min'=>3, 'max'=>12),
// dla scenariusza rejestracji register, hasło z atrybutu password musi zgadzać się z tym
// z atrybutu password2
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// dla scenariusza logowania login, atrybut zawierający hasło musi by  uwierzytelniony
array('password', 'authenticate', 'on'=>'login'),
~~~


Zabezpieczanie przypisywania atrybutów (ang. Securing Attribute Assignments)
------------------------------

Po utworzeniu instancji modelu, często potrzebujemy wypełnić jej atrybuty danymi 
dostarczonymi przez użytkowników końcowych. Można to zrobić wygodnie przy użyciu 
następującego grupowego przypisania:

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
  $model->attributes=$_POST['LoginForm'];
~~~

Ostatnia linia jest grupowym przypisaniem, które przypisuje każdy wpis w `$_POST['LoginForm']` 
do odpowiadającego mu atrybutu w modelu dla scenariusza logowania `login`. Jest to równoznaczne z następującym przypisaniem:

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name is a safe attribute)
		$model->$name=$value;
}
~~~


Kluczową sprawą jest, aby określić, które atrybuty są bezpieczne. Na przykład, jeśli 
uwidocznimy klucz główny  tablicy jako bezpieczny, wtedy osoba atakująca dostanie
szanse do zmodyfikowania klucza głównego danego rekordu i tym samym manipulację danych,
do których nie jest on upoważniony.

###Deklarowanie bbezpiecznych atrybutów

Atrybut traktowany jest jako bezpieczny jeśli pojawia się on w regule 
sprawdzania poprawności, posaidającej zastosowanie w danym scenariuszu. Na przykład,

~~~
[php]
array('username, password', 'required', 'on'=>'login, register'),
array('email', 'required', 'on'=>'register'),
~~~

W powyższym kodzie, atrybuty `username` oraz `password` są wymaganymi atrybutami  
w scenariuszu `register`. W rezultacie, jesli dokonamy masowego przypisania  
w scenariuszu `login`, tylko `username` oraz `password` będą masowo przypiane, gdyż 
tylko one są atrybutami, które pojawiły się w regule sprawdzania poprawności dla
scenariusza `login`. Z drugiej strony, jeśli będzie to scenariusz `register`,
wszystkie trzy atrybuty będą masowo przypisane.

~~~
[php]
// scenariusz login
$model=new User('login');
if(isset($_POST['User']))
  $model->attributes=$_POST['User'];

// scenariusz register 
$model=new User('register');
if(isset($_POST['User']))
  $model->attributes=$_POST['User'];
~~~

Dlaczego więc korzystać z takiej polityki w celu określenia czy atrybut jest bezpieczny
czy też nie? Uzasadnieniem tego jest to, że jeśli atrybut posiada już jedną lub kilka
reguł sprawdzania poprawności nie musimy się o nic więcej martwić.  

Należy pamiętać, co ważne, iż reguły sprawdzania poprawności są używane do sprawdzania
danych wprowadzanych przez użytkownika, rzadziej do danych, które generujemy w kodzie
(np. stempel czasu, automatycznie generowany klucz główny). Dlatego też NIE NALEŻY
DODAWAĆ reguł walidacji dla tych atrybutów, które nie są oczekiwane jako dane wejściowe
od użytkownika.
Czasami, chcemy zadeklarować atrybut jako bezpieczny, pomimo tego, że nie mamy dla niego żadnej 
określonej reguły. Dobryn przykładem jest atrybut reprezentujący zawartość aartykułu.
który może zawierać dowolne dane wprowadzone przez użytkownika. W tym celu możemy
użyć specjalnej reguły `safe` aby osiągnąc ten cel:

~~~
[php]
array('content', 'safe')
~~~

Dla komplementarności, istnieje również reguła `unsafe`, które używana jest do zadeklarowania 
wprost, iż atrybut jest niebezpieczny: 

~~~
[php]
array('permission', 'unsafe')
~~~

Atrybut `unsafe` jest używany rzadko i jest wyjątkiem do naszego wcześniej zdefiniowanego
atrybutu `safe`.

Dla danych wejściowych, które nie są bezpieczne, musimy je przypisać do odpowiadających
mu atrybutów przy użyciu indywidualnych wyrażeń przypisujących wartość, jak w kolejnym
przykładzie:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~


Wyzwalanie sprawdzania poprawności (ang. Triggering Validation)
---------------------

Kiedy model jest wypełniony danymi pochodzącymi od użytkownika, możemy wywołać metodę 
[CModel::validate()] w celu rozpoczęcia procesu sprawdzania poprawności danych. 
Metoda ta zwraca wartość na podstawie której podejmowana jest decyzja, czy sprawdzanie 
wartości zakończyło się sukcesem czy też nie. Dla modeli [CActiveRecord] sprawdzanie 
poprawności może być również automatycznie wyzwolone, podczas wywołania metody [CActiveRecord::save()].

Scenariusz możemy ustawić za pomocą właściwości [scenario|CModel::scenario] 
i w ten sposób wskazać, który zestaw reguł sprawdzania poprawności powinien zostać zastosowany.

Sprawdzanie poprawności jest wykonywane na podstawie scenariusza. Właściwość [scenario|CModel::scenario]
określa, który scenariusz modelu jest wykorzystywany oraz który zestaw reguł sprawdzania
poprawności powinien zostać użyty. Na przykład, w scenariuszu logowania `login` chcemy sprawdzić
jedynie nazwę użytkownika `username` oraz hasło `password` dla modelu użytkownika. Natomiast
w scenariuszu rejestracji `register` potrzebujemy sprawdzić większą ilość danych, takich jak
email `email`, adres `address`, itp. Następujący przykład pokazuje jak wykonać sprawdzanie
poprawności w scenariuszu rejestracji `register`:

~~~
[php]
// tworzenie modelu użytkownika User w scenariuszu rejestracji. Równoznaczne z:
// $model=new User;
// $model->scenario='register';
$model=new User('register');

// wypełnianie modelu wartościami wejściowymi
$model->attributes=$_POST['User'];

// wykonanie sprawdzania poprawności
if($model->validate())   // jeśli dane wejściowe są poprawne
    ...
else
    ...
~~~

Odpowiedni scanariusz z którym powiązana jest reguła może zostać określony
poprzez opcję `on` w regule. Jeśli opcja `on` nie została ustawiona, oznacza to
że reguła będzie używana we wszystkich scenariuszach. Na przykład,

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

Pierwsza reguła będzie miała zastosowanie do wszystkich scenariuszy,
zaś następne dwie będą używane dla scenariusza rejestracji `register`.

Zwracanie błędów sprawdzania poprawności (ang. Retrieving Validation Errors)
----------------------------

Po sprawdzaniu poprawności, ewentualne błędy będą zachowane w obiekcie modelu.
Możemy je zwrócić poprzez wywołanie metody [CModel::getErrors()]
oraz [CModel::getError()]. Obie metody różnią od siebie się tym, iż pierwsza
zwróci *wszystkie* błędy dla określonego atrybutu modelu, gdy druga
zwróci jedynie *pierwszy* błąd.

Etykiety atrybutów (ang. Attribute Labels)
----------------

Podczas projektowania formularza często potrzebujemy wyświetlić etykietę (tekst) dla
każdego pola wejściowego. Etykieta mówi użytkownikowi jakiego rodzaju informacji 
oczekujemy podczas wprowadzania danych do pola. Chociaż możemy zapisać na sztywno 
etykietę w widoku, dostarczy nam to większej elastyczności i będzie bardziej wygodne, 
jeśli zapiszemy je w odpowiednim modelu.

Domyślnie [CModel] zwróci nazwę atrybutu jako etykietę. Można to dostosować do swoich 
potrzeb poprzez nadpisanie metody [attributeLabels()|CModel::attributeLabels]. 
Jak zobaczymy w następnych punktach, zdefiniowanie etykiet w modelu umożliwia nam tworzenie 
formularzy szybciej i lepiej.

<div class="revision">$Id: form.model.txt 3482 2011-12-13 09:41:36Z mdomba $</div>