Flusso di sviluppo
====================

Dopo aver descritto i concetti fondamentali di Yii, adesso vediamo il flusso 
tipico per lo sviluppo di una web application utilizzando Yii. Il flusso presume 
che si sia già fatta l'analisi dei requisiti come anche la necessaria analisi di 
progettazione dell'applicazione.

   1. Creare la struttura delle cartelle. Lo strumento `yiic` descritto in 
[Crea la tua prima applicazione con Yii](/doc/guide/quickstart.first-app) può 
essere utilizzato per velocizzare questo passaggio.

   2. Configurare l'[applicazione](/doc/guide/basics.application). Ciò è ottenuto 
modificando il file di configurazione dell'applicazione. Questa fase potrebbe anche 
richiedere la scrittura di alcuni component dell'applicazione (es. il component utente).

   3. Creare una classe [model](/doc/guide/basics.model) per ogni tipo di dato che deve 
essere gestito. Lo strumento `Gii` descritto in 
[Crea la tua prima applicazione con Yii](/doc/guide/quickstart.first-app#implementing-crud-operations)
ed in [Generazione automatica del codice](/doc/guide/topics.gii) può essere usata per 
generare automaticamente le classi per l'[active record](/doc/guide/database.ar) per ogni 
tabella interessata del database.

   4. Creare una classe [controller](/doc/guide/basics.controller) per ogni tipo 
di richiesta dell'utente. Il modo in cui classificare le richieste dell'utente 
dipende dalle esigenze reali. In generale, se una classe model deve essere accessibile 
dall'utente, dovrebbe disporre della corrispondente classe controller. Lo strumento 
`Gii` può automatizzare pure questo passaggio.

   5. Implementare le [action](/doc/guide/basics.controller#action) e le loro 
corrispondenti [view](/doc/guide/basics.view). Questo è ciò che in realtà 
si deve fare.

   6. Configurare, nelle classi controller, i [filter](/doc/guide/basics.controller#filter) 
delle action necessari.

   7. Creare i [temi](/doc/guide/topics.theming) se la funzionalità dei 
temi è necessaria.

   8. Creare la traduzione dei messaggi se è necessaria 
l'[internazionalizzazione](/doc/guide/topics.i18n).

   9. Individuare i dati e le view che possono essere messe nella cache ed applicare 
le tecniche di [caching](/doc/guide/caching.overview) appropriate.

   10. Per completare [mettere a punto](/doc/guide/topics.performance) e quindi mettere 
in produzione.

Per ciascuno dei passi sopra indicati, potrebbe essere necessario creare ed eseguire codice di test.

<div class="revision">$Id: basics.workflow.txt 2718 2010-12-07 15:17:04Z qiang.xue $</div>