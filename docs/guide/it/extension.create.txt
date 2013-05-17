Creare estensioni
===================

Quanto segue, sono le linee guida per lo sviluppo di estensioni per Yii.

* Bisogna cercare di rendere le estensioni indipendenti da altro codice. La dipendenza da altre classi, estensioni o pacchetti, potrebbe rendere difficoltosa la loro installazione per gli utenti che desiderano utilizzarle.
* Tutti i file di una stessa estensione, dovrebbero essere organizzati sotto la stessa cartella al fine di mantenere il codice il più ordinato possibile.
* Le classi in una estensione dovrebbero avere lo stesso prefisso onde evitare conflitti con i nomi di classi di altre estensioni.
* È importante fornire l'estensione di una corretta documentazione. Questo serve a ridurre il tempo necessario agli altri sviluppatori per usare questa estensione.
* Un'estensione deve usare una licenza appropriata. Se vuoi rendere la tua estensione utilizzabile sia in progetti open-source che in progetti closed-source. Puoi considerare di usare la licenza BSD, MIT, etc. Ma non la licenza GPL che richiede che i progetti derivati siano open-source a loro volta.

Di seguito, si descrive come creare una nuova estensione, secondo le linee guida 
che si possono leggere nell'[overview](/doc/guide/extension.overview). È consigliato 
seguire le linee guida anche nei propri progetti, quando i componenti che si vanno a 
creare non vengono pubblicati sul sito di Yii.

Component dell'applicazione
---------------------

Un [component dell'applicazione](/doc/guide/basics.application#application-component)
può implementare l'interfaccia [IApplicationComponent] oppure estendere la classe 
[CApplicationComponent]. Il metodo principale da implementare è il metodo 
[IApplicationComponent::init] nel quale il componente esegue il suo lavoro di 
inizializzazione. Questo metodo viene invocato dopo che il componente è stato 
creato ed i valori iniziali (come spiegato nella [configurazione 
dell'applicazione](/doc/guide/basics.application#application-configuration)) 
sono applicati.

Di default, un component dell'applicazione è creato ed inizializzato solo quando 
viene caricato la prima volta. Se un component dell'applicazione necessita di 
essere caricato prima dell'istanziazione dell'applicazione, si può indicare il 
suo ID nella proprietà [CApplication::preload].

Behavior
--------

Per creare un behavior, bisogna implementare l'interfaccia [IBehavior]. Per 
convenienza, Yii fornisce una classe base chiamata [CBehavior] che implementa 
già questa interfaccia e fornisce alcuni metodi aggiuntivi che possono farci comodo.

Quando si sviluppa un behavior per [CModel] o [CActiveRecord], si può anche 
estendere [CModelBehavior] o [CActiveRecordBehavior]. Queste classi base offrono 
caratteristiche aggiuntive che sono specificatamente create per [CModel] e 
[CActiveRecord]. Per esempio, la classe [CActiveRecordBehavior] implementa un set 
di metodi per rispondere al ciclo di vita degli eventi scatenati da un oggetto 
ActiveRecord. Una classe figlia può sovrascrivere questi metodi per inserire del 
codice personalizzato.

Il codice seguente mostra un esempio di un behavior ActiveRecord. Quando questo 
behavior è collegato ad un oggetto ActiveRecord e quando questo oggetto salva 
utilizzando il metodo save, questo behavior esegue delle azioni automaticamente.

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


Widget
------

Un [widget](/doc/guide/basics.view#widget) può estendere dalla classe [CWidget] 
o da sue classi figlie.

Il metodo più semplice di creare un nuovo widget è quello di estendere un Widget 
già esistente sovraccaricando i suoi metodi o cambiando le sue proprietà di default. 
Per esempio se si vuole usare un CSS differente per [CTabView] si può configurare 
la sua proprietà [CTabView::cssFile]. Si può anche estendere [CTabView] come 
mostrato di seguito, così da non aver bisogno di configurare la proprietà usando 
il widget.

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

Nel codice precedente, vine sovrascritto il metodo [CWidget::init] ed assegnato 
alla proprietà [CTabView::cssFile] l'indirizzo del nostro nuovo foglio di stile 
(se la proprietà non è già stata impostata). Mettiamo il nuovo css nella stessa 
directory che contiene la classe MyTabView, in questo modo possono essere 
pacchettizzate come una estensione. Siccome il css non è accessibile al web, 
abbiamo bisogno di pubblicarlo dentro ad un asset.

Per creare un nuovo widget da zero, abbiamo principalmente bisogno di implementare 
due metodi:
[CWidget::init] e [CWidget::run]. Il primo è chiamato quando noi usiamo 
`$this->beginWidget`. Il secondo quando viene chiamato `$this->endWidget`. 
Se vogliamo catturare e processare il contenuto mostrato tra l'invocazione di 
questi due metodi, possiamo iniziare l'[output buffering](http://us3.php.net/manual/en/book.outcontrol.php) 
dentro a [CWidget::init] e recuperare l'output bufferizzato dentro al metodo [CWidget::run].

Un widget spesso include un css, del codice javascript ed altre risorse. 
Chiamiamo questi file assets perché loro stanno tutti raccolti con la classe del 
widget e solitamente non sono accessibili all'utente web. Per rendere questi file 
accessibili al web, abbiamo bisogno di pubblicarli usando [CWebApplication::assetManager]. 
Come mostrato nello snippet di codice, per registrare uno script dobbiamo utilizzare [CClientScript]:

~~~
[php]
class MyWidget extends CWidget
{
	protected function registerClientScript()
	{
		// ...publish CSS or JavaScript file here...
		$cs=Yii::app()->clientScript;
		$cs->registerCssFile($cssFile);
		$cs->registerScriptFile($jsFile);
	}
}
~~~

Un widget può anche avere un proprio file view. Se deve essere così, è necessario 
creare una directory chiamata `views` dentro alla directory contenente la classe 
del widget. Nella classe del widget, per renderizzare la view, bisogna utilizzare 
il metodo `$this->render('ViewNamÈ)` che è simile a quello che viene utilizzato in 
un controller.

Action
------

Una [action](/doc/guide/basics.controller#action) può estendere la classe [CAction] 
oppure una sua classe figlia. Il metodo principale che deve essere implementato per 
una azione è [IAction::run].

Filter
------
Un [filter](/doc/guide/basics.controller#filter) può estendere la classe [CFilter] 
o una sua classe figlia. Il metodo principale che deve essere implementato per un 
filter è [CFilter::preFilter] e [CFilter::postFilter]. Il primo viene invocato 
prima che l'action sia eseguita. Il secondo dopo.

~~~
[php]
class MyFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logica che viene applicata prima che l'action venga eseguita
		return true; // false qualora l'action non debba essere eseguita
	}

	protected function postFilter($filterChain)
	{
		// logica che viene applicata dopo che l'action venga eseguita
	}
}
~~~

Il parametro `$filterChain` è di tipo [CFilterChain] che contiene informazioni 
riguardo l'action che è filtrata in questo momento.


Controller
----------
Un [controller](/doc/guide/basics.controller) distribuito come una estensione è 
un'estensione che può estendere [CExtController], invece che [CController]. La 
ragione principale sta nel fatto che  [CController] considera come file di view 
i file posizionati sotto la cartella `application.views.ControllerID`, mentre 
[CExtController] considera le view che si trovano sotto la cartella `views` che 
è una sottodirectory che contiene la classe del controller. In questo modo è 
facile ridistribuire il controller insieme ai suoi file view.

Validator
---------
Un variatore può estendere la classe [CValidator] ed implementare il suo metodo 
[CValidator::validateAttribute].

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

Console Command
---------------
Un [console command](/doc/guide/topics.console)  può estendere la classe 
[CConsoleCommand] ed implementare il suo metodo [CConsoleCommand::run]. 
Opzionalmente, possiamo sovraccaricare anche il metodo [CConsoleCommand::getHelp] 
per fornire alcune informazioni di aiuto del comando.

~~~
[php]
class MyCommand extends CConsoleCommand
{
	public function run($args)
	{
		// $args gives an array of the command-line arguments for this command
	}

	public function getHelp()
	{
		return 'Usage: how to use this command';
	}
}
~~~

Modulo
------
Fate riferimento alla sezione [moduli](/doc/guide/basics.module#creating-module).

Una linea guida generale per sviluppare un modulo è che dovrebbe essere autosufficiente. 
I file di risorsa (come CSS, Javascript ed immagini) che sono usati da un modulo 
dovrebbero essere distribuiti insieme al modulo. Ed il modulo dovrebbe pubblicarli 
in modo tale che siano accessibili via web.

Component generico
-----------------
Sviluppare un component generico è come scrivere una classe. Il component, 
può anche essere indipendente ed usato anche da altri sviluppatori.

<div class="revision">$Id: extension.create.txt 1423 2009-09-28 01:54:38Z qiang.xue $</div>