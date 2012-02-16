Telepítés
=========

A Yii telepítése a következő két lépésből áll:

   1. A Yii keretrendszer letöltése a [yiiframework.com](http://www.yiiframework.com/)-ról.
   2. A letöltött fájl kicsomagolása egy webről elérhető könyvtárba.

> Tip|Tipp: Nem feltétlenül szükséges a Yii-t egy webről eléhető könyvtárba
telepíteni. Minden Yii alkalmazásnak van egy belépési parancsfájlja és gyakorlatilag
ez az egyetlen fájl, amit elérhetővé kell tenni web felhasználók számára. A többi
PHP parancsfájl, beleértve a Yii-hez tartozókat, jobb, ha nem elérhető a webről, mivel
támadás célpontjává válhatnak.

Követelmények
-------------

A Yii telepítése után célszerű megbizonyosodni arról, hogy webszerverünk teljesít
minden követelményt, ami a Yii használatához szükséges. Ez könnyen megtehető a
követelmény-ellenőrző parancsfájl böngészőn keresztüli futtatásával, mely a
következő URL-en érhető el:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

A Yii által támasztott alapkövetelmény egy webszerver, mely rendelkezik PHP 5.1.0
(vagy későbbi) támogatással. A Yii tesztelve lett [Apache HTTP szerver](http://httpd.apache.org/)-rel
Windows és Linux operációs rendszereken. Minden bizonnyal más webszervereken is
futtatható, feltéve, hogy rendelkezik PHP 5 támogatással.

<div class="revision">$Id: quickstart.installation.txt 359 2008-12-14 19:50:41Z qiang.xue $</div>