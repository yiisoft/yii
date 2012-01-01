Lavorare con i database
=====================

Yii fornisce un supporto potente per la programmazione con i database.

Costruito sull'estensione del PHP Data Objects (PDO), il Data Access Object (DAO) di Yii 
consente l'accesso a diversi sistemi di gestione database (DBMS) con un'unica 
interfaccia uniforme. Le applicazioni sviluppate utilizzando Yii DAO possono essere facilmente
modificate per utilizzare un diverso DBMS senza dover modificare la parte di
codice inerente l'accesso al database.

Il Yii Query Builder (costruttore di query di Yii) offre un metodo object-oriented per la costruzione di
query SQL, che aiuta a ridurre i rischi di attacchi per SQL injection.

L'Active Record di Yii (AR), implementato con l'approccio Object-Relational 
Mapping (ORM) ormai ampiamente adottato, semplifica ulteriormente la programmazione con i database.
Con la rappresentazione della tabella come se fosse una classe ed un record come se fosse una sua istanza, l'AR di Yii elimina il
compito ripetitivo di scrivere quelle istruzioni SQL riguardanti prevalentemente le operazioni CRUD
(create, read, update and delete - creare, leggere, aggiornare e cancellare).


Anche se le funzionalità incluse in Yii sono in grado di soddisfare quasi tutti i
compiti inerenti il database, è comunque possibile continuare ad utilizzare le proprie librerie per database nella propria
applicazione Yii. È un dato di fatto, il framework Yii è stato progettato accuratamente per essere
utilizzato insieme ad altre librerie esterne.

<div class="revision">$Id: database.overview.txt 2666 2010-11-17 19:56:48Z qiang.xue $</div>