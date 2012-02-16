Bezpieczeństwo
========

Zabezpieczenie przed Cross-site Scripting 
-------------------------------
Cross-site scripting (znany jako XSS) występuje gdy aplikacja webowa otrzyma    
złośliwe dane od użytkownika. Często atakujący wstrzykują skrypty JavaScript,
VBScript, ActiveX, HTML, lub Flash do podatnych aplikacji w celu oszukania 
użytkowników aplikacji w celu uzyskania jego danych. Na przykład, źle zaprojektowany
system forum, może wyświetlić dane wprowadzone przez użytkownika w poście forum 
bez żadnego sprawdzania.

Atakujący może wtedy wstrzyknąć kawałek złośliwego kodu JavaScript do postu, tak,   
że gdy inni użytkownicy będą czytać ten post, JavaScript nieoczekiwanie zostanie 
uruchomiony na ich komputerze.

Jednym z najbardziej ważnych kroków, aby uchronić się przed atakami XSS jest 
sprawdzenie danych wprowadzonych przez użytkownika przed ich wyświetleniem. 
Aby osiągnąć ten cel, można przeprowadzić rozkodowanie HTML w danych wejściowych użytkownika.
Jednakże, w pewnych sytuacjach rozkodowywanie HTML może być niepożądane, ponieważ
dezaktywuje ono wszystkie tagi HTML.

Yii dołącza pracę wykonaną przez [HTMLPurifier](http://htmlpurifier.org/)
i dostarcza deweloperowi w postaci użytecznego komponentu nazwanego [CHtmlPurifier], 
który zawiera [HTMLPurifier](http://htmlpurifier.org/). Komponent ten jest w stanie 
usunąć cały złośliwy kod przy użyciu dokładnie zbadanej, bezpiecznej białej listy 
i upewnić się, że filtrowana zawartość zgodna jest ze standardami.

Komponent [CHtmlPurifier] może być używany zarówno jako [widżet](/doc/guide/basics.view#widget)
jak i [filtr](/doc/guide/basics.controller#filter). Gdy [CHtmlPurifier] jest używany jako widżet, 
 będzie on oczyszczał zwartość wyświetlaną w jego ciele w widoku. Na przykład:

~~~
[php]
<?php $this->beginWidget('CHtmlPurifier'); ?>
...wyświetla tutaj zawartość wprowadzoną przez użytkownika...
<?php $this->endWidget(); ?>
~~~


Cross-site Request Forgery Prevention
-------------------------------------

Ataki Cross-Site Request Forgery (CSRF) występują kiedy złośliwa strona internetowa 
powoduje, iż przeglądarka internetowa użytkownika wykonuje niechcianą akcję na zaufanej stronie.
Na przykład złośliwa strona internetowa posiada stronę zawierającą obrazek, którego tag 
`src` wskazuje nas stronę banku: `http://bank.example/withdraw?transfer=10000&to=someone`.
Jeśli użytkownik, który posiada ciasteczko z danymi z logowania do strony bankowej
odwiedzi tą złośliwą stronę, akcja przetransferowania 1000 dolarów to kogoś innego będzie
wywołana. 
 W przeciwieństwie do ataku XSS, ???TODO???
which exploits the trust a user has for a particular site,
CSRF exploits the trust that a site has for a particular user.

Aby uchronić się przed atakami CSRF, ważnym jest 

, it is important to abide to the rule
that `GET` requests should only be allowed to retrieve data rather
than modify any data on the server. And for `POST` requests, they
should include some random value which can be recognized by the server
to ensure the form is submitted from and the result is sent back to
the same origin.


Yii implementuje system ochrony przed atakami CSRF w celu pokonania ataków opartych 
na żądaniach `POST`. Jest on oparty na przechowywaniu losowej wartości w ciasteczku
i porównywaniu jej z wartością przekazywaną w żądaniu `POST`.

Domyślnie, ochrona przed atakami CSRF jest niedostępna. Aby ją udostępnić, skonfiguruj
komponent aplikacji [CHttpRequest] w [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration)
w następujący sposób:

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCsrfValidation'=>true,
		),
	),
);
~~~

Aby wyświetlić formularz, wywołaj metodę [CHtml::form] zamiast pisania tagów formularza 
HTML bezpośrednio w kodzie. Metoda [CHtml::form] osadzi wymaganą, losową wartość 
w ukrytym polu, tak, że będzie ona przekazana celem sprawdzenia, czy nie nastąpił atak CSRF.


Ochrona przed atakami wykorzystującymi ciasteczka (ang. Cookie Attack Prevention)
------------------------
Ochrona ciasteczek przed atakami jest szczególnie ważna, ponieważ ID sesji jest 
zazwyczaj przechowywane w ciasteczkach. Jeśli ktoś może posiąść ID sesji, 
może właściwie posiąść wszystkie odpowiadające sesji informacje.

Istnieje kilka środków zaradczych, aby zapobiec atakom przy wykorzystaniu ciasteczek.

* Aplikacja może używać SSL do utworzenia bezpiecznego kanału komunikacji i jedynie 
  przekazywać ciasteczko identyfikujące przez połączenie HTTPS. Dlatego atakujący
  nie mogą odszyfrować zawartości przekazywanej w ciasteczku. 
* Wygaszaj sesje poprawnie, włączając w to wszystkie ciasteczka oraz tokeny sesji,
 w celu zmniejszenia prawdopodobieństwa ataku.
* Zapobiegaj atakom XSS, które powodują wykonanie samowolnie obcego kodu w przeglądarce użytkownika 
i odsłaniają zawartość jego ciasteczek.
* Sprawdzaj zawartość ciasteczek i sprawdzaj czy ich zawartość nie została zmieniona.

Yii implementuje system sprawdzania poprawności ciasteczek, który chronik ciasteczka przed
modyfikacjami. W szczególności wykonuje on sprawdzenie HMAC dla zawartości ciasteczek, 
jeśli sprawdzanie poprawności ciasteczek jest włączone.

Domyślnie sprawdzanie poprawności ciasteczek jest wyłączone. Aby je włączyć, skonfiguruj 
komponent aplikacji [CHttpRequest] w [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration)
w następujący sposób:

~~~
[php]
return array(
	'components'=>array(
		'request'=>array(
			'enableCookieValidation'=>true,
		),
	),
);
~~~

Aby używać systemu sprawdzania poprawności ciasteczek dostarczonego wraz z Yii,
należy odczytywać zawartość ciasteczek poprzez kolekcję [cookies|CHttpRequest::cookies] 
zamiast bezpośrednio przez `$_COOKIES`:

~~~
[php]
// zwraca ciasteczko o określonej nazwie
$cookie=Yii::app()->request->cookies[$name];
$value=$cookie->value;
......
// wysyła ciasteczko
$cookie=new CHttpCookie($name,$value);
Yii::app()->request->cookies[$name]=$cookie;
~~~


<div class="revision">$Id: topics.security.txt 2535 2010-10-11 08:28:08Z mdomba $</div>