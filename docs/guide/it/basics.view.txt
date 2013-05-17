View
====

Una view è uno script PHP consistente principalmente di elementi per l'interfaccia utente. 
Può contenere codice PHP, ma si raccomanda che questo codice non alteri
i dati del model e che rimanga relativamente semplice. Nello spirito 
della separazione tra logica e presentazione, larghi blocchi di logica dovrebbero 
essere collocati nei controller o nei model piuttosto che nelle view.
 
Una view ha un nome che è utilizzato per identificare il file di script della view quando 
viene visualizzata. Il nome di una view è lo stesso nome del file di script della view. 
Per esempio, il nome della view `edit` si riferisce al file di script della view che si chiama `edit.php`. 
Per visualizzare una view, chiamare [CController::render()] passando il nome della view.
Il metodo cercherà la view corrispondente all'interno della cartella `protected/views/ControllerID`.

All'interno dello script della view, si può accedere all'istanza del controller utilizzando `$this`.
Si può così `tirare giù` qualunque proprietà del controller inserendo 
`$this->propertyName` nella view.

Si può anche utilizzare il seguente approccio `push` per passare dati alla view:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

Nell'esempio precedente, il metodo [render()|CController::render] estrarrà l'array 
del secondo parametro in variabili. Come risultato, nello script della view è possibile 
accedere alle variabili locali `$var1` e `$var2`.

Layout
------

Layout è una view speciale che è utilizzata per decorare le view. Solitamente 
contiene parti di interfaccia utente che sono comuni tra diverse view.
Per esempio, un layout può contenere un'intestazione (header) e un piè pagina (footer), 
ed incorporare la view in mezzo, in questo modo:

~~~
[php]
......header qui......
<?php echo $content; ?>
......footer qui......
~~~

dove `$content` memorizza il risultato della rappresentazione della view.

Il layout è applicato implicitamente quando viene chiamato il metodo [render()|CController::render].
Per default, lo script della view `protected/views/layouts/main.php` è utilizzato come 
layout. Ciò può essere personalizzato modificando sia [CWebApplication::layout] che 
[CController::layout]. Per visualizzare una view senza l'applicazione di un layout, 
chiamare invece [renderPartial()|CController::renderPartial].

Widget
------

Un widget è un'istanza di [CWidget] o una classe figlia di [CWidget]. Si tratta di un 
componente che è usato principalmente per scopi di presentazione. Un widget solitamente 
è incorporato nello script della view per generare un'interfaccia utente complessa, ma
autonoma. Per esempio, il widget calendario può essere utilizzato per rappresentare 
un'interfaccia utente complessa con un calendario. I widget agevolano un migliore 
riutilizzo del codice per l'interfaccia utente.

Per utilizzare un widget, procedere come indicato di seguito nello script di una view:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...contenuto che potrebbe essere catturato dal widget...
<?php $this->endWidget(); ?>
~~~

oppure

~~~
[php]
<?php $this->widget('path.to.WidgetClass'); ?>
~~~

Quest'ultimo esempio viene utilizzato quando il widget non necessita di alcun contenuto.

I widget possono essere configurati per per personalizzare il proprio comportamento. 
Ciò si ottiene impostando le loro proprietà iniziali chiamando 
[CBaseController::beginWidget] oppure [CBaseController::widget]. Per esempio, 
quando si utilizza il widget [CMaskedTextField], ci avrebbe fatto piacere specificare 
la maschera da utilizzare. Si può fare ciò passando un array di valori di proprietà iniziale 
come segue, dove le chiavi dell'array sono i nomi delle proprietà ed i valori dell'array 
sono i valori iniziali delle corrispondenti proprietà del widget:

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

Per definire un nuovo widget, estendere [CWidget] e sovraccaricare ii metodi 
[init()|CWidget::init] e [run()|CWidget::run]:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// Questo metodo è invocato da CController::beginWidget()
	}

	public function run()
	{
		// Questo metodo è invocato da CController::endWidget()
	}
}
~~~

Come un controller, un widget può avere anche una propria view. Per default, i file 
delle view del widget sono ubicati all'interno della sottocartella `views` della 
cartella che contiene il file della classe del widget. Queste view possono essere 
visualizzate chiamando [CWidget::render()], similmente a quanto accade in un controller.
L'unica differenza è che nessun layout verrà applicato alla view di un widget. Inoltre, 
`$this` in una view si riferisce all'istanza del widget piuttosto che all'istanza del controller.

View di sistema
-----------

Le view di sistema si riferiscono a view utilizzate da Yii per visualizzare errori e 
memorizzare informazioni. Per esempio, quando un utente richiede un controller o una 
action che non esiste, Yii genererà un'eccezione spiegando l'errore. Yii visualizza 
l'eccezione usando specifiche view di sistema.

La denominazione delle view di sistema segue determinate regole. Nomi come `errorXXX` si riferiscono 
alle view per la visualizzazione di [CHttpException] con il codice di errore `XXX`. 
Per esempio, se è stata generata [CHttpException] con il codice di errore 404, sarà 
la visualizzata la view `error404`.

Yii fornisce una serie di view di sistema di default che si trovano nella cartella 
`framework/views`. Possono essere personalizzare creando altri file di view con lo stesso nome
all'interno della cartella `protected/views/system`.

<div class="revision">$Id: basics.view.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>