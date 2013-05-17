Tworzenie rozszerzeń
===================

Ponieważ rozszerzenie zostało pomyślane do używania przez innych deweloperów, 
potrzeba dodatkowego wysiłku aby go stworzyć. Poniżej kilka ogólnych wskazówek:

* Rozszerzenie powinno być samowystarczające. Oznacza to, jego zależności zewnętrzne
powinny być minimalne. Rozszerzenie może przyprawić o ból głowy jeśli wymaga
instalacji dodatkowych pakietów, klas lub plików.
* Pliki należące do rozszerzenia powinny zostać zorganizowane w tym samym katalogi,
którego nazwa jest identyczna jak nazwa rozszerzenia.
* Klasy w rozszerzeniu powinny być poprzedzone pewną literą(ami) aby uniknąć konfliktów
nazw z klasami z innych rozszerzeń.
* Rozszerzenie powinno być dostarczone wraz ze szczegółowymi instrukcjami instalacyjnymi
oraz udokumentowanym API. Zredukuje to czas i nakłady poświęcone przez innych programistów
podczas używania rozszerzenia.
* Rozszerzenie powinno używać odpowiedniej licencji. Jeśli chcesz uczynić 
twoje rozszerzenie dostępne zarówno dla projektów open-source jak i o kodzie zamkniętym
powinieneś rozważyć używanie licencji takich jak BSD, MIT, itp. ale nie GPL, gdyż ta wymaga
aby kod wywodził się od innego kodu open-source.

Następnie, opiszemy jak utworzyć nowe rozszerzenie, odpowiednio do katergori
opisanych w [przeglądzie](/doc/guide/extension.overview). Opis ten również ma zastosowanie
kiedy tworzysz komponenty do użytku we własnych projektach.

Komponent aplikacji
---------------------

[Komponent aplikacji](/doc/guide/basics.application#application-component) powinien 
implementować interfejs [IApplicationComponent] lub dziedziczyć z [CApplicationComponent]. 
Główna metoda, która musi zostać zaimplementowana to [IApplicationComponent::init], 
w której to komponent wykonuje pewną pracę początkową. Metoda ta jest wywoływana
zaraz po tym jak komponent jest utworzony i wartości początkowe (określone w [konfiguracji 
aplikacji](/doc/guide/basics.application#application-configuration))
zostały nadane.

Domyślnie, komponent aplikacji jest tworzony i inicalizowany tylko wtedy, gdy
żądamy dostępu do nie go po raz pierwszy podczas obsługiwania żądania. Jeśli komponent
aplikacji musi być utworzony zaraz po tym jak instancja aplikacji została utworzona, 
powinien jedo ID zostać dodane do listy we właściwości [CApplication::preload].


Zachowanie (ang. Behavior)
--------

Aby utworzyć zachowanie nalezy zaimplementować interfejs [IBehavior]. Dla ułatwienia 
Yii dostarcza klasę bazową [CBehavior], która już implementuje ten interfejs oraz 
dostarcza pewne dodatkowe, przydatne metody. Klasy potomne muszą głównie implementować 
dodatkowe metody, które zamierzamy udostepnić komponentom, do których dołączamy zachowanie.

Podczas tworzenia zachowań dla klas [CModel] oraz [CActiveRecord], możemy odpowiednio 
rozszerzać odpowiadające im klasy zachowań [CModelBehavior] oraz [CActiveRecordBehavior].
Te bazowe klasy oferują dodatkowe funkcjonalności, które zostały stworzone specjalnie dla
klas [CModel] oraz [CActiveRecord]. Na przykład klasa [CActiveRecordBehavior] implementuje 
zestaw metod, które odpowiadają zdarzeniom wywoływanym w obiekcie AR w trakcie jego cyklu życia.
Metoda pochodna może zatem nadpisywać te metody, aby "wtrącić" dodatkowy kod, który
będzie uczestniczyć w cyklu życia AR.

Następujący kod pokazuje przykład zachowanie dla rekordu aktywnego (AR). 
Kiedy takie zachowanie jest dołączane do obiektu AR oraz kiedy obiek AR jest zapisywany
poprzez wywołanie metody `save()`, przypisze ono automatycznie atrybutom `create_time`
oraz `update_time` wartość znacznika czasu (ang. timestamp).

~~~
[php]
class TimestampBehavior extends CActiveRecordBehavior
{
  public function beforeSave($event)
  {
    if($this->owner->isNewRecord)
      $this->owner->create_time=time();
    else
      $this->owner->update_time=time();
  }
}
~~~


Widżet
------

[Widżet](/doc/guide/basics.view#widget) powinien dziedziczyć z klasy [CWidget] 
lub jej klas pochodnych.

Najprostszym sposobem utworzenia nowego widżetu jest rozszerzenie istniejącego 
widżetu oraz nadpisanie jego metod lub zmienienie jego domyślnych wartości własności.
Na przykład, jeśli chcesz używać ładniejszego stylu dla klasy [CTabView], powinieneś 
skonfigurować jej właściwość [CTabView::cssFile] jeśli używasz tego widżetu. 
Możesz również rozszerzyć klasę [CTabView] następująco, tak że nie będzie wymagane
dłużej używanie konfiguracji tej właściwości podczas używania widżetu.

~~~
[php]
class MyTabView extends CTabView
{
	public function init()
	{
		if($this->cssFile===null)
		{
			$file=dirname(__FILE__).DIRECTORY_SEPARATOR.'tabview.css';
			$this->cssFile=Yii::app()->getAssetManager()->publish($file);
		}
		parent::init();
	}
}
~~~

Powyżej, nadpisujemy metodę [CWidget::init] oraz przypisujemy URL do [CTabView::cssFile] 
naszego nowego stylu CSS jeśli właściwość nie jest ustawiona. Umieszczamy nowy plik stylu
CSS w tym samym katalogu zawierającym plik klasy `MyTabView`, tak że mogą one być 
spakowane jako rozszerzenie. Ponieważ plik stylu CSS nie jest dostępny w sieci
musimy go opublikować jako zasób.

Aby utworzyć nowy widżet od zera, musimy głównie zaimplementować dwie metody:
[CWidget::init] oraz [CWidget::run]. Pierwsza metoda jest wołana kiedy używamy `$this->beginWidget` 
do wstawiania widżetu w widok a druga metoda jest wołana wtedy, gdy wołamy `$this->endWidget`.
Jeśli chcemy przechwycić i przetworzyć zawartość wyświetlaną pomiędzy wywołaniami 
tych dwóch metod, możemy rozpocząć [buforowanie wyjścia](http://us3.php.net/manual/en/book.outcontrol.php)
w [CWidget::init] oraz zwrócić zbuforowane wyjście w [CWidget::run] w celach
dalszego przetwarzania.

Widżet często dołącza CSS, JavaScript lub inne pliki zasobów do strony, która używa 
widżetu. Nazywamy te pliki *zasobami* (ang. assets) ponieważ znajdują się razem z 
plikami klas widżetu i są zazwyczaj niedostępne dla internauty. Aby uczynić te pliki
dostępnymi w sieci, musimy opublikować je używając [CWebApplication::assetManager], 
tak jak pokazano w poniższym fragmencie kodu. Poza tym, jeśli chcemy dołączyć plik CSS
lub JavaScript do aktualnej strony, musimy go zarejestrować używając [CClientScript]:

~~~
[php]
class MyWidget extends CWidget
{
	protected function registerClientScript()
	{
		// ...opublikuj tutaj pliki CSS lub JavaScript...
		$cs=Yii::app()->clientScript;
		$cs->registerCssFile($cssFile);
		$cs->registerScriptFile($jsFile);
	}
}
~~~

Widżet może również posiadać swój własny plik widoku. Jeśli tak, utwórz katalog 
nazwany `views` w katalogu zawierającym plik klasy widżetu i umieść w nim wszystkie 
pliki widoku. W klasie widżetu, w celu wygenerowania widoku widżetu użyj 
`$this->render('ViewName')`, co jest bardzo podobne do tego co robiliśmy w kontrolerze.

Akcja
------

[Akcja](/doc/guide/basics.controller#action) powinna dziedziczyć z klasy [CAction]
lub jej klas pochodnych. Główną metodą, która musi być zaimplementowana dla akcji to [IAction::run].

Filtr
------
[Filtr](/doc/guide/basics.controller#filter) powinien dziedziczyć z [CFilter] lub jego 
klas pochodnych. Główne metody, które muszą zostać zaimplementowane dla filtru 
to [CFilter::preFilter] oraz [CFilter::postFilter]. Pierwsza jest wołana przed wywołaniem akcją
gdy druga wołana jest po.

~~~
[php]
class MyFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logika stosowana przed wykonaniem akcji
		return true; // false if the action should not be executed
	}

	protected function postFilter($filterChain)
	{
		// logika stosowana po wykonaniu akcji
	}
}
~~~

Parametr `$filterChain` jest typu [CFilterChain] i zawiera informacje dotyczące 
akcji, któa jest aktualnie filtrowana.

Kontroler
----------
[Kontroler](/doc/guide/basics.controller) dystrybuowany jako rozszerzenie powinien 
dziedziczyć z [CExtController], zamiast z [CController]. Głównym powodem jest to, że 
[CController] zakłada, że pliki widoku kontrolera znajdują się w `application.views.ControllerID`, 
gdy zaś [CExtController] zakłada, że pliki widoku znajdują się w katalogu `views`, 
który jest podkatalogiem katalogu zawierającego plik klasy kontrolera. Z tego też powodu,
łatwiej można redystrybuować kontroler, ponieważ pliki widoku znajdują się razem z plikami 
klasy kontrolera.

Walidator
---------
Walidator powinien dziedziczyć z [CValidator] i implementować swoją metodę [CValidator::validateAttribute].

~~~
[php]
class MyValidator extends CValidator
{
	protected function validateAttribute($model,$attribute)
	{
		$value=$model->$attribute;
		if($value has error)
			$model->addError($attribute,$errorMessage);
	}
}
~~~

Konsola poleceń
---------------
[Konsola poleceń](/doc/guide/topics.console) powinna dziedziczyć z [CConsoleCommand] 
i implementować swoją metodę [CConsoleCommand::run]. Opcjonalnie, możemy nadpisać 
[CConsoleCommand::getHelp] aby dostarczyć trochę informacji dotyczących polecenia.

~~~
[php]
class MyCommand extends CConsoleCommand
{
	public function run($args)
	{
		// $args zwraca tablicę argumentów wiersza polecenia dla tego polecenia
	}

	public function getHelp()
	{
		return 'Używanie: jak używać tego polecenia';
	}
}
~~~

Moduł
------
Zerknij do sekcji dotyczącej [modułów](/doc/guide/basics.module#creating-module) 
aby zobaczyć jak utworzyć moduł.

Ogólną wytyczną dla tworzenia moduły jest to, iż powinien on być samowystarczalny. 
Pliki zasobów (takie jak CSS, JavaScript, obrazki), które są używane w module,
powinny być dystrybuowane razem z modułem, a moduł powinien opublikować jest, tak
żeby były one dostępne dla internauty.


Generyczne komponenty
-----------------
Tworzenie rozszerzeń generycznych komponentów to jak pianie klasy. Również tutaj, 
komponent powinien być samowystarczalny, tak że może ony być w łatwy sposób
wykorzystany przez innych deweloperów.

<div class="revision">$Id: extension.create.txt 1423 2009-09-28 01:54:38Z qiang.xue $</div>