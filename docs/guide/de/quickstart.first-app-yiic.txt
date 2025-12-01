Codegenerierung an der Kommandozeile (veraltet)
====================================

> Note|Hinweis: Seit Version 1.1.2 wird die Codegenerierung mit `yiic shell`
> nicht mehr empfohlen. Verwenden Sie stattdessen den leistungsfähigeren 
> erweiterbaren Codegenerator in [Gii](/doc/guide/topics.gii).

Öffnen Sie ein Befehlsfenster und geben Sie die folgenden Befehle ein:

~~~
% cd WebVerzeichnis/testdrive
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

Mit obigem `yiic shell`-Kommando gelangt man an eine Eingabeaufforderung, 
um Befehle an die Anwendung zu senden.  Mit dem Unterbefehl 
`model User tbl_user` wird zunächst die Modelklasse für die
`tbl_user`-Tabelle erzeugt. `crud User` analysiert dann das eben
erzeugte Model `User` und generiert anschließend den Code für die CRUD-Operationen.


> Note|Hinweis: Falls Ihnen Fehler wie "...could not find driver" 
> ("...konnte Treiber nicht finden") begegnen, obwohl Ihr Server
> alle Voraussetzungen bzgl. PDO erfüllt,  können Sie versuchen, 
> `yiic` wie folgt aufzurufen:
>
> ~~~
> % php -c pfad/zu/php.ini protected/yiic.php shell
> ~~~
> 
> wobei `pfad/zu/php.ini` auf die richtige INI-Datei verweist.

Sie können nun die Früchte Ihrer Arbeit genießen, indem sie folgende URL
aufrufen:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Sie sehen eine Liste aller Benutzer in der Tabelle `tbl_user`.

Klicken Sie auf den Link `Benutzer anlegen`. Falls Sie noch nicht angemeldet
sind, werden Sie zur Anmeldeseite weitergeleitet. Nach der
Anmeldung erscheint ein Eingabeformular, mit dem Sie einen neuen
Benutzer hinzufügen können. Füllen Sie das Formular aus, und klicken Sie
unten auf den Button `Erstellen`. Bei Eingabefehlern erhalten Sie einen
freundlichen Hinweis und der Datensatz wird nicht gespeichert.
Nach dem erfolgreichen Speichern sollte der neu angelegte Benutzer in der
Benutzerliste erscheinen.

Wiederholen Sie die obigen Schritte, und fügen Sie weitere Benutzer hinzu.
Vielleicht haben Sie schon bemerkt, dass automatisch eine
Seitenblätterung (engl.: pagination) angezeigt wird, sobald zu viele Einträge
für eine Seite vorhanden sind.

Wenn Sie sich nun mit `admin/admin` als Administrator anmelden, können Sie
hier Admin-Seite aufrufen:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Sie sehen eine übersichtliche Tabelle aller Benutzer. Zum Ändern der
Sortierung können Sie auf einen Spaltentitel klicken. Und auch hier wird eine
Seitenblätterung angezeigt, sobald einige Einträge vorhanden sind.

All dies haben Sie erreicht, ohne dafür eine einzige Codezeile
schreiben zu müssen!

![Administrationsseite für Benutzer](first-app6.png)

![Seite zum Erstellen eines neuen Benutzers](first-app7.png)

<div class="revision">$Id: quickstart.first-app-yiic.txt 2098 2010-05-05 19:49:51Z qiang.xue $</div>
