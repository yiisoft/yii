Vue
===

Une vue est un script PHP qui comprend principalement des éléments de l'interface
utilisateur. Elle peut contenir du code PHP, mais il est fortement recommandé
de ne pas altérer le modèle de données et de garder le code de la vue le plus 
simple possible. Pour respecter le concept de séparation des couches "logique"
et "présentation", le code lié à la partie logique doit être placée soit dans 
le contrôleur soit dans le modèle et non pas dans la vue.

Une vue a un nom qui permet d'identifier le fichier de la vue lors de l'opération
de rendu. Le nom de la vue est identique au nom du fichier. Par exemple,
la vue `edit` fait réfèrence au fichier PHP `edit.php`. Pour effectuer
l'opération de rendu, il faut appeler [CController::render()] avec le
nom de la vue. Cette méthode va rechercher le fichier correspondant dans le 
dossier `protected/views/ControllerID`.

Il est possible d'accéder à l'instance du contrôleur depuis la vue
en utilisant `$this`. Il est ainsi possible de `récupérer` dans la vue
n'importe quelle propriété du contrôleur en évaluant `$this->propertyName`.

Il est aussi possible d'utiliser cette méthode pour `pousser` des données
dans la vue: 

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

Dans le code ci-dessus, la méthode [render()|CController::render] va extraire
le contenu du second paramètre puis les charger dans des variables. En conséquence,
il est possible d'accéder directement dans la vue aux variables locales `$var1` et `$var2`.

Gabarit
-------

Le gabarit (Layout) est une vue spéciale qui est utilisée pour décorer les vues. 
Dans la plupart des cas, il contient des portions de l'interface utilisateur qui sont
communes à plusieurs vues. 
Par exemple, un gabarit pourrait contenir l'entête et le pied de page. Le contenu de 
la vue étant disposé entre les deux,

~~~
[php]
......header here......
<?php echo $content; ?>
......footer here......
~~~

la variable `$content` contient le rendu de la vue.

Le gabarit (Layout) est appliqué de manière implicite lors de l'appel à [render()|CController::render].
Par défaut, le script de la vue `protected/views/layouts/main.php` est utilisé en tant que
gabarit. Le nom peut être modifié en modifiant [CWebApplication::layout] ou 
[CController::layout]. Pour effectuer un rendu sans appliquer le gabarit,
il suffit d'appeler la méthode [renderPartial()|CController::renderPartial] en lieu et place
de [render()|CController::render].

Widget
------

Un widget est une instance de [CWidget] ou d'une classe dérivée. C'est un composant
principalement destiné à la gestion de la présentation. La plupart du temps, les Widgets
sont embarqués dans une vue pour générer des éléments complexes et autonomes de l'interface 
utilisateur. Les widgets permettent d'augmenter le taux de ré-utilisabilité au sein
des interfaces.

Pour utiliser un widget, il suffit de:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...contenu qui peut être capturé par le widget...
<?php $this->endWidget(); ?>
~~~

ou

~~~
[php]
<?php $this->widget('path.to.WidgetClass'); ?>
~~~

La seconde version est utilisée lorsque le qidget ne nécessite pas de contenu.

Les widgets peuvent être configuré pour adapter leurs comportements. Ceci
est effectué en modifiant les propriétés lors de l'appel à [CBaseController::beginWidget] 
ou [CBaseController::widget]. Par exmple, lors de l'utilisation du widget
[CMaskedTextField], nous voudrions pouvoir spécifier le masque à utiliser.
Cette adaptation est possible en passant un tableau qui contient les valeurs comme suit,

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

Pour définir un nouveau widget, il faut étendre [CWidget] et surcharger
les méthodes [init()|CWidget::init] et [run()|CWidget::run]:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// this method is called by CController::beginWidget()
	}

	public function run()
	{
		// this method is called by CController::endWidget()
	}
}
~~~

Comme pour un contrôleur, un widget peut avoir sa propre vue. Par défaut,
les fichiers des vues des widgets sont stockés dans le sous-dossier `views`
du dossier qui contient le fichier du widget. Ces vues peuvent être traitées
en appelant la méthode [CWidget::render()]. La principale différence entre les 
contrôleurs et les vues est que les gabarits ne sont pas appliqués sur les widgets.

Vue Système
-----------

Les vues systèmes sont des vues utilisés par Yii pour afficher les erreurs
ou des informations de log. Par exemple, lorsqu'un utilisateur appelle un 
contrôleur ou une action inexistente, Yii va lever une exception qui appelera
l'erreur. Yii affiche les exceptions en utilisant les vues système spécifiques.

Le nommage des vues système doit respecter quelques règles. Des noms tel
que `errorXXX` se réfèrent à des vues qui permette d'afficher les erreurs [CHttpException].
Par exempl, si [CHttpException] est levée avec un code erreur 404, la vue `error404`
sera affichée.

Yii apporte un série de vues système par défaut qui sont stockés dans 
`framework/views`. Elle peuvent être adaptés en créant des vues qui portent le
même nom dans le dossier `protected/views/system`.

<div class="revision">$Id: basics.view.txt 416$</div>
