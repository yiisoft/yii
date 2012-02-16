Model-View-Controller (MVC)
===========================

Yii implementa il design pattern [schema di progettazione] model-view-controller 
(MVC), che è largamente adottato nella programmazione Web. L'obiettivo di 
MVC è quello di separare la business logic [logica di funzionamento] dalle 
considerazioni relative all'interfaccia utente, cosicché gli sviluppatori 
possono modificare ciascuna parte più facilmente senza influenzare le altre.
Nel MVC il model rappresenta le informazioni (i dati) e la business logic; la 
view contiene elementi dell'interfaccia utente come testi, form di inserimento 
dati; e il controller gestisce le comunicazioni tra model e view.

Yii, oltre ad implementare MVC, introduce un front-controller, chiamato 
`Application` il quale incapsula il contesto di esecuzione per il processo di 
una richiesta. Application raccoglie alcune informazioni sulla richiesta 
dell'utente e poi le smista al controller appropriato per ulteriori 
manipolazioni.

Il seguente diagramma mostra la struttura statica di un'applicazione Yii:

![Struttura statica di un'applicazione Yii](structure.png)


Workflow tipico
------------------
Il seguente diagramma mostra il workflow [flusso di lavoro] tipico di 
un'applicazione Yii quando essa gestisce una richiesta utente:


![Workflow tipico di un'applicazione Yii](flow.png)

   1. Un utente esegue una richiesta all'URL `http://www.example.com/index.php?r=post/show&id=1` 
ed il web server gestisce la richiesta eseguendo lo script di avvio `index.php`.
   2. Lo script di avvio crea un'istanza dell'[Applicazione](/doc/guide/basics.application) 
e la esegue.
   3. L'Application riceve le informazioni dettagliate della richiesta 
dell'utente da un [component dell'applicazione](/doc/guide/basics.application#application-component) 
che si chiama `request`.
   4. L'Application determina sia il [controller](/doc/guide/basics.controller) 
che l'[action](/doc/guide/basics.controller#action) richiesti grazie all'aiuto 
dell'application component che si chiama `urlManager`. In questo esempio, il 
controller è `post`, il quale si riferisce alla classe `PostController`; e 
l'action è `show`, il cui significato attuale è determinato dal controller.
   5. L'applicazione crea una istanza del controller richiesto per gestire 
ulteriormente la richiesta utente. Il controller determina che l'action `show` 
si riferisce al metodo, all'interno della classe del controller, che si chiama 
`actionShow`. Il metodo crea ed esegue i filtri (es. controllo accessi, benchmark) 
associati con questa action. L'action viene eseguita se ciò è permesso dai filtri.
   6. L'action, tramite il [model](/doc/guide/basics.model), legge dal database 
il `Post` il cui ID è `1`.
   7. L'action produce una [view](/doc/guide/basics.view) chiamata `show` con i 
prodotti dal model `Post`.
   8. La view legge e visualizza gli attributi del model `Post`.
   9. La view esegue alcuni [widget](/doc/guide/basics.view#widget).
   10. La produzione della view viene incorporata in un [layout](/doc/guide/basics.view#layout).
   11. L'action completa la produzione della view e ne visualizza il risultato all'utente.

<div class="revision">$Id: basics.mvc.txt 3321 2011-06-26 12:54:22Z mdomba $</div>