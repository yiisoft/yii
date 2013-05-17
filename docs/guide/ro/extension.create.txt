Crearea extensiilor
===================

O extensie cere un efort in plus din partea programatorului, deoarece o extensie are
scopul de a fi folosita de catre alti programatori. In cele ce urmeaza, avem
cateva principii generale:

* O extensie ar trebui sa fie de sine statatoare. Adica, toate dependentele sale externe
  ar trebui sa fie reduse la minim. Daca extensia ar necesita instalarea de pachete aditionale,
  alte clase sau alte fisiere, vor fi prea multe dureri de cap pentru cei care o vor folosi.
* Fisierele care apartin extensiei ar trebui sa fie organizate in interiorul directorului extensiei
  (care trebuie sa ii poarte numele).
* Clasele unei extensii ar trebui sa fie prefixate cu unele litere pentru a evita conflictul
  de nume cu clasele altor extensii.
* O extensie ar trebui sa aiba informatii despre instalare si documentatie API.
  Aceste doua informatii reduc timpul si efortul depus de eventualii programatori care o vor folosi.
* O extensie ar trebui sa foloseasca o licenta corespunzatoare. Daca vrem ca extensia
  sa fie si open-source si proiect privat, putem lua in considerare licentele
  BSD, MIT, etc., nu GPL pentru ca sub GPL, codul va fi open-source de asemenea.

In cele ce urmeaza, vom descrie cum sa cream o extensie noua, pornind de la categorisirea
facuta in [generalitati despre extensii](/doc/guide/extension.overview).
Aceste descrieri sunt valabile si in cazul in care cream o componenta folosita in special
in propriile noastre proiecte.

Componenta de aplicatie
-----------------------

O [componenta de aplicatie](/doc/guide/basics.application#application-component)
trebuie sa implementeze interfata [IApplicationComponent] sau sa fie derivata din
clasa [CApplicationComponent]. Pricipala metoda care trebuie implementata este
[IApplicationComponent::init] in care se fac initializarile componentei.
Aceasta metoda este apelata dupa ce componenta este creata si dupa ce sunt initializate
proprietatile clasei cu valorile initiale specificate in 
[configurarea aplicatiei](/doc/guide/basics.application#application-configuration).

Implicit, o componenta a aplicatiei este creata si initializata doar atunci cand este accesata
prima data in timpul tratarii unei cereri de la utilizatorul web. Daca o componenta a aplicatiei
trebuie sa fie creata imediat dupa ce este creata instanta aplicatiei insasi,
va trebui sa ii adaugam ID-ul in proprietatea [CApplication::preload].


Widget
------

Un [widget](/doc/guide/basics.view#widget) trebuie sa fie derivat din clasa [CWidget] sau
dintr-o clasa derivata. 

Cel mai usor mod de a crea un nou widget este sa il derivam dintr-un widget existent
si sa ii suprascriem metodele sau sa ii schimbam valorile sale initiale. De exemplu, daca
vrem sa folosim un alt stil CSS pentru [CTabView], ar putem sa configuram proprietatea
sa [CTabView::cssFile]. Putem de asemenea deriva [CTabView] dupa cum urmeaza, ca sa nu mai fie nevoie
sa configuram proprietatea de fiecare data cand folosim widget-ul.

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

In codul de mai sus, suprascriem metoda [CWidget::init] si atribuim proprietatii
[CTabView::cssFile] URL-ul cu noul stil CSS. Punem apoi fisierul cu noul stil CSS
in acelasi director care contine fisierul clasei `MyTabView` pentru a putea fi impachetate intr-o
extensie. Deoarece fisierul cu stilul CSS nu este accesibil utilizatorilor Web, trebuie sa il publicam
ca fiind un asset.

Pentru a crea un nou widget de la zero, in principal trebuie sa implementam doua metode:
[CWidget::init] si [CWidget::run]. Prima metoda este apelata atunci cand folosim
`$this->beginWidget` pentru a insera un widget intr-un view. A doua metoda este apelata atunci cand
apelam `$this->endWidget`. Daca vrem sa capturam si sa procesam continutul afisat intre aceste doua
apeluri de metode, putem porni [output buffering](http://us3.php.net/manual/en/book.outcontrol.php)
in [CWidget::init] si in [CWidget::run] sa extragem output-ul memorat pentru procesare ulterioara.

Cand folosim un widget, de obicei trebuie sa includem CSS-ul, javascript-ul sau alte fisiere
in pagina care foloseste widget-ul. Denumim aceste fisiere *assets* pentru ca ele stau impreuna
cu fisierul clasei widget-ului si nu sunt accesibile in mod normal utilizatorilor Web.
Pentru a face accesibile aceste fisiere, trebuie sa le publicam
folosind [CWebApplication::assetManager], dupa cum am aratat in snippet-ul de mai sus.
In plus, daca vrem sa includem un fisier CSS sau javascript in pagina curenta, trebuie sa
inregistram fisierul folosind [CClientScript]:

~~~
[php]
class MyWidget extends CWidget
{
	protected function registerClientScript()
	{
		// ...publicam fisiere CSS sau JavaScript aici...
		$cs=Yii::app()->clientScript;
		$cs->registerCssFile($cssFile);
		$cs->registerScriptFile($jsFile);
	}
}
~~~

Un widget poate de asemenea sa aiba propriile sale fisiere view. In cazul acesta,
cream un directorul cu numele `views` in interiorul directorului care contine fisierul clasei
widget-ului, si punem acolo toate fisierele view. In clasa widget-ului, pentru a procesa
un view al widget-ului, folosim `$this->render('ViewName')`, la fel ca in cazul unui controller.

Action
------

Un [action](/doc/guide/basics.controller#action) trebuie sa fie derivat din clasa [CAction]
sau una din clasele sale derivate. Principala metoda care trebuie implementata pentru un action
este [IAction::run].

Filtru
------
Un [filtru](/doc/guide/basics.controller#filter) trebuie sa fie derivat din clasa [CFilter]
sau una din clasele sale derivate. Principalele metode care trebuie implementate pentru un filtru
sunt [CFilter::preFilter] si [CFilter::postFilter]. [CFilter::preFilter] este apelat inainte ca action-ul
sa fie executat. [CFilter::postFilter] este apelat dupa ce action-ul a fost executat.

~~~
[php]
class MyFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// lucruri de aplicat inainte ca action-ul sa fie executat
		return true; // false daca action-ul NU ar trebui sa fie executat
	}

	protected function postFilter($filterChain)
	{
		// lucruri de aplicat dupa ce action-ul a fost executat
	}
}
~~~

Parametrul `$filterChain` este de tip [CFilterChain] care contine informatii despre action-ul
care este filtrat in acest moment.


Controller
----------
Un [controller](/doc/guide/basics.controller) distribuit ca extensie
ar trebui sa fie derivat din clasa [CExtController], in loc de [CController]. Motivul
principal este ca [CController] presupune ca fisierele view ale controller-ului sunt localizate
in `application.views.ControllerID`, in timp ce [CExtController] presupune ca fisierele view
sunt localizate in interiorul directorului `views` care este un subdirector al directorului care contine
fisierul clasei controller-ului. De aceea, este mai usor sa redistribuim controller-ul
din moment ce fisierele sale view stau impreuna cu fisierul clasei controller-ului.


Validator
---------
Un validator ar trebui sa fie derivat din [CValidator] si sa implementeze metoda
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
[console command](/doc/guide/topics.console) ar trebui sa fie derivata din clasa
[CConsoleCommand] si sa implementeze metoda [CConsoleCommand::run].
Optional, putem suprascrie [CConsoleCommand::getHelp] pentru a pune la dispozitie
informatii despre comanda.

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
		return 'Folosire: cum folosim aceasta comanda';
	}
}
~~~

Modul
-----
Pentru a crea un modul, trebuie vazuta sectiunea despre [module](/doc/guide/basics.module#creating-module).

Un principiu general pentru dezvoltarea unui modul este ca ar trebui sa fie de sine statator.
Fisierele cu resursele lui (CSS, JavaScript, imagini) ar trebui sa fie distribuite impreuna cu modulul.
In acelasi timp, modului trebuie sa le publice pentru a fi accesibile utilizatorilor Web.

Componenta generica
-------------------
Dezvoltarea unei extensii de componenta generica este la fel ca scrierea unei clase.
Din nou, ar trebui sa fie de sine statatoare pentru a fi usor de folosit de alti programatori.

<div class="revision">$Id: extension.create.txt 749 2009-02-26 02:11:31Z qiang.xue $</div>