Folosirea bibliotecilor 3rd-Party
=================================

Yii este proiectat foarte atent pentru a permite integrarea usoara a bibliotecilor
third-party, pentru a extinde functionalitatea Yii.
Atunci cand folosim biblioteci third-party intr-un proiect, intampinam de obicei probleme
in legatura cu includerea fisierelor si denumirea claselor.
Deoarece toate clasele Yii sunt prefixate cu litera `C`, este putin probabil
sa apara vreun conflict de denumire; si pentru ca Yii se bazeaza pe
[SPL autoload](http://us3.php.net/manual/en/function.spl-autoload.php)
pentru a executa includerea fisierelor claselor, se poate descurca usor cu alte biblioteci
daca si ele folosesc acelasi feature de autoloading sau PHP include path pentru a include
fisierele claselor.


Mai jos folosim un exemplu pentru a arata cum sa folosim componenta
[Zend_Search_Lucene](http://www.zendframework.com/manual/en/zend.search.lucene.html)
din [platforma Zend](http://www.zendframework.com) in interiorul unei aplicatii Yii.

Mai intai, vom extrage fisierul platformei Zend intr-un director sub
`protected/vendors`, presupunand ca `protected` este [directorul de baza al aplicatiei](/doc/guide/basics.application#application-base-directory).
Verificam daca exista fisierul `protected/vendors/Zend/Search/Lucene.php`.

Apoi, la inceputul fisierului cu clasa controller-ului, inseram urmatoarele linii:

~~~
[php]
Yii::import('application.vendors.*');
require_once('Zend/Search/Lucene.php');
~~~

Codul de mai sus include fisierul `Lucene.php`. Deoarece folosim o cale relativa,
trebuie sa modificam calea PHP include path pentru ca acest fisier sa fie localizat corespunzator.
Acest lucru il facem prin apelarea `Yii::import` inainte de `require_once`.

O data ce toate de mai sus sunt pregatite, putem folosi clasa `Lucene`
intr-un action al unui controller in felul urmator:

~~~
[php]
$lucene=new Zend_Search_Lucene($pathOfIndex);
$hits=$lucene->find(strtolower($keyword));
~~~


<div class="revision">$Id: extension.integration.txt 251 2008-11-19 22:28:46Z qiang.xue $</div>