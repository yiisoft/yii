Lavorare con i Form
=================

La raccolta dei dati degli utenti tramite form HTML è uno dei principali compiti 
nello sviluppo di web application. Oltre a progettare i form, gli sviluppatori devono
riempire il form con dati esistenti o valori di default, validare l'input dell'utente,
visualizzare appropriati messaggi di errore per input non valido, e salvare l'input 
in un archivio permanente. Yii semplifica enormemente questo flusso di lavoro 
grazie alla sua architettura MVC.

Ecco qui di seguito i tipici passi che sono necessari quando si ha che fare con i form in Yii:

   1. Creare una classe model rappresentante i dati che si vogliono raccogliere;
   1. Creare un action del controller contenente solo codice necessario per rispondere all'invio di dati mediante form.
   1. Creare un form nello script della view associata all'action del controller.

Nelle prossime sottosezioni, sono descritte nel dettaglio ciascuno di questi passi.

<div class="revision">$Id: form.overview.txt 163 2008-11-05 12:51:48Z weizhuo $</div>