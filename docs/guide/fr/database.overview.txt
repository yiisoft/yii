Travailler avec les bases de données
====================================

La gestion des bases de données dans Yii est très complète et performante. Basé sur l'extension PHP Data Objects (PDO), Yii Data Access Objects (DAO) permet d'accéder à différents systèmes de gestion de base de données (SGBD) au travers d'une seule interface uniforme. Les applications développées avec Yii DAO sont utilisables immédiatement sous d'autres SGBD, sans modification des sources. Yii Active Record (AR), implémenté selon l'approche bien connue Object-Relational Mapping (ORM), simplifie grandement les accès à la base de donnée. En représentant les tables par des classes et les lignes par des instances, Yii AR élimine la tache répétitive d'écriture des requêtes SQL pour les opérations CRUD (create, read, update et delete).

Bien que Yii DAO et AR soient capable de manipuler la quasi totalité des tâches relatives à la base de donnée, vous avez toujours la possibilité d'utiliser votre propre librairie de base de données dans votre application Yii. En effet, le framework Yii est conçu pour etre utilisé en parallèle avec d'autres librairies tierces.

<div class="revision">$Id: database.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>
