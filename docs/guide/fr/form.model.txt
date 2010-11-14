Créer le modèle
===============

En amont de l'écriture du code HTML d'un formulaire, il est nécessaire de réfléchir
aux types de données que nous demanderons aux utilisateurs et quelles règles
s'appliqueront à ces données. Une classe représentant un modèle de données
pourra être utilisée pour sauvegarder ces données. Un modèle, tel que défini
dans la sous section [Modèle](/doc/guide/basics.model), est le point central
pour sauvegarder les entrées utilisateurs et pour les valider.

Selon ce que l'on souhaite faire des données entrées par l'utilisateur, il est possible
de créer deux types de modèles. Si les données sont utilisées puis immédiatement supprimées,
un [form model](/doc/guide/basics.model) sera utilisé; si les données sont récupérées pour
etre enregistrées dans une base de données, un [active record](/doc/guide/database.ar) sera utilisé.
Ces deux types de modèles partagent la même classe mère [CModel] qui défini une interface commune
requise pour les formulaires.

> Note: Nous utiliserons principalement des modèles form dans les exemples suivants.
Cependant, tout est applicable sur des modèles [active record](/doc/guide/database.ar).

Définir une classe pour un modèle
---------------------------------

Ci dessous nous créons une classe pour le modèle `LoginForm` afin de récupérer les données utilisateurs
sur une page de connection. Comme ces informations de connection ne seront utilisées que pour
authentifier l'utilisateur, sans être enregistrées, nous créerons `LoginForm` comme un modèle form.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

Trois attributs sont déclarés dans `LoginForm`: `$username`, `$password` et
`$rememberMe`. Ils servent à stocker le nom d'utilisateur et le mot de passe
entrés par l'utilisateur, ainsi que l'option pour rester connecté.
Comme `$rememberMe` à pour valeur par défaut `false`, l'option correspondante
quand le formulaire sera affiché sera décochée.

> Info: Plutot que d'appeler ces variables des propriétés, le terme 
d'attribut est préféré afin de les distinguer des propriétés normales. Un
attribut est une propriété qui est principalement utilisé pour stocker des
données venant d'entrées utilisateur ou de la base de données.

Déclarer des règles de validation
---------------------------------

Une fois que l'utilisateur à envoyé son formulaire et que les données sont
stockées dans le modèle, il faut vérifier que ces données sont valides avant
de les utiliser. Pour cela un ensemble de règles sont appliquées à ces données
afin de les valider. Pour spécifier ces règles, on utilise la méthode `rules()`
qui retourne un tableau avec ces règles.

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

Le code ci dessus déclare que `username` et `password` sont tout deux requis,
`password` doit être un champ mot de passe qui correspondra avec le login, et `rememberMe`
doit etre un booléen.

Chaque règle retournée par `rules()` doit suivre le format suivant:

~~~
[php]
array('AttributeList', 'Validator', 'on'=>'ScenarioList', ...options supplémentaires)
~~~

où `AttributeList` est une chaine de caractères d'attributs séparés par des virgules qui
doivent être validés selon la règle; `Validator` spécifie quel type de
validation doit être effectuée; le paramètre `on`, optionel, spécifie une liste de
scénarios pour lesquels la règle doit être appliquée; et les options supplémentaires
sont des paires de noms-valeurs qui sont utilisés pour initialiser les valeurs des propriétés
correspondantes du validateur.

Il existe trois méthodes pour spécifier un `Validator` dans une règle de validation.


La première, où un `Validator` peut être le nom d'une méthode dans une classe,
tel que `authenticate` dans l'exemple ci dessus. La méthode de validation doit suivre
la signature suivante:

~~~
[php]
/**
 * @param string le nom de l'attribut à valider
 * @param array options spécifiée dans la règle de validation
 */
public function ValidatorName($attribute,$params) { ... }
~~~

Une deuxième où le `Validator` peut etre le nom d'une classe de validation. Quand la règle
est appliquée, une instance de cette classe sera créée pour exécuter la validation.
Les options supplémentaires dans la règle seront utilisées pour initialiser les attributs de
cette instance. Une classe de validation doit hériter de [CValidator].

Une troisième où le `Validator` sera un alias prédéfinit d'une classe de validation. Dans l'exemple
ci dessus, le nom `required` est un alias pour [CRequiredValidator]
qui s'assure que la valeur de l'attribut a validater n´est pas vide. Vous trouverez ci dessous
la liste complète des alias de `Validator` prédéfinis:

   - `boolean`: alias pour [CBooleanValidator], s'assure que l'attribut est
soit [CBooleanValidator::trueValue] ou [CBooleanValidator::falseValue].

   - `captcha`: alias pour [CCaptchaValidator], s'assure que l'attribut est
égal à un code de vérification affiché dans un [CAPTCHA](http://en.wikipedia.org/wiki/Captcha).

   - `compare`: alias pour [CCompareValidator], s'assure que the attribute est égal à un autre attribut ou constante.

   - `email`: alias pour [CEmailValidator], s'assure que l'attribut est une
adresse email valide.

   - `default`: alias pour [CDefaultValueValidator], assigne une valeur par défaut aux attributs.

   - `exist`: alias pour [CExistValidator], s'assure que la valeur de l'attribut
existe dans la colonne de la table spécifiée.

   - `file`: alias pour [CFileValidator], s'assure que l'attribute contient
le nome d'un fichier uploadé.

   - `filter`: alias pour [CFilterValidator], transforme un attribut grâce à un filtre.

   - `in`: alias pour [CRangeValidator], s'assure que les données sont contenu dans une
liste de valeurs pré-specifiées.

   - `length`: alias pour [CStringValidator], s'assure que la longueur de la valeur est
dans un certain interval.

   - `match`: alias pour [CRegularExpressionValidator], s'assure que la valeur satisfait
une expression régulière.

   - `numerical`: alias pour [CNumberValidator], s'assure que la valeur est un 
nombre valide.

   - `required`: alias pour [CRequiredValidator], s'assure que l'attribut n´est
pas vide.

   - `type`: alias pour [CTypeValidator], s'assure que l'attribut est d'un type de
donnée spécifique.

   - `unique`: alias pour [CUniqueValidator], s'assure que la valeur est unique dans
une colonne de table.

   - `url`: alias pour [CUrlValidator], s'assure que la valeur est une URL valide.

Quelques exemples d'utilisation de ces `Validator` prédéfinis:

~~~
[php]
// username est requis
array('username', 'required'),
// username doit être entre 3 et 12 caractères
array('username', 'length', 'min'=>3, 'max'=>12),
// quand dans un scénario d'inscription, password doit être égal à password2
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// quand dans un scénario de connection, password doit être correspondre avec login
array('password', 'authenticate', 'on'=>'login'),
~~~


Sécuriser l'assignation des attributs
-------------------------------------

Une fois que l'instance du modèle est créée, il est courant de devoir
assigner ses attributs avec les données soumises par les utilisateurs.
Cela peut être aisément fait grâce à cette assignation massive suivante:

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
	$model->attributes=$_POST['LoginForm'];
~~~

La dernière opération est appelée *assignation massive* en cela qu´elle assigne
toutes les entrées de `$_POST['LoginForm']` aux attributs correspondant du modèle.
Elle est équivalente aux assignations suivantes:

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name is a safe attribute)
		$model->$name=$value;
}
~~~

Il est crucial de déterminer quels attributs sont sains. Par exemple, si nous
déclarons une clé primaire d'une table comme étant saine, alors un attaquant pourrait
avoir la possibilité de modifier cette clé et ainsi altérer des données qu´il ne devrait
pas être autorisé à modifier.

La règle pour décider quels attributes sont sains est différent dans la version 1.0
et 1.1. Nous allons les décrire séparemment.

###Attributs sains in 1.1

Dans la version 1.1, un attribut est considéré comme sain si il apparait dans une
règle de validation qui est appliqué dans le scénario courant. Par exemple,

~~~
[php]
array('username, password', 'required', 'on'=>'login, register'),
array('email', 'required', 'on'=>'register'),
~~~

Dans le code ci dessus, les attributs `username` et `password` sont requis dans le
scénario `login`, alors que les attributs `username`, `password` et `email` sont requis
dans le scénario `register`. Ainsi, si une assignation massive est effectuée lors d'un
scénario `login`, seulement `username` et `password` seront assignés massivement
car ce sont les seuls apparaissant dans les règles de validation pour le scénario `login`.
Sinon, si le scénario est `register`, les trois attributs pourront être assignés massivement.

~~~
[php]
// scénario login
$model=new User('login');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];

// scénario register
$model=new User('register');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];
~~~

Pourquoi utiliser un telle politique pour déterminier si un attribut est sain ou non?
L´idée derrière ce principe est que si un attribut à déjà une ou plus règles de validation
qui lui sont appliquées pour le valider, pourquoi se soucier encore de lui?

Il est important de se rappeler que les règles de validation sont utilisées pour vérifier
les données provenant des utilisateurs plutot que les données générées par le code (par
exemple timestamp, clés primaires auto-générées).
Ainsi, ne PAS ajouter de règles de validation pour les attributes qui ne doivent pas
recevoir de données des utilisateurs.

Parfois, il est utile de déclarer un attribut comme sain, bien que nous n´ayons aucune
règle spécifique pour lui. Un exemple pourrait être un attribut représentant le contenu d'un
article qui pourrait recevoir potentiellement n´importe quelle valeur. Nous pouvont dans
ce cas utiliser la règle spéciale `safe` pour parvenir à nos fins:

~~~
[php]
array('content', 'safe')
~~~

Pour être complet, il existe aussi une règle `unsafe` qui est utilisé explicitement pour déclarer
un attribut comme unsafe:

~~~
[php]
array('permission', 'unsafe')
~~~

Cette règle `unsafe` est rarement utilisée, et c´est une exception à notre précédente définition
des attributs sains.


###Attributs sains in 1.0

Dans la version 1.0, la tache de décider si une donnée est saine ou non se fait
sur la base de la valeur de retour de la méthode `safeAttributes` et du scénario
spécifié. Par défaut, la méthode retourne toutes les variable publiques de [CFormModel]
en tant qu´attributs sains, alors qu´elle retourne toutes les colonnes de la table
comme attributs sains à l'exception de la clé primaire pour [CActiveRecord].
Il est possible de surcharger cette méthode pour limiter les attributs sains selon
les scénarios. Par exemple, un modèle utilisateur peut contenir de nombreux attributs,
mais pour le scénario `login`, seuls les attributs `username` et `password` sont utiles.
Cette limite s'effectue comme suivant:

~~~
[php]
public function safeAttributes()
{
	return array(
		parent::safeAttributes(),
		'login' => 'username, password',
	);
}
~~~

Plus exactement, la valeur de retour de la méthode `safeAttributes` doit avoir la
structure suivant:

~~~
[php]
array(
   // ces attributs peuvent être assignés massivement pour tous les scénarios
   // qui ne sont pas explicitement cités ci dessous
   'attr1, attr2, ...',
	 *
   // ces attributs peuvent être assignés massivement seulement pour le scénario 1
   'scenario1' => 'attr2, attr3, ...',
	 *
   // ces attributs peuvent être assignés massivement seulement pour le scénario 2
   'scenario2' => 'attr1, attr3, ...',
)
~~~

Si le modèle est indépendant de scénario (par exemple, si il est utilisé dans un
seul scénario, ou tous les scénarios partagent le même ensemble d'attributs sains),
la valeur de retour peut être simplifiée comme une simple chaine de caractères:

~~~
[php]
'attr1, attr2, ...'
~~~

Pour les données qui ne sont pas saines, il faut les assigner aux attributs
correspondants en utilisant les opérations d'assignation individuelle, comme suivant:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~


Déclenchement de la validation
------------------------------

Une fois que les données soumises par l'utilisateur ont été assignées au modèle,
il est possible d'appeler [CModel::validate()] pour déclencher le processus de validation
des données. Cette méthode retourne une valeur indiquant si la validation s'est déroulée
sans erreur ou non. Pour les modèles [CActiveRecord], la validation sera aussi
automatiquement déclenchée à l'appel de la méthode [CActiveRecord::save()].

Il est possible d'assigner un scénario avec la propriété [scenario|CModel::scenario]
et d'indiquer quel ensemble de règles de validation doit être appliqué.

La validation est effectué sur la base des scénario. La propriété [scenario|CModel::scenario]
spécifie dans quel scénario le modèle est actuellement utilisé et quel ensemble de règles de
validation doivent être utilisées. Par exemple, dans le scénario `login`, nous ne voulons
valider que les entrées `username` et `password` du modèle user; alors que dans le scénario
`register`, nous voulons valider plus d'entrées, telles que `email`, `address`, etc.
L'exemple suivant montre comment éffectuer une validation dans le scénario `register`:

~~~
[php]
// cré le modèle User dans un scénario register. Cela est équivalent à:
// $model=new User;
// $model->scenario='register';
$model=new User('register');

// assigne les données du modèle
$model->attributes=$_POST['User'];

// effectue la validation
if($model->validate())   // si les données sont valides
    ...
else
    ...
~~~

Les scénarios applicables associés à une règle sont spécifiés au travers
de l'option `on` dans la règle. Si l'option `on` n´est pas spécifiée, cela signifie
que la règle sera utilisée dans tous les scénarios. Par exemple,

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

La première règle sera appliquée dans tous les scénarios, alors que
les deux règles suvantes s'appliqueront uniquement au scénario `register`.


Récupération des erreurs de validation
--------------------------------------

Une fois que la validation est finie, toutes les erreurs susceptibles d'avoir
été rencontrées sont stockées dans l'objet. Il est alors possible de récupérer les
messages d'erreurs en appelant [CModel::getErrors()] et [CModel::getError()]. La
différence entre ces deux méthodes est que la première renvoie *toutes* les erreurrs
pour un certain attribut du modèle alors que la seconde méthode renverra uniquement
la *première* erreur.

Libellés des attributs
----------------------

A la conception d'un formulaire, il est souvent nécessaire d'afficher un libellé
pour chaque champs. Le libellé renseigne l'utilisateur sur le type d'information
qu´il est censé donner. Bien qu´il soit possible de l'écrire directement dans la
vue, il est plus pratique et cela offre plus de flexibilité de spécifier ces libellés
dans le modèle.

Par défaut, [CModel] retournera simplement le nom de l'attribut comme libellé.
Il est possible de personnaliser cela en surchargeant la méthode [attributeLabels()|CModel::attributeLabels]. Comme nous le verrons dans les sections suivantes, spécifier
des libellés au niveau du modèle permet de créer des formulaires plus rapidement.

<div class="revision">$Id: form.model.txt 2285 2010-07-28 20:40:00Z qiang.xue $</div>
