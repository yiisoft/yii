Travailler avec les formulaires
===============================

La récupération de données utilisateurs au travers de formulaires HTML
est l'une des tâche la plus fréquente en développement web. Au delà de
la création des formulaires, les développeurs doivent aussi les remplir
avec les données existantes ou les valeurs par défaut, valider la saisie
faite par les utilisateurs, afficher les messages d'erreurs appropriés
pour les entrées invalides, et sauver les entrées de manière persistante.
Yii simplifie grandement ce processus grâce à l'architecture MVC.

Voici les étapes classiques à suivre pour gérer les formulaires sous Yii:

   1. Créer la classe du modèle représentant les champs des données à collecter;
   1. Créer l'action dans le controlleur avec du code traitant la soumission du formulaire.
   1. Créer le formulaire correspondant à l'action du controlleur dans le script de vue.

Dans les prochaines sections, nous allons voir chacune de ces étapes en détail.

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>
