Script de démarrage
===================

Le script de démarrage est le script PHP qui permet de gérer les requêtes 
utilisateur. C'est le seul script que l'utilisateur final peut voir et utiliser.

Dans la plupart des cas, le script de démarrage d'une application Yii se
résume au code PHP suivant,

~~~
[php]
// commenter la ligne suivante pour passe ne mode Production
defined('YII_DEBUG') or define('YII_DEBUG',true);
// inclusion du fichier d'initialisation de Yii
require_once('chemin/vers/yii/framework/yii.php');
// inclusion du fichier de configuration de 
// l'application
$configFile='chemin/vers/config/file.php';
// création de l'instance de l'application
// et exécution
Yii::createWebApplication($configFile)->run();
~~~

Le script inclus en tout premier lieu le fichier `yii.php` pour initialiser 
le framework puis créé l'instance de l'application web en fonction du
fichier de configuration et enfin exécute l'application.

Mode de debuggage
-----------------

Une application Yii peut fonctionner en mode de débuggage ou en mode production.
Le mode de fonctionnement de l'application est fixé par la valeur de la contante
`YII_DEBUG`. Cette contante (booléen) est définie à `false` par défaut (mode 
production). Pour basculer l'application en mode de debbugage, il suffit
d'assigner la valeur `true` à la constante `YII_DEBUG` avant d'inclure le script
d'initialisation du frameword `yii.php`.
Exécuter une application en mode de debuggage est bien évidemment moins performant
car un maximum d'informations (logs internes) sont générés et conservés. En contre
partie, la richesse et la diversité de ces informations est particulièrement
utile pendant les phases de développement pour identifier et corriger les 
erreurs qui surviennent.

<div class="revision">$Id: basics.entry.txt 1850 $</div>
