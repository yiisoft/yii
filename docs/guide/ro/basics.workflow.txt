Fluxul de dezvoltare
====================

In acest moment avem definite conceptele fundamentale ale Yii. Acum putem arata
fluxul obisnuit de dezvoltare atunci cand se creaza o aplicatie web cu ajutorul Yii.
Fluxul presupune ca am facut analiza cerintelor aplicatiei si designul ei.

   1. Cream scheletul structurii de directoare. Pentru a accelera acest pas,
poate fi folosita unealta `yiic` descrisa in [Crearea primei aplicatii Yii](/doc/guide/quickstart.first-app).

   2. Configuram [application](/doc/guide/basics.application). Acest pas este facut
prin modificarea fisierul de configurare al aplicatiei. Acest pas poate necesita
scrierea unor componente (ex. componenta user).

   3. Cream o clasa [model](/doc/guide/basics.model) pentru fiecare tip de date
care trebuie administrate. Din nou, `yiic` poate fi folosit pentru a genera automat
clasa [active record](/doc/guide/database.ar) pentru fiecare tabela de interes din baza de date.

   4. Cream o clasa [controller](/doc/guide/basics.controller) pentru fiecare tip de cerere
utilizator. In general, daca o clasa model trebui sa fie accesata de catre utilizatori,
ar trebui sa aiba o clasa controller corespunzatoare. Unealta `yiic` poate de asemenea sa faca
acest pas automat.

   5. Implementam [action-uri](/doc/guide/basics.controller#action) si
[view-urile](/doc/guide/basics.view) lor corespunzatoare. Acest pas implica
din partea noastra programare cu adevarat.

   6. Configuram [filtre](/doc/guide/basics.controller#filter) necesare de action-uri in clasele controller.

   7. Cream [teme](/doc/guide/topics.theming) daca este necesar.

   8. Cream mesaje traduse daca este necesara [internationalizarea](/doc/guide/topics.i18n).

   9. Identificam date si view-uri care pot fi introduse in cache si aplicam tehnici de
[caching](/doc/guide/caching.overview).

   10. In final, facem [optimizari](/doc/guide/topics.performance) si apoi facem publica aplicatie.

Pentru fiecare din pasii de mai sus, pot fi create si executate test cases (cazuri de test).

<div class="revision">$Id: basics.workflow.txt 323 2008-12-04 01:40:16Z qiang.xue $</div>