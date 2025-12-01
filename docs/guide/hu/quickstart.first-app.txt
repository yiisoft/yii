Az első Yii alkalmazás létrehozása
==================================

Hogy tapasztalatot szerezzünk a Yii-vel, ebben a részben bemutatjuk, hogyan
hozhatjuk létre első Yii alkalmazásunkat. A hathatós `yiic` eszközt fogjuk
használni, ami alkalmas bizonyos kód létrehozási feladatok automatizálásara.
Kényelem szempontjából feltételezzük, hogy `YiiRoot` a könyvtár, ahova a Yii
telepítve lett, valamint `WebRoot` a webszerverünk 'document root'-ja.

Futtassuk a `yiic`-t parancssorból a következők szerint:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Note|Figyelem: A `yiic` futtatásakor Mac OS, Linux vagy Unix rendszereken
> szükség lehet a `yiic` fájl jogosultságainak módosítására, hogy futtatható legyen.
> Alternatívaként az eszköz a következőképpen is futtatható:
>
> ~~~
> % cd WebRoot/testdrive
> % php YiiRoot/framework/yiic.php webapp WebRoot/testdrive
> ~~~

Ezáltal létrehoztunk egy Yii alkalmazás-vázat a `WebRoot/testdrive` könyvtárban.
Az alkalmazás könyvtárstruktúrája megegyezik a legtöbb Yii alkalmazás számára
szükségessel.

Egyetlen sor kód írása nélkül, már ki is próbálhatjuk első Yii alkalmazásunkat,
ha böngészőnkkel a következő URL-re navigálunk:

~~~
http://hostname/testdrive/index.php
~~~

Mint láthatjuk, az alkalmazás három lapból áll: a főoldal, egy kapcsolat lap, és
a belépési oldal. A főoldal néhány, az alkalamazással valamint a felhasználó
bejelentkezési állapotával kapcsolatos, alap információval szolgál, a kapcsolati
lapon egy űrlapot találunk, amit kitöltve a felhasználók elküdhetik
számunkra kérdéseiket/kéréseiket, a bejelentkezési lapon pedig lehetőség van
a felhasználók azonosítására, mielőtt engedélyeznénk a hozzáférést érzékeny
tartalmakhoz.
További részletekért következzék néhány képernyőkép:

![Főoldal](first-app1.png)

![Kapcsolati lap](first-app2.png)

![Bejelentkezési lap, beviteli hibákkal](first-app3.png)

![Bejelentkezési lap sikeres belépés után](first-app4.png)

![Bejelentkezési lap](first-app5.png)


A következő ábra bemutatja az alkalmazás könyvtárstruktúráját. Részletes magyarázat
a struktúra miértjéről a [Konvenciók](/doc/guide/basics.convention#directory) részben.

~~~
testdrive/
   index.php                 a webalkalmazás belépési parancsfájlja
   assets/                   közzétett forrásfájlokat tartalmaz
   css/                      CSS fájlokat tartalmaz
   images/                   képfájlokat tartalmaz
   themes/                   alkalmazás témákat tartalmaz
   protected/                az alkalamazás védett fájljait tartalmazza
      yiic                   yiic parancssori szkript
      yiic.bat               yiic parancssori szkript Windows-hoz
      commands/              egyéni 'yiic' parancsok tárolására
         shell/              egyéni 'yiic shell' parancsok tárolására
      components/            újrahasznosítható felhasználói komponensek
         MainMenu.php        a 'MainMenu' widget osztály
         Identity.php        az azonosításra használt 'Identity' osztály
         views/              a widget-ek nézet fájljainak tárolására
            mainMenu.php     nézet fájl a 'MainMenu' widget-hez
      config/                konfigurációs fájlok tárolására
         console.php         a parancssori alkalmazás beállításai
         main.php            a webalkalmazás beállításai
      controllers/           kontroller osztályok tárolására
         SiteController.php  alapértelmezett kontroller osztály
      extensions/            harmadik féltől származó kiterjesztések
      messages/              lefordított üzenetek tárolására
      models/                modell osztályok tárolására
         LoginForm.php       űrlap modell a 'login' tevékenységhez
         ContactForm.php     űrlap modell a 'contact' tevékenységhez
      runtime/               ideiglenes fájlok tárolására
      views/                 kontrollerek nézet és elrendezés fájljainak tárolására
         layouts/            elrendezés fájlok tárolására
            main.php         az alapértelmezett elrendezés a nézetekhez
         site/               a 'site' kontrollerhez tartozó nézet fájlok
            contact.php      a 'contact' tevékenységehez tartozó nézet
            index.php        az 'index' tevékenységhez tartozó nézet
            login.php        a 'login' tevékenységhez tartozó nézet
         system/             rendszerszintű nézet fájlok tárolására
~~~

Kapcsolódás adatbázishoz
------------------------

A legtöbb webalkalmazás adatbázissal támogatott. A mi próbaalkalamzásunk sem
kivétel ez alól. Egy adatbázis használatához mindenekelőtt tudatnunk kell az
alkalmazással, hogy hogyan csatlakozzon hozzá. Ezt elérhetjük az alkalmazás
beállításfájljának `WebRoot/testdrive/protected/config/main.php` módosításával,
az alábbiak szerint:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/source.db',
		),
	),
	......
);
~~~

A fentiekben hozzáadunk egy `db` elemet a `components` tömbhöz, ami utasítja az
alklamazást, hogy szükség esetén csatlakozzon a `WebRoot/testdrive/protected/data/source.db`
SQLite adatbázishoz.

> Note|Figyelem: A Yii adatbázis-képessé tételéhez engedélyeznünk kell a PHP PDO
kiterjesztését, valamint a meghajtó specifikus PDO kiterjesztést. A próbaalkalmazáshoz
szükségünk van a `php_pdo` és a `php_pdo_sqlite` kiterjesztések engedélyezésére.

Végül fel kell készítenünk az SQLite adatbázisunkat, hogy a fenti beállítás
használható legyen. Egy SQLite kezelő eszköz használatával készíthetünk
egy adatbázist a következő séma alapján:

~~~
[sql]
CREATE TABLE User (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

> Note|Figyelem: Ha MySQL adatbázist használunk, a fenti SQL-ben le kell cserélnünk
> az `AUTOINCREMENT` kifejezést `AUTO_INCREMENT`-re. 

Az egyszerűség kedvéért csak egy, `User` nevű táblát hozunk létre az adatbázisban.
Az SQLite adatbázis a `WebRoot/testdrive/protected/data/source.db` helyen van elmentve.
Megjegyzendő, hogy mind a fájlnak, mind pedig az azt tartalmazó könyvtárnak
írhatónak kell lennie a webszerver folyamata által az SQLite követelményei szerint.


CRUD műveletek megvalósítása
----------------------------

Térjünk a lényegre! Szeretnénk megvalósítani a CRUD (létrehozás, olvasás, frissítés
és törlés) műveleteket az éppen létrehozott `User` táblához. Ez is egy általánosan
szükséges rész a gyakorlati alkalmazásokban.

Ahelyett, hogy magunk bajlódnánk a kód megírásával, ismét a hathatós `yiic`
eszközhöz folyamodunk, hogy automatikusan létrehozza számunkra a szükséges kódot.
Ez a folymat *scaffolding*-ként (állványozás) is ismert. Indítsunk egy parancssori
ablakot és hajtsuk végre a következőkben felsorolt parancsokat:

~~~
% cd WebRoot/testdrive
% protected/yiic shell
Yii Interactive Tool v1.0
Please type 'help' for help. Type 'exit' to quit.
>> model User
   generate User.php

The 'User' class has been successfully created in the following file:
    D:\wwwroot\testdrive\protected\models\User.php

If you have a 'db' database connection, you can test it now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   generate create.php
      mkdir D:/wwwroot/testdrive/protected/views/user
   generate update.php
   generate list.php
   generate show.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

A fentiekben a `yiic shell` parancsot használjuk az alkalmazás vázunk módosításához.
A parancssorban két alparancsot hajtunk végre: `model User` és `crud User`.
Az előbbi létrehoz egy modell osztályt a `User` táblához, míg az utóbbi megvizsgálja
a `User` modellt és létrehozza a CRUD műveletekhez szükséges kódokat.

> Note|Figyelem: Belefuthatunk "...could not find driver"-hez hasonló hibákba,
> holott a követelmény ellnőrző szerint a PDO és a megfelelő PDO meghajtó engedélyezve
> van. Ha netán ez történne, megpróbálhatjuk a `yiic` eszköz következők szerinti
> futtatását:
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> aholis a `path/to/php.ini` a valós PHP ini fájlra vonatkozik.

És most, felkeresve a következő URL-t, gyönyörködjünk az eddigi munkánkban:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Ez kilistázza a felhasználó bejegyzéseket a `User` táblában. Mivel a táblánk még
nem tartalmaz rekordokat, jelenleg üres lesz a lista.

Kattintsunk a `New User` hivatkozásra a lapon. A bejelntekezési lapon találjuk
magunkat, hacsak már ezt megelőzően be nem jelentkeztünk. Bejlentkezés után
egy űrlappal találjuk szembe magunkat, aminek segítségével új felhasználót
rögzíthetünk. Töltsük ki az űrlapot és kattintsunk a `Create` gombra.
Ha hibát vétettünk az űrlap kitöltésekor, egy kis hibaüzenet lesz az eredmény,
megakadályozandó, hogy elmentsük a megadott adatokat. Sikeres mentés után a
listán megjelenik a frissen hozzáadott felhasználó.

Ismételjük meg a fenti lépéseket további felhasználók hozzáadásához. Vegyük
észre, hogy a lista automatikusan lapozhatóvá válik, ha egy lapra túl sok
felhasználó bejegyzés kerülne.

Ha adminisztrátorként lépünk be az `admin/admin` felhasználónév/jelszó párossal,
megtekinthetjük a felhasználó-kezelési lapot a következő URL-en:

~~~
http://hostname/proba/index.php?r=user/admin
~~~

Ez a felhasználó bejegyzések egy kis táblázatát mutatja. A táblázat fejléceire
kattintva az adott oszlop szerint rendezhetjük a listát. Valamint csakúgy, mint
a felhasználó lista oldal, az adminisztrációs oldal is automatikusan lapozható,
ha túl sok felhasználó jutna egy lapra.

Ez a sok remek képesség mind anélkül jött létre, hogy egy sor kódot is
írtunk volna!

![Felhasználó-kezelési oldal](first-app6.png)

![Új felhasználó létrehozása](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 891 2009-03-25 15:20:56Z qiang.xue $</div>