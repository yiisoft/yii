Lucrul cu baze de date
======================

Yii pune la dispozitie un suport puternic pentru programarea cu bazele de date.
Fiind creat pe baza extensiei PHP Data Objects (PDO), Yii DAO (Data Access Objects) asigura
accesul la diverse DBMS (sisteme de gestiune de baze de date) intr-o singura interfata.
Aplicatiile craete folosind Yii DAO nu trebuie modificate atunci cand se doreste
schimbarea sistemului DBMS. Mai mult, Yii AR (Active Record), o abordare ORM foarte cunoscuta,
simplifica si mai mult programarea cu bazele de date. Prin reprezentarea unei tabele ca fiind
o clasa, si prin reprezentarea unui rand din aceasta tabela ca fiind o instanta a clasei,
Yii AR elimina task-ul repetitiv de a scrie acele instructiuni SQL care in majoritatea cazurilor
sunt doar instructiuni CRUD (create, read, update si delete). 

Desi Yii DAO si Yii AR se pot descurca in aproape toate situatiile, putem totusi
folosi propriile noastre biblioteci de acces la baze de date. De fapt, platforma Yii
este proiectata foarte atent pentru a fi folosita usor cu biblioteci externe third-party.

<div class="revision">$Id: database.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>