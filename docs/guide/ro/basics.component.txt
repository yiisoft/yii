Componente
==========

Aplicatiile Yii sunt construite pe baza componentelor, acestea fiind
obiecte scrise pentru a indeplini un anumit rol. O componenta este o
instanta a clasei [CComponent] - sau a unei clase derivate din ea. 
Folosirea unei componente implica in general accesarea proprietatilor sale
si activarea/tratarea (raise/handle) evenimentelor sale. Clasa de baza
[CComponent] contine metodele de definire a proprietatilor si evenimentelor.

Proprietatile unei componente
-----------------------------

O proprietate este asemanatoare cu o variabila publica a unui obiect.
Putem citi valoarea ei sau putem sa ii atribuim o valoare. De exemplu:

~~~
[php]
$width=$component->textWidth;     // get the textWidth property
$component->enableCaching=true;   // set the enableCaching property
~~~

Pentru a defini o proprietate intr-o componenta, putem sa declaram simplu
o variabila publica in clasa componentei. Totusi, o modalitate mai flexibila ar fi
definirea de metode getter si setter in felul urmator:

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

Codul de mai sus defineste o proprietate `textWidth` (numele este
case-insensitive). Cand se citeste proprietatea, `getTextWidth()` este invocata
si valoarea returnata devine valoarea proprietatii. In mod similar, cand se atribuie
o valoare proprietatii, `setTextWidth()` este invocata. Daca metoda setter nu este
definita, atunci proprietatea va fi read-only, iar incercarea de a o scrie va avea ca efect
activarea unei exceptii. Folosind metodele getter si setter pentru a defini o proprietate
avem avantajul ca putem executa logica aditionala atunci cand se citeste sau se scrie o
proprietate (ex. cand se face validare de date, cand se activeaza evenimente).

>Note|Nota: Este o mica diferenta intre o proprietate definita prin metodele getter si setter
>si o variabila a unei clase. Numele proprietatii este case-insensitive, in timp ce
>numele variabilei este case-sensitive.

Evenimentele unei componente
----------------------------

Evenimentele sunt proprietati speciale ale caror valori pot fi nume de metode (denumite `event
handlers`, metode care trateaza evenimente). Atasarea/Atribuirea unei metoda la un eveniment va
avea ca efect apelarea metodei ori de cate ori si oriunde evenimentul este activat.
De aceea, comportamentul unei componente poate fi modificat intr-un fel neanticipat in
perioada de dezvoltare a componentei.

Un eveniment al unei componente este definit printr-o metoda al carei nume incepe cu `on`.
Ca si proprietatile definite cu metode getter/setter, numele evenimentelor sunt case-insensitive.
Codul urmator defineste un eveniment `onClicked`:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

`$event` este o instanta a [CEvent] sa a unei clase copil care reprezinta parametrul evenimentului. 

Putem sa atasam o metoda acestui eveniment in felul urmator:

~~~
[php]
$component->onClicked=$callback;
~~~

`$callback` se refera la un callback valid PHP. Poate fi o functie globala sau o metoda a unei clase.
Daca este o metoda a unei clase, callback-ul trebuie dat ca un array: `array($object,'numeMetoda')`.

Un event handler va fi in felul urmator:

~~~
[php]
function numeMetoda($event)
{
    ......
}
~~~

`$event` este parametrul care descrie evenimentul (provine din apelul `raiseEvent()`).
Parametrul `$event` este o instanta a clasei [CEvent] sau a unei clase derivate.
Trebuie sa contina cel putin informatiile despre cine a activat evenimentul. 

Daca apelam `onClicked()` in acest moment, evenimentul `onClicked` va fi activat (in interiorul lui
`onClicked()`), iar event handler-ul atasat va fi invocat automat. 

Un eveniment poate fi atasat mai multor handlere.  Cand un eveniment este activat,
handler-ele vor fi invocate in ordinea in care au fost atasate evenimentului.
Daca un handler decide sa intrerupa invocarea hadler-elor urmatoare, poate sa seteze
[$event->handled|CEvent::handled] cu valoarea true.


Behaviour
---------

Incepand cu versiunea 1.0.2, o componenta are adaugat suport pentru [mixin](http://en.wikipedia.org/wiki/Mixin)
si ii poate fi atasata un behavior (=comportament), sau mai multe behaviours. Un *behaviour* este un obiect
ale carui metode pot fi 'mostenite' de catre componenta atasata in scopul de a castiga functionalitate noua
in loc de specializare (mostenirea normala de clase).
O componenta poate fi atasata impreuna cu mai multe behaviours, obtinand astfel 'mostenire multipla'.

Clasele behavior trebuie sa implementeze interfata [IBehavior]. Cele mai multe behaviours extind clasa de baza
[CBehavior]. Daca o clasa behaviour are nevoie sa fie atasata unui [model](/doc/guide/basics.model), poate extinde
si clasa [CModelBehavior] sau [CActiveRecordBehavior] care implementeaza features specifice modelelor.

Pentru a folosi behavior, trebuie sa fie atasat unei componente inainte de toate apeland metoda clasei behaviour
[attach()|IBehavior::attach]. Dupa aceea putem apela o metoda behavior prin componenta:

~~~
[php]
// $name identifica in mod unic behavior al unei componente
$behavior->attach($name,$component);
// test() este o metoda a $behavior
$component->test();
~~~

Behavior poate fi accesat ca o proprietate normala a unei componente. De exemplu, daca behaviour
cu numele `tree` este atasat unei componente, atunci putem obtine referinta la acest obiect behaviour astfel:

~~~
[php]
$behavior=$component->tree;
// echivalentu cu urmatoarea linie:
// $behavior=$component->asa('tree');
~~~

Behavior poate fi temporar dezactivat. Astfel metodele sale nu mai sunt disponibile componentei. De exemplu:

~~~
[php]
$component->disableBehavior($name);
// urmatoarea instructiune va genera o exceptie
$component->test();

// dar acum va functiona corect
$component->enableBehavior($name);
$component->test();
~~~

Este posibil ca doua behaviours atasate la aceeasi componenta sa aiba metode cu acelasi nume.
In acest caz, metoda primului behaviour atasat va avea prioritate. 

Atunci cand clasele behaviour sunt folosite impreuna cu [evenimente](#component-event), devin si mai puternice.
Un behaviour, atunci cand este atasat unei componente, poate atasa unele din metodele sale unor evenimente
ale componentei. Astfel, behaviour are posibilitatea de a observa sau de a schimba fluxul de executie normal al componentei.

<div class="revision">$Id: basics.component.txt 683 2009-02-16 05:20:17Z qiang.xue $</div>