Instalare
=========

Instalarea platformei Yii implica in mare urmatorii doi pasi:

   1. Descarcam platforma Yii de pe site-ul [yiiframework.com](http://www.yiiframework.com/).
   2. Dezarhivam fisierul editiei descarcate de Yii intr-un director accesibil pe Web.

> Tip|Sfat: Platforma Yii nu trebuie neaparat instalata intr-un director accesibil de pe Web.
O aplicatie Yii are un fisier de intrare care este de obicei singurul fisier care
trebuie sa fie expus utilizatorilor Web. Celelalte scripturi PHP, inclusiv cele
ale platformei Yii, ar trebui sa fie protejate fata de accesul din Web din moment ce
ar putea fi accesate si modificate de catre persoane neautorizate.

Cerinte
-------

Dupa instalarea Yii, ar trebui sa verificam daca serverul nostru
indeplineste toate cerintele pentru folosirea corespunzatoare a platformei Yii.
Putem face acest lucru prin scriptul de verificare cerinte al carui URL este
urmatorul:

~~~
http://hostname/path/to/yii/requirements/index.php
~~~

Cerinta minima este ca serverul Web sa suporte versiunea PHP 5.1.0
(sau o versiune mai recenta). Platforma Yii a fost testata
cu [serverul HTTP Apache](http://httpd.apache.org/) in Windows si Linux.
Poate rula de asemenea si pe alte servere Web atat timp cat este pus la
dispozitie PHP 5.

<div class="revision">$Id: quickstart.installation.txt 359 2008-12-14 19:50:41Z qiang.xue $</div>