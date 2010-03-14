Skapa Action
============

När en modell finns på plats, vidtar arbetet med att skriva logik som behövs för 
att manipulera modellen. Vi placerar denna logik i en kontrollers åtgärd 
(action). I exemplet med login-formuläret erfordras följande kod:

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// collects user input data
		$model->attributes=$_POST['LoginForm'];
		// validates user input and redirect to previous page if validated
		if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// displays the login form
	$this->render('login',array('model'=>$model));
}
~~~

I ovanstående exempel skapas först en instans av `LoginForm`; om inkommen 
request är av typen POST (underförstått att det ifyllda login-formuläret 
skickats), förses `$model` med inskickad data `$_POST['LoginForm']`; därefter 
valideras inmatningen och om denna befinns korrekt styrs användarens webbläsare 
till sidan som tidigare erfordrade autentisering. Om däremot valideringen 
misslyckas, eller åtgärden körs initialt, renderas `login`-vyn, vars innehåll 
kommer att beskrivas i nästa underavsnitt.

> Tip|Tips: I `login`-åtgärden används `Yii::app()->user->returnUrl` för att få 
tag på URL:en till sidan som tidigare behövde autentisering. Komponenten 
`Yii::app()->user` är av typen [CWebUser] (eller nedärvd klass) vilken 
representerar information om användarsessionen (t.ex. användarnamn, status). För 
fler detaljer, se [Autentisering och auktorisation](/doc/guide/topics.auth).

Låt oss titta närmare på följande PHP-sats som förekommer i `login`-åtgärden:

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

Som beskrivits i  [Säkra upp 
attributtilldelningar](/doc/guide/form.model#securing-attribute-assignments), 
tilldelar denna rad modellen de data användaren skickat in. Propertyn 
`attributes` definieras av [CModel] vilken förväntar sig en array av 
namn-värdepar och tilldelar varje värde till motsvarande attribut i modellen. Så om 
`$_POST['LoginForm']` ger oss en sådan array, är ovanstående kodrad ekvivalent 
med följande längre kodavsnitt (förutsatt att varje erforderligt attribut finns 
med i arrayen):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note|Märk: För att `$_POST['LoginForm']` skall ge oss en array i stället för 
en sträng, håller vi oss till en konvention vid namngivning av vyns 
inmatningsfält. Mer detaljerat, ett inmatningsfält som motsvarar ett attribut 
`a` i modellklassen `C`, får nammnet `C[a]`. Till exempel använder vi 
`LoginForm[username]` som namn på inmatningsfältet som motsvarar attributet 
`username`.

Den återstående uppgiften är nu att skapa `login`-vyn vilken skall innehålla ett 
HTML-formulär med de erforderliga inmatningsfälten.

<div class="revision">$Id: form.action.txt 1837 2010-02-24 22:49:51Z qiang.xue $</div>