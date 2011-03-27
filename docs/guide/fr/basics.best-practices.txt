Bonnes Pratiques MVC 
====================

Bien que l'architecture Modèle-Vue-Contrôleur (MVC) soit connu par la quasi totalité des dévelopeur web, de nombreuses personnes s'interrogent encore sur la meilleure facon de l'appliquer lors du développement d'une application. Les idées centrales derrière MVC sont **la réutilisation et le séparation logique du code**. Nous allons voir dans cette section les règles générales à suivre afin de respecter au mieux l'architecture MVC lors de développement d'une application Yii.

Afin de mieux expliquer ces règles, on partira sur le principe qu'une application web est constituée de différentes sous applications tels que:

* front end: le site web auquel les utilisateurs ont accès;
* back end: le site permettant d'administrer l'application. Habituellement doté d'un accès restreint;
* console: une application constituée de commandes à lancer depuis un terminal ou au travers d´une tache planifiée;
* Web API: fourni des interfaces pour les applications tièrces qui souhaitent s'intégrer avec l'application.

Les sous-applications pourront être implémentées en tant que [modules](/doc/guide/basics.module), ou comme une application Yii partageant une partie du code avec d'autre sous-applications.


Modèle
------

Un [Modèle](/doc/guide/basics.model) représente la structure à la base de l'application Web. Les modèles sont souvent partagés
entre les différentes sous-applications de l'application Web. Par exemple, un modèle `LoginForm` pourra aussi bien être utilisé par
le front end que le back end d'une application; un modèle `News` pourra être utilisé au travers de la ligne de commande, d'APIs,
et le front/back end d'une application. Ainsi les modèles:

* doivent contenir les propriétés représentant les données spécifiques;

* doivent contenir la logique applicative (par exemple les règles de validation) afin d'assurer que les données affichées sont bien conformes avec les spécifications;

* peuvent contenir du code pour manipuler des données. Par exemple, un modèle `SearchForm`, au delà d'afficher les données issue d'une recherche, peut contenir une méthode `search` qui implémente la recherche effective.

Parfois, suivre cette dernière règle abouti à un modèle très lourd, contenant trop de code pour une unique classe. Cela peut aussi rendre
le modèle difficile à maintenir si le code est utilisé dans différents buts. Par example, un modèle `News` peut contenir une méthode
appelée `getLatestNews` qui sera utilisée seulement par le front end; il pourrait aussi contenir une méthode appelée `getDeletedNews`
qui ne serait utilisée que par le back end. Cela ne pose pas de problème pour une petite ou moyenne application. Pour les applications plus importantes, la technique suivante pourra être utilisée pour rendre les modèles plus maintenables:

* Définir un modèle `NewsBase` qui contiendra seulement le code partagé par les différentes sous-applications (front end, back end);

* Dans chaque sous-application, définir un modèle `News` qui étendra `NewsBase`, et placer tout le code spécifique à la sous-application dans ce modèle `News`.

Ainsi, si l'on veut utiliser cette technique, on ajoute un modèle `News` dans l'application front end contenant seulement la méthode
`getLatestNews`, et on ajoute un autre modèle `News` dans l'application back end, qui lui ne contiendrait que la méthode `getDeletedNews`.

En général, les modèles ne doivent pas contenir la logique gérant l'interaction avec les utilisateurs. De manière plus spécifiques, les modèles:

* ne doivent pas utiliser `$_GET`, `$_POST`, ou d'autres variables similaires liées étroitement à la requête. Il ne faut pas oublier qu'un modèle peut être utilisé par une sous-application complètement différente (par exemple des tests unitaires, une API) qui n'utilise pas ces variables pour représenter les requêtes utilisateurs. Ces variables provenant de la requête utilisateur doivent être gérées par le contrôleur.

* doivent éviter l'utilisation de code HTML ou d'autre code d'affichage direct. En effet la méthode d'affichage dépend en fonction de l'utilisateur final (par exemple un front end et un back end peuvent afficher le détail d'une news d'une façon complètement différente), et il est préférable de laisser le soin de l'affichage aux vues.


Vue
---

Les [vues](/doc/guide/basics.view) sont en charge de la représentation des modèles dans le format souhaité par l'utilisateur. En général, les vues:

* doivent principalement contenir du code d'affichage, tel que du HTML, et du code PHP simple permettant de parcourir, formatter et afficher des données;

* doivent éviter d'exécuter des requêtes sur la BD. Ce type de code se trouve normalement dans les modèles.

* doivent éviter l'accès direct à `$_GET`, `$_POST`, ou d'autres variables similaires représentant la requête de l'utilisateur. C'est le rôle du
contrôleur. La vue doit être centrée sur de l'affichage et sur la structure des données fournies par le contrôleur et/ou le modèle, et non pas
accéder directement aux données de la requête ou de la base de donnée.

* peuvent accéder à des propriétés et méthodes des contrôleurs et modèles directement. Cependant, ces accès ne doivent être fait que dans un but d'affichage.


Les vues peuvent être réutilisées de différent moyens:

* Layout (Structure): les zone d'affichage communes (par exemple en-tete et pied de page) peuvent être décrites dans les vues layout.

* Vues partielles: les vues partielles (vues n'utilisant pas les layouts) sont utiles pour réutiliser des fragments de code d'affichage. Par exemple, on peut utiliser la vue partielle `_form.php` pour afficher les champs d'entrées d'un formulaire qui sera utilisé aussi bien dans les pages de création du modèle que dans celles de mise à jour.

* Widgets: dans les cas ou beaucoup de logique est nécessaire pour afficher une vue partielle, cette vue peut être transformée en widget dont sa classe sera la plus à meme de recevoir cette logique. Pour les widgets qui génèrent beaucoup de code HTML, il est préférable d'utiliser des fichiers spécifiques à ce widget pour cette vue et contenant ce code HTML.

* Classes Helper: il est souvent utile dans les vues de recourir à du code très court spécifique à de petites taches telles que le formatage de données ou la génération de tags HTML. Plutot que de placer ce code directement dans les fichiers de vues, une meilleur approche est de le mettre dans une classe helper. Ensuite, il vous suffit d'utiliser ces classes dans vos fichiers de vues. Yii fournit un exemple pour cette approche. Yii a une classe helper [CHtml] tres puissante pouvant produire du code HTML souvant utilisé. Les classes Helper peuvent être déposées dans le [répertoire de chargement automatique](/doc/guide/basics.namespace) afin qu'elles soient disponibles sans les inclure explicitement.


Contrôleur
----------

Un [contrôleur](/doc/guide/basics.controller) est la pièce maitresse reliant les modèles, vues et autres composants afin de former une application complète. Les contrôleurs sont en charge de la gestion directe des requêtes utilisateurs. Ainsi, les contrôleurs:

* peuvent accéder a `$_GET`, `$_POST` et autres variables PHP représentant les requêtes utilisateurs;

* peuvent instancier des modèles et gérer leur cycle de vie. Par exemple, dans une action typique de mise à jour d'un modèle, un contrôleur pourra tout d'abord créer une instance du modèle; puis remplir le modèle avec les entrées utilisateurs à partir de `$_POST`; et après avoir enregistré le modèle, le contrôleur pourra rediriger le navigateur de l'utilisateur vers la page affichant les détails du modèle. A noter que dans cette implémentation l'enregistrement du modèle doit se trouver dans le modèle plutot que dans le contrôleur.

* doivent éviter de contenir des requêtes SQL, dont leur place réside dans les modèles.

* doivent éviter de contenir du code HTML ou tout autre code d'affichage. Ce code est normalement situé dans les vues.


Dans une application MVC bien conçue, les contrôleurs sont souvent très léger, contenant seulement quelques douzaines de lignes de code; alors que les modèles sont plus importants en taille, contenant la plupart du code responsable pour afficher et manipuler les données. En effet cela est du au fait que les structures de données et la logique applicative représentés par les modèles sont en général très spécifiques à une application particulière, et ont besoin d'être grandement adaptés pour répondre aux besoins de l'application; alors que la logique du contrôleur suit souvent des patterns similaires entre applications et peut ainsi être simplifiée par le framework sous-jacent ou les classes de base.


<div class="revision">$Id: basics.best-practices.txt 2795 $</div>
