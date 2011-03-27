Używanie generatora formularzy
==================

Podczas tworzenia formularzy HTML często mamy do czynienia z pisaniem dużej ilość powtarzającego
się kodu widoków, który trudno jest użyć ponownie w innych projektach. Na przykład, potrzebujemy 
skojarzyć każde pole wejściowe z etykietą tekstową i wyświetlić możliwe błędy pochodzące ze 
sprawdzania poprawności wprowadzonych danych. Aby dać większą możliwość ponownego wykorzystania
tego kodu, możemy używać funkcjonalności generatora formularzy.

Założenia początkowe
--------------

Generator formularzy w Yii używa obiektu [CForm] do reprezentowania specyfikacji potrzebnych do  
opisania formularza HTML, właczając informację o tym, które dane modelu powiązane są z formularzem,
jaki rodzaj pól wejściowych znajdziemy w formularzy oraz jak wygenerować cały formularz.
Programista potrzebuje głównie utworzyć obiekt [CForm] a następnie wywołuje swoje metody generujące
zawartość aby wyświetlić ją w formularzu.

Specyfikacje danych wejściowych formualrza zorganizowane są w formie hierarchii elementów.
Na szczycie hierarcii znajduje się obiekt [CForm]. Ten formularz będący rdzeniem hierarchii 
zarządza swoimi potomkami za pomocą dwóch kolekcji: [CForm::buttons] oraz [CForm::elements]. 
Pierwsza zawiera elementy będace przyciskami (takie jak przycisk wysyłania, czy też resetowania
danych formularza), druga zaś składa się z elementów wejściowych, statycznych tekstów oraz podformularzy.
Podformularzem nazywamy obiekt [CForm] zawierający się w kolekcji [CForm::elements] innego formularza.
Może on posiadać swój własny model danych oraz kolekcje [CForm::buttons] i [CForm::elements].

Gdy użytkownik wysyła formularz, wprowadzone dane do pól wejściowych całej hierarchii formularza są przesyłane, 
włączając te, które należą do podformularzy. [CForm] dostarcza wygodnych metod, które automatycznie 
przypisują dane wejściowe do odpowiadających im atrybutów modelu i dokonuje sprawdzania poprawności
danych.

Tworzenie prostego formularza
----------------------

W dalszej części pokażemy jak używać generatora formularzy w celu utworzenia formularza logowania.

Najpierw napiszemy następujący kod dla akcji login:

~~~
[php]
public function actionLogin()
{
	$model = new LoginForm;
	$form = new CForm('application.views.site.loginForm', $model);
	if($form->submitted('login') && $form->validate())
		$this->redirect(array('site/index'));
	else
		$this->render('login', array('form'=>$form));
}
~~~

W powyższym kodzie utworzyliśmy obiekt [CForm] używając specyfikacji wskazanej
poprzez alias ścieżki `application.views.site.loginForm` (co zostanie później krótko
wyjaśnione). Obiekt [CForm] jest powiązany z modelem `LoginForm` tak jak to opisywaliśmy 
w [Tworzeniu modelu](/doc/guide/form.model).

Jak zapisano w kodzie, jeśli formularz został przesłany i nie wystąpił żaden błąd podczas
sprawdzania poprawności, przekierujemy użytkownika przeglądarki do strony `site/index`. 
W przeciwnym przypadku wygenerujemy widok `login` z formularzem. 

Alias ścieżki `application.views.site.loginForm` aktualnie wskazuje na plik PHP
`protected/views/site/loginForm.php`. Plik powinien zwracać tablicę PHP reprezentującą konfigurację 
potrzebną przez [CForm], tak jak to pokazano poniżej:

~~~
[php]
return array(
	'title'=>'Please provide your login credential',

    'elements'=>array(
        'username'=>array(
            'type'=>'text',
            'maxlength'=>32,
        ),
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),
        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),

    'buttons'=>array(
        'login'=>array(
            'type'=>'submit',
            'label'=>'Login',
        ),
    ),
);
~~~

Konfiguracja to tablica asocjacyjna zawierająca pary nazwa-wartość, które są uzywane do 
zainicjalizowania odpowiednich właściwości [CForm]. Taj jak już wspominialiśmy, 
najważniejszymi właściwościami, które należy skonfigurować są [CForm::elements] oraz [CForm::buttons].
Każdy z nich posiada tablicę określająca liste elementów formularza. Więcej informacji 
o tym w jaki sposób skonfigurować elementy podamy w w dalszych akapitach.

Na koniec napiszemy skrypt widoku `login`, który może być tak prosty jak ten poniżej:

~~~
[php]
<h1>Login</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~

> Tip|Wskazówka: Powyższy kod `echo $form;` jest równoważny do `echo $form->render();`.
> Dzieje się tak, ponieważ [CForm] implementuje magiczną metodę `__toString`,
> która wywołuje metodę `render()` i zwraca jej rezultat w postaci łańcucha będącego
> reprezentacją formularza obiektu.


Definiowanie elementów formularza
------------------------

Uzywając generatora formularzy, większość nakładu pracy przeniesiona jest z pisania kodu
widoku skryptu na określanie elementów formularza. W tym podrozdziale opiszemy jak zdefiniować
właściwość [CForm::elements]. Nie będziemy opisywać kolekcji [CForm::buttons] ze względu na to, że
jej konfiguracja jest prawie taka sama jak [CForm::elements].

Właściwość [CForm::elements] przyjmuje jako swoją wartość tablicę. Każdy element tablicy
określa pojedynczy element formularza, który może być polem wejściowym, statycznym
tekstem lub też podformularzem.

### Definiowanie elementów wejściowych

Element wejściowy składa się głównie z etykiety, pola wejściowego, tekstu podpowiedzi oraz
"wyświetlacza" błędu. Musi on być powiązany z atrybutem modelu. Opis elementu wejśiowego
reprezentowany jest poprzez instancję [CFormInputElement]. Następujący kod w tablicy 
[CForm::elements] określa pojedynczy element wejśiowy:

~~~
[php]
'username'=>array(
    'type'=>'text',
    'maxlength'=>32,
),
~~~

Określa on, iż atrybut modelu posiada nazwę `username` a pole wejściowe jest typu tekstowego `text`,
którego atrybut maksymalnej długości `maxlength` wynosi 32. 
Każda właściwość do zapisu [CFormInputElement] może zostać skonfigurowana w powyższy sposób. Na przykład,
możemy określić opcję podpowiedzi [hint|CFormInputElement::hint] w celu wyświetlenia tekstu podpowiedzi
jak również opcję [items|CFormInputElement::items] jeśli pole wejściowe powinno być listą (ang. list
box), listą rozwijaną (ang. drop-down box list), listą pól wyboru (ang. check-box list) czy też listą
opcji (ang. radio-button list). Jeśli nazwa opcji nie jest właściwością [CFormInputElement], zostanie
potraktowana jako atrybut odpowiadającego mu pola wejściowego HTML. Przykładowo, `maxlength` 
z powyższego przykładu nie jest właściwością [CFormInputElement], więc zostanie wyświetlony jako atrybut
`maxlength` pola wejściowego HTML. 

Opcja [typ|CFormInputElement::type] zasługuje na dodatkową uwagę. Określa ona typ pól wejściowych, które zostaną 
wygenerowane. Na przykład, `text` oznacza, iż zwykłe pole tekstowe powinno zostać wygenerowane;
`password` oznacza, iż pole wejściowe hasła powinno zostać wygenerowane. [CFormInputElement] rozpoznaje następujące
wbudowane typy:

 - text
 - hidden
 - password
 - textarea
 - file
 - radio
 - checkbox
 - listbox
 - dropdownlist
 - checkboxlist
 - radiolist
 
Poza powyższymi, wbudowanymi typami, chcielibyśmy opisać trochę bardziej szczegółowo używanie tych typów
list, do kórych należą `dropdownlist`, `checkboxlist` oraz `radiolist`. Typy te, wymagają ustawienia
właściwości [items|CFormInputElement::items] odpowiadających im elemetnów wejściowych. Można to zrobić 
w następujący sposób:

~~~
[php]
'gender'=>array(
    'type'=>'dropdownlist',
    'items'=>User::model()->getGenderOptions(),
    'prompt'=>'Wybierz jedną z opcji:',
),

...

class User extends CActiveRecord
{
	public function getGenderOptions()
	{
		return array(
			0 => 'Mężczyzna',
			1 => 'Kobieta',
		);
	}
}
~~~

Powyższy kod wygeneruje listę drop rozwijaną, z tekstem podpowiedzi "
The above code will generate a drop-down list selector with prompt text "wybierz jedną z opcji:".
Opcje wyboru to odpowiednio "Mężczyzna" i "Kobieta", które są zwracane przez metodę `getGenderOptions`
klasy modelu użytkownika `User`. 

Poza tymi wbudowanymi typami, opcja [typ|CFormInputElement::type] może również zawierać nazwę klasy widżetu 
lub też alias ścieżki do niej. Klasa widżetu musi dziedziczyć z klasy [CInputWidget] lub [CJuiInputWidget]. Podczas wyświetlania  
elementów wejściowych instancja podanej klasy widżetu zostanie utworzona i wygenerowana. Widżet będzie konfigurowany
przy użyciu specyfikacji, tak jak to się dzieje w przyapdku elementu wejściowego.

### Definiowanie tekstu statycznego

W wielu przypadkach, formularz może zawierać, poza polami wejściowymi, pewien dekorujący kod HTML.
Na przykład, możemy potrzebować linii poziomej w celu oddzielenia różnych części formualrza czy też
obrazka, które będzie potrzebmy w pewnych miejsach aby polepszyć wizualną stronę formularza. Możemy
zdefiniować ten kod HTML jako statyczny tekst w kolekcji [CForm::elements]. Aby to uczynić, po prostu
opiszemy statyczny tekst jako tablicę elementów znajdujących się w odpowiedniej kolejności w [CForm::elements]. 
Na przykład:

~~~
[php]
return array(
    'elements'=>array(
		......
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),

        '<hr />',

        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),
	......
);
~~~

W powyższym przykładzie wstawiliśmy poziomą linię pomiędzy polami hasła `password` a zapamiętaj mnie `rememberMe`.

Stayczny tekst najlepiej jest używać, kiedy zawartość tekstu oraz jego pozycja nie są regularne. 
Jeśli każdy element wejściowy w formularzu potrzebuje być udekorowany w podobny sposób, powinniśmy 
urozmaicić sposób wyświetlania formularza, tak jak to zostało pokrótce wyjaśnione w tej sekcji.

### Definiowanie podformularzy

Podformualrze używane są do dzielenia długich formularzy na kilka, logicznie połączonych części.
Na przykład, możemy podzielić formularz rejestracyjny użytkownika na dwa podformularze:
informacje logowania oraz informacje o profilu. Każdy z podformularzy może lub nie być powiązany
z modelem danych. W przypadku formularza rejestracji, jeśli dane logowania oraz profilu użytkownika 
zapisujemy w dwóch oddzielnych tabelach bazodanowych (a zatem przy użyciu dóch modeli), wtedy 
każdy podformularz będzie powiązany z odpowiadającym im modelem danych. Jeśli zapisujemy wszystko
w jednej tabeli bazy danych, wtedy żaden podformularz nie zawiera modelu danych, ponieważ  
dzielą ten sam model z formularzem głównym (ang. root form).
Podformularz jest również reprezentowany jako obiekt [CForm]. W celu opisania podformularza, 
powinniśmy skonfigurować właściwość [CForm::elements] z elementem, którego typem jest `form`:

~~~
[php]
return array(
    'elements'=>array(
		......
        'user'=>array(
            'type'=>'form',
            'title'=>'Login Credential',
            'elements'=>array(
            	'username'=>array(
            		'type'=>'text',
            	),
            	'password'=>array(
            		'type'=>'password',
            	),
            	'email'=>array(
            		'type'=>'text',
            	),
            ),
        ),

        'profile'=>array(
        	'type'=>'form',
        	......
        ),
        ......
    ),
	......
);
~~~

Tak jak w przypadku formualrza głównego (ang. root form), musimy przede wszystkim określić właściwość
[CForm::elements] dla podformularza. Jeśli podformularz ma być powiązany z medelem danych, możemy
także skonfigurować jego właściwość [CForm::model].

Czasami, możem chcieć reprezentować formularz przy użyciu klasy inna niż domyślna [CForm]. 
Na przykład, co zostanie pokrótce przedstawione w tej sekcji, możemy chcieć rozszerzyć [CForm] 
w celu zmienienia logiki generowania formularzy. 
Poprzez określenie typu elementu wejściowwego jako `form`, podformularz będzie automatycznie reprezentowany 
jako obiekt, którego klasa jest taka sama jak jego formularz nadrzędny. Jeśli zatem określimy element
type elementu wejściowego jako coś takiego `XyzForm` (łańcuch znaków kończący się `Form`),
wtedy podformularz będzie reprezentowany poprzez obiekt `XyzForm`.


Dostęp do elementów formularza
-----------------------

Dostęp do elementów dormualrza jest tak prosty jak dostęp do elementów tablicy. 
Właściwość [CForm::elements] zwraca obiekt [CFormElementCollection], który
rozszerza klasę [CMap] i pozwala na dostęp do swoich elementów tak jak zwykła tablica.
Na przykład, w celu uzyskania dostępu do elementu `username` w przykładzie formularza logowania,
możemy użyć następującego kodu:

~~~
[php]
$username = $form->elements['username'];
~~~
Aby uzyskać dostęp do elementu `email` w przykładzie formularza rejestracji, możemy użyć  

~~~
[php]
$email = $form->elements['user']->elements['email'];
~~~

Ponieważ [CForm] implementuje dostęp jak do tablicy dla swoich właściwości [CForm::elements], 
powyższy kod może zostać uproszczony do:

~~~
[php]
$username = $form['username'];
$email = $form['user']['email'];
~~~


Tworzenie zagnieżdżonych formularzy
----------------------

Opisaliśmy już podformualrze. Fromularze zawierające podformularze nazywamy formularzami zagnieżdżonymi.
W części tej użyjemy formularz rejestracji użytkownika jako przykład do pokazania jak utworzyć
zagnieżdżony formualrz powiązany z wieloma modelami danych. Założymy,
że dane poswiadczające tożsamość użytkownika przechowywane są w modelu `User`,
gdy zaś informacje z profilu przechowywane są w modelu `Profile`.

najpier utworzymy akcję rejestracji `register` w następujący sposób:

~~~
[php]
public function actionRegister()
{
	$form = new CForm('application.views.user.registerForm');
	$form['user']->model = new User;
	$form['profile']->model = new Profile;
	if($form->submitted('register') && $form->validate())
	{
		$user = $form['user']->model;
		$profile = $form['profile']->model;
		if($user->save(false))
		{
			$profile->userID = $user->id;
			$profile->save(false);
			$this->redirect(array('site/index'));
		}
	}

	$this->render('register', array('form'=>$form));
}
~~~

W powyższym kodzie utworzliśmy formularz używając konfiguracji określownej w `application.views.user.registerForm`.
Po tym jak formularz jest przesłany a sprawdzenie poprawności zakończyło się sukcesem, staramy się zapisać
modele użytkonika oraz profilu. Zwracamy model użytkownika i profilu poprzez dostęp do właściwości `model` 
odpowiadającego im obiektu podformularza. Ponieważ sprawdzanie poprawności zostało już wykonane,
wołamy metodę `$user->save(false)` aby pominąć walidację. Dokłanie tak samo robimy w przyapdku modelu profilu.

Następnie tworzymy plik konfiguracji formularza `protected/views/user/registerForm.php`:

~~~
[php]
return array(
	'elements'=>array(
		'user'=>array(
			'type'=>'form',
			'title'=>'Login information',
			'elements'=>array(
		        'username'=>array(
		            'type'=>'text',
		        ),
		        'password'=>array(
		            'type'=>'password',
		        ),
		        'email'=>array(
		            'type'=>'text',
		        )
			),
		),

		'profile'=>array(
			'type'=>'form',
			'title'=>'Profile information',
			'elements'=>array(
		        'firstName'=>array(
		            'type'=>'text',
		        ),
		        'lastName'=>array(
		            'type'=>'text',
		        ),
			),
		),
	),

    'buttons'=>array(
        'register'=>array(
            'type'=>'submit',
            'label'=>'Register',
        ),
    ),
);
~~~

W powyższym kodzie, gdy określiliśmy każdy podformularz, również zdefiniowaliśmy jego właściwość tytułu [CForm::title].
Domyślnie logika generowania formularzy zamknie każdy podformularz w elemencie field-set, który użyje tej
właściwości jako jego tytułu.

Na koniec, utworzymy prosty skrypt widoku rejestracji `register`:

~~~
[php]
<h1>Register</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~


Dostosowywanie wyświetlania formularza
------------------------

Największą korzyścia z używanie generatora formularzy jest separacja warstwy logiki 
(konfiguracja formularza zapisana jest w oddzielnym pliku) i warstwy prezentacji
(metoda [CForm::render]). W rezultacie, możemy dostosowywać wyświetlanie formularza zarówno przez nadpisywanie
[CForm::render] jak i dostarczanie częściowych widoków do generowania formularzy. Oba sposoby 
mogą zachować konfigurację formualrza nietkniętą i być w łatwy sposób ponownie użyte.

Podczas nadpisywania metody [CForm::render] należy głównie przejrzeć kolekcje 
[CForm::elements] oraz [CForm::buttons] i wywołać metodę [CFormElement::render] 
dla każdego z elementów formularzy. Na przykład:

~~~
[php]
class MyForm extends CForm
{
	public function render()
	{
		$output = $this->renderBegin();

		foreach($this->getElements() as $element)
			$output .= $element->render();

		$output .= $this->renderEnd();

		return $output;
	}
}
~~~

Możemy również napisać skrypt `_form` do wygenerowania formualrza:

~~~
[php]
<?php
echo $form->renderBegin();

foreach($form->getElements() as $element)
	echo $element->render();

echo $form->renderEnd();
~~~

Aby użyć tego skryptu widoku, po prostu wywołujemy:

~~~
[php]
<div class="form">
$this->renderPartial('_form', array('form'=>$form));
</div>
~~~

Jeśli ogólne generowanie elementów nie sprawdza się dla danego formularza (na przykład, formularz
potrzebuje pewnych niestandardowych dekoracji dla pewnych elementów), możemy w pliku widoku 
zrobić co następuje:

~~~
[php]
tutaj: kilka złożonych elementy UI

<?php echo $form['username']; ?>

tutaj: kilka złożonych elementy UI

<?php echo $form['password']; ?>

tutaj: kilka złożonych elementy UI
~~~

Chociaż wygląda na to, że w ostatnim podejściu generator formularzy nie przynosi zbyt wiele korzyści,  
gdyż nadal porzebujemy pisać podobną ilość kodu formularza. Mimo to, jest to sposób wciąż przynoszący 
korzyści, gdyż formularz jest opisywany za pomocą oddzielnego pliku konfiguracyjnego, co pomaga 
programistom lepiej skupić się na logice.

<div class="revision">$Id: form.builder.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>