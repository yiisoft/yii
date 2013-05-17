Module
======

> Note: Le support des modules est disponible depuis la version 1.0.3.

Un module est un bout de logiciel autonome qui comporte des
[modèles](/doc/guide/basics.model), des [vues](/doc/guide/basics.view),
des [contrôleurs](/doc/guide/basics.controller) et autres composants.
Sous plusieurs aspects, un module est assez similaire à une [application](/doc/guide/basics.application).
La principale différence est qu'un module ne peut être déployé seul et doit
absolument être inclus dans une application. Les utilisateurs peuvent
accéder aux contrôleurs d'un module comme ils le font avec les contrôleurs de
l'application.

Les modules sont utiles dans divers cas.
Pour une application conséquente, il est possible de la diviser en
plusieurs modules, chacun étant développé et maintenu indépendament.
Des fonctionnalités génériques telles que la gestion des utilisateurs, des commentaires
peuvent être déployées sous la forme de modules et ainsi être réutilisées simplement
dans d'autres projets.


Créer Un Module
---------------

Un module est conçu à l'intérieur d'un dossier. Ce dossier défini son [ID|CWebModule::id] unique.
La structure d'un module est similaire à celle du
[dossier de base de l'application](/doc/guide/basics.application#application-base-directory).
Ci-dessous, la structure du module `forum`:

~~~
forum/
   ForumModule.php            La classe du module
   components/                Composants réutilisables
      views/                  Vues widget
   controllers/               Contrôleurs
      DefaultController.php   Contrôleur par défaut
   extensions/                Extensions tierces
   models/                    Modèles
   views/                     Vues et Layouts
      layouts/                Layouts
      default/                Vues du contrôleur par défaut
         index.php            La vue index
~~~

Un module doit avoir une classe qui étends [CWebModule].
Le nom de cette classe est défini par l'expression `ucfirst($id).'Module'`,
ou `$id` aorrespond à l'ID du module (ou au nom du dossier du module).
La classes du module est le noyau central qui permet de gérer et sauvegarder
toutes les informations nécessaires au bon foncitonnement du code.
Par exemple, il est possible d'utiliser [CWebModule::params] pour sauvegarder
les paramètres, et d'utiliser [CWebModule::components] pour partager les
[composants applicatifs](/doc/guide/basics.application#application-component) au niveau du module.

> Astuce|Tip: Il est possible d'utiliser l'outil `yiic` pour créer le squelette d'un module. Par exemple, pour créer le module `forum`, il faut exécuter la commande CLI suivante:
>
> ~~~
> % cd WebRoot/testdrive
> % protected/yiic shell
> Yii Interactive Tool v1.0
> Please type 'help' for help. Type 'exit' to quit.
> >> module forum
> ~~~


Utilisation d'un Module
-----------------------

Pour utiliser un module, il faut le déployer dans le dossier `modules` du
[dossier de base de l'application](/doc/guide/basics.application#application-base-directory).
Il faut ensuite déclarer l'ID du module au niveau de la propriété [modules|CWebApplication::modules]
de l'application.
Par exemple, pour pouvoir utiliser le module `forum`, il est possible d'utiliser
la [configuration d'application](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

Un module peut aussi être configuré. L'usage est très similaire à
la configuration des [composants d'application](/doc/guide/basics.application#application-component).
Par exemple, le module `forum` pourrait avoir une propriété nommée
`postPerPage` au sein de sa class qui pourrait être configurée dans la
[configuration de l'application](/doc/guide/basics.application#application-configuration) comme suit:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

L'instance d'un module peut être accédé via la propriété [module|CController::module] p
du contrôleur courant. Au travers de l'instance du module, il est possible d'accéder
aux informations qui sont partagées au niveau du module. Par exemple,
au lieu d'accéder à `postPerPage`, il est possible d'utiliser l'expression suivante:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// ou $this référence l'instance du contrôleur
// $postPerPage=$this->module->postPerPage;
~~~

L'action d'un contrôleur d'un module peut être accédé en utilisant la [route](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID`. Par exemple, en assumant que le module `forum` a un contrôleur nommé `PostController`, il est possible d'utiliser la [route](/doc/guide/basics.controller#route) `forum/post/create` pour référence l'action `create` au sein du contrôleur. L'URL correspondant à cette route serait `http://www.example.com/index.php?r=forum/post/create`.

> Astuce|Tip: Si un contrôleur et dans un sous-dossier de `controllers`, il est possible d'utiliser le format de [route](/doc/guide/basics.controller#route) ci-dessus. Par exemple, si `PostController` est sous `forum/controllers/admin`, il est possible de référence l'action `create` en utilisant `forum/admin/post/create`.


Modules Imbriqués (nested)
--------------------------

Les modules peuvent être imbriqués. Le premier est appelé *module père* et le second *module fils*. Les modules fils doivent être placés dans le dossier `modules` du module père. Pour accéder à l'action d'un contrôleur d'un module enfant, il faut utiliser la rout `parentModuleID/childModuleID/controllerID/actionID`.


<div class="revision">$Id: basics.module.txt 745 $</div>