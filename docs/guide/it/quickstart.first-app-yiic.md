Generazione di Codice usando gli Strumenti di riga di comando (sconsigliato)
=========================================

> Nota: Il generatore di codice da shell `yiic` è sconsigliato già dalla versione 1.1.2. Si consiglia invece di utilizzare il generatore di codice "Web-based" più potente e flessibile che è disponibile qui: [Gii](/doc/guide/topics.gii).

Aprire una finestra a riga di comando ed eseguire i comandi elencati di seguito,

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.1
Please type 'help' for help. Type 'exit' to quit.
>> model User tbl_user
   generate models/User.php
   generate fixtures/tbl_user.php
   generate unit/UserTest.php

The following model classes are successfully generated:
    User

If you have a 'db' database connection, you can test these models now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   generate UserTest.php
   mkdir D:/testdrive/protected/views/user
   generate create.php
   generate update.php
   generate index.php
   generate view.php
   generate admin.php
   generate _form.php
   generate _view.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

Qui sopra è stato usato il comando da shell `yiic` per interagire con lo schema 
dell'applicazione.
Al prompt sono stati lanciati due comandi: `model User tbl_user` 
e `crud User`. Il primo genera una classe model chiamata `User` per la tabella 
`tbl_user`, mentre il secondo analizza il model `User` e genera il codice 
implementando le corrispondenti operazioni CRUD. 

> Nota: Potresti riscontrare errori tipo "...could not find driver", sebbene
> il controllo requisiti riporti che hai abilitato il PDO
> e il driver PDO corrispondente. Se ciò accade, puoi provare
> a lanciare lo strumento `yiic` come segue,
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> dove `path/to/php.ini` rappresenta il file ini di PHP corrente.

Si può vedere il risultato del nostro lavoro visitando l'URL:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Verrà mostrata una lista di utenti della tabella`tbl_user`.

Clicca sul pulsante `Create User` nella pagina. Se non ti sei già loggato in 
precedenza sarai indirizzato alla pagina di login. Dopo il login, potrai vedere 
un form che ti permetterà di aggiungere un nuovo utente. Compila il form e 
clicca sul pulsante `Create`. Se c'è un errore di input, ti sarà mostrato un 
messaggio di errore che serve a prevenire il salvataggio. Tornando all'elenco 
utenti registrati, dovresti vedere l'utente appena aggiunto alla lista.

Ripeti i passi sopra esposti per aggiungere più utenti. Nota che la pagina della 
lista utenti verrà automaticamente suddivisa in più pagine se ci sono troppi 
utenti da visualizzare in una pagina sola.

Se ci autentichiamo come amministratori utilizzando le credenziali 
`admin/admin`, possiamo vedere la pagina dell'utente admin con il seguente 
URL: 

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

In questo modo verrà visualizzato l'elenco degli utenti in formato tabulare 
elegante. Si può cliccare sull'intestazione della cella per ordinare la 
corrispondente colonna. Si può cliccare sui bottoni di ciascuna riga dati per 
visualizzare, aggiornare o cancellare la riga dati corrispondente.
Si possono sfogliare pagine diverse. Si può anche filtrare e cercare i dati a 
cui si è interessati.

Tutte queste caratteristiche simpatiche sono disponibili senza che sia 
necessario scrivere una sola riga di codice!

![User admin page](first-app6.png)

![Create new user page](first-app7.png)

<div class="revision">$Id: quickstart.first-app-yiic.txt 2098 2010-05-05 19:49:51Z qiang.xue $</div>