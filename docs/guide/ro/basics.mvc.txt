Model-View-Controller (MVC)
===========================

MVC este un concept foarte raspandit in programarea Web.
Scopul MVC este de a tine separate logica business-ului si interfata utilizator, astfel
incat cei care intretin aplicatia sa schimbe mult mai usor o parte, fara a afecta
alte parti. In MVC, modelul contine informatiile (datele) si regulile business;
view contine elemente din interfata utilizator (texte, input-uri ale formularelor etc);
controller-ul genstioneaza comunicatia dintre model si view.

In afara de MVC, Yii introduce un front-controller, cu numele application,
care reprezinta contextul in care se executa procesarea cererii client. Application
rezolva cererea utilizator si o trimite mai departe controller-ului corespunzator care
va trata efectiv cererea.

urmatoarea diagrama arata structura statica a unei aplicatii Yii:

![Structura statica a aplicatiei Yii](structure.png)


Fluxul tipic
------------
Urmatoarea diagrama arata fluxul tipic de lucru al unei aplicatii Yii atunci cand trateaza
o cerere client:

![Fluxul tipic al aplicatiei Yii](flow.png)

   1. Un utilizator face o cerere prin URL-ul `http://www.example.com/index.php?r=post/show&id=1`,
iar serverul Web trateaza cererea prin executarea fisierul bootstrap `index.php`.
   2. Fisierul `index.php` creaza o instanta [application](/doc/guide/basics.application) si o ruleaza. 
   3. Aplicatia obtine informatii detaliate despre cererea utilizatorului de la o
the detailed user request information from
[componenta a aplicatiei](/doc/guide/basics.application#application-component) cu numele `request`.
   4. Aplicatia determina [controller-ul](/doc/guide/basics.controller)
si [action-ul](/doc/guide/basics.controller#action) cu ajutorul componentei `urlManager`.
In acest exemplu, controller-ul este `post` si se refera la clasa `PostController`;
action-ul este `show`, iar semnificatia numelui este determinata de controller-ul in cauza.
   5. Aplicatia creaza o instanta a controller-ului necesar pentru a trata mai departe cererea.
Controller-ul intelege ca `show` se refera la metoda cu numele `actionShow` din clasa controller-ului.
Apoi, aplicatia creaza si executa filtrele (ex. controlul accesului, benchmarking, etc) asociate
cu aceast action. Action-ul este executat daca este permis de catre filtre.
   6. Action-ul citeste din baza de date un [model](/doc/guide/basics.model) `Post` al carui ID este `1`.
   7. Action-ul genereaza un [view](/doc/guide/basics.view) cu numele `show` si cu modelul `Post`.
   8. View-ul citeste si afiseaza atributele modelului `Post`.
   9. View-ul executa cateva [widget-uri](/doc/guide/basics.view#widget).
   10. Rezultatul generat de view este inclus intr-un [layout](/doc/guide/basics.view#layout).
   11. Action-ul termina generarea view-ului si afiseaza rezultatul utilizatorului.


<div class="revision">$Id: basics.mvc.txt 419 2008-12-28 05:35:39Z qiang.xue $</div>