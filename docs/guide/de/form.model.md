Erstellen des Models
====================

Bevor man sich an das HTML für ein Formular macht, sollte man sich überlegen,
welche Daten überhaupt erfasst werden sollen und welchen Regeln diese Daten
entsprechen müssen. Die nötigen Informationen dazu können in einer Modelklasse
festgehalten werden. Wie im Kapitel [Model](/doc/guide/basics.model)
beschrieben, ist ein Model ja der zentrale Ort zur Speicherung und Validierung
von eingegebenen Daten.

Es stehen zwei Modeltypen zur Auswahl, je nachdem, wie die eingegebenen Daten
weiterverwendet werden sollen. Geht es lediglich darum, Daten zu erfassen, zu
verabeiten und dann wieder zu verwerfen, bietet sich ein
[Formularmodel](/doc/guide/basics.model) an. Sollen die Daten hingegen in einer
Datenbank gespeichert werden, ist ein [ActiveRecord](/doc/guide/database.ar)
die bessere Wahl. Beide Modeltypen stammen von der selben Basisklasse [CModel]
ab. In ihr sind die Teile der Schnittstelle definiert, die beide Typen
gemeinsam haben.

> Note|Hinweis: Obwohl in den Beispielen dieses Abschnitts hauptsächlich
Formularmodels vorkommen, kann man stattdessen genauso auch 
[ActiveRecords](/doc/guide/database.ar) verwenden. Die Verfahren sind bei
beiden die selben.


Definieren der Modelklasse
---------------------------

Im folgenden Beispiel erzeugen wir die Modelklasse `LoginForm` für die Daten
einer Anmeldeseite. Da diese Daten nur zum Authenifizieren eines Benutzers
verwendet werden und ansonsten nicht gespeichert werden müssen, verwenden wir
ein Formularmodel.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

`LoginForm` hat die drei Attribute `$username`, `$password` und `$rememberMe`.
Darin werden entpsrechend der Benutzername, das Passwort und die Option
"Angemeldet bleiben" (engl.: Remember me) gespeichert. Da `$rememberMe` 
bereits den Startwert `false` hat, wird die entsprechende Option beim ersten 
Anzeigen des Anmeldeformulars nicht markiert werden.

> Info|Info: Für Modeleigenschaften verwenden wir den Begriff *Attribute*, 
um sie von normalen Klasseneigenschaften zu unterscheiden. Attribute dienen
also hautpsächlich dazu, Benutzerdaten oder Datenbankwerte abzulegen.


Bestimmen der Validierungsregeln
--------------------------------

Wenn ein Besucher das Formular abschickt und die Daten in das Model übernommen
werden sollen, müssen diese vorher überprüft werden. Das geschieht bei der
sog. Validierung (engl.: validation) anhand einer Reihe von Regeln. Diese
Regeln werden in der Modelmethode `rules()` festgelegt und in Form eines
Arrays zurückgegeben:

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

Mit dieser `rules()`-Methode wird bestimmt, dass `username` und `password`
zwingend ausgefüllt sein müssen (engl.: required) und `rememberMe` vom Typ
Boolean sein muss. Außerdem soll `password` mit der Methode `authenticate`
geprüft werden, wo die eigentliche Anmeldung erfolgt.

Jede der Regeln im zurückgegebenen Array muss folgendem Format entsprechen:

~~~
[php]
array('AttributListe', 'Validator', 'on'=>'SzenarienListe', ...Zusätzliche Optionen)
~~~

`AttributListe` enthält eine Reihe von Attributnamen (mit Komma getrennt), für
die diese Regel gelten soll. `Validator` (sinngem.: Gültigkeitsprüfer) gibt an, 
welche Art der Prüfung durchgeführt werden soll. Der `on` Parameter ist optional 
und gibt die Szenarien an, in denen diese Regel überhaupt verwendet werden
soll. `Zusätzliche Optionen` können als Name-Wert-Paare angegeben werden um
weitere Validatoreigenschaften zu konfigurieren.

Es gibt drei Varianten, wofür `Validator` stehen kann. Erstens kann es der
Name einer Methode innerhalb der aktuellen Modelklasse sein, wie
`authenticate` im obigen Beispiel. Diese Methode muss folgende Signatur
aufweisen:

~~~
[php]
/**
 * @param string $attribute der Name des Attributs, das geprüft werden soll
 * @param array $params die für diese Prüfregel angegebenen Optionen
 */
public function ValidatorName($attribute,$params) { ... }
~~~

Zweitens kann `Validator` der Name einer ganzen Validatorklasse sein. Bei der
Validierung wird dann eine Instanz dieser Klasse erzeugt, die die eigentliche
Prüfung durchführt. Über die zusätzlichen Optionen können die
Objekteigenschaften dieser Instanz konfiguriert werden. Eine solche Klasse
muss von [CValidator] abgeleitet werden.

Drittens kann `Validator` einem von mehreren vorgegebenen Aliasen entsprechen. 
Im obigen Beispiel ist der Name `required` ein Alias für [CRequiredValidator].
Dieser stellt sicher, dass der zu prüfende Attributwert nicht leer ist.

Hier eine Übersicht aller verwendbaren Aliase:

   - `boolean`: Alias für [CBooleanValidator]; prüft, ob der
Attributwert [CBooleanValidator::trueValue] oder [CBooleanValidator::falseValue] ist.

   - `captcha`: Alias für [CCaptchaValidator]; prüft, ob der Attributwert mit dem 
angezeigten [CAPTCHA](http://de.wikipedia.org/wiki/Captcha)-Code übereinstimmt.

   - `compare`: Alias für [CCompareValidator]; prüft, ob das Attribut mit einem anderen
Attribut oder einer Konstanten übereinstimmt.

   - `email`: Alias für [CEmailValidator]; prüft auf eine gültige E-Mail Adresse.

   - `date` : Alias für [CDateValidator]; stellt sicher, dass das Attribut
ein gültiges Datum, eine Uhrzeit oder eine Datetime enthält.

   - `default`: Alias für [CDefaultValueValidator]; weist dem Attribut einen
Standardwert zu.

   - `exist`: Alias für [CExistValidator]; stellt sicher, dass der Attributwert in 
einer bestimmten Tabellenspalte existiert

   - `file`: Alias für [CFileValidator]; stellt sicher, dass das Attribut den Namen einer
hochgeladenen Datei enthält.

   - `filter`: Alias für [CFilterValidator]; wandelt den Attributwert mit
einem Filter um.

   - `in`: Alias für [CRangeValidator]; prüft, ob der Attributwert in einer
vorgegebenen Liste von Werten enthalten ist.

   - `length`: Alias für [CStringValidator]; stellt sicher, dass die Länge des
Attributwerts innerhalb eines bestimmten Bereichs liegt.

   - `match`: Alias für [CRegularExpressionValidator]; prüft, ob der
Attributwert einem bestimmten regulären Ausdruck entsprechen.

   - `numerical`: Alias für [CNumberValidator]; prüft, ob das Attribut
eine gültige Zahl enthält.

   - `required`: Alias für [CRequiredValidator]; stellt sicher, dass das Attribut nicht
leer ist.

   - `type`: Alias für [CTypeValidator]; prüft, ob der Attributwert von einem
bestimmten Datentyp ist.

   - `unique`: Alias für [CUniqueValidator]; stellt sicher, dass der Attributwert nur
einmal in einer bestimmten Tabellenspalte vorkommt.

   - `url`: Alias für [CUrlValidator]; prüft, ob das Attribut eine gültige URL enthält.

Hier einige Beispiele, wie man diese Validatoren verwendet:

~~~
[php]
// `username` ist zwingend erforderlich
array('username', 'required'),
// `username` muss 3 bis 12 Zeichen lang sein
array('username', 'length', 'min'=>3, 'max'=>12),
// `password` muss im `register`-Szenario mit `password2` übereinstimmen
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// `password` muss im `login`-Szenario authentifiziert werden
array('password', 'authenticate', 'on'=>'login'),
~~~

Sichere Attributzuweisungen
---------------------------

Nach dem Instanziieren eines Models muss dieses oft mit den Daten eines
Webformulars befüllt werden. Das geschieht am einfachsten mit einer
sogenannten *Massenzuweisung* (engl.: *massive assignment*):

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
	$model->attributes=$_POST['LoginForm'];
~~~

In der letzten Zeile wird jedes Modelattribut automatisch mit dem
entsprechenden Wert in `$_POST['LoginForm']` befüllt. Diese Schreibweise ist
eine Abkürzung für folgenden (Pseudo-)Code:

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name ist ein sicheres Attribut)
		$model->$name=$value;
}
~~~

Es ist sehr wichtig, festzulegen, welche Attribute als "sicher" gelten (also
über eine Massenzuweisung beschrieben werden dürfen). Würde man zum Beispiel 
auch das Attribut für den Primärschlüssel einer Tabelle als sicher
definieren, könnte ein Angreifer diesen evtl. bei einem gegebenen
Record verändern und sich so Zugang zu ansonsten geschützten Daten verschaffen.

###Definition von sicheren Attribute

Ein Attribut gilt als sicher, wenn es dafür eine
Validierungsregel im gegebenen Szenario gibt. Zum Beispiel:

~~~
[php]
array('username, password', 'required', 'on'=>'login, register'),
array('email', 'required', 'on'=>'register'),
~~~

Im Szenario `login` sind hier die Attribute `username` und `password` zwingend
erforderlich, im Szenario `register` die Attribute `username`, `password` und
`email`. Im `login`-Szenario können somit nur die Attribute
`username` und `password` per Massenzuweisung verändert werden, da dies die einzigen
Attribute sind, die auch eine Regel in diesem Szenario haben.
Im Szenario `register` können hingegen alle drei Attribute per Massenzuweisung
befüllt werden.

~~~
[php]
// Im Szenario login
$model=new User('login');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];

// Im Szenario register
$model=new User('register');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];
~~~

Was ist der Hintergrund dieser Konvention? Nun, wenn ein Attribut bereits
mittels einer Regel überprüft wurde, worüber sollte man sich dann noch Sorgen?

Man bedenke, dass Validierungsregeln ohnehin nur dazu da sind, um Daten, die von
"außen" (also von Besuchern) stammen, zu prüfen. Nicht aber die Daten, die man
per eigenem Code generiert und in Attribute schreibt (z.B. Zeitstempel oder
autogenerierte Primärschlüssel). Fügen Sie daher AUF KEINEN FALL Regeln für 
jene Attribute hinzu, die nicht mit Daten von Besuchern befüllt werden müssen.

Es kann vorkommen, dass ein Attribut sicher sein soll, obwohl es keine
spezielle Regel dafür gibt. Zum Beispiel das Attribut für einen Artikeltext, 
bei dem jeglicher Inhalt erlaubt sein soll. In diesem Fall kann man die
spezielle Regel `safe` verwenden:

~~~
[php]
array('inhalt', 'safe')
~~~

Der Vollständigkeit halber gibt es auch eine `unsafe`-Regel, die ein Attribut
explizit als `nicht sicher` festlegt:

~~~
[php]
array('erlaubnis', 'unsafe')
~~~

Diese Regel wird nur selten verwendet. Sie bildet eine Ausnahme zu unserer
Konvention über sichere Attribute.

Möchte man einem nicht-sicheren Attribut Daten zuweisen, muss dies von Hand
erfolgen:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~

Auslösen der Validierung
------------------------

Wurde ein Model mit gesendeten Daten befüllt, kann die Validierung mit
[CModel::validate()] durchgeführt werden. Der Rückgabewert zeigt an, ob die
Validierung erfolgreich war.  Bei einem [CActiveRecord]-Model wird die 
Validierung zudem automatisch ausgelöst, sobald man die Modelmethode
[CActiveRecord::save()] ausführt.

Die Validierung erfolgt immer im Rahmen eines bestimmten Szenarios. Über 
[scenario|CModel::scenario] wird das aktuelle Szenario gesetzt und somit
bestimmt, welche Regeln angewendet werden sollen. Bei einem Benutzermodel sollen 
z.B. im `login`-Szenario nur `username` und `password` überprüft werden. Im
Szenario `register` hingegen sollen evtl. weitere Attribute validiert werden, z.B.
`email`, `address`, etc. Die Validierung würde man dann wie folgt durchführen:

~~~
[php]
// Erstellt ein neues Model User im register-Szenario. Enstpricht diesem Code:
// $model=new User;
// $model->scenario='register';
$model=new User('register');

// Befüllt das Model mit den gesendeten Daten
$model->attributes=$_POST['User'];

// Führt die Validierung durch
if($model->validate())   // falls Eingabewerte in Ordung ...
    ...
else
    ...
~~~

Um anzugeben, in welchem Szenario eine Regel verwendet werden soll, werden die
Szenarien in der `on`-Option der Regel angegeben. Ist die `on`-Option nicht gesetzt, 
gilt die Validierungsregel in allen Szenarien. Ein Beispiel:

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

Die erste Regel wird in allen Szenarien verwendet, die anderen beiden
nur um Szenario `register`.


Abfragen von Validierungsfehlern
--------------------------------

Bei der Validierung werden die auftretenden Fehler im Model gespeichert. 
Mit den Modelmethoden [CModel::getErrors()] und [CModel::getError()] können 
diese Fehler hinterher abgerufen werden. Die erste Methode liefert *alle*
Fehler für das angegebene Attribut zurück, die zweite nur den *ersten* Fehler.


Attributlabel
--------------

Jedes Eingabefeld in einem Formular benötigt normalerweise ein Label(Beschriftung), 
um den Inhalt des Eingabefeldes zu beschreiben. Man kann ein
Label zwar fest in einem View hinterlegen, aber die Labels im Model zu
definieren lässt mehr Flexibilität zu und ist am Ende meist komfortabler.

Standardmäßig liefert [CModel] einfach den Attributnamen als Label zurück.
Überschreibt man jedoch die Methode [attributeLabels()|CModel::attributeLabels],
können die Labels angepasst werden. Wie wir in den nächsten Abschnitten sehen
werden, ermöglicht die Labeldefinition innerhalb des Models, die 
zugehörigen Formulare schnell und effektiv zu erzeugen.

<div class="revision">$Id: form.model.txt 3482 2011-12-13 09:41:36Z mdomba $</div>
