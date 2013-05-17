Komponent
=========

Aplikacje Yii są zbudowane na komponentach, które są obiektami napisanymi zgodnie 
ze specyfikacją. Komponent jest instancją klasy [CComponent] lub jej klas potomnych.
Używanie komponentu sprowadza się głównie do używania jego właściwości oraz 
wywoływania/obsługi jego zdarzeń. Klasa bazowa [CComponent] określa jak należy
zdefiniować właściwości oraz zdarzenia.

Właściwość komponentu
------------------

Właściwość komponentu jest jak publiczna zmienna obiektu. Możemy przeczytać jej 
wartość lub też przypisać ją do niej. Na przykład:

~~~
[php]
$width=$component->textWidth;     // pobierz właściwość textWidth 
$component->enableCaching=true;   // ustaw właściwość enableCaching
~~~

Aby zdefiniować właściwość komponentu, możemy po prostu zadeklarować publiczną 
zmienną w klasie komponentu. Jednakże, bardziej elastycznym sposobem, jest zdefiniowanie
getterów oraz setterów w następujący sposób:

~~~
[php]
public function getTextWidth()
{
    return $this->_textWidth;
}

public function setTextWidth($value)
{
    $this->_textWidth=$value;
}
~~~

Powyższy kod definiuje właściwość do zapisu nazwaną `textWidth` (nazwa zależy 
od wielkości liter). Podczas czytania właściwości, wywoływana jest metoda `getTextWidth()`,
a wartość, którą zwróciła staje się wartością właściwości; podobnie, gdy ustawiamy
właściwość, wywoływana jest metoda `setTextWidth()`. Jeśli setter nie został zdefiniowany,
właściwość będzie można tylko odczytywać a próba jej ustawienia zakończy się 
rzuceniem wyjątku. Używanie getterów oraz setterów do definiowania właściwości 
ma tą zaletę, że dodatkowa logika (np. sprawdzanie poprawności, wywoływanie zdarzeń)
może zostać wywołana podczas odczytu lub zapisu właściwości.

>Note|Uwaga: Istnieje drobna różnica pomiędzy właściwością zdefiniowaną za pomocą 
metod getter/setter a zmienną w klasie. Nazwa pierwszej nie zależy od wielkości liter
w przeciwieństwie do drugiej.

Zdarzenia komponentu
---------------

Zdarzenia komponentów są specjalnymi właściwościami, które biorą metody (nazywane 
`uchwytami zdarzeń` - event handlers) jako swoje wartości. Dołączając (wiążąc) 
metodę do zdarzenia, spowodujemy, że metoda ta będzie wywołana automatycznie w miejscu
gdzie wołane jest zdarzenie. Dlatego też, zachowanie komponentu, może zostać zmienione 
w sposób nieprzewidziany podczas tworzenia go.

Zdarzenie komponentu jest zdefiniowane poprzez utworzenie metody, której nazwa
rozpoczyna się słowem `on`. Tak jak nazwy właściwości zdefiniowane za pomocą metod
getter/setter, nazwy zdarzeń również nie zależą od wielkości liter. Następujący 
kod definiuje zdarzenie `onClicked`:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

gdzie `$event` jest instancją klasy [CEvent] lub jej klas potomnych reprezentujących 
parametry zdarzenia.

Możemy, przypiąć metodę do tego zdarzenia w następujący sposób:

~~~
[php]
$component->onClicked=$callback;
~~~

gdzie `$callback` referuje do poprawnego callbacku PHP. Może to być globalna funkcja
lub metoda klasy. W przypadku tej drugiej, callback musi zostać przekazany jako 
tablica: `array($object,'nazwaMetody')`.

Składnia uchwytu zdarzenia powinna być następująca:

~~~
[php]
function methodName($event)
{
    ......
}
~~~

gdzie `$event` jest parametrem opisującym zdarzenie (pochodzi z `raiseEvent()`).
Parametr `$event` jest instancją klasy [CEvent] lub jej klas pochodnych. 
Musi ona posiadać co najmniej informację o tym kto wywołuje zdarzenie.

Uchwytem zdarzenia może być również anonimowa funkcja co jest wspierane przez PHP w wersji 5.3 lub wyższej. Na przykład:

~~~
[php]
$component->onClicked=function($event) {
  ......
}
~~~

Jeśli wywołamy teraz `onClicked() zdarzenie `onClicked` zostanie wywołane 
(wewnątrz `onClicked()`) i dołączony uchwyt zdarzenia będzie wywołany automatycznie.

Do zdarzenia można dołączyć wiele uchwytów. Kiedy zdarzenie jest wywoływane, 
uchwyty będą wywołane w kolejności w jakiej zostały dołączone do zdarzenia. 
Jeśli uchwyt decyduje o nie wywołaniu pozostałych uchwytów, powinien mieć ustawioną 
wartość [$event->handled|CEvent::handled] jako true.


Zachowanie komponentu
------------------

Komponenty posiadają wsparcie dla [mixin](http://en.wikipedia.org/wiki/Mixin)
oraz można do nich dołączyć jeden lub więcej zachowań (behaviour). *Zachowanie* jest 
obiektem, którego metody mogą być 'dziedziczone' przez dołączone do niego komponenty   
bezpośrednio poprzez sumowanie funkcjonalności zamiast specjalizacji (np. zwykłe 
dziedziczenie klas). Do komponentu można dołączyć kilka zachowań i w ten sposób
osiągnąć 'wielokrotne dziedziczenie'.

Klasa zachowania musi implementować interfejs [IBehavior]. Dla większości zachowań wystarczy,
jeśli będą one rozszerzać klasę bazową [CBehavior]. Jeśli zachowanie powinno zostać 
dołączone do [modelu](/doc/guide/basics.model) może także dziedziczyć z [CModelBehavior] lub 
[CActiveRecordBehavior] które implementują dodatkowe funkcjonalności charakterystyczne
dla modeli.

Aby używać zachowania, musi być ono najpierw dołączone do komponentu poprzez wywołanie metody 
zachowania [attach()|IBehavior::attach]. Następnie możemy wywołać metodę zachowania 
poprzez komponent:

~~~
[php]
// $name jednoznacznie identyfikuje zdarzenie komponentu
$component->attachBehavior($name,$behavior);
// test() jest metodą $behavior
$component->test();
~~~

Dostęp do dołączonego zdarzenia jest taki sam jak dla zwykłej właściwości komponentu. 
Na przykład, jeśli nazwa zdarzenia `tree` jest dołączona do komponentu, możemy otrzymać
referencję do tego obiektu zdarzenia używając:

~~~
[php]
$behavior=$component->tree;
// równoznaczne do: 
// $behavior=$component->asa('tree');
~~~

Zdarzenie może być tymczasowo wyłączone, tak że jego metody są niedostępne poprzez komponent.
Na przykład:

~~~
[php]
$component->disableBehavior($name);
// następujące wyrażenie rzuci wyjątek
$component->test();
$component->enableBehavior($name);
// teraz zadziała
$component->test();
~~~

Jest możliwe, że dwa zdarzenia dołączone do tego samego komponentu mają metody 
o tej samej nazwie. W takim przypadku, metoda pierwszego dołączonego zdarzenia będzie
miała pierwszeństwo.


Zachowania używane razem ze [zdarzeniami](/doc/guide/basics.component#component-event) stają się jeszcze bardziej 
potężne. Zachowanie, dołączane do komponentu, może dołączyć pewną część swoich metod 
do pewnych zdarzeń komponentu. W taki sposób, zdarzeniu udostępnia się możliwość
obserwowania lub zmiany zwykłego przepływu wykonywania czynności komponentu. 

Właściwości zachowania dostępne są poprzez komponent, do którego są one przypisane. 
Właściwości obejmują zarówno publiczne zmienne klasy jak i metody get (gettery) 
i set (settery) zachowania. Na przykład, jeśli zachowanie   
posiada właściwość o nazwie `xyz` i jest ono powiązane z komponentem `$a`, to możemy 
użyć wyrażenia `$a->xyz` w celu otrzymania dostępu do właściwości zachowania.

<div class="revision">$Id: basics.component.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>