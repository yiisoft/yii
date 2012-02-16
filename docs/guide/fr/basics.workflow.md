Cycle de développement
======================

Après avoir décrit les fondamentaux de Yii, nous allons décrire le 
cycle de développement à mettre en oeuvre pour réaliser une application
utilisant Yii. Le cycle de développement ci-dessous assume que la
conception a déjà été réalisée.

   1. Créer le squelette de l'application. L'outil `yiic` décrit dans la
partie [Créer sa première applicationYii](/doc/guide/quickstart.first-app) 
peut être utilisé pour accélérer cette étape.

   2. Configurer [l'application](/doc/guide/basics.application). Il faut
adapter la configuration de l'application. Lors de cet étape, il est souvent
nécessaire d'écrire de nouveaux composants (e.g. le composant utilisateur)

   3. Créer les classes des [modèles](/doc/guide/basics.model) pour tous les
types de données à gérer. Ici encore, `yiic` peut être utilisé pour générer
les [active record](/doc/guide/database.ar) liés à chaque table.

   4. Créer un [contrôleur](/doc/guide/basics.controller) pour chaque
type de requête. Le découpage de l'application est étroitement lié à la conception.
En général, si un modèle doit être rendu accessible aux utilisateurs, le
contrôleur associé doit être créé. L'outil `yiic` peut aussi automatiser cette étape.

   5. Implémenter les [actions](/doc/guide/basics.controller#action) et les 
[vues](/doc/guide/basics.view) correspondantes. C'est ici que le travail
commence.

   6. Configurer les [filtres](/doc/guide/basics.controller#filter) d'action au sein
des contrôleurs.

   7. Créer les [thèmes](/doc/guide/topics.theming) si la fonctionnalité est 
nécessaire.

   8. Créer les traductions si [l'internationalisation](/doc/guide/topics.i18n) 
est nécessaire.

   9. Trouver les données et les vues qui peuvent être cachées et mettre en
oeuvre les technique de [caching](/doc/guide/caching.overview) adaptées.

   10. Finalisation [tuning](/doc/guide/topics.performance) et deploiement.

Pour chaque étape, il est recommandé de créer les tests unitaires associés.

<div class="revision">$Id: basics.workflow.txt 1034 2008-12-04 01:40:16Z qiang.xue $</div>