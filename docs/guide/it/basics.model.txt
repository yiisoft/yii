Model
=====

Un model è un'istanza di [CModel] o una classe che estende [CModel]. I model sono 
utilizzati per mantenere i dati e le loro regole di business.

Un model rappresenta un singolo oggetto di dati. Potrebbe essere una riga in una 
tabella del database o un form html con campi di input utente. Ogni campo dell'oggetto dati
è rappresentato da un attributo del model. L'attributo è un'etichetta e può
essere convalidato da un insieme di regole.

Yii implementa due generi di model: I model form e gli active record. Entrambi 
si estedono dalla stessa classe base [CModel].

Un model form è un'istanza di [CFormModel]. I model form sono utilizzati per memorizzare
i dati raccolti dall'input dell'utente. Questo tipo di dati spesso sono raccolti, utilizzati e
poi scartati. Per esempio, su una pagina di login, possiamo usare un model form per
rappresentare le informazioni di username e password che sono forniti da un utente 
finale. Per maggiori informazioni, si prega di fare riferimento a 
[Lavorare con i Form](/doc/guide/form.overview).

Active Record (AR) è un design pattern utilizzato per astrarre l'accesso al database 
in stile orientato agli oggetti. Ogni oggetto AR è un'istanza di
[CActiveRecord] o di una sottoclasse di quella classe, che rappresenta una singola riga in una
tabella del database. I campi nella riga sono rappresentate come proprietà 
dell'oggetto AR. Dettagli sull'AR possono essere trovati in [Active Record](/doc/guide/database.ar).

<div class="revision">$Id: basics.model.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>