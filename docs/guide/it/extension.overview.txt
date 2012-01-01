Estendere Yii
=============

Estendere Yii è una attività comune nella fase di sviluppo (di una web aplication). Per esempio, quando
si scrive un nuovo controller, si sta estendendo Yii ereditando dalla sua classe [CController]; 
quando si scrive un nuovo widget, si sta estendendo [CWidget] o un'altra classe 
widget esistente. Se il codice esteso è progettato per essere riutilizzato 
da altri sviluppatori, possiamo definirlo una *extension* (estensione).

Solitamente una extension *serve per un unico scopo*. Dal punto di vista di Yii, 
una extension può essere classificata nei seguenti modi:

 * [componente dell'applicazione](/doc/guide/basics.application#application-component)
 * [behavior](/doc/guide/basics.component#component-behavior)
 * [widget](/doc/guide/basics.view#widget)
 * [controller](/doc/guide/basics.controller)
 * [action](/doc/guide/basics.controller#action)
 * [filter](/doc/guide/basics.controller#filter)
 * [applicazione da riga di comando](/doc/guide/topics.console)
 * validator: un validatore è una classe component che estende [CValidator].
 * helper: un'helper è una classe con un unico metodo statico. È simile ad una funzione globale che utilizza il nome della classe come suo namespace.
 * [modulo](/doc/guide/basics.module): un modulo è un'unità software a sé stante costituito da [model](/doc/guide/basics.model), [view](/doc/guide/basics.view), [controller](/doc/guide/basics.controller) ed altri componenti di supporto. In molti aspetti, un modulo somiglia ad un'[applicazione](/doc/guide/basics.application). La differenza principale è che il modulo è contenuto nell'applicazione. Per esempio, potremmo avere un modulo che si occupi della gestione degli utenti.

Un'estensione potrebbe anche essere un componente che non rientri in nessuna delle 
precedenti categorie. È un dato di fatto, Yii è stato attentamente progettato 
in modo tale che quasi ogni pezzo del suo codice possa essere esteso e 
personalizzato per adattarsi ad esigenze specifiche.

<div class="revision">$Id: extension.overview.txt 2739 2010-12-14 01:50:04Z weizhuo $</div>