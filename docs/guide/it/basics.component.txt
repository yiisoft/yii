Component
=========

Le applicazioni Yii sono costruite su component che sono oggetti scritti per uno scopo  
specifico. Un component è un'istanza di [CComponent] o una sua classe derivata. 
L'uso di un component coinvolge principalmente l'accesso alle sue proprietà e la 
generazione/gestione dei suoi eventi. La classe base [CComponent] specifica come 
definire le proprietà e gli eventi.

Proprietà del component
------------------

La proprietà del component è come il membro di una variabile pubblica di un oggetto. 
Si può leggere il valore od assegnare ad essa un valore. Per esempio, 

~~~
[php]
$width=$component->textWidth;     // recupera la proprietà di textWidth
$component->enableCaching=true;   // imposta la proprietà di enableCaching
~~~

Per definire la proprietà di un component, si può semplicemente dichiarare il 
membro di una variabile pubblica nella classe component. Una modalità più 
flessibile, comunque, è quella di definire i metodi getter e setter come qui 
di seguito:

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

Il codice qui sopra definisce una proprietà scrivibile chiamata `textWidth` (il nome 
è indifferente al maiuscolo/minuscolo). Durante la lettura della proprietà, 
viene invocata `getTextWidth()` ed il valore restituito diventa il valore della proprietà; 
Allo stesso modo, quando viene scritta la proprietà, viene invocata `setTextWidth()`. 
Qualora il metodo setter non sia definito, la proprietà sarà a sola lettura ed il 
tentativo di scrittura genererà un'eccezione. L'uso dei metodi getter e setter per 
definire una proprietà ha il vantaggio che la logica supplementare (es. l'esecuzione della
validazione, la generazione di eventi) può essere eseguita durante la lettura e la 
scrittura della proprietà.

>Note:|Nota: C'è una leggera differenza tra la proprietà definita tramite i metodi 
getter/setter e la variabile membro di una classe. Il nome della prima è indifferente  
al maiuscolo/minuscolo mentre la seconda è sensibile al maiuscolo/minuscolo.

Eventi del component
---------------

Gli eventi dei component sono proprietà speciali che hanno i metodi (chiamati 
`gestori degli eventi`) come loro valori. Il collegamento (assegnazione) di un 
metodo ad un evento farà si che il metodo venga invocato automaticamente nel momento 
in cui l'evento venga generato. Quindi, il comportamento di un component può essere 
modificato in un modo che potrebbe non essere stato previsto durante lo sviluppo 
del component.

Un evento del component è definito tramite la definizione di un metodo il cui nome 
inizia con `on`. Come i nomi delle proprietà sono definiti tramite i metodi getter/setter, 
i nomi degli eventi sono insensibili al maiuscolo/minuscolo. Il codice che segue 
definisce un evento `onClicked`:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

dove `$event` è una istanza di [CEvent] o una classe figlia che rappresenta il 
parametro dell'evento.

Si può collegare un metodo a questo evento come segue:

~~~
[php]
$component->onClicked=$callback;
~~~

dove `$callback` si riferisce ad una funzione callback valida di PHP. Può essere 
una funzione globale o un metodo di una classe. Nel secondo caso, la funzione callback 
deve essere data come array: `array($object,'methodName')`.

La firma di un gestore di evento deve essere come segue:

~~~
[php]
function methodName($event)
{
    ......
}
~~~

dove `$event` è il parametro che descrive l'evento (che proviene dalla chiamata 
di `raiseEvent()`). Il parametro `$event` è una istanza di [CEvent] o la sua classe 
derivata. Come minimo, contiene l'informazione su chi abbia generato l'evento.

Un gestore di eventi può essere anche una funzione anonima che è supportata da 
PHP 5.3 o superiore. Per esempio, 

~~~
[php]
$component->onClicked=function($event) {
	......
}
~~~

Se ora viene chiamata `onClicked()`, l'evento `onClicked` sarà generato (all'interno 
di `onClicked()`), ed il gestore di evento collegato sarà invocato automaticamente.

Un evento può essere collegato a più gestori. Quando è generato un evento, 
i gestori saranno invocati nell'ordine in cui essi sono collegati all'evento.
Se un gestore volesse impedire che gli altri gestori venagno invocati, si può 
impostare [$event->handled|CEvent::handled] a true.

Behavior del component
------------------

Il component supporta il pattern [mixin](http://en.wikipedia.org/wiki/Mixin) e può essere 
collegato con uno o più behavior. Un *behavior* è un oggetto i cui metodi possono 
essere 'ereditati' dai component collegati tramite lo strumento di collezionare 
funzionalità piuttosto che specializzazioni (es. normale ereditarietà della classe).
Un component può essere collegato a più behavior e ottenere così 'l'ereditarietà multipla'.

Le classi behavior devono implementare l'interfaccia [IBehavior]. La maggior parte 
delle behavior possono estendere dalla classe base [CBehavior]. Se un behavior necessita 
di essere collegato ad un [model](/doc/guide/basics.model), si può estendere da 
[CModelBehavior] o [CActiveRecordBehavior] che implementano funzionalità aggiuntive 
specifiche per i model.

Per usare un behavior, è necessario che prima sia collegato ad un component chiamando il metodo 
[attach()|IBehavior::attach]. Quindi si può chiamare un metodo behavior tramite il component:

~~~
[php]
// $name identifica univocamente il behavior nel component
$component->attachBehavior($name,$behavior);
// test() è un metodo di $behavior
$component->test();
~~~

Un behavior collegato può essere utilizzato come una normale proprietà di un component.
Per esempio, se un behavior chiamato `tree` è collegato ad un component, si può 
ottenere il riferimento a questo oggetto behavior usando:

~~~
[php]
$behavior=$component->tree;
// equivalente al seguente codice:
// $behavior=$component->asa('tree');
~~~

Un behavior può essere disabilitato temporaneamente cosicché i suoi metodi non sono 
disponibili tramite il component. Per esempio, 

~~~
[php]
$component->disableBehavior($name);
// la seguente dichiarazione genererà una eccezione
$component->test();
$component->enableBehavior($name);
// adesso funziona
$component->test();
~~~

È possibile che due behavior collegati allo stesso componente abbiano metodi con 
lo stesso nome. In questo caso, il metodo del behavior collegato per primo avrà la precedenza.

Quando sono usati insieme ad [eventi](/doc/guide/basics.component#component-event), 
i behavior sono ancora più potenti. Un behavior, quando viene collegato ad un component, 
può collegare alcuni dei suoi metodi ad alcuni eventi del component. Così facendo, 
il behavior ha la possibilità di osservare o modificare il normale flusso di 
esecuzione del component.

Si può accedere alla proprietà di un behavior tramite il component a cui è collegato. 
Le proprietà comprendono sia i membri di variabili pubbliche che le proprietà definite 
tramite i getter e/o setter del behavior. Per esempio, se un behavior ha una proprietà 
chiamata `xyz` ed il behavior è collegato al component `$a`, si può usare l'espressione 
`$a->xyz` per accedere alla proprietà del behavior.

<div class="revision">$Id: basics.component.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>