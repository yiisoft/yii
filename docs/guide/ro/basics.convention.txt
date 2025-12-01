Conventii
=========

Yii este in favoarea conventiilor si sustine mai putin configuratiile.
Doar urmand conventiile se pot crea aplicatii Yii sofisticate, fara a scrie sau
a gestiona configuratii complexe. Totusi, Yii poate fi customizat in aproape
orice aspect atunci cand configuratii noi sunt necesare.

Mai jos descriem conventiile care sunt recomandate pentru programarea in Yii.
Pentru convenienta, presupunem ca `WebRoot` este directorul in care este instalat
aplicatia Yii.

URL
---

Implicit, Yii recunoaste URL-uri cu urmatorul format:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

Variabila `r` (face parte din GET) se refera la
[ruta](/doc/guide/basics.controller#route), care este folosit de Yii pentru a
extrage controller-ul si action-ul. Daca `ActionID` nu este precizat, controller-ul
va lansa action-ul implicit (definit in [CController::defaultAction]); iar daca
`ControllerID` lipseste si el (sau variabla `r` lipseste cu totul), atunci aplicatia
va folosi controller-ul implicit (definit prin [CWebApplication::defaultController]).

Cu ajutorul clasei [CUrlManager], este posibila crearea si recunoastea mai multor
URL SEO-friendly, precum `http://hostname/ControllerID/ActionID.html`. Acest feature
este prezentat in detaliu in [Gestiunea URL](/doc/guide/topics.url).

Cod
---

Yii recomanda ca denumirea variabilelor, functiilor si tipurilor de clase sa se faca in
stil camel. Stilul camel inseamna capitalizarea fiecarui cuvant (prima litera a cuvantului este litera mare)
din interiorul numelui si alaturarea cuvintelor fara spatii intre ele.
Pentru a se face diferenta fata de numele de clase, numele de variabile si functii
ar trebui sa inceapa cu un cuvant complet in lower-case. (ex. `$basePath`,
`runController()`, `LinkPager`). Pentru variabilele private ale unei clase, este recomandat
sa punem un caracter underscore `_` in fata numelor (ex. `$_actionList`).

Conceptul de namespace a fost implementat o data cu versiunea PHP 5.3.0. Pentru ca versiunile
anterioare de PHP nu au implementat acest concept, recomandam denumirea claselor
intr-un fel unic pentru a evita conflictele de nume cu alte clase externe Yii.
that classes be named in some unique way to avoid name conflict with
Tot din acest motiv, toate clasele Yii au in fata litera "C".

In ce priveste controller-ele, regula speciala este ca numele lor trebuie sa
fie urmate de cuvantul `Controller`. De exemplu, clasa `PageController` va avea ID-ul `page`. 
Deci ID-ul controller-ului este numele clasei (cu prima litera mica), din care se indeparteaza apoi cuvantul`Controller`.
Aceasta regula face aplicatia mai sigura din punctul de vedere al securitatii. De asemenea, URL-urile sunt mai compacte
(ex. `/index.php?r=page/index` in loc de `/index.php?r=PageController/index`).

Configuratie
------------

O configuratie este de fapt un array cu perechi key-value. Fiecare key reprezinta
numele proprietatii obiectului configurat. Fiecare value reprezinta valoarea initiala
a proprietatii respective. De exemplu, `array('name'=>'My
application', 'basePath'=>'./protected')` initializeaza proprietatile `name` si
`basePath` cu valorile initiale `My application`, respectiv `./protected`.

Orice proprietate cu drept de scriere din orice obiect poate fi configurata. Daca nu este configurata,
proprietatea va lua valoarea implicita. Cand configuram o proprietate, este
folositor sa citim documentatia ei, astfel in cat valoarea initiala sa fie valida.

Fisiere
-------

Conventia pentru numele de fisiere depinde de tipurile lor.

Fisierele claselor ar trebui sa fie denumite cu numele public al claselor respective.
De exemplu, clasa [CController] este in fisierul `CController.php`. O clasa publica
este o clasa care poate fi folosita de orice alta clasa. Fiecare fisier care contine o clasa
ar trebui sa contina cel mult o clasa publica. Clasele private (care sunt folosite doar de catre
o clasa publica) ar trebui sa existe in acelasi fisier in care exista clasa publica. 

Fisierele de tip view ar trebui sa aiba numele view-ului respectiv.
De exemplu, view-ul `view1` ar trebui sa fie in fisierul `view1.php`.
Un fisier view este un fisier PHP care contine cod PHP si HTML doar cu
scop de prezentare pentru client. 

Fisierele de configurare pot fi denumite in orice fel. Un fisier de configurare
este un fisier PHP al carui singur scop este sa returneze un array care contine configuratia.

Directoare
----------

Yii este structurat initial conform unui set implicit de directoare folosite pentru
diverse scopuri. Fiecare director poate fi customizat daca este nevoie. 

   - `WebRoot/protected`: acesta este [application base
directory](/doc/guide/basics.application#application-base-directory). Contine toate
fisierele PHP si fisierele de date care trebuie securizate fata de exterior. Poate fi
schimbat prin [CWebApplication::basePath]. Yii contine un alias implicit avand numele
`application` care este asociat cu aceasta cale. Accesul la acest director,
inclusiv orice subdirector, ar trebui sa fie interzis tuturor utilizatorilor Web. 

   - `WebRoot/protected/runtime`: acest director contine toate fisierele private temporare
generate in timpul rularii aplicatiei. Acest director poate fi schimbat prin [CApplication::runtimePath].
Acest director trebuie sa dea drepturi de scriere procesului serverului Web. 

   - `WebRoot/protected/extensions`: acest director contine toate extensiile third-party. 
Acest director poate fi schimbat prin [CApplication::extensionPath].

   - `WebRoot/protected/modules`: acest director contine toate
[modulele](/doc/guide/basics.module) aplicatiei, fiecare reprezentand un subdirector.

   - `WebRoot/protected/controllers`: acest director contine toate fisierele cu clasele
controller-elor. Acest director poate fi schimbat prin [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: acest director contine toate fisierele view,
inclusiv view-urile controller-elor, view-urile layout si view-urile sistem. Acest director poate fi schimbat prin
[CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: acest director contine fisierele view
pentru un singur controller (identificat prin ID-ul sau, `ControllerID`). Acest director
poate fi schimbat prin [CController::getViewPath].

   - `WebRoot/protected/views/layouts`: acest director contine toate fisierele view
care contin layout-uri. Acest director poate fi schimbat prin [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: acest director contine toate fisierele view ale sistemului.
View-urile sistemului sunt template-uri folosite pentru afisarea exceptiilor si erorilor.
Acest director poate fi schimbat prin [CWebApplication::systemViewPath].

   - `WebRoot/assets`: acest director contine fisiere asset publicate. Un fisier asset
este un fisier privat care poate fi publicat pentru a deveni accesibil utilizatorilor Web. Acest
director trebuie sa dea drept de scriere procesului serverului Web. Acest director poate fi schimbat prin [CAssetManager::basePath].

   - `WebRoot/themes`: acest director contine diverse teme in care poate fi prezentata
aplicatia Yii. Fiecare subdirector reprezinta o singura tema al carui nume este numele
subdirectorului respectiv. Acest director poate fi schimbat prin [CThemeManager::basePath].

<div class="revision">$Id: basics.convention.txt 749 2009-02-26 02:11:31Z qiang.xue $</div>