Verwenden des Form-Builders
===========================

Beim Erstellen von HTML-Formularen stellt man oft fest, dass in Views immer
wieder ähnlicher Code auftaucht, den man aber nur schwer in anderen Projekten
wiederverwenden kann. Für jedes Eingabefeld muss zum Beispiel immer ein Label
generiert und die aufgetretenen Fehler angezeigt werden. Zwecks besserer
Wiederverwendbarkeit kann man dafür den Form-Builder (engl. sinngem. "Formularersteller")
einsetzen.


Grundprinzip
------------

Der Yii Form-Builder verwendet ein [CForm]-Objekt, in dem sämtliche Angaben
über ein HTML-Formular abgelegt sind, also u.a., welche Datenmodels mit dem 
Formular verknüpft sind, welche Art von Eingabefeldern verwendet werden und 
wie das ganze Formular gerendert werden soll. Als Entwickler braucht man 
somit im Wesentlichen nur noch dieses [CForm]-Objekt konfigurieren und kann 
dann dessen Render-Methode aufrufen, um das Formular anzuzeigen.

Die Angaben zu den Eingabefeldern sind als hierarchische Struktur von 
Formularelementen angelegt. Den Wurzelknoten dieser Baumstruktur bildet 
das [CForm]-Objekt selbst. Dieses Element hat zwei Gruppen von Kindelementen:
[CForm::buttons] und [CForm::elements]. [CForm::buttons] enthält alle Buttons des
Formulars (z.B. Submit- oder Resetbuttons), [CForm::elements] sämtliche Eingabeelemente, 
statischen Texte und Subformulare. Ein Subformular ist einfach ein weiteres 
[CForm]-Objekt innerhalb der [CForm::elements]-Liste eines anderen Formulars. 
Es kann sein eigenes Datenmodel, eigene [CForm::buttons] und [CForm::elements] 
enthalten.

Beim Absenden eines Formulars werden alle Daten in den Eingabefeldern der 
Formularhierarchie übergeben, inklusive der Daten in den Subformular-Feldern. 
[CForm] bietet einige komfortable Methoden, um die gesendeten Daten automatisch 
den entsprechenden Models zuzuweisen und die Validierung durchzuführen.


Erstellen eines einfachen Formulars
-----------------------------------

Unten sehen wir ein Beispiel, wie man mit dem Form-Builder ein Anmeldeformular
erstellen kann.

Zunächst wird dazu eine Controlleraction für die Anmeldung angelegt:

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

Hier wird ein [CForm]-Objekt mit den Angaben aus `application.views.site.loginForm` 
erzeugt (der Dateipfad wird als Pfadalias angegeben). Dieses [CForm]-Objekt
ist hier als Beispeil mit dem `LoginForm`-Model verknüpft, das wir schon im Kapitel 
[Erstellen des Models](/doc/guide/form.model) verwendet haben.

Der Code ist relativ einfach zu verstehen: Falls das Formular abgeschickt wurde
(`$form->submitted('login')`) und alle Eingabefelder fehlerfrei sind
(`$form->validate()`) wird auf die Seite `site/index` umgeleitet. Andernfalls
soll der `login`-View mit diesem Formular gerendert werden.

Der Pfadalias `application.views.site.loginForm` verweist auf die PHP-Datei
`protected/views/site/loginForm.php`. Diese Datei muss ein Array
mit der [CForm]-Konfiguration zurückliefern: 

~~~
[php]
return array(
	'title'=>'Bitte geben Sie Ihre Anmeldedaten ein',

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
            'label'=>'Anmelden',
        ),
    ),
);
~~~

Wie üblich entsprechen die Schlüsselnamen dieses Arrays den Namen der 
Klassenvariablen von [CForm], die mit den entsprechenden Werten belegt werden
sollen. Die wichtigsten Einträge sind hierbei [CForm::elements] und
[CForm::buttons]. Jede dieser Eigenschaften besteht aus einem weiteren Array,
das die Formularelemente definiert. Darauf gehen wir später noch genauer ein.

Schließlich wird noch das `login`-Viewscript benötigt, das im einfachsten Fall
so aussehen kann:

~~~
[php]
<h1>Anmeldung</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~

> Tip|Tipp: `echo $form` ist äquivalent zu `echo $form->render();`, da [CForm]
> nämlich die magische PHP-Methode `__toString` enthält. Darin wird `render()`
> aufgerufen und dessen Ausgabe, also das fertige HTML-Formular, zurückgeliefert. 


Angeben der Formularelemente
----------------------------

Verwendet man den Form-Builder, verlagert sich der Tätigkeitsschwerpunkt
weg vom Erstellen des Views, hin zur Definition der Formularelemente.
Im folgenden beschreiben wir, wie diese Angaben für [CForm::elements]
aussehen müssen. Für [CForm::buttons] gilt analog das selbe, weshalb
wir darauf nicht weiter eingehen.

[CForm::elements] erwartet ein Array als Wert, wobei jedes Element einem 
Formularelement entspricht. Dabei kann es sich um ein Eingabelement,
statischen Text oder ein Subformular handeln.

### Definieren eines Eingabeelements

Ein Eingabeelement besteht im Wesentlichen aus einem Label, einem Eingabefeld,
einem Hilfstext und einer Fehleranzeige. Es muss außerdem mit einem Modelattribut
verknüpft sein. Die Angaben für ein Element werden in Form einer
[CFormInputElement]-Instanz festgelegt. Folgender Beispielcode aus einem
[CForm::elements]-Array definiert ein solches Element:

~~~
[php]
'username'=>array(
    'type'=>'text',
    'maxlength'=>32,
),
~~~

Damit wird festgelegt, dass das entsprechende Modelattribut `username` heißt
und das Eingabefeld vom Typ `text` mit einem `maxlength`-Attribut von 32
sein soll. 

So kann jede beschreibbare Eigenschaft eines [CFormInputElement]s konfiguriert
werden. Mit der [hint|CFormInputElement::hint]-Option kann man so zum
Beispiel einen Hilfstext angeben oder mit [items|CFormInputElement::items] die
Elemente in einer DropDown-, CheckBox- oder RadioButton-List bestimmen
(entsprechend den Methoden in [CHtml]). Handelt es sich bei einer Option nicht
um den Namen einer [CFormInputElement]-Eigenschaft, wird sie als Attribut
des entsprechenden HTML-Elements verwendet. Im obigen Beispiel wird daher
z.B. das `maxlength`-Attribut als HTML-Attribut des entsprechenden
Eingabefeldes gerendert.

Sehen wir uns die [type|CFormInputElement::type]-Option näher an. Mit ihr wird
der Typ des Eingabefelds festgelegt. Der Typ `text` steht zum Beispiel für ein
normales Textfeld, `password` für ein Passwortfeld. Folgende Typen werden "von
Haus aus" von [CFormInputElement] erkannt:

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

Von allen diesen eingebauten Typen wollen wir näher auf die Verwendung der
"Listen"-Typen eingehen, also `dropdownlist`, `checkboxlist` und `radiolist`. 
Diese Typen erfordern, dass die [items|CFormInputElement::items]-Eigenschaft
des entsprechenden Eingabeelements wie folgt angegeben wird:

~~~
[php]
'gender'=>array(
    'type'=>'dropdownlist',
    'items'=>User::model()->getGeschlechtOptions(),
    'prompt'=>'Bitte wählen:',
),

...

class User extends CActiveRecord
{
	public function getGeschlechtOptions()
	{
		return array(
			0 => 'Männlich',
			1 => 'Weiblich',
		);
	}
}
~~~

Dieser Code erzeugt eine Dropdownliste mit dem Aufforderungstext "Bitte
wählen:". Die Liste enthält die Optionen "Männlich" und "Weiblich", so wie
sie von der `getGeschlechtOptions`-Methode in der `User`-Klasse geliefert
werden.

Daneben kann die [type|CFormInputElement::type]-Option auch den Namen
einer Widgetklasse oder deren Pfadalias enthalten. Die Widgetklasse muss
lediglich [CInputWidget] oder [CJuiInputWidget] erweitern. Beim Rendern des Elements
wird eine Instanz der angegebenen Widgetklasse mit den angegebenen
Elementparametern erzeugt und gerendert.


### Verwenden von statischem Text

In vielen Fällen enthält ein Formular zusätzlichen, rein "dekorativen" HTML-Code,
wie zum Beispiel eine horizontale Linie um Formularabschnitte voneinander
zu trennen. An anderen Stellen wird eventuell ein Bild zur Auflockerung des
Formulars verwendet. Solche statischen Elemente werden einfach als String an
der entsprechenden Stelle des [CForm::elements]-Arrays angeben. 

Hier ein Beispiel:

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

Zwischen die Elemente für `password` und `rememberMe` wird so eine horizontale
Linie eingefügt.

Statischer Text eignet sich am besten für unregelmäßig verteilte Inhalte.
Sollen hingegen alle Elemente mit ähnlicher "Dekoration" versehen werden, ist
es günstiger, eine eigene Rendermethode zu verwenden. Darauf werden wir weiter
unten noch eingehen.


### Verwenden von Subformularen

Subformulare eignen sich dazu, sehr lange Formulare in mehrere logisch
zusammenhängende Blöcke zu gliedern. Ein langes Registrierungsformular könnte
man so z.B. in Anmelde- und Profildaten unterteilen. Ein Subformular kann - muss 
aber nicht - mit einem eigenen Datenmodel verknüpft sein. 
Wenn beim erwähnten Registrierungsformular die Anmelde- und
Profildaten in zwei unterschiedlichen Datebanktabellen  (und damit
zwei Datenmodels) gespeichert werden, würde man jedes Subformular mit dem
entsprechenden Datenmodel verknüpfen. Speichert man alles in einer
einzelnen Tabelle braucht keines der Subformulare ein Model, 
da sie dann das Model des Wurzelformulars verwenden.

Ein Subformular ist ebenfalls ein [CForm]-Objekt. Ein Subformular
wird dem [CForm::elements]-Array als Element vom Typ `form`
hinzugefügt:

~~~
[php]
return array(
    'elements'=>array(
		......
        'user'=>array(
            'type'=>'form',
            'title'=>'Anmeldedaten',
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

Auch das Subformular besteht im Wesentlichen wieder aus Einträgen im
[CForm::elements]-Array. Soll das Subformular mit einem
eigenen Model verknüpft werden, kann dies über die [CForm::model] angegeben
werden.

Manchmal kann es nötig sein, eine andere Formklasse statt [CForm] zu
verwenden. Wie wir in Kürze sehen werden, kann man z.B. eine eigene Klasse von
[CForm] ableiten, um die Renderlogik anzupassen. Sämtliche Subformulare
verwenden standardmäßig die selbe Klasse wie deren Elternelement. Soll ein
Subformular eine andere Klasse verwenden, kann der Typ auf `XyzForm`
gesetzt werden (also einen String, der auf `Form` endet). Das Subformular wird
dann als `XyzForm`-Objekt erstellt.


Zugriff auf Formularelemente
----------------------------

Auf Formularelemente kann man wie auf Arrayelemente zugreifen.
Liest man die Eigenschaft [CForm::elements] aus, erhält man ein Objekt
vom Typ [CFormElementCollection] zurück, das wiederum von [CMap] abgeleitet
wurde. Dadurch kann es wie ein normales Array angesprochen werden. 
Das Element `username` des weiter oben definierten Loginformulars 
erhält man zum Beispiel mit:

~~~
[php]
$username = $form->elements['username'];
~~~

Und auf das `email`-Element des Registrierungsformulars kann man so zugreifen:

~~~
[php]
$email = $form->elements['user']->elements['email'];
~~~

[CForm] implementiert außerdem das ArrayAccess-Interface so, dass man damit 
direkt auf [CForm::elements] zugreifen kann. Statt den obigen Zeilen kann man
daher noch einfacher schreiben:

~~~
[php]
$username = $form['username'];
$email = $form['user']['email'];
~~~


Erstellen eines verschachtelten Formulars
-----------------------------------------

Formulare, die, wie eben beschrieben, Subformulare enthalten, nennen wir
verschachtelte Formulare (engl.: nested forms). Anhand des erwähnten
Registrierungsformulars zeigen wir hier, wie man ein verschachteltes 
Formular erstellt, das mit mehreren Datenmodels verknüpft ist.
Dabei seien die Anmeldeinformation im Model `User` und die Profildaten im
Model `Profile` gespeichert.

Zunächst benötigen wir eine `register`-Action:

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

Die Formularkonfiguration wird hier in `application.views.user.registerForm`
abgelegt. Wurde das Formular abgeschickt und die Daten erfolgreich geprüft,
wird versucht, die Models `User` und `Profile` zu speichern. Diese Models
können über die `model`-Eigenschaft des jeweiligen Subformulars bezogen 
werden. Da die Validierung bereits durchgeführt wurde, wird
`$user->save(false)` aufgerufen, um eine nochmalige Prüfung zu verhindern.
Mit dem `Profile`-Model wird ebenso verfahren.

Sehen wir uns als nächstes die Formularkonfiguration in
`protected/views/user/registerForm.php` an:

~~~
[php]
return array(
	'elements'=>array(
		'user'=>array(
			'type'=>'form',
			'title'=>'Anmeldedaten',
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
			'title'=>'Profildaten',
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
            'label'=>'Registrieren',
        ),
    ),
);
~~~

Bei jedem Subformular wird hier auch eine [CForm::title]-Eigenschaft definiert.
Standardmäßig sorgt die Renderlogik dafür, dass jedes Subformular in ein
eigenes fieldset mit dieser Eigenschaft als Titel eingebettet wird.

Nun fehlt nur noch das Viewscript für `register`:

~~~
[php]
<h1>Registrierung</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~


Anpassen der Formularausgabe
----------------------------

Der eigentliche Nutzen des Form-Builders liegt in der Trennung von Logik
(Formularkonfiguration in einer eigenen Datei) und Präsentation
([CForm::render]-Methode). Dadurch kann die Anzeige des Formulars angepasst
werden. Entweder, indem man [CForm::render] überschreibt oder indem man einen
Teilview zum Rendern des Formulars angibt. Beide Ansätze sind unabhängig von
der Formularkonfiguration und lassen sich so einfach wiederverwenden.

Überschreibt man [CForm::render], so müssen dort eigentlich nur
[CForm::elements] und [CForm::buttons] in einer Schleife durchlaufen 
und die [CFormElement::render]-Methode jedes Elements aufgerufen
werden:

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

Zum Rendern des Formular kann man auch ein Viewscript `_form` verwenden:

~~~
[php]
<?php
echo $form->renderBegin();

foreach($form->getElements() as $element)
	echo $element->render();

echo $form->renderEnd();
~~~

Dieses Script kann so aufgerufen werden:

~~~
[php]
<div class="form">
$this->renderPartial('_form', array('form'=>$form));
</div>
~~~

Falls ein Formular nicht mit diesem allgemeinen Renderansatz dargestellt
werden kann (z.B. weil einige Elemente vollkommen anders aussehen müssen),
kann man im Viewscript auch so verfahren:

~~~
[php]
Einige komplexe UI-Elemente hier

<?php echo $form['username']; ?>

Einige komplexe UI-Elemente hier

<?php echo $form['password']; ?>

einige komplexe UI-Elemente hier
~~~

Bei dieser Methode scheint der Form-Builder nicht viel zu nützen, da man immer
noch fast genauso viel Code wie bisher braucht. Trotzdem kann sich der Einsatz
lohnen, da das Formular in einer separaten Datei definiert wird und sich
der Entwickler so besser auf die Logik konzentrieren kann.

<div class="revision">$Id: form.builder.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
