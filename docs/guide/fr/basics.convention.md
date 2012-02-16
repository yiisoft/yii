Conventions
===========

Au delà de la configuration, Yii favorise la mise en oeuvre de conventions. 
Un bon respect des conventions permet de créer des applications Yii 
évoluées sans gérer ni écrire des configurations complexes. Bien évidemment,
grâce à la configuration, les divers aspects de Yii peuvent être adaptés 
dans quasiment tous les cas de figure.

Nous allons décrire dans les paragraphes suivants les recommandations que
devrait suivre tout développeur Yii. Par convention, `WebRoot` correspond au
dossier dans lequel l'application Yii est installée.

URL
---

Nativement, Yii reconnait les formats d'URL suivants:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

La variable GET `r` correspond à la [route](/doc/guide/basics.controller#route) 
qui est transcodée par le framework en contrôleur et action. Si `ActionID` 
n'est pas spécifiée, le contrôleur prendra l'action par défaut (définie via la propriété [CController::defaultAction]); 
de plus, si `ControllerID` n'est pas défini (ou si la variable `r` est absente), l'application
utilisera le contrôleur par défaut (défini via la propriété [CWebApplication::defaultController]).

En utilisant le [CUrlManager], il est possible de concevoir et de reconnaître 
d'autres formats d'URL tel que `http://hostname/ControllerID/ActionID.html`. 
Cette fonctionnalité est décrite en détail dans le chapitre [Gestion des URLs](/doc/guide/topics.url).

Programmation / Code
--------------------

Yii recommande d'écrire les variables, functions et classes en camel case. Cela
signifie qu'il faut mettre en majuscule la première lettre de chaque mot puis
fusionner le tout sans espace.
Dans le cas des noms de variables et de fonctions, la première lettre doit être 
mise en minuscule pour pouvoir les différencier des noms de classes (e.g. `$basePath`,
`runController()`, `LinkPager`). Pour les propriétés privées des classes, 
il est recommandé de préfixer leur nom d'une underscore (e.g.
`$_actionList`).

Sachant que la notion de namespace n'est pas supporté par les versions de PHP 
antérieures à la 5.3.0, il est recommandé de nommer les classes de manière
unique afin d'éviter tout conflit avec les classes tierces. C'est pour cette 
raison que toutes les classes du framework sont préfixées de la lettre "C".

Une règle de nommage spécifique s'applique aux noms des classes des contrôleurs. 
Il est impératif de leur suffixer le mot `Controller` car l'ID
est défini par le nom de la classe auquel il faut supprimer 
le suffixe `Controller` et mettre la première lettre en minuscule.
Par exemple, la classe `PageController` aura comme ID `page`. Cette règle permet
de mieux sécuriser l'application et de rendre les URLs incluant un
contrôleur plus lisibles (e.g. `/index.php?r=page/index` au lieu de
`/index.php?r=PageController/index`).

Configuration
-------------

Une configuration est un tableau de paires clés-valeurs. Chaque clé 
représente le nom de la propriété d'un objet à configurer et chaque valeur
correspond à sa valeur initiale. Par exemple, `array('name'=>'Mon
application', 'basePath'=>'./protected')` définira les propriétés `name` et
`basePath` aux valeurs définies dans ce tableau.

Toute propriété d'un objet accessible en écriture peut être configurée. Si
elle n'est pas configurée, la propriété prendra alors sa valeur par défaut. Avant de 
configurer une propriété, il est important de se référer à la documentation pour
connaître les valeurs acceptables.

Fichiers
--------

Les conventions de nommage des fichiers dépendent de leur type et de leur finalité.

Les fichiers de classes doivent porter le nom de leur classe principale/publique. 
Par exemple, la classe [CController] doit être dans le fichier `CController.php`. 
Une classe principale/publique est une classe qui peut être utilisée par 
n'importe quelle autre classe. Il est donc important que chaque fichier de classe
contienne une classe principale/publique. A l'inverse, les classes privées 
(classes utilisées par une seule classe publique/principale) peuvent être
intégrées dans le fichier de la classe publique qui les références.

Les fichiers des vues doivent avoir le même nom que la vue. Par exemple,
la vue `index` doit être dans le fichier `index.php`. Un fichier de vue est 
un script PHP qui peut contenir du HTML ainsi que du code PHP a condition
que ce code serve uniquement à la présentation des données.

Il n'y a pas de convention pour les fichiers de configuration. Un fichier
de configuration étant un script PHP qui retourne un tableau associatif 
représentant la configuration.

Dossier
-------

Par défaut, Yii s'appuie sur plusieurs répertoires. Chacun peut
être configuré en fonction des besoins.

   - `WebRoot/protected`: C'est le [dossier de base de l'application](/doc/guide/basics.application#application-base-directory) 
qui contient tous les éléments sensibles (PHP et données). Yii dispose d'un
raccourcis par défaut `application` associé à ce chemin. Tout accès à ce dossier, 
ainsi qu'à ce qu'il contient doit être interdit aux utilisateurs web. Ce chemin
peut être modifié via la propriété [CWebApplication::basePath].

   - `WebRoot/protected/runtime`: ce dossier contient les fichiers
temporaires de l'application. Le processus web doit pouvoir y accéder en écriture. 
Ce chemin peut être modifié via la propriété [CApplication::runtimePath].

   - `WebRoot/protected/extensions`: ce dossier contient les extensions tierces. 
Ce chemin peut être modifié via la propriété [CApplication::extensionPath].

   - `WebRoot/protected/modules`: ce dossier contient tous les 
[modules](/doc/guide/basics.module) de l'application, chacun étant dans un 
sous dossier.

   - `WebRoot/protected/controllers`: ce dossier contient tous les contrôleurs. 
Ce chemin peut être modifié via la propriété [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: ce dossier contient toutes les vues,
incluant les vues contrôleur, système et layout. Ce chemin peut être 
modifié via la propriété [CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: ce dossier contient toutes les 
vues spécifiques à une contrôleur. Dans le cas présent, `ControllerID` 
correspond à l'ID du contrôleur. Ce chemin peut être modifié via 
la propriété [CController::getViewPath].

   - `WebRoot/protected/views/layouts`: ce dossier contient tous les 
layouts. Ce chemin peut être modifié via la propriété [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: ce dossier contient toutes les
vues systèmes. Les vues systèmes sont des gabarits permettant l'affichage
des exceptions et des erreurs. Ce chemin peut être modifié via 
la propriété [CWebApplication::systemViewPath].

   - `WebRoot/assets`: ce dossier contient les assets publiés. Un asset est un fichier
privé qui peut être publié et donc rendu accessible à l'utilisateur web. 
Le processus web doit pouvoir y accéder en écriture. Ce chemin peut être modifié via
la propriété [CAssetManager::basePath].

   - `WebRoot/themes`: ce dossier contient les divers thèmes qui peuvent être
utilisés par l'application. Chaque sous dossier correspond à un et un seul thème dont
le nom est le nom du dossier. Ce chemin peut être modifié via 
la propriété [CThemeManager::basePath].

Base de données
---------------

La plupart des applications Web utilisent une base de données. En guise de bonne pratique,
nous proposons les conventions de nommage suivantes (qui ne sont pas nécessaire au bon
fonctionnement de Yii):

   - Les noms de tables et de colonnes doivent être en minuscules.

   - Les mots au sein d'un nom doivent être séparés par des underscores (ex: `product_order`).

   - Les noms de tables peuvent être au singulier ou au pluriel, mais pas les deux.
Pour simplifier, nous recommandons d'utiliser des noms singuliers.

   - Les noms des tables peuvent être préfixés d'une chaine commune telle que `tbl_`. Cela peut
être utile lorsque des tables de plusieurs applications distinctes doivent coexister au sein
de la même base de données.

<div class="revision">$Id: basics.convention.txt 1906 $</div>
