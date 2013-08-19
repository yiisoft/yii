Créer sa première applicatin Yii
================================

Pour se faire la main sur Yii, nous allons décrire dans cette section comment créér
notre première application avec le framework Yii. Nous allons utiliser le puissant outil
`yiic` qui peut automatiser la création du code pour certaines taches. Par convention,
nous supposons que `YiiRoot` est le répertoire où Yii est installé, and `WebRoot` est
la racine Web du serveur.

Lancer `yiic` sur la ligne de commande comme suit :

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Note: Sur Mac OS, Linux ou Unix, il sera peut être nécessaire de
> modifier les permissions sur le fichier `yiic` pour le rendre exécutable.
> Il est aussi possible de lancer l'outil comme suit :
>
> ~~~
> % cd WebRoot/testdrive
> % php YiiRoot/framework/yiic.php webapp WebRoot/testdrive
> ~~~

Cette commande va créer un squelette d'application Yii sous le répertoire
`WebRoot/testdvive`. L'application a une structure de répertoire qui
est nécessaire à la plupart des applications `Yii`

Sans écrire une seule ligne de code, nous pouvons tester notre première application
Uii, en accédant à l'url suivante dans le navigateur :

~~~
http://hostname/testdrive/index.php
~~~

Comme nous pouvons le constater, l'application a trois pages: la page d'accueil,
la page de contact et la page de login. La page d'accueil nous montre des informations
sur l'application, ainsi que sur l'état de l'utilisateur connecté. La page de contact
affiche un formulaire de contact que les utilisateurs peuvent remplir, et la page de
login permet aux utilisateurs de d'authentifier avant d'accéder à un contenu privé.
Les copies d'écran suivantes nous montrent plus de details.


![Page d'accueil](first-app1.png)

![Page Contact](first-app2.png)

![Page de contact avec des erreurs de saisie](first-app3.png)

![Page de contact envoyée avec succès](first-app4.png)

![Page de login](first-app5.png)


Le schéma suivant montre la structure des répertoires de notre applications.
Veuillez consulter la page [Conventions](/doc/guide/basics.convention#directory) pour
de plus amples informations à propos de cette structure.

~~~
testdrive/
   index.php                 Point d'entrée de l'application.
   assets/                   Contient les fichiers de ressources publiés
   css/                      Contient les fichiers CSS de l'application
   images/                   Contient les images
   themes/                   Contient les différents thèmes de l'application
   protected/                Contient les fichiers protégés de l'application
      yiic                   L'outil en ligne de commande 'yiic'
      yiic.bat               L'outil en ligne de commande 'yiic' pour Windows
      commands/              Contient les commandes personnalisées pour l'outil 'yiic'
         shell/              Contient les commandes personnalisées pour l'outil 'yiic shell'
      components/            Contient les composants utilisateur
         MainMenu.php        Le widget 'MainMenu'
         Identity.php        La class 'Identity' utilisée pour l'authentification
         views/              Contient les fichiers 'Vue' des widgets
            mainMenu.php     Le fichier Vue pour le widget 'MainMenu"
      config/                Contient les fichiers de configuration
         console.php         Le fichier de configuration pour l'application en ligne de commande
         main.php            Le fichier de confiruration pour l'application Web
      controllers/           Contient les fichiers des Controlleurs
         SiteController.php  Le controlleur par défaut
      extensions/            Contient les extensions tierces
      messages/              Contient les messages traduits
      models/                Contient les fichiers des Modèles
         LoginForm.php       Le modèle de type Formulaire pour l'action 'Login'
         ContactForm.php     Le modèle de type Formulaire pour l'action 'Contact'
      runtime/               Contient les fichiers temporaires générés par l'application
      views/                 Contient les fichiers Vue des controlleurs et Layout
         layouts/            Contient les fichiers Vue des Layout
            main.php         Le 'layout' par défaut pour toutes les vues
         site/               Contient les fichiers Vue pour le controlleur 'site'
            contact.php      La vue pour l'action 'Contact'
            index.php        La vue pour l'action 'index'
            login.php        La vue pour l'action 'login'
         system/             Contient les fichiers pour les vues system
~~~

Connexions aux bases de données
-------------------------------

La plupart des applications web sont gérées par une base de données.
Notre application de test n'est pas une exception. Pour utiliser une base de
donnée, nous devons auparavant signaler à l'application comment se connecter à
celle-ci. Pour cela, il faut modifier le fichier de configuration de l'application
`WebRoot/testdrive/protected/config/main.php` comme ceci :

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/source.db',
		),
	),
	......
);
~~~

Dans cet exemple, nous ajoutons une entrée nommée `db` aux `composants`, qui
permet à l'application de se conencter à la base de donnée SQLlite
`WebRoot/testdrive/protected/data/source.db` quand celà est nécessaire.

> Note|Note : Pour utiliser les fonctionnalités de base de données de Yii, il
> est nécessaire d'activer l'extension PDO dans PHP, ainsi que les différents drivers
> PDO spécifiques à chaque base de donnée. Pour notre application de test, nous
> avons besoin que les extensions `php_pdo` et `php_pdo_sqlite` soient activées.

Pour finir, nous devons préparer une base de donnée SQLite. En utilisant les outils
d'administration SQLite, nous pouvons créer une base de donnée avec le modèle suivant :

~~~
[sql]
CREATE TABLE User (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

> Note: Si vous utilisez MySQL, vous devez remplacer `AUTOINCREMENT`
> par `AUTO_INCREMENT` dans le code SQL ci-dessus.

Par souci de simplification, nous créeons seulement une table `User` dans notre
base. Le fichier de base de donnée SQLite est sauvé sous le nom

`WebRoot/testdrive/protected/data/source.db`. Notez que le nom de fichier ainsi
que le répertoire qui le contient doivent être accessibles en écriture par l'utilisateur
exécutant le serveur web.


Implémentations des opérations CRUD
-----------------------------------

Maintenant, la partie amusante. Nous aimerions implémenter les opérations CRUD
(Create, Read, Update and Delete, soit Création, Lecture, Modification et Suppression)
sur la table `User` que nous venons de créer. C'est une opération nécessaire la
plupart du temps.

Plutot que d'écrire du code, nous allons encore nous servir du puissant outil `yiic`,
afin de générer automatiquement le code. Ce processus est aussi connu sous le nom de
*scaffolding*. Ouvrez une fenêtre de ligne de commande, et exécutez les commandes suivantes :

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.0
Please type 'help' for help. Type 'exit' to quit.
>> model User
   generate User.php

The 'User' class has been successfully created in the following file:
    D:\wwwroot\testdrive\protected\models\User.php

If you have a 'db' database connection, you can test it now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   mkdir D:/wwwroot/testdrive/protected/views/user
   generate create.php
   generate update.php
   generate list.php
   generate show.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

Ci-dessus, nous utilisons la commande `yiic shell` pour interagir avec notre
application. Au prompt, nous exécutons 2 sous-commandes : `model User`, et `crud User`.
La première genère une class "Model" pour la table `User`, et la seconde lit ce modèle
pour générer le code qui implémente les traitements CRUD.

> Note|Note : Si vous rencontrez des erreurs comme "...could not find driver", alors
> que le script de test des pré-requis signale que PDO et les drivers associés sont
> bien activés, vous pouvez essayer de lancer l'outil `yiic` de la manière suivante :
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> où `path/to/php.ini` représente le chemin d'accès au fichier php.ini

Jetons maintenant un oeil à notre application par cette URL :

~~~
http://hostname/testdrive/index.php?r=user
~~~

Cela va afficher la liste des entrées dans la table `User`. Etant donné que
notre table est vide, rien n'apparaîtra pour le moment.

Cliquez sur le lien `New User` sur la page. Si nous n'étions pas encore connecté,
nous allons être redirigés vers la page de login. Après s'être connecté, un formulaire
contenant tous les champs permettant la création d'un utilisateur sera affiché.
Completez le formulaire, et cliquez sur le bouton `Create`. Si une erreur de saisie
s'est glissée dans le formulaire, un message d'erreur sera affiché, nous empêchant
alors de sauvegarer l'entrée. En revenant à la liste des utilisateurs, nous devrions
alors voir le nouvel utilisateur que nous avons créé.

Répetez ces étapes pour ajouter plus d'utilisateur. Notez que la liste d'utilisateur
sera automatiquement paginée s'il y a trop d'utilisateur à afficher sur une seule
page.

Si nous nous connectons en tant qu'administrateur en utilisant `admin/admin`, nous
pouvons visiter l'a page d'administration à l'URL suivante :

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Cette page affiche une jolie table des utilisateurs. Il est possible de cliquer
sur les entêtes de la table afin de la trier selon la colonne correspondante. Et,
de la même manière que la liste précédente, cette table sera paginée si le nombre
d'entrées est trop important.

Toutes ces fonctionnalités ont été réalisées sans écrire une seule ligne de code !

![Page d'administration des utilisateurs](first-app6.png)

![Page de création d'un nouvel utilisateur](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 1264 $</div>