Instalacja
==========

Instalacja Yii w zasadzie składa się z następujących dwóch kroków:

   1. Ściągnięcia frameworku Yii ze strony [yiiframework.com](http://www.yiiframework.com/).
   2. Rozpakowania plików Yii do katalogu dostępnego poprzez sieć.

> Tip|Wskazówka: Yii nie musi być instalowany w katalogu dostępnym sieciowo.
Aplikacja Yii posiada skrypt rozruchowy, który jest właściwie jedynym plikiem
jaki musisz udostępnić użytkownikom internetu. Inne skrypty PHP, włączając
w to skrypty frameworku Yii, nie powinny być dostępne z poziomu internetu;
w przeciwnym przypadku mogą one być narażone na exploity.

Wymagania
---------

Tuż po zainstalowaniu Yii możesz chcieć sprawdzić czy twój serwer spełnia 
wymagania stawiane przez Yii. Możesz zrobić to uruchamiając w swojej
przeglądarce skrypt weryfikujący te wymagania:

~~~
http://hostname/ścieżka/do/yii/requirements/index.php
~~~

Yii wymaga PHP w wersji 5.1, dlatego też na serwerze powininno być zainstalowane i dostępne
PHP w wersji 5.1 lub wyższej.
Yii został przetestowany na [serwerze HTTP Apache](http://httpd.apache.org/) w systemach Windows i Linux. 
Może również zostać uruchomiony na innych platformach i serwerach wspierających PHP 5.

<div class="revision">$Id: quickstart.installation.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>