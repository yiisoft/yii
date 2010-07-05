Kodgenerering med kommandoradsverktyg (under utfasning)
=======================================================

> Note|Märk: Kodgeneratorerna i `yiic shell` fasas ut från och med version 1.1.2.
Istället rekommenderas de mer kapabla och utbyggbara webbaserade kodgeneratorer 
som finns tillgängliga i [Gii](/doc/guide/topics.gii).

Öppna ett kommandoradsfönster och kör nedanstående kommandon,

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

Ovan används `shell`-kommandot i `yiic` till att interagera med mallapplikationen. 
Vid kommandoprompten kör vi två underkommandon: `model User tbl_user` och `crud User`. 
Den förra skapar en modellklass `User` för tabellen `tbl_user`, medan den senare  
analyserar `User`-modellen och genererar koden som implementerar motsvarande CRUD-operationer.

> Note|Märk: Det kan hända att felmeddelanden i stil med "...could not find driver"
> uppstår, även om systemkravskontrollen visar att PDO och motsvarande databasdrivrutin 
> redan är aktiva. Om detta inträffar kan man försöka med att köra `yiic`-verktyget på 
> följande sätt,
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> där `path/to/php.ini` representerar den rätta ini-filen för PHP.

Resultatet kan nu beskådas genom inmatning av URL:en:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Detta kommer att presentera en lista med poster från tabellen `tbl_user`.

Klicka på knappen `Create User` på sidan. Inloggningssidan kommer att visas (såvida 
vi inte loggat in tidigare). Efter inloggningen presenteras ett 
inmatningsformulär där en ny user-post kan läggas till. Fyll i formuläret och 
klicka på knappen `Create`. Om det förekommer något inmatningsfel kommer en 
trevlig felmeddelanderuta visas, vilken förhindrar att felaktig inmatning 
sparas. Tillbaka i listsidan skall den nyligen tillagda user-posten dyka upp i listan.

Upprepa ovanstående för att lägga till fler användare. Lägg märke till att 
listsidan automatiskt kommer att paginera user-posterna om de är för många för 
att visas på en sida.

Genom inloggning som administratör med `admin/admin`, kan user:s administrationssida visas via följande URL:

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Detta presenterar user-posterna i ett trevligt tabulärt format. Sorteringskolumn 
kan väljas genom klick på respektive kolumnrubrik. Genom klick på knapparna i varje 
rad kan vi visa i formulär, uppdatera eller ta bort den motsvarande raden med data.
Vi kan översiktligt se olika sidor samt filtrera och söka efter data av intresse.

Allt detta uppnåddes utan att skriva en enda rad kod!

![User-administreringssida](first-app6.png)

![Skapa ny user-sida](first-app7.png)


<div class="revision">$Id: quickstart.first-app-yiic.txt 2098 2010-05-05 19:49:51Z qiang.xue $</div>