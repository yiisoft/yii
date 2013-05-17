Erstellen der Action
====================

Steht das Model bereit, kann man daran gehen, die Programmlogik zum Verändern des
Models umzusetzen. Der dazu benötigte Code wird in einer Controlleraction untergebracht. 
Für unsere Anmeldeseite kann der Code so aussehen:

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// Erfasst die gesendeten Formulardaten
		$model->attributes=$_POST['LoginForm'];
        // Validiert die Daten und kehrt zur vorherigen Seite zurück, 
		// wenn die Prüfung erfolgreich war.
	    if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// Zeigt das Anmeldeformular
	$this->render('login',array('model'=>$model));
}
~~~

Zunächst wird eine Instanz des `LoginForms` erstellt. Liegen Formulardaten im
POST-Request vor (was heißt, dass das Formular abgeschickt wurde), wird
`$model` mit den Daten aus `$_POST['LoginForm']` befüllt. Ist die folgende
Validierung des Models erfolgreich, wird der Browser auf die Seite umgeleitet,
die der Besucher ohne die dafür nötige Anmeldung aufrufen wollte. Ergab die Prüfung
Fehler oder wird die Action zum ersten mal ausgeführt, wird der View `login`
gerendert. Darauf werden wir im nächsten Abschnitt näher eingehen.

> Tip|Tipp: Die `login`-Action benutzt `Yii::app()->user->returnUrl` um die URL
der Seite zu ermitteln, für die der Benutzer zunächst authentifiziert werden
musste. Die Komponente `Yii::app()->user` ist vom Typ [CWebUser] (oder einer 
Kindklasse) und steht für Informationen der aktuellen Benutzersitzung 
(z.B. Benutzername, Status). Weitere Einzelheiten dazu finden Sie unter 
[Authentifizierung und Autorisierung](/doc/guide/topics.auth).
 

Sehen wir uns die folgende Anweisung aus obigem Code genauer an:

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

Wie im Abschnitt [Sichere Attributzuweisungen](/doc/guide/form.model#securing-attribute-assignments)
beschrieben, befüllt diese Codezeile das Model mit den gesendeten
Formulardaten.  Die `attributes`-Eigenschaft ist in [CModel] definiert. Sie erwartet 
ein Array aus Name-Wert-Paaren und weist jedem Modelattribut den entsprechenden Wert zu. 
Geht man davon aus, dass in `$_POST['LoginForm']` ein Array in eben dieser
Form übergeben wird, könnte man statt dieser einen Zeile auch folgenden, etwas
länglichen Code verwenden (vorausgesetzt, dass jedes erforderliche Attribut im Array
vorhanden ist):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|Hinweis: Damit `$_POST['LoginForm']` ein Array statt eines String
liefert, hält Yii sich an eine Konvention bezüglich Namen für Formularfelder
in einem View. Das Eingabefeld, das dem Attribut `a` einer Modelklasse `C` 
entspricht, heißt `C[a]`.  Das Eingabefeld für das das Attribut `username` 
aus obigem Beispiel würde daher `LoginForm[username]` heißen.

Schließlich muss nun noch der `login`-View mit einem HTML-Formular erstell
werden.

<div class="revision">$Id: form.action.txt 1837 2010-02-24 22:49:51Z qiang.xue $</div>
